<?php

namespace App\Http\Controllers;

use App\Exports\StudentDataExport;
use App\Imports\StudentsImport;
use App\Models\School;
use App\Repositories\ClassSchool\ClassSchoolInterface;
use App\Repositories\ClassSection\ClassSectionInterface;
use App\Repositories\FormField\FormFieldsInterface;
use App\Repositories\SchoolSetting\SchoolSettingInterface;
use App\Repositories\SessionYear\SessionYearInterface;
use App\Repositories\Student\StudentInterface;
use App\Repositories\Subscription\SubscriptionInterface;
use App\Repositories\User\UserInterface;
use App\Services\BootstrapTableService;
use App\Services\CachingService;
use App\Services\FeaturesService;
use App\Services\ResponseService;
use App\Services\SubscriptionService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Throwable;
use TypeError;

class StudentController extends Controller {
    private StudentInterface $student;
    private UserInterface $user;
    private ClassSectionInterface $classSection;
    private FormFieldsInterface $formFields;
    private SessionYearInterface $sessionYear;
    private CachingService $cache;
    private SubscriptionInterface $subscription;
    private SchoolSettingInterface $schoolSettings;
    private SubscriptionService $subscriptionService;
    private ClassSchoolInterface $classSchool;

    public function __construct(StudentInterface $student, UserInterface $user, ClassSectionInterface $classSection, FormFieldsInterface $formFields, SessionYearInterface $sessionYear, CachingService $cachingService, SubscriptionInterface $subscription, SchoolSettingInterface $schoolSettings, SubscriptionService $subscriptionService, ClassSchoolInterface $classSchool) {
        $this->student = $student;
        $this->user = $user;
        $this->classSection = $classSection;
        $this->formFields = $formFields;
        $this->sessionYear = $sessionYear;
        $this->cache = $cachingService;
        $this->subscription = $subscription;
        $this->schoolSettings = $schoolSettings;
        $this->subscriptionService = $subscriptionService;
        $this->classSchool = $classSchool;
    }

    public function index() {
        ResponseService::noPermissionThenRedirect('student-list');
        $class_sections = $this->classSection->all(['*'], ['class', 'class.stream', 'section', 'medium']);

        if(Auth::user()->school_id) {
            $extraFields = $this->formFields->defaultModel()->where('user_type', 1)->orderBy('rank')->get();    
        } else {
            $extraFields = $this->formFields->defaultModel()->orderBy('rank')->get();
        }
       
        $sessionYears = $this->sessionYear->all();
        $features = FeaturesService::getFeatures();
        $sessionYear = $this->cache->getDefaultSessionYear();

        $classFees = DB::table('fees_class_types')
            ->join('fees', 'fees_class_types.fees_id', '=', 'fees.id')
            ->select('fees_class_types.class_id', DB::raw('SUM(fees_class_types.amount) as total_amount'))
            ->where('fees_class_types.optional', 0)
            ->where('fees.session_year_id', $sessionYear->id)
            ->whereNull('fees.deleted_at')
            ->groupBy('fees_class_types.class_id')
            ->pluck('total_amount', 'class_id');

        return view('students.details', compact('class_sections', 'extraFields', 'sessionYears', 'features', 'classFees'));
    }

    public function create() {
        ResponseService::noPermissionThenRedirect('student-create');
        $class_sections = $this->classSection->all(['*'], ['class', 'class.stream', 'section', 'medium']);
        $sessionYear = $this->cache->getDefaultSessionYear();
        $get_student_id = $this->student->builder()->withTrashed()->max('id');
        $admission_no = ($get_student_id ?? 0) + 1;

        if(Auth::user()->school_id) {
            $extraFields = $this->formFields->defaultModel()->where('user_type', 1)->orderBy('rank')->get();    
        } else {
            $extraFields = $this->formFields->defaultModel()->orderBy('rank')->get();
        }

        $sessionYears = $this->sessionYear->all();
        $features = FeaturesService::getFeatures();

        $classFees = DB::table('fees_class_types')
            ->join('fees', 'fees_class_types.fees_id', '=', 'fees.id')
            ->select('fees_class_types.class_id', DB::raw('SUM(fees_class_types.amount) as total_amount'))
            ->where('fees_class_types.optional', 0)
            ->where('fees.session_year_id', $sessionYear->id)
            ->whereNull('fees.deleted_at')
            ->groupBy('fees_class_types.class_id')
            ->pluck('total_amount', 'class_id');

        return view('students.create', compact('class_sections', 'admission_no', 'extraFields', 'sessionYears', 'features', 'classFees'));
    }

    public function store(Request $request) {
        ResponseService::noPermissionThenRedirect(['student-create']);
        $request->validate([
            'full_name'           => 'required',
            'mobile'              => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/',
            'image'               => 'nullable|mimes:jpeg,png,jpg,svg|image|max:2048',
            'dob'                 => 'required',
            'class_section_id'    => 'required|numeric',
            /*NOTE : Unique constraint is used because it's not school specific*/
            'admission_no'        => 'required|unique:users,email',
            'admission_date'      => 'required',
            'session_year_id'     => 'required|numeric',
            'guardian_email'      => 'nullable|email',
            'guardian_full_name'  => 'required|string',
            'guardian_mobile'     => 'nullable|numeric',
            'guardian_gender'     => 'required|in:male,female',
            'status'              => 'nullable|in:0,1',
            'pen_no'              => 'nullable|string|max:191',
            'aadhar_pic'          => 'nullable|mimes:jpeg,png,jpg,svg,pdf|max:2048',
            'guardian_image'      => 'nullable|mimes:jpeg,png,jpg,svg|image|max:2048',
            'guardian_aadhar_pic' => 'nullable|mimes:jpeg,png,jpg,svg,pdf|max:2048',
            'guardian_relation'   => 'required|in:father,mother,guardian,father_mother',
            'mother_name'         => 'required_if:guardian_relation,father_mother|string|nullable',
            'mother_mobile'       => 'nullable|numeric',
            'mother_image'        => 'nullable|mimes:jpeg,png,jpg,svg|image|max:2048',
            'mother_aadhar_pic'   => 'nullable|mimes:jpeg,png,jpg,svg,pdf|max:2048',
            'admission_amount_paid' => 'nullable|numeric|min:0',
            'payment_package'     => 'nullable|string|in:yearly,half_yearly,quarterly,monthly',
            'previous_year_balance' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Check free trial package
            $today_date = Carbon::now()->format('Y-m-d');
            $subscription = $this->subscription->builder()->doesntHave('subscription_bill')->whereDate('start_date', '<=', $today_date)->where('end_date', '>=', $today_date)->whereHas('package', function ($q) {
                $q->where('is_trial', 1);
            })->first();

            // If free trail package
            if ($subscription) {
                $systemSettings = $this->cache->getSystemSettings();
                $student = $this->user->builder()->role('Student')->withTrashed()->count();
                if ($student >= $systemSettings['student_limit']) {
                    $message = "The free trial allows only " . $systemSettings['student_limit'] . " students.";
                    ResponseService::errorResponse($message);
                }
            } else {
                // Regular package? Check Postpaid or Prepaid
                $subscription = $this->subscriptionService->active_subscription(Auth::user()->school_id);
                // If prepaid plan check student limit
                if ($subscription && $subscription->package_type == 0) {
                    $status = $this->subscriptionService->check_user_limit($subscription, "Students");
                    
                    if (!$status) {
                        ResponseService::errorResponse('You reach out limits');
                    }
                }
            }

            // Get the user details from the guardian details & identify whether that user is guardian or not. if not the guardian and has some other role then show appropriate message in response
            if (!empty($request->guardian_email)) {
                $guardianUser = $this->user->builder()->whereHas('roles', function ($q) {
                    $q->where('name', '!=', 'Guardian');
                })->where('email', $request->guardian_email)->withTrashed()->first();
                if ($guardianUser) {
                    ResponseService::errorResponse("Email ID is already taken for Other Role");
                }
            }
            $userService = app(UserService::class);
            $sessionYear = $this->sessionYear->findById($request->session_year_id);

            // Split student full name
            $name_parts = explode(' ', trim($request->full_name), 2);
            $first_name = $name_parts[0];
            $last_name = isset($name_parts[1]) ? $name_parts[1] : '.';

            // Split guardian full name
            $gname_parts = explode(' ', trim($request->guardian_full_name), 2);
            $guardian_first_name = $gname_parts[0];
            $guardian_last_name = isset($gname_parts[1]) ? $gname_parts[1] : '.';

            $guardian = $userService->createOrUpdateParent(
                $guardian_first_name,
                $guardian_last_name,
                $request->guardian_email,
                $request->guardian_mobile,
                $request->guardian_gender,
                $request->file('guardian_image'),
                null,
                $request->guardian_relation,
                $request->file('guardian_aadhar_pic'),
                $request->mother_name,
                $request->mother_mobile,
                $request->file('mother_image'),
                $request->file('mother_aadhar_pic')
            );
            $is_send_notification = true;
            $user = $userService->createStudentUser($first_name, $last_name, $request->admission_no, $request->mobile, $request->dob, $request->gender, $request->image, $request->class_section_id, $request->admission_date, $request->current_address, $request->permanent_address, $sessionYear->id, $guardian->id, $request->extra_fields ?? [], $request->status ?? 0, $is_send_notification, $request->pen_no, $request->aadhar_pic, $request->input('apply_class_fee', 0), $request->input('apply_van_fee', 0), $request->input('apply_admission_fee', 0), $request->input('payment_package'), $request->input('previous_year_balance', 0.00));

            // Record fee payment during registration if admission_amount_paid is provided
            if ($request->filled('admission_amount_paid') && $request->admission_amount_paid > 0) {
                $amount = $request->admission_amount_paid;
                $student = $user->student;
                if ($student) {
                    $classSection = DB::table('class_sections')->where('id', $student->class_section_id)->first();
                    if ($classSection) {
                        $fees = \App\Models\Fee::where('class_id', $classSection->class_id)
                            ->where('session_year_id', $sessionYear->id)
                            ->with('fees_class_type.fees_type')
                            ->first();

                        if ($fees) {
                            $fees->filterForStudent($student);
                            
                            // Add admission fee (₹ 2,000) to total if applied
                            $admissionFeeAmount = $student->apply_admission_fee ? 2000 : 0;
                            $totalWithAdmission = $fees->total_compulsory_fees + $admissionFeeAmount;
                            
                            $feesPaidResult = \App\Models\FeesPaid::create([
                                'date'                => date('Y-m-d'),
                                'is_fully_paid'       => $amount >= $totalWithAdmission,
                                'is_used_installment' => 0,
                                'fees_id'             => $fees->id,
                                'student_id'          => $student->user_id,
                                'amount'              => $amount,
                                'school_id'           => $student->school_id,
                            ]);

                            \App\Models\CompulsoryFee::create([
                                'student_id'   => $student->user_id,
                                'type'         => 'Full Payment',
                                'mode'         => 1,
                                'amount'       => $amount,
                                'fees_paid_id' => $feesPaidResult->id,
                                'date'         => date('Y-m-d'),
                                'status'       => 'Success',
                                'school_id'    => $student->school_id,
                            ]);

                            // Send WhatsApp notification
                            try {
                                $currency_symbol = $this->cache->getSchoolSettings('currency_symbol', $student->school_id) ?? '₹';
                                $notifyUserIds = [];
                                if (!empty($student->guardian_id)) {
                                    $notifyUserIds[] = $student->guardian_id;
                                }
                                if (!empty($student->user_id)) {
                                    $notifyUserIds[] = $student->user_id;
                                }
                                if (!empty($notifyUserIds)) {
                                    $title = 'Fees Payment Received';
                                    $template = $this->cache->getSchoolSettings('whatsapp-template-fee-payment', $student->school_id);
                                    if (!empty($template)) {
                                        $template = htmlspecialchars_decode($template);
                                        $balance_amount = max(0, $totalWithAdmission - $amount);
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
                                            $student->guardian->full_name ?? '',
                                            $student->user->first_name . ' ' . $student->user->last_name,
                                            $fees->name,
                                            $amount,
                                            $currency_symbol,
                                            $this->cache->getSchoolSettings('school_name', $student->school_id) ?? '',
                                            $balance_amount,
                                            $fees->due_date,
                                            url('/')
                                        ], $template);
                                    } else {
                                        $body = 'Dear Parent/Student, fee payment of ' . $currency_symbol . ' ' . $amount . ' has been received successfully towards ' . $fees->name . ' for ' . $student->user->first_name . ' ' . $student->user->last_name . '.';
                                    }
                                    $type = 'Fees';
                                    send_notification($notifyUserIds, $title, $body, $type);
                                }
                            } catch (Throwable $e) {
                                \Illuminate\Support\Facades\Log::error("Student registration payment notification error: " . $e->getMessage());
                            }
                        }
                    }
                }
            }

            DB::commit();
            ResponseService::successResponse('Data Stored Successfully');
        } catch (Throwable $e) {
            // IF Exception is TypeError and message contains Mail keywords then email is not sent successfully
            if ($e instanceof TypeError && Str::contains($e->getMessage(), [
                    'Failed',
                    'Mail',
                    'Mailer',
                    'MailManager'
                ])) {
                DB::commit();
                ResponseService::warningResponse("Student Registered successfully. But Email not sent.");
            } else {
                DB::rollBack();
                ResponseService::logErrorResponse($e, "Student Controller -> Store method");
                ResponseService::errorResponse();
            }

        }
    }

    public function update($id, Request $request) {
        ResponseService::noAnyPermissionThenSendJson(['student-create', 'student-edit']);
        $rules = [
            'full_name'           => 'required',
            'mobile'              => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/',
            'image'               => 'nullable|mimes:jpeg,png,jpg,svg|image|max:2048',
            'dob'                 => 'required',
            'session_year_id'     => 'required|numeric',
            'guardian_email'      => 'nullable|email|unique:users,email',
            'pen_no'              => 'nullable|string|max:191',
            'aadhar_pic'          => 'nullable|mimes:jpeg,png,jpg,svg,pdf|max:2048',
            'guardian_full_name'  => 'required|string',
            'guardian_mobile'     => 'nullable|numeric',
            'guardian_image'      => 'nullable|mimes:jpeg,png,jpg,svg|image|max:2048',
            'guardian_aadhar_pic' => 'nullable|mimes:jpeg,png,jpg,svg,pdf|max:2048',
            'guardian_relation'   => 'required|in:father,mother,guardian,father_mother',
            'mother_name'         => 'required_if:guardian_relation,father_mother|string|nullable',
            'mother_mobile'       => 'nullable|numeric',
            'mother_image'        => 'nullable|mimes:jpeg,png,jpg,svg|image|max:2048',
            'mother_aadhar_pic'   => 'nullable|mimes:jpeg,png,jpg,svg,pdf|max:2048',
            'payment_package'     => 'nullable|string|in:yearly,half_yearly,quarterly,monthly',
            'admission_no'        => 'required|unique:users,email,' . $id,
            'previous_year_balance' => 'nullable|numeric|min:0',
        ];
        if (is_numeric($request->guardian_id)) {
            $rules['guardian_email'] = 'nullable|email|unique:users,email,' . $request->guardian_id;
        }
        $request->validate($rules);

        try {
            DB::beginTransaction();
            $userService = app(UserService::class);
            $sessionYear = $this->sessionYear->findById($request->session_year_id);

            // Split student full name
            $name_parts = explode(' ', trim($request->full_name), 2);
            $first_name = $name_parts[0];
            $last_name = isset($name_parts[1]) ? $name_parts[1] : '.';

            // Split guardian full name
            $gname_parts = explode(' ', trim($request->guardian_full_name), 2);
            $guardian_first_name = $gname_parts[0];
            $guardian_last_name = isset($gname_parts[1]) ? $gname_parts[1] : '.';

            $guardian = $userService->createOrUpdateParent(
                $guardian_first_name,
                $guardian_last_name,
                $request->guardian_email,
                $request->guardian_mobile,
                $request->guardian_gender,
                $request->file('guardian_image'),
                $request->parent_reset_password,
                $request->guardian_relation,
                $request->file('guardian_aadhar_pic'),
                $request->mother_name,
                $request->mother_mobile,
                $request->file('mother_image'),
                $request->file('mother_aadhar_pic')
            );

            $userService->updateStudentUser($id, $first_name, $last_name, $request->mobile, $request->dob, $request->gender, $request->image, $sessionYear->id, $request->extra_fields ?? [], $guardian->id, $request->current_address, $request->permanent_address, $request->reset_password, $request->class_section_id, $request->pen_no, $request->aadhar_pic, $request->input('apply_class_fee', 0), $request->input('apply_van_fee', 0), $request->input('apply_admission_fee', 0), $request->input('payment_package'), $request->admission_no, $request->input('previous_year_balance', 0.00));
            DB::commit();
            ResponseService::successResponse('Data Updated Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "Student Controller -> Update method");
            ResponseService::errorResponse();
        }
    }

    public function show(Request $request) {
        ResponseService::noPermissionThenRedirect('student-list');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'ASC');
        $search = request('search');

        if (Auth::user()->hasRole('Teacher')) {
            $request->validate([
                'class_id' => 'required'
            ],[
                'class_id.required' => 'The class field is required.'
            ]);
        }

        $sql = $this->student->builder()->where('application_type', 'offline')->where('application_type', 'online')
        ->orwhere(function ($query) {
            $query->where('application_status', 1); // Only online applications with status 1
        })
        ->with('user.extra_student_details.form_field', 'guardian', 'class_section.class.stream', 'class_section.section', 'class_section.medium')
            ->where(function ($query) use ($search) {
                $query->when($search, function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('user_id', 'LIKE', "%$search%")
                            ->orWhere('class_section_id', 'LIKE', "%$search%")
                            ->orWhere('admission_no', 'LIKE', "%$search%")
                            ->orWhere('roll_number', 'LIKE', "%$search%")
                            ->orWhere('admission_date', 'LIKE', date('Y-m-d', strtotime("%$search%")))
                            ->orWhereHas('user', function ($q) use ($search) {
                                $q->where('first_name', 'LIKE', "%$search%")
                                    ->orwhere('last_name', 'LIKE', "%$search%")
                                    ->orwhere('email', 'LIKE', "%$search%")
                                    ->orwhere('dob', 'LIKE', "%$search%")
                                    ->orWhereRaw("concat(first_name,' ',last_name) LIKE '%" . $search . "%'");
                            })->orWhereHas('guardian', function ($q) use ($search) {
                                $q->where('first_name', 'LIKE', "%$search%")
                                    ->orwhere('last_name', 'LIKE', "%$search%")
                                    ->orwhere('email', 'LIKE', "%$search%")
                                    ->orwhere('dob', 'LIKE', "%$search%")
                                    ->orWhereRaw("concat(first_name,' ',last_name) LIKE '%" . $search . "%'");
                            });
                    });
                });
                //class filter data
            })->when(request('class_id') != null, function ($query) {
                $classId = request('class_id');
                $query->where(function ($query) use ($classId) {
                    $query->where('class_section_id', $classId);
                });
            })->when(request('session_year_id') != null, function ($query) {
                $sessionYearID = request('session_year_id');
                $query->where(function ($query) use ($sessionYearID) {
                    $query->where('session_year_id', $sessionYearID);
                });
            });

        if ($request->show_deactive) {
            $sql = $sql->whereHas('user', function ($query) {
                $query->where('status', 0)->withTrashed();
            });
        } else {
            $sql = $sql->whereHas('user', function ($query) {
                $query->where('status', 1);
            });
        }

        if ($request->exam_id && $request->exam_id != 'data-not-found') {
            $sql = $sql->has('exam_result')->whereHas('exam_result', function($q) use($request) {
                $q->where('exam_id',$request->exam_id);
            });
        }

        $total = $sql->count();
        if (!empty($request->class_id)) {
            $sql = $sql->orderBy('roll_number', 'ASC');
        } else {
            $sql = $sql->orderBy($sort, $order);
        }
        $sql->skip($offset)->take($limit);
        $res = $sql->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $no = 1;
        foreach ($res as $row) {
            $operate = '';
            if (!$request->show_deactive) {
                if (Auth::user()->can('student-edit')) {
                    $operate .= BootstrapTableService::editButton(route('students.update', $row->user->id, ['data-id' => $row->id]));
                    $operate .= BootstrapTableService::button('fa fa-exclamation-triangle', route('student.change-status', $row->user_id), ['btn-gradient-info', 'deactivate-student'], ['title' => __('inactive')]);
                }
            } else {
                $operate .= BootstrapTableService::button('fa fa-check', route('student.change-status', $row->user_id), ['btn-gradient-success', 'activate-student'], ['title' => __('active')]);
            }

            if (Auth::user()->can('student-delete')) {
                $operate .= BootstrapTableService::trashButton(route('student.trash', $row->user_id));
            }
            $student_gender = $row->user->gender;
            $guardian_gender = $row->guardian->gender ?? '';
            $row->user->gender = trans(strtolower($row->user->gender));
            $row->guardian->gender = trans(strtolower($row->guardian->gender ?? ''));
            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $tempRow['eng_student_gender'] = $student_gender;
            $tempRow['eng_guardian_gender'] = $guardian_gender;
            
            $initialPayment = \App\Models\CompulsoryFee::where([
                'student_id' => $row->user_id,
                'type' => 'Full Payment'
            ])->sum('amount');
            $tempRow['initial_payment'] = $initialPayment;
            // $tempRow['user.dob'] = format_date($row->user->dob);
            // $tempRow['admission_date'] = format_date($row->admission_date);
            
            // $tempRow['extra_fields'] = $row->user->extra_student_details()->has('form_field')->with('form_field')->get();
            $tempRow['extra_fields'] = $row->user->extra_student_details;
            foreach ($row->user->extra_student_details as $key => $field) {
                $data = '';
                if ($field->form_field->type == 'checkbox') {
                    $data = json_decode($field->data);
                } else if($field->form_field->type == 'file') {
                    $data = '<a href="'.Storage::url($field->data).'" target="_blank">DOC</a>';
                } else if($field->form_field->type == 'dropdown') {
                    $data = $field->form_field->default_values;
                    $data = $field->data ?? '';
                } else {
                    $data = $field->data;
                }
                $tempRow[$field->form_field->name] = $data;
            }
            
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function destroy($user_id) {
        ResponseService::noPermissionThenSendJson('student-delete');
        try {
            DB::beginTransaction();
            $this->user->deleteById($user_id);
            $this->student->builder()->where('user_id', $user_id)->delete();
            DB::commit();
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "Student Controller -> Delete method");
            ResponseService::errorResponse();
        }
    }

    public function changeStatus($userId) {
        try {
            // ResponseService::noFeatureThenSendJson('Student Management');
            ResponseService::noPermissionThenRedirect('student-edit');
            DB::beginTransaction();
            $user = $this->user->findTrashedById($userId);
            if ($user->status == 0) {
                $subscription = $this->subscriptionService->active_subscription(Auth::user()->school_id);
                // If prepaid plan check student limit
                if ($subscription && $subscription->package_type == 0) {
                    $status = $this->subscriptionService->check_user_limit($subscription, "Students");
                    
                    if (!$status) {
                        ResponseService::errorResponse('You reach out limits');
                    }
                }
            }
                        
            $newStatus = $user->status == 0 ? 1 : 0;
            $deletedAt = $user->status == 1 ? now() : null;
            $this->user->builder()->where('id', $userId)->withTrashed()->update(['status' => $newStatus, 'deleted_at' => $deletedAt]);
            $this->student->builder()->where('user_id', $userId)->withTrashed()->update(['deleted_at' => $deletedAt]);
            DB::commit();
            ResponseService::successResponse('Data Updated Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, 'Student Controller ---> Change Status');
            ResponseService::errorResponse();
        }
    }

    public function changeStatusBulk(Request $request) {
        // ResponseService::noFeatureThenSendJson('Student Management');
        ResponseService::noPermissionThenRedirect('student-create');
        try {
            DB::beginTransaction();
            foreach (json_decode($request->ids, false, 512, JSON_THROW_ON_ERROR) as $key => $userId) {
                $studentUser = $this->user->findTrashedById($userId);
                if ($studentUser->status == 0) {
                    $subscription = $this->subscriptionService->active_subscription(Auth::user()->school_id);
                    // If prepaid plan check student limit
                    if ($subscription && $subscription->package_type == 0) {
                        $status = $this->subscriptionService->check_user_limit($subscription,"Students");
                        
                        if (!$status) {
                            ResponseService::errorResponse('You reach out limits');
                        }
                    }
                }

                $newStatus = $studentUser->status == 0 ? 1 : 0;
                $deletedAt = $studentUser->status == 1 ? now() : null;
                $this->user->builder()->where('id', $userId)->withTrashed()->update(['status' => $newStatus, 'deleted_at' => $deletedAt]);
                $this->student->builder()->where('user_id', $userId)->withTrashed()->update(['deleted_at' => $deletedAt]);
            }
            DB::commit();
            ResponseService::successResponse("Status Updated Successfully");
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e);
            ResponseService::errorResponse();
        }
    }

    public function trash($id) {
        // ResponseService::noFeatureThenSendJson('Student Management');
        ResponseService::noPermissionThenSendJson('student-delete');
        try {
            DB::beginTransaction();
            
            // Get student record with guardian
            $student = $this->student->builder()->with('guardian')->where('user_id', $id)->first();
            
            if ($student && $student->guardian) {
                // Count total students with same guardian_id
                $guardianStudentCount = $this->student->builder()->where('guardian_id', $student->guardian_id)->count();

                // If guardian has exactly one student, delete the guardian
                if ($guardianStudentCount == 1) {
                    $this->user->builder()->where('id', $student->guardian->id)->withTrashed()->forceDelete();
                }
            }

            // Delete student and user records
            $this->student->builder()->where('user_id', $id)->withTrashed()->forceDelete();
            $this->user->builder()->where('id', $id)->withTrashed()->forceDelete();

            DB::commit();
            ResponseService::successResponse("Data Deleted Permanently");
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "Student Controller ->Trash Method", 'cannot_delete_because_data_is_associated_with_other_data');
            ResponseService::errorResponse();
        }
    }

    public function trashBulk(Request $request) {
        ResponseService::noPermissionThenSendJson('student-delete');
        try {
            DB::beginTransaction();
            $ids = json_decode($request->ids, false, 512, JSON_THROW_ON_ERROR);
            foreach ($ids as $id) {
                // Get student record with guardian
                $student = $this->student->builder()->with('guardian')->where('user_id', $id)->first();
                
                if ($student && $student->guardian) {
                    // Count total students with same guardian_id
                    $guardianStudentCount = $this->student->builder()->where('guardian_id', $student->guardian_id)->count();

                    // If guardian has exactly one student, delete the guardian
                    if ($guardianStudentCount == 1) {
                        $this->user->builder()->where('id', $student->guardian->id)->withTrashed()->forceDelete();
                    }
                }

                // Delete student and user records
                $this->student->builder()->where('user_id', $id)->withTrashed()->forceDelete();
                $this->user->builder()->where('id', $id)->withTrashed()->forceDelete();
            }
            DB::commit();
            ResponseService::successResponse("Data Deleted Permanently");
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "Student Controller ->Trash Bulk Method", 'cannot_delete_because_data_is_associated_with_other_data');
            ResponseService::errorResponse();
        }
    }

    public function createBulkData() {
        ResponseService::noPermissionThenRedirect('student-create');
        $class_section = $this->classSection->all(['*'], ['class', 'class.stream', 'section', 'medium']);
        $sessionYears = $this->sessionYear->all();
        return view('students.add_bulk_data', compact('class_section', 'sessionYears'));
    }

    public function storeBulkData(Request $request) {
        ResponseService::noPermissionThenRedirect('student-create');
        $validator = Validator::make($request->all(), [
            'session_year_id'  => 'required|numeric',
            'class_section_id' => 'nullable',
            'file'             => 'required|mimes:csv,txt,xls,xlsx'
        ]);
        if ($validator->fails()) {
            ResponseService::errorResponse($validator->errors()->first());
        }
        try {
            Excel::import(new StudentsImport($request->class_section_id, $request->session_year_id, $request->is_send_notification), $request->file);
            ResponseService::successResponse('Data Stored Successfully');
        } catch (ValidationException $e) {
            if ($e instanceof TypeError && Str::contains($e->getMessage(), [
                'Failed',
                'Mail',
                'Mailer',
                'MailManager'
            ])) {
                DB::commit();
                ResponseService::warningResponse("Student Registered successfully. But Email not sent.");
            } else {
                ResponseService::errorResponse($e->getMessage());
            }
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Student Controller -> Store Bulk method");
            ResponseService::errorResponse();
        }
    }

    public function resetPasswordIndex() {
        $class_section = $this->classSection->builder()->with('class', 'class.stream', 'section')->get();
        return view('students.reset-password', compact('class_section'));
    }

    public function resetPasswordShow() {
        ResponseService::noPermissionThenRedirect('reset-password-list');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');

        $sql = $this->user->builder()->where('reset_request', 1);
        if (!empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where(function ($query) use ($search) {
                $query->where('id', 'LIKE', "%$search%")->orwhere('email', 'LIKE', "%$search%")
                    ->orwhere('first_name', 'LIKE', "%$search%")
                    ->orwhere('last_name', 'LIKE', "%$search%")
                    ->orWhereRaw("concat(users.first_name,' ',users.last_name) LIKE '%" . $search . "%'");
            });
        }

        $total = $sql->count();

        $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->get();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $no = 1;
        foreach ($res as $row) {
            $operate = BootstrapTableService::button('fa fa-edit', route('student.reset-password.update', $row->id), ['reset_password', 'btn-gradient-primary', 'btn-action', 'btn-rounded btn-icon'], ['title' => trans("reset_password"), 'data-id' => $row->id, 'data-dob' => $row->dob]);
            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function resetPasswordUpdate(Request $request) {
        ResponseService::noPermissionThenRedirect('student-change-password');
        try {
            DB::beginTransaction();
            $dob = date('dmY', strtotime($request->dob));
            $password = Hash::make($dob);
            $this->user->update($request->id, ['password' => $password, 'reset_request' => 0]);
            DB::commit();

            ResponseService::successResponse('Data Updated Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "Student Controller -> Reset Password method");
            ResponseService::errorResponse();
        }
    }

    public function rollNumberIndex() {
        ResponseService::noPermissionThenRedirect('student-create');
        $class_section = $this->classSection->all(['*'], ['class', 'class.stream', 'section', 'medium']);

        return view('students.assign_roll_no', compact('class_section'));
    }

    public function rollNumberUpdate(Request $request) {
        ResponseService::noPermissionThenRedirect('student-create');
        $validator = Validator::make(
            $request->all(),
            ['roll_number_data.*.roll_number' => 'required',],
            ['roll_number_data.*.roll_number.required' => trans('please_fill_all_roll_numbers_data')]
        );
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        try {
            DB::beginTransaction();
            foreach ($request->roll_number_data as $data) {
                $updateRollNumberData = array(
                    'roll_number' => $data['roll_number']
                );

                // validation required when the edit of roll number is enabled

                // $class_roll_number_data = $this->student->builder()->where(['class_section_id' => $student->class_section_id,'roll_number' => $data['roll_number']])->whereNot('id',$data['student_id'])->count();
                // if(isset($class_roll_number_data) && !empty($class_roll_number_data)){
                //     $response = array(
                //         'error' => true,
                //         'message' => trans('roll_number_already_exists_of_number').' - '.$i
                //     );
                //     return response()->json($response);
                // }
                // TODO : Use upsert here
                $this->student->update($data['student_id'], $updateRollNumberData);
            }
            DB::commit();
            ResponseService::successResponse('Data Updated Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "Student Controller -> updateStudentRollNumber");
            ResponseService::errorResponse();
        }
    }

    public function rollNumberShow(Request $request) {
        ResponseService::noPermissionThenRedirect('student-create');
        try {
            ResponseService::noPermissionThenRedirect('student-list');
            $currentSessionYear = $this->cache->getDefaultSessionYear();
            $class_section_id = $request->class_section_id;
            $sql = $this->user->builder()->with('student');
            $sql = $sql->whereHas('student', function ($q) use ($class_section_id, $currentSessionYear) {
                $q->where(['class_section_id' => $class_section_id, 'session_year_id' => $currentSessionYear->id]);
            });
            if (!empty($_GET['search'])) {
                $search = $_GET['search'];
                $sql->where(function ($query) use ($search) {
                    $query->where('first_name', 'LIKE', "%$search%")
                        ->orwhere('last_name', 'LIKE', "%$search%")
                        ->orwhere('email', 'LIKE', "%$search%")
                        ->orwhere('dob', 'LIKE', "%$search%")
                        ->orWhereHas('student', function ($q) use ($search) {
                            $q->where('id', 'LIKE', "%$search%")
                                ->orWhere('user_id', 'LIKE', "%$search%")
                                ->orWhere('class_section_id', 'LIKE', "%$search%")
                                ->orWhere('admission_no', 'LIKE', "%$search%")
                                ->orWhere('admission_date', 'LIKE', date('Y-m-d', strtotime("%$search%")))
                                ->orWhereHas('user', function ($q) use ($search) {
                                    $q->where('first_name', 'LIKE', "%$search%")
                                        ->orwhere('last_name', 'LIKE', "%$search%")
                                        ->orwhere('email', 'LIKE', "%$search%")
                                        ->orwhere('dob', 'LIKE', "%$search%");
                                });
                        });
                });
            }
            if ($request->sort_by == 'first_name') {
                $sql = $sql->orderBy('first_name', $request->order_by);
            }
            if ($request->sort_by == 'last_name') {
                $sql = $sql->orderBy('last_name', $request->order_by);
            }
            $total = $sql->count();
            $res = $sql->get();

            $bulkData = array();
            $bulkData['total'] = $total;
            $rows = array();
            $no = 1;
            $roll = 1;
            $index = 0;

            // TODO : improve this
            foreach ($res as $row) {
                $tempRow = $row->toArray();
                $tempRow['no'] = $no++;
                $tempRow['student_id'] = $row->student->id;
                $tempRow['old_roll_number'] = $row->student->roll_number;

                // for edit roll number comment below line
                $tempRow['new_roll_number'] = "<input type='hidden' name='roll_number_data[" . $index . "][student_id]' class='form-control' readonly value=" . $row->student->id . "> <input type='hidden' name='roll_number_data[" . $index . "][roll_number]' class='form-control' value=" . $roll . ">" . $roll;

                // and uncomment below line
                // $tempRow['new_roll_number'] = "<input type='hidden' name='roll_number_data[" . $index . "][student_id]' class='form-control' readonly value=" . $row->student->id . "> <input type='text' name='roll_number_data[" . $index . "][roll_number]' class='form-control' value=" . $roll . ">";

                $tempRow['user_id'] = $row->id;
                $tempRow['admission_no'] = $row->student->admission_no;
                $tempRow['admission_date'] = $row->student->admission_date;
                $rows[] = $tempRow;
                $index++;
                $roll++;
            }

            $bulkData['rows'] = $rows;
            return response()->json($bulkData);
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Student Controller -> listStudentRollNumber");
            ResponseService::errorResponse();
        }
    }

    public function downloadSampleFile() {
        try {
            return Excel::download(new StudentDataExport(), 'Student_import.xlsx');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, 'Student Controller ---> Download Sample File');
            ResponseService::errorResponse();
        }
    }

    public function update_profile()
    {
        ResponseService::noPermissionThenRedirect('student-edit');
        
        $class_sections = $this->classSection->all(['*'], ['class', 'class.stream', 'section', 'medium']);
        return view('students.add_bulk_profile',compact('class_sections'));
        
    }

    public function list($id = null, Request $request)
    {
        ResponseService::noPermissionThenRedirect('student-edit');
        $search = request('search');

        $res = array();
        $total = 0;
        if (!empty($request->class_id)) {
            $sql = $this->student->builder()->with('user', 'guardian', 'class_section.class', 'class_section.section', 'class_section.medium')
                ->where(function ($query) use ($search) {
                    $query->when($search, function ($query) use ($search) {
                        $query->where(function ($query) use ($search) {
                            $query->where('user_id', 'LIKE', "%$search%")
                                ->orWhere('roll_number', 'LIKE', "%$search%")
                                ->orWhereHas('user', function ($q) use ($search) {
                                    $q->where('first_name', 'LIKE', "%$search%")
                                        ->orwhere('last_name', 'LIKE', "%$search%")
                                        ->orwhere('email', 'LIKE', "%$search%")
                                        ->orwhere('dob', 'LIKE', "%$search%");
                                });
                        });
                    });
                })->when(request('class_id') != null, function ($query) {
                    $classId = request('class_id');
                    $query->where(function ($query) use ($classId) {
                        $query->where('class_section_id', $classId);
                    });
                });

            $sql = $sql->whereHas('user', function ($query) {
                $query->where('status', 1);
            });
            $total = $sql->count();
            $sql = $sql->orderBy('roll_number', 'ASC');
            $res = $sql->get();
        }
        
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $no = 1;
        foreach ($res as $row) {
            
            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);

    }

    public function store_update_profile(Request $request)
    {
        ResponseService::noAnyPermissionThenRedirect(['student-edit']);

        try {
            $data = array();
            if ($request->student_image) {
                foreach ($request->student_image as $key => $profile) {
                    $data[] = [
                        'id' => $key,
                        'image' => $profile
                    ];
                }
            }
            if ($request->guardian_image) {
                foreach ($request->guardian_image as $key => $profile) {
                    $data[] = [
                        'id' => $key,
                        'image' => $profile
                    ];
                }
            }
            $this->user->upsertProfile($data,['id'],['image']);
            // $this->user->upsert($data,['id'],['image']);
            ResponseService::successResponse('Profile Updated Successfully');
            
        } catch (\Throwable $th) {
            ResponseService::logErrorResponse($th);
            ResponseService::errorResponse();
        }
    }

    public function generate_id_card_index() {
        ResponseService::noFeatureThenRedirect('ID Card - Certificate Generation');
        ResponseService::noAnyPermissionThenRedirect(['student-list', 'class-teacher']);

        $class_sections = $this->classSection->all(['*'], ['class', 'class.stream', 'section', 'medium']);
        $sessionYears = $this->sessionYear->all();

        return view('students.generate_id_card', compact('class_sections', 'sessionYears'));
    }

    public function generate_id_card(Request $request) {
        ResponseService::noFeatureThenRedirect('ID Card - Certificate Generation');
        ResponseService::noAnyPermissionThenRedirect(['student-list', 'class-teacher']);
        $request->validate([
            'user_id' => 'required'
        ], [
            'user_id.required' => trans('Please select at least one record')
        ]);
        try {
            $user_ids = explode(",",$request->user_id);
            $settings = $this->cache->getSchoolSettings();
            if (!isset($settings['student_id_card_fields'])) {
                return redirect()->route('id-card-settings')->with('error',trans('settings_not_found'));
            }

            $settings['student_id_card_fields'] = explode(",",$settings['student_id_card_fields']);

            $data = explode("storage/", $settings['signature'] ?? '');
            $settings['signature'] = end($data);

            $data = explode("storage/", $settings['background_image'] ?? '');
            $settings['background_image'] = end($data);

            $data = explode("storage/", $settings['horizontal_logo'] ?? '');
            $settings['horizontal_logo'] = end($data);

            $sessionYear = $this->cache->getDefaultSessionYear();
            $valid_until = date('F j, Y',strtotime($sessionYear->end_date));
            $height = $settings['page_height'] * 2.8346456693;
            $width = $settings['page_width'] * 2.8346456693;
            // $customPaper = array(0,0,360,200);
            $customPaper = array(0,0,$width,$height);
            $students = $this->user->builder()->select('id','first_name','last_name','image','school_id','gender','dob')->with('student:id,user_id,class_section_id,school_id,guardian_id,roll_number','student.class_section.class','student.class_section.section','student.class_section.medium','student.class_section.class.stream','student.guardian:id,mobile,first_name,last_name')->whereHas('student',function($q) use($user_ids) {
                $q->whereIn('id',$user_ids);
            })->with(['extra_student_details' => function($q) {
                $q->whereHas('form_field',function($query) {
                    $query->where('display_on_id',1)->whereNull('deleted_at');
                })->with('form_field');
            }])->get();


            $settings['page_height'] = ($settings['page_height'] * 3.7795275591).'px';
            
            $pdf = PDF::loadView('students.students_id_card',compact('students','sessionYear','valid_until','settings'));
            $pdf->setPaper($customPaper);

            
            return $pdf->stream();
            return view('students.id_card_pdf');
        } catch (\Throwable $th) {
            ResponseService::logErrorResponse($th);
            ResponseService::errorResponse();
        }
    }

    public function admissionForm()
    {
        try {
            if (Auth::user()) {
                $schoolSettings = $this->cache->getSchoolSettings();
            } else {
                $fullDomain = $_SERVER['HTTP_HOST'] ?? '';
                $parts = explode('.', $fullDomain);
                $subdomain = $parts[0];
                
                $school = School::on('mysql')->where('domain', $fullDomain)->orwhere('domain', $subdomain)->first();
                if ($school) {
                    $schoolSettings = $this->cache->getSchoolSettings('*', $school->id);
                }
            }
            
            $data = explode("storage/", $schoolSettings['horizontal_logo'] ?? '');
                $schoolSettings['horizontal_logo'] = end($data);
    
            if ($schoolSettings['horizontal_logo'] == null) {
                $systemSettings = $this->cache->getSystemSettings();
                $data = explode("storage/", $systemSettings['horizontal_logo'] ?? '');
                $schoolSettings['horizontal_logo'] = end($data);
            }
    
            $pdf = PDF::loadView('students.admission_form',compact('schoolSettings'));
            return $pdf->stream();
        } catch (\Throwable $th) {
            
        }
        
    }

    public function onlineRegistrationIndex()
    {
        ResponseService::noPermissionThenRedirect('student-list');
        $class_sections = $this->classSection->all(['*'], ['class', 'class.stream', 'section', 'medium']);
        $classes = $this->classSchool->builder()->with('medium','stream')->get();
     
        $extraFields = $this->formFields->defaultModel()->orderBy('rank')->get();
        $sessionYears = $this->sessionYear->all();
        $features = FeaturesService::getFeatures();

        return view('students.online_registration', compact('class_sections', 'extraFields', 'sessionYears', 'features', 'classes'));
    }

    public function onlineRegistrationList(Request $request)
    {
        ResponseService::noPermissionThenRedirect('student-list');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'ASC');
        $search = request('search');

        $sql = $this->student->builder()->where('application_type', 'online')->where('application_status', 0)->with('user.extra_student_details.form_field', 'guardian', 'class.medium','class.stream')
            ->where(function ($query) use ($search) {
                $query->when($search, function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('user_id', 'LIKE', "%$search%")
                            ->orWhere('class_section_id', 'LIKE', "%$search%")
                            ->orWhere('admission_no', 'LIKE', "%$search%")
                            ->orWhere('roll_number', 'LIKE', "%$search%")
                            ->orWhere('admission_date', 'LIKE', date('Y-m-d', strtotime("%$search%")))
                            ->orWhereHas('user', function ($q) use ($search) {
                                $q->where('first_name', 'LIKE', "%$search%")
                                    ->orwhere('last_name', 'LIKE', "%$search%")
                                    ->orwhere('email', 'LIKE', "%$search%")
                                    ->orwhere('dob', 'LIKE', "%$search%")
                                    ->orWhereRaw("concat(first_name,' ',last_name) LIKE '%" . $search . "%'");
                            })->orWhereHas('guardian', function ($q) use ($search) {
                                $q->where('first_name', 'LIKE', "%$search%")
                                    ->orwhere('last_name', 'LIKE', "%$search%")
                                    ->orwhere('email', 'LIKE', "%$search%")
                                    ->orwhere('dob', 'LIKE', "%$search%")
                                    ->orWhereRaw("concat(first_name,' ',last_name) LIKE '%" . $search . "%'");
                            });
                    });
                })
                ->whereHas('user', function($q) {
                    $q->where('status', 0);
                }) ;
                //class filter data
            })
            ->when(request('class_id') != null, function ($query) {
                $classId = request('class_id');
                $query->where(function ($query) use ($classId) {
                    $query->where('class_id', $classId);
                });
            });

        if ($request->exam_id && $request->exam_id != 'data-not-found') {
            $sql = $sql->has('exam_result')->whereHas('exam_result', function($q) use($request) {
                $q->where('exam_id',$request->exam_id);
            });
        }

        $total = $sql->count();
        if (!empty($request->class_id)) {
            $sql = $sql->orderBy('roll_number', 'ASC');
        } else {
            $sql = $sql->orderBy($sort, $order);
        }
        $sql->skip($offset)->take($limit);
        $res = $sql->get();
    
      
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $no = 1;
        foreach ($res as $row) {
            $operate = '';
          
            if (Auth::user()->can('student-edit')) {
                $operate .= BootstrapTableService::editButton(route('update-application-status', $row->user->id, ['data-id' => $row->id]));
            }
             

            if (Auth::user()->can('student-delete')) {
                $operate .= BootstrapTableService::trashButton(route('student.trash', $row->user_id));
            }

            $student_gender = $row->user->gender;
            $guardian_gender = $row->guardian->gender;
            $row->user->gender = trans(strtolower($row->user->gender));
            $row->guardian->gender = trans(strtolower($row->guardian->gender));
            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $tempRow['eng_student_gender'] = $student_gender;
            $tempRow['eng_guardian_gender'] = $guardian_gender;
            $tempRow['extra_fields'] = $row->user->extra_student_details;
            foreach ($row->user->extra_student_details as $key => $field) {
                $data = '';
                if ($field->form_field->type == 'checkbox') {
                    $data = json_decode($field->data);
                } else if($field->form_field->type == 'file') {
                    $data = '<a href="'.Storage::url($field->data).'" target="_blank">DOC</a>';
                } else if($field->form_field->type == 'dropdown') {
                    $data = $field->form_field->default_values;
                    $data = $field->data ?? '';
                } else {
                    $data = $field->data;
                }
                $tempRow[$field->form_field->name] = $data;
            }
            
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function updateBulkApplicationStatus(Request $request)
    {
        ResponseService::noPermissionThenRedirect('student-create');
        $request->validate([
            'class_section_id' => $request->application_status == '0' ? 'nullable' : 'required'
        ],[
            'class_section_id' => 'The assign class section field is required'
        ]);
        try {
            $userService = app(UserService::class);
            DB::beginTransaction();
            foreach (json_decode($request->ids, false, 512, JSON_THROW_ON_ERROR) as $key => $userId) {
                $user = $this->user->findTrashedById($userId);
                $student = $this->student->builder()->where('user_id', $userId)->first();
                if ($user->status == 0) {
                    $subscription = $this->subscriptionService->active_subscription(Auth::user()->school_id);
                    // If prepaid plan check student limit
                    if ($subscription && $subscription->package_type == 0) {
                        $status = $this->subscriptionService->check_user_limit($subscription,"Students");
                        
                        if (!$status) {
                            ResponseService::errorResponse('You reach out limits');
                        }
                    }
                }
                if($request->application_status == 1)
                {
                    $this->student->builder()->where('user_id', $userId)->withTrashed()->update(['application_status' => 1, 'class_section_id' => $request->class_section_id]);
                    $password = str_replace('-', '', date('d-m-Y', strtotime($user->dob)));
                    $guardian = $this->user->guardian()->where('id', $student->guardian_id)->firstOrFail();
                    $userService->sendRegistrationEmail($guardian, $user, $student->admission_no, $password);
                }
                else{
                    $this->student->builder()->where('user_id', $userId)->withTrashed()->update(['application_status' => 0, 'class_section_id' => $request->class_section_id]);
                    $guardian = $this->user->guardian()->where('id', $student->guardian_id)->firstOrFail();
                    $class = $this->classSchool->builder()->where('id', $student->class_id)->with('medium','stream')->first();
                    $class_name = $class->full_name;
                   
                    $userService->sendApplicationRejectEmail($user,  $class_name, $guardian);
                    
                }
            }
            DB::commit();
            ResponseService::successResponse("Status Updated Successfully");
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e);
            ResponseService::errorResponse();
        }
    }

    public function updateApplicationStatus(Request $request)
    {
        ResponseService::noPermissionThenRedirect('student-create');

        $request->validate([
            'first_name'        => 'required|string',
            'last_name'         => 'required|string',
            'dob'               => 'required',
            'gender'            => 'required|in:male,female',
            'class_section_id'  => 'required_if:application_status,1',
            'image'             => 'nullable|mimes:jpeg,png,jpg,svg|image|max:2048',
            'aadhar_pic'        => 'nullable|mimes:jpeg,png,jpg,svg,pdf|max:2048',
        ],[
            'class_section_id.required_if' => 'The class section field is required when application status is accepted.'
        ]);

        try {
            $userService = app(UserService::class);
            DB::beginTransaction();
            
            $user = $this->user->findTrashedById($request->edit_user_id);
            $student = $this->student->builder()->where('user_id', $request->edit_user_id)->first();
            if ($user->status == 0) {
                $subscription = $this->subscriptionService->active_subscription(Auth::user()->school_id);
                // If prepaid plan check student limit
                if ($subscription && $subscription->package_type == 0) {
                    $status = $this->subscriptionService->check_user_limit($subscription,"Students");
                    
                    if (!$status) {
                        ResponseService::errorResponse('You reach out limits');
                    }
                }
            }

            // Update student user details
            $userService->updateStudentUser(
                $request->edit_user_id,
                $request->first_name,
                $request->last_name,
                $user->mobile,
                $request->dob,
                $request->gender,
                $request->image,
                $request->session_year_id,
                $request->extra_fields ?? [],
                $student->guardian_id,
                $user->current_address,
                $user->permanent_address,
                $request->reset_password,
                $request->class_section_id,
                $student->pen_no,
                $request->aadhar_pic
            );

            // Fetch updated models
            $user = $this->user->findTrashedById($request->edit_user_id);
            $student = $this->student->builder()->where('user_id', $request->edit_user_id)->first();

            if($request->application_status == 1)
            {
                $this->student->builder()->where('user_id', $request->edit_user_id)->withTrashed()->update(['application_status' => 1, 'class_section_id' => $request->class_section_id]);
                $password = str_replace('-', '', date('d-m-Y', strtotime($user->dob)));
                $guardian = $this->user->guardian()->where('id', $student->guardian_id)->firstOrFail();
                $userService->sendRegistrationEmail($guardian, $user, $student->admission_no, $password);
                $userService->sendRegistrationWhatsApp($guardian, $user, $student->admission_no, $password);
            }
            else{
                $this->student->builder()->where('user_id', $request->edit_user_id)->withTrashed()->update(['application_status' => 0]);
                $guardian = $this->user->guardian()->where('id', $student->guardian_id)->firstOrFail();
                $userService->sendApplicationRejectEmail($user, $student, $guardian);
            }
            DB::commit();
            ResponseService::successResponse("Status Updated Successfully");
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e);
            ResponseService::errorResponse();
        }
    }

    public function getclassSectionByClass($class_id)
    {
        try {
            $class_sections = $this->classSection->builder()->where('class_id',$class_id)->with('class', 'class.stream', 'section', 'medium')->get();
            ResponseService::successResponse('Data Fetched Successfully', $class_sections);
        } catch (Throwable $e) {
            
                ResponseService::logErrorResponse($e, "Student Controller -> getclassSectionByClass method");
                ResponseService::errorResponse();
        }
    }
}
