<?php

namespace App\Imports;

use App\Repositories\FormField\FormFieldsInterface;
use App\Repositories\SessionYear\SessionYearInterface;
use App\Repositories\Student\StudentInterface;
use App\Repositories\Subscription\SubscriptionInterface;
use App\Repositories\User\UserInterface;
use App\Rules\TrimmedEnum;
use App\Services\CachingService;
use App\Services\ResponseService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JsonException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Str;
use Throwable;
use TypeError;

class StudentsImport implements WithMultipleSheets
{
    private mixed $classSectionID;
    private mixed $sessionYearID;
    private mixed $is_send_notification;

    public function __construct($classSectionID, $sessionYearID, $is_send_notification)
    {
        $this->classSectionID = $classSectionID;
        $this->sessionYearID = $sessionYearID;
        $this->is_send_notification = $is_send_notification;
    }

    /**
     * @throws Throwable
     */
    public function sheets(): array
    {
        return [
            new FirstSheetImport($this->classSectionID, $this->sessionYearID, $this->is_send_notification)
        ];
    }
}

class FirstSheetImport implements ToCollection, WithHeadingRow
{
    private mixed $classSectionID;
    private mixed $sessionYearID;
    private mixed $is_send_notification;

    /**
     * @param $classSectionID
     * @param $sessionYearID
     * @param $is_send_notification
     */

    // Import the Class Section and Repositories
    public function __construct($classSectionID, $sessionYearID, $is_send_notification)
    {
        $this->classSectionID = $classSectionID;
        $this->sessionYearID = $sessionYearID;
        $this->is_send_notification = $is_send_notification;
    }

    /**
     * @throws JsonException
     * @throws Throwable
     */
    public function collection(Collection $collection)
    {
        $student = app(StudentInterface::class);
        $formFields = app(FormFieldsInterface::class);
        $sessionYear = app(SessionYearInterface::class);

        $subscription = app(SubscriptionInterface::class);
        $user = app(UserInterface::class);
        $cache = app(CachingService::class);

        $filteredCollection = $collection->filter(function ($row) {
            return !empty(trim($row['student_full_name'] ?? ''));
        });

        $validator = Validator::make($filteredCollection->toArray(), [
            '*.class_section_id'     => 'nullable|numeric',
            '*.admission_no'         => 'nullable|unique:users,email',
            '*.student_full_name'    => 'required',
            '*.gender'               => ['required', new TrimmedEnum(['male', 'female', 'm', 'f'])],
            '*.date_of_birth'        => 'nullable',
            '*.admission_date'       => 'nullable',
            '*.pen_no'               => 'required',
            '*.current_address'      => 'nullable',
            '*.permanent_address'    => 'nullable',
            '*.father_and_mother'    => 'nullable',
            '*.father_full_name'     => 'required',
            '*.father_phone_number'  => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/',
            '*.mother_full_name'     => 'required',
            '*.mother_phone_number'  => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/',
            '*.apply_class_fee'      => ['nullable', new TrimmedEnum(['Yes', 'No', 'yes', 'no', '1', '0'])],
            '*.apply_van_fee'        => ['nullable', new TrimmedEnum(['No Van', 'Van 1', 'Van 2', 'no van', 'van 1', 'van 2', '0', '1', '2'])],
            '*.previous_year_balance'=> 'nullable|numeric|min:0',
            '*.admission_amount_paid'=> 'nullable|numeric|min:0',
            '*.admission_fee'        => ['nullable', new TrimmedEnum(['Yes', 'No', 'yes', 'no', '1', '0'])],
            '*.paid_fees'            => 'nullable|numeric|min:0',
        ],[
            '*.class_section_id.numeric' => 'Class Section ID must be numeric.',
            '*.student_full_name.required' => 'Please enter the student full name.',
            '*.gender.required' => 'Please select the gender.',
            '*.pen_no.required' => 'Please enter the PEN Number.',
            '*.father_full_name.required' => 'Please enter the father full name.',
            '*.mother_full_name.required' => 'Please enter the mother full name.',
            '*.admission_amount_paid.numeric' => 'Please enter a valid numeric value for admission amount paid.',
            '*.paid_fees.numeric' => 'Please enter a valid numeric value for paid fees.',
        ]);

        //             If Validation fails then this will throw the ValidationFail Exception
        $validator->validate();

        // Check free trial package
        $today_date = Carbon::now()->format('Y-m-d');
        $get_subscription = $subscription->builder()->doesntHave('subscription_bill')->whereDate('start_date','<=',$today_date)->where('end_date','>=',$today_date)->whereHas('package',function($q){
            $q->where('is_trial',1);
        })->first();

        $userService = app(UserService::class);
        $sessionYear = $sessionYear->findById($this->sessionYearID);

        // Find maximum numeric admission number in this school/tenant to continue from
        $allAdmissionNos = $student->builder()->withTrashed()->pluck('admission_no');
        $maxAdmissionNo = 0;
        foreach ($allAdmissionNos as $noVal) {
            if (is_numeric($noVal)) {
                $maxAdmissionNo = max($maxAdmissionNo, intval($noVal));
            }
        }
        $nextAdmissionNo = $maxAdmissionNo + 1;

        DB::beginTransaction();
        foreach ($filteredCollection as $row) {

            // Check free trial package
            if ($get_subscription) {
                $systemSettings = $cache->getSystemSettings();
                $count_student = $user->builder()->role('Student')->withTrashed()->count();
                if ($count_student >= $systemSettings['student_limit']) {
                    $message = "The free trial allows only ".$systemSettings['student_limit']." students.";
                    ResponseService::errorResponse($message);
                    break;
                }
            }
            $row = is_array($row) ? $row : $row->toArray();

            $rowClassSectionID = $this->classSectionID;
            if (isset($row['class_section_id']) && trim($row['class_section_id']) !== '') {
                $rowClassSectionID = intval($row['class_section_id']);
            }
            if (empty($rowClassSectionID)) {
                throw new \Exception("Class Section ID is required. Please specify class_section_id in the Excel row or select Class Section on the page.");
            }

            // Determine guardian relation
            $rawRelation = strtolower(trim($row['father_and_mother'] ?? ''));
            if ($rawRelation === 'father & mother' || $rawRelation === 'father_mother') {
                $guardian_relation = 'father_mother';
            } elseif ($rawRelation === 'mother') {
                $guardian_relation = 'mother';
            } elseif ($rawRelation === 'father') {
                $guardian_relation = 'father';
            } else {
                $guardian_relation = 'father_mother';
            }

            // Determine primary guardian name and phone
            if ($guardian_relation === 'mother') {
                // Mother is primary guardian
                $gname_parts = explode(' ', trim($row['mother_full_name'] ?? ''), 2);
                $guardian_first_name = $gname_parts[0];
                $guardian_last_name = $gname_parts[1] ?? '';
                $guardian_mobile = $row['mother_phone_number'] ?? null;
                $guardian_gender = 'female';
                $mother_name = null;
                $mother_mobile = null;
            } elseif ($guardian_relation === 'father_mother') {
                // Father is primary guardian, mother details stored separately
                $gname_parts = explode(' ', trim($row['father_full_name'] ?? ''), 2);
                $guardian_first_name = $gname_parts[0];
                $guardian_last_name = $gname_parts[1] ?? '';
                $guardian_mobile = $row['father_phone_number'] ?? null;
                $guardian_gender = 'male';
                // Mother name (full name as single string for mother_name field)
                $mother_name = $row['mother_full_name'] ?? null;
                $mother_mobile = $row['mother_phone_number'] ?? null;
            } else {
                // Father is primary guardian
                $gname_parts = explode(' ', trim($row['father_full_name'] ?? ''), 2);
                $guardian_first_name = $gname_parts[0];
                $guardian_last_name = $gname_parts[1] ?? '';
                $guardian_mobile = $row['father_phone_number'] ?? null;
                $guardian_gender = 'male';
                $mother_name = null;
                $mother_mobile = null;
            }

            // Create or update Parent/Guardian
            $guardian = $userService->createOrUpdateParent(
                $guardian_first_name, 
                $guardian_last_name, 
                null, // no email from Excel
                $guardian_mobile, 
                $guardian_gender, 
                null, // no image
                null, // no reset_password
                $guardian_relation,
                null, // no aadhar_pic
                $mother_name,
                $mother_mobile,
                null, // no mother_image
                null  // no mother_aadhar_pic
            );
            
            $admission_no = null;
            if (isset($row['admission_no']) && trim($row['admission_no']) !== '') {
                $admission_no = trim($row['admission_no']);
            } else {
                $admission_no = $nextAdmissionNo++;
            }

            $dob = !empty($row['date_of_birth']) ? date('Y-m-d', strtotime($row['date_of_birth'])) : date('Y-m-d');
            $admission_date = !empty($row['admission_date']) ? date('Y-m-d', strtotime($row['admission_date'])) : date('Y-m-d');
            $current_address = !empty($row['current_address']) ? trim($row['current_address']) : 'N/A';
            $permanent_address = !empty($row['permanent_address']) ? trim($row['permanent_address']) : 'N/A';

            $previous_year_balance = 0.00;
            if (isset($row['previous_year_balance']) && is_numeric($row['previous_year_balance'])) {
                $previous_year_balance = floatval($row['previous_year_balance']);
            }
            
            // Parse gender to standard lowercase value
            $gender = strtolower(trim($row['gender'] ?? ''));
            if ($gender === 'm' || $gender === 'male') {
                $gender = 'male';
            } elseif ($gender === 'f' || $gender === 'female') {
                $gender = 'female';
            } else {
                $gender = 'male'; // fallback
            }

            // Split student full name
            $name_parts = explode(' ', trim($row['student_full_name'] ?? ''), 2);
            $student_first_name = $name_parts[0];
            $student_last_name = $name_parts[1] ?? '';

            // Parse apply_class_fee
            $apply_class_fee = 1; // Default to Yes/1
            if (isset($row['apply_class_fee'])) {
                $val = strtolower(trim($row['apply_class_fee']));
                if ($val === 'no' || $val === '0') {
                    $apply_class_fee = 0;
                }
            }

            // Parse apply_van_fee
            $apply_van_fee = 0; // Default to No Van/0
            if (isset($row['apply_van_fee'])) {
                $val = strtolower(trim($row['apply_van_fee']));
                if ($val === 'van 1' || $val === '1') {
                    $apply_van_fee = 1;
                } elseif ($val === 'van 2' || $val === '2') {
                    $apply_van_fee = 2;
                }
            }

            // Parse admission_fee (Yes/No for ₹ 2,000)
            $apply_admission_fee = 0;
            if (isset($row['admission_fee'])) {
                $val = strtolower(trim($row['admission_fee']));
                if ($val === 'yes' || $val === '1') {
                    $apply_admission_fee = 1;
                }
            }

            // Parse paid_fees / admission_amount_paid
            $paid_fees = 0;
            if (isset($row['paid_fees']) && is_numeric($row['paid_fees']) && $row['paid_fees'] > 0) {
                $paid_fees = floatval($row['paid_fees']);
            } elseif (isset($row['admission_amount_paid']) && is_numeric($row['admission_amount_paid']) && $row['admission_amount_paid'] > 0) {
                $paid_fees = floatval($row['admission_amount_paid']);
            }

            try {
                $createdUser = $userService->createStudentUser($student_first_name, $student_last_name, $admission_no, null, $dob, $gender, null, $rowClassSectionID, $admission_date, $current_address, $permanent_address, $sessionYear->id, $guardian->id, [], 1, $this->is_send_notification, $row['pen_no'] ?? null, null, $apply_class_fee, $apply_van_fee, $apply_admission_fee, null, $previous_year_balance);

                // Record fee payment during registration if paid_fees is provided
                $studentModel = $createdUser->student;
                if ($paid_fees > 0 && $studentModel) {
                    $classSection = DB::table('class_sections')->where('id', $studentModel->class_section_id)->first();
                    if ($classSection) {
                        $fees = \App\Models\Fee::where('class_id', $classSection->class_id)
                            ->where('session_year_id', $sessionYear->id)
                            ->with('fees_class_type.fees_type')
                            ->first();

                        if ($fees) {
                            $fees->filterForStudent($studentModel);
                            
                            // Add admission fee (₹ 2,000) to total if applied
                            $admissionFeeAmount = $apply_admission_fee ? 2000 : 0;
                            $totalWithAdmission = $fees->total_compulsory_fees + $admissionFeeAmount;
                            
                            $feesPaidResult = \App\Models\FeesPaid::create([
                                'date'                => date('Y-m-d'),
                                'is_fully_paid'       => $paid_fees >= $totalWithAdmission,
                                'is_used_installment' => 0,
                                'fees_id'             => $fees->id,
                                'student_id'          => $studentModel->user_id,
                                'amount'              => $paid_fees,
                                'school_id'           => $studentModel->school_id,
                            ]);

                            \App\Models\CompulsoryFee::create([
                                'student_id'   => $studentModel->user_id,
                                'type'         => 'Full Payment',
                                'mode'         => 1,
                                'amount'       => $paid_fees,
                                'fees_paid_id' => $feesPaidResult->id,
                                'date'         => date('Y-m-d'),
                                'status'       => 'Success',
                                'school_id'    => $studentModel->school_id,
                            ]);

                            // Send WhatsApp notification
                            try {
                                $currency_symbol = $cache->getSchoolSettings('currency_symbol', $studentModel->school_id) ?? '₹';
                                $notifyUserIds = [];
                                if (!empty($studentModel->guardian_id)) {
                                    $notifyUserIds[] = $studentModel->guardian_id;
                                }
                                if (!empty($studentModel->user_id)) {
                                    $notifyUserIds[] = $studentModel->user_id;
                                }
                                if (!empty($notifyUserIds)) {
                                    $title = 'Fees Payment Received';
                                    $template = $cache->getSchoolSettings('whatsapp-template-fee-payment', $studentModel->school_id);
                                    if (!empty($template)) {
                                        $template = htmlspecialchars_decode($template);
                                        $balance_amount = max(0, $totalWithAdmission - $paid_fees);
                                        $body = str_replace([
                                            '{parent_name}',
                                            '{student_name}',
                                            '{fee_name}',
                                            '{amount}',
                                            '{currency_symbol}',
                                            '{school_name}',
                                            '{balance_amount}',
                                            '{due_date}',
                                            '{url}'
                                        ], [
                                            $studentModel->guardian->full_name ?? '',
                                            $studentModel->user->first_name . ' ' . $studentModel->user->last_name,
                                            $fees->name,
                                            $paid_fees,
                                            $currency_symbol,
                                            $cache->getSchoolSettings('school_name', $studentModel->school_id) ?? '',
                                            $balance_amount,
                                            $fees->due_date,
                                            url('/')
                                        ], $template);
                                    } else {
                                        $body = 'Dear Parent/Student, fee payment of ' . $currency_symbol . ' ' . $paid_fees . ' has been received successfully towards ' . $fees->name . ' for ' . $studentModel->user->first_name . ' ' . $studentModel->user->last_name . '.';
                                    }
                                    $type = 'Fees';
                                    send_notification($notifyUserIds, $title, $body, $type);
                                }
                            } catch (Throwable $e) {
                                \Illuminate\Support\Facades\Log::error("Bulk import registration payment notification error: " . $e->getMessage());
                            }
                        }
                    }
                }
            } catch (Throwable $e) {
                // IF Exception is TypeError and message contains Mail keywords then email is not sent successfully
                if ($e instanceof TypeError && Str::contains($e->getMessage(), [
                        'Mail',
                        'Mailer',
                        'MailManager'
                    ])) {
                    continue;
                }
                DB::rollBack();
                throw $e;
            }
        }
        DB::commit();
        return true;
    }
}
