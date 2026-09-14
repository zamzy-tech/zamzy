<?php

namespace App\Services;

use App\Repositories\ExtraFormField\ExtraFormFieldsInterface;
use App\Repositories\Student\StudentInterface;
use App\Repositories\User\UserInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;
use Throwable;

class UserService {
    private UserInterface $user;
    private StudentInterface $student;
    private ExtraFormFieldsInterface $extraFormFields;

    public function __construct(UserInterface $user, StudentInterface $student, ExtraFormFieldsInterface $extraFormFields) {
        $this->user = $user;
        $this->student = $student;
        $this->extraFormFields = $extraFormFields;
    }

    /**
     * @param $mobile
     * @return string
     */
    public function makeParentPassword($mobile) {
        return !empty($mobile) ? $mobile : '12345678';
    }

    /**
     * @param $dob
     * @return string
     */
    public function makeStudentPassword($dob) {
        return str_replace('-', '', date('d-m-Y', strtotime($dob)));
    }

    /**
     * @param $first_name
     * @param $last_name
     * @param $email
     * @param $mobile
     * @param $gender
     * @param null $image
     * @return Model|null
     */
    public function createOrUpdateParent($first_name, $last_name, $email, $mobile, $gender, $image = null, $reset_password = null, $relationship = 'guardian', $aadhar_pic = null, $mother_name = null, $mother_mobile = null, $mother_image = null, $mother_aadhar_pic = null) {
        $password = $this->makeParentPassword($mobile);

        $parent = array(
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'mobile'     => $mobile,
            'gender'     => $gender,
            'occupation' => $relationship,
            'school_id'  => Auth::user()->school_id
        );

        if ($relationship === 'father_mother') {
            $parent['mother_name'] = $mother_name;
            $parent['mother_mobile'] = $mother_mobile;
            if (!empty($mother_image)) {
                $parent['mother_image'] = UploadService::upload($mother_image, 'guardian');
            }
            if (!empty($mother_aadhar_pic)) {
                $parent['mother_aadhar_pic'] = UploadService::upload($mother_aadhar_pic, 'guardian');
            }
        } else {
            $parent['mother_name'] = null;
            $parent['mother_mobile'] = null;
            $parent['mother_image'] = null;
            $parent['mother_aadhar_pic'] = null;
        }

        //NOTE : This line will return the old values if the user is already exists
        $user = null;
        if (!empty($email)) {
            $user = $this->user->guardian()->where('email', $email)->first();
        } else if (!empty($mobile)) {
            $user = $this->user->guardian()->where('mobile', $mobile)->first();
        }
        
        if (!empty($image)) {
            $parent['image'] = UploadService::upload($image, 'guardian');
        }
        if (!empty($aadhar_pic)) {
            $parent['aadhar_pic'] = UploadService::upload($aadhar_pic, 'guardian');
        }
        if (!empty($user)) {
            if (isset($parent['image'])) {
                if ($user->getRawOriginal('image') && Storage::disk('public')->exists($user->getRawOriginal('image'))) {
                    Storage::disk('public')->delete($user->getRawOriginal('image'));
                }
            }
            if (isset($parent['aadhar_pic'])) {
                if ($user->getRawOriginal('aadhar_pic') && Storage::disk('public')->exists($user->getRawOriginal('aadhar_pic'))) {
                    Storage::disk('public')->delete($user->getRawOriginal('aadhar_pic'));
                }
            }
            if ($relationship === 'father_mother') {
                if (isset($parent['mother_image'])) {
                    if ($user->getRawOriginal('mother_image') && Storage::disk('public')->exists($user->getRawOriginal('mother_image'))) {
                        Storage::disk('public')->delete($user->getRawOriginal('mother_image'));
                    }
                }
                if (isset($parent['mother_aadhar_pic'])) {
                    if ($user->getRawOriginal('mother_aadhar_pic') && Storage::disk('public')->exists($user->getRawOriginal('mother_aadhar_pic'))) {
                        Storage::disk('public')->delete($user->getRawOriginal('mother_aadhar_pic'));
                    }
                }
            } else {
                if ($user->getRawOriginal('mother_image') && Storage::disk('public')->exists($user->getRawOriginal('mother_image'))) {
                    Storage::disk('public')->delete($user->getRawOriginal('mother_image'));
                }
                if ($user->getRawOriginal('mother_aadhar_pic') && Storage::disk('public')->exists($user->getRawOriginal('mother_aadhar_pic'))) {
                    Storage::disk('public')->delete($user->getRawOriginal('mother_aadhar_pic'));
                }
            }
            if ($reset_password) {
                $parent['password'] = Hash::make($password);
            }
            if (!empty($email) && empty($user->email)) {
                $parent['email'] = $email;
            }
            $user->assignRole('Guardian');
            
            $user->update($parent);
        } else {
            $parent['password'] = Hash::make($password);
            $parent['email'] = !empty($email) ? $email : 'guardian_' . ($mobile ?? uniqid()) . '@placeholder.local';
            $user = $this->user->create($parent);
            $user->assignRole('Guardian');
        }

        return $user;
    }

    /**
     * @param string $first_name
     * @param string $last_name
     * @param string $admission_no
     * @param string|null $mobile
     * @param string $dob
     * @param string $gender
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile|null $image
     * @param int $classSectionID
     * @param string $admissionDate
     * @param null $current_address
     * @param null $permanent_address
     * @param int $sessionYearID
     * @param int $guardianID
     * @param array $extraFields
     * @param int $status
     * @return Model|null
     * @throws JsonException
     * @throws Throwable
     */

    public function createStudentUser(string $first_name, string $last_name, string $admission_no, string|null $mobile, string $dob, string $gender, \Symfony\Component\HttpFoundation\File\UploadedFile|null $image, int $classSectionID, string $admissionDate, $current_address = null, $permanent_address = null, int $sessionYearID, int $guardianID, array $extraFields = [], int $status, $is_send_notification = null, string|null $pen_no = null, \Symfony\Component\HttpFoundation\File\UploadedFile|null $aadhar_pic = null, int $apply_class_fee = 1, int $apply_van_fee = 0, int $apply_admission_fee = 0, string|null $payment_package = null, float|null $previous_year_balance = 0.00) {
        $password = $this->makeStudentPassword($dob);
        //Create Student User First
        $user = $this->user->create([
            'first_name'        => $first_name,
            'last_name'         => $last_name,
            'email'             => $admission_no,
            'mobile'            => $mobile,
            'dob'               => date('Y-m-d', strtotime($dob)),
            'gender'            => $gender,
            'password'          => Hash::make($password),
            'school_id'         => Auth::user()->school_id,
            'image'             => $image,
            'status'            => $status,
            'current_address'   => $current_address,
            'permanent_address' => $permanent_address,
            'deleted_at'        => $status == 1 ? null : '1970-01-01 01:00:00'
        ]);
        $user->assignRole('Student');

        $roll_number_db = $this->student->builder()->select(DB::raw('max(roll_number)'))->where('class_section_id', $classSectionID)->first();
        $roll_number_db = $roll_number_db['max(roll_number)'];
        $roll_number = $roll_number_db + 1;

        $student = $this->student->updateOrCreate( ['user_id' => $user->id] ,[
            'user_id'          => $user->id,
            'class_section_id' => $classSectionID,
            'admission_no'     => $admission_no,
            'roll_number'      => $roll_number,
            'admission_date'   => date('Y-m-d', strtotime($admissionDate)),
            'guardian_id'      => $guardianID,
            'session_year_id'  => $sessionYearID,
            'pen_no'           => $pen_no,
            'aadhar_pic'       => $aadhar_pic,
            'apply_class_fee'  => $apply_class_fee,
            'apply_van_fee'    => $apply_van_fee,
            'apply_admission_fee' => $apply_admission_fee,
            'payment_package'  => $payment_package,
            'previous_year_balance' => $previous_year_balance ?? 0.00
        ]);

        // Store Extra Details
        $extraDetails = array();
        foreach ($extraFields as $fields) {
            $data = null;
            if (isset($fields['data'])) {
                $data = (is_array($fields['data']) ? json_encode($fields['data'], JSON_THROW_ON_ERROR) : $fields['data']);
            }
            $extraDetails[] = array(
                'user_id'    => $student->user_id,
                'form_field_id' => $fields['form_field_id'],
                'data'          => $data,
            );
        }
        if (!empty($extraDetails)) {
            $this->extraFormFields->createBulk($extraDetails);
        }

        $guardian = $this->user->guardian()->where('id', $guardianID)->firstOrFail();
        $parentPassword = $this->makeParentPassword($guardian->mobile);
        if ($is_send_notification) {
            if (!empty($guardian->email)) {
                $this->sendRegistrationEmail($guardian, $user, $student->admission_no, $password);
            }
            
            // Send WhatsApp notification using template
            $schoolId = $user->school_id;
            if (!empty($schoolId) && !empty($guardian->mobile)) {
                $this->sendRegistrationWhatsApp($guardian, $user, $student->admission_no, $password);
            }
        }
        return $user;
    }

    /**
     * @param $userID
     * @param $first_name
     * @param $last_name
     * @param $mobile
     * @param $dob
     * @param $gender
     * @param $image
     * @param $sessionYearID
     * @param array $extraFields
     * @param null $guardianID
     * @param null $current_address
     * @param null $permanent_address
     * @return Model|null
     * @throws JsonException
     */
    public function updateStudentUser($userID, $first_name, $last_name, $mobile, $dob, $gender, $image, $sessionYearID, array $extraFields = [], $guardianID = null, $current_address = null, $permanent_address = null, $reset_password = null, $classSectionID, string|null $pen_no = null, \Symfony\Component\HttpFoundation\File\UploadedFile|null $aadhar_pic = null, int $apply_class_fee = 1, int $apply_van_fee = 0, int $apply_admission_fee = 0, string|null $payment_package = null, string|null $admission_no = null, float|null $previous_year_balance = 0.00) {
        $studentUserData = array(
            'first_name'        => $first_name,
            'last_name'         => $last_name,
            'mobile'            => $mobile,
            'dob'               => date('Y-m-d', strtotime($dob)),
            'current_address'   => $current_address,
            'permanent_address' => $permanent_address,
            'gender'            => $gender,
        );

        if (!empty($current_address)) {
            $studentUserData['current_address'] = $current_address;
        }

        if (!empty($permanent_address)) {
            $studentUserData['permanent_address'] = $permanent_address;
        }

        if (isset($reset_password)) {
            $studentUserData['password'] = Hash::make($this->makeStudentPassword($dob));
        }


        if ($image) {
            $studentUserData['image'] = $image;
        }

        if ($admission_no) {
            $studentUserData['email'] = $admission_no;
        }

        //Create Student User First
        $user = $this->user->update($userID, $studentUserData);

        $studentData = array(
            'guardian_id'     => $guardianID,
            'session_year_id' => $sessionYearID,
            'class_section_id' => $classSectionID,
            'pen_no'          => $pen_no,
            'apply_class_fee' => $apply_class_fee,
            'apply_van_fee'   => $apply_van_fee,
            'apply_admission_fee' => $apply_admission_fee,
            'payment_package'  => $payment_package,
            'previous_year_balance' => $previous_year_balance ?? 0.00,
        );

        if ($admission_no) {
            $studentData['admission_no'] = $admission_no;
        }

        if ($aadhar_pic) {
            $studentData['aadhar_pic'] = $aadhar_pic;
        }

        $student = $this->student->update($user->student->id, $studentData);
        $extraDetails = [];
        foreach ($extraFields as $fields) {
            if ($fields['input_type'] == 'file') {
                if (isset($fields['data']) && $fields['data'] instanceof UploadedFile) {
                    $extraDetails[] = array(
                        'id'            => $fields['id'],
                        'user_id'    => $student->user_id,
                        'form_field_id' => $fields['form_field_id'],
                        'data'          => $fields['data']
                    );
                }
            } else {
                $data = null;
                if (isset($fields['data'])) {
                    $data = (is_array($fields['data']) ? json_encode($fields['data'], JSON_THROW_ON_ERROR) : $fields['data']);
                }
                $extraDetails[] = array(
                    'id'            => $fields['id'],
                    'user_id'    => $student->user_id,
                    'form_field_id' => $fields['form_field_id'],
                    'data'          => $data,
                );
            }
        }
        $this->extraFormFields->upsert($extraDetails, ['id'], ['data']);
        $user->assignRole('Student');
        DB::commit();
        return $user;
    }

    /**
     * @param $email
     * @param $name
     * @param $plainTextPassword
     * @param $childName
     * @param $childAdmissionNumber
     * @param $childPlainTextPassword
     * @return void
     * @throws Throwable
     */
    public function sendRegistrationEmail($guardian, $child, $childAdmissionNumber, $childPlainTextPassword) {
        try {
            $school_name = Auth::user()->school->name;

            $email_body = $this->replacePlaceholders($guardian, $child, $childAdmissionNumber, $childPlainTextPassword);
            $data = [
                'subject'                => 'Admission Application Approved - Welcome to ' . $school_name,
                'email'                  => $guardian->email,
                'email_body'             => $email_body
            ];

            Mail::send('students.email', $data, static function ($message) use ($data) {
                $message->to($data['email'])->subject($data['subject']);
            });
        } catch (\Throwable $th) {
            if (Str::contains($th->getMessage(), ['Failed', 'Mail', 'Mailer', 'MailManager'])) {
                ResponseService::warningResponse("Message send successfully. But Email not sent.");
            } else {
                ResponseService::errorResponse(trans('error_occured'));
            }
        }

    }

    public function sendRegistrationWhatsApp($guardian, $child, $childAdmissionNumber, $childPlainTextPassword) {
        try {
            $schoolId = $child->school_id;
            if (empty($schoolId)) {
                return;
            }
            
            // Format the message
            $cache = app(CachingService::class);
            $schoolSettings = $cache->getSchoolSettings('*', $schoolId);
            $school_name = $schoolSettings['school_name'] ?? '';
            $parentPassword = $this->makeParentPassword($guardian->mobile);
            $login_url = url('/');
            $app_link = "https://school.tehub.in/downloads/Tehub_School_Parent.apk";
            
            $template = $schoolSettings['whatsapp-template-parent'] ?? null;
            if (!empty($template)) {
                $template = htmlspecialchars_decode($template);
                $whatsappMsg = str_replace([
                    '{school_name}',
                    '{parent_name}',
                    '{child_name}',
                    '{url}',
                    '{parent_mobile}',
                    '{parent_password}',
                    '{admission_no}',
                    '{child_password}',
                    '{app_link}'
                ], [
                    $school_name,
                    $guardian->full_name,
                    $child->full_name,
                    $login_url,
                    $guardian->mobile,
                    $parentPassword,
                    $childAdmissionNumber,
                    $childPlainTextPassword,
                    $app_link
                ], $template);
            } else {
                $whatsappMsg = "*Admission Approved - Welcome to " . $school_name . "*\n\n" .
                               "Dear " . $guardian->full_name . ",\n" .
                               "Your child " . $child->full_name . "'s admission application has been approved.\n\n" .
                               "*Parent Login Details:*\n" .
                               "Login Link: " . $login_url . "\n" .
                               "Username/Mobile: " . $guardian->mobile . "\n" .
                               "Password: " . $parentPassword . "\n\n" .
                               "*Student Login Details:*\n" .
                               "Username/Admission No: " . $childAdmissionNumber . "\n" .
                               "Password: " . $childPlainTextPassword . "\n\n" .
                               "*Download App:* " . $app_link . "\n\n" .
                               "Thank you,\n" .
                               $school_name;
            }
            
            // Send via cURL to tehub WhatsApp backend
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://sms.tehub.in/api/campaign/send-single');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'userId' => 'school_' . $schoolId,
                'phone' => $guardian->mobile,
                'message' => $whatsappMsg
            ]));

            $res = curl_exec($ch);
            if ($res === false) {
                \Illuminate\Support\Facades\Log::error("WhatsApp registration send failed for school " . $schoolId . ", phone " . $guardian->mobile . ". Error: " . curl_error($ch));
            } else {
                $resDecoded = json_decode($res, true);
                if (isset($resDecoded['error'])) {
                    \Illuminate\Support\Facades\Log::error("WhatsApp registration API returned error: " . $resDecoded['error']);
                }
            }
            curl_close($ch);
        } catch (\Throwable $th) {
            \Illuminate\Support\Facades\Log::error("WhatsApp Registration Notification error: " . $th->getMessage());
        }
    }

    public function sendStaffRegistrationWhatsApp($user, $plainTextPassword) {
        try {
            $schoolId = $user->school_id;
            if (empty($schoolId)) {
                return;
            }
            
            // Format the message
            $cache = app(CachingService::class);
            $schoolSettings = $cache->getSchoolSettings('*', $schoolId);
            $school_name = $schoolSettings['school_name'] ?? '';
            $login_url = url('/');
            $app_link = "https://school.tehub.in/downloads/Tehub_School_Staff.apk";
            
            $template = $schoolSettings['whatsapp-template-staff'] ?? null;
            if (!empty($template)) {
                $template = htmlspecialchars_decode($template);
                $whatsappMsg = str_replace([
                    '{school_name}',
                    '{full_name}',
                    '{url}',
                    '{email}',
                    '{password}',
                    '{app_link}'
                ], [
                    $school_name,
                    $user->full_name,
                    $login_url,
                    $user->email,
                    $plainTextPassword,
                    $app_link
                ], $template);
            } else {
                $whatsappMsg = "*Welcome to " . $school_name . "*\n\n" .
                               "Dear " . $user->full_name . ",\n" .
                               "Your account has been created successfully.\n\n" .
                               "*Login Details:*\n" .
                               "URL: " . $login_url . "\n" .
                               "Username: " . $user->email . "\n" .
                               "Password: " . $plainTextPassword . "\n\n" .
                               "*Download App:* " . $app_link . "\n\n" .
                               "Thank you,\n" .
                               $school_name;
            }
            
            // Send via cURL to tehub WhatsApp backend
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://sms.tehub.in/api/campaign/send-single');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'userId' => 'school_' . $schoolId,
                'phone' => $user->mobile,
                'message' => $whatsappMsg
            ]));

            $res = curl_exec($ch);
            if ($res === false) {
                \Illuminate\Support\Facades\Log::error("WhatsApp staff registration send failed for school " . $schoolId . ", phone " . $user->mobile . ". Error: " . curl_error($ch));
            } else {
                $resDecoded = json_decode($res, true);
                if (isset($resDecoded['error'])) {
                    \Illuminate\Support\Facades\Log::error("WhatsApp staff registration API returned error: " . $resDecoded['error']);
                }
            }
            curl_close($ch);
        } catch (\Throwable $th) {
            \Illuminate\Support\Facades\Log::error("WhatsApp Staff Registration Notification error: " . $th->getMessage());
        }
    }

    private function replacePlaceholders($guardian, $child, $childAdmissionNumber, $childPlainTextPassword)
    {

        $cache = app(CachingService::class);
        $schoolSettings = $cache->getSchoolSettings();
        $systemSettings = $cache->getSystemSettings();

        $templateContent = $schoolSettings['email-template-parent'] ?? '';
        // Define the placeholders and their replacements
        $placeholders = [
            '{parent_name}' => $guardian->full_name,
            '{code}' => Auth::user()->school->code,
            '{email}' => $guardian->email,
            '{password}' => $guardian->mobile,
            '{school_name}' => $schoolSettings['school_name'],

            '{child_name}' => $child->full_name,
            '{grno}' => $child->email,
            '{child_password}' => $childPlainTextPassword,
            '{admission_no}' => $childAdmissionNumber,

            '{support_email}' => $schoolSettings['school_email'] ?? '',
            '{support_contact}' => $schoolSettings['school_phone'] ?? '',

            '{android_app}' => $systemSettings['app_link'] ?? '',
            '{ios_app}' => $systemSettings['ios_app_link'] ?? '',

            // Add more placeholders as needed
        ];

        // Replace the placeholders in the template content
        foreach ($placeholders as $placeholder => $replacement) {
            $templateContent = str_replace($placeholder, $replacement, $templateContent);
        }

        return $templateContent;
    }

    public function sendStaffRegistrationEmail($user, $password)
    {
        try {
            $cache = app(CachingService::class);
            $schoolSettings = $cache->getSchoolSettings();
            $email_body = $this->replaceStaffPlaceholders($user, $password, $schoolSettings);
            $data = [
                'subject'     => 'Welcome to ' . $schoolSettings['school_name'],
                'email'       => $user->email,
                'email_body'  => $email_body
            ];

            Mail::send('teacher.email', $data, static function ($message) use ($data) {
                $message->to($data['email'])->subject($data['subject']);
            });
        } catch (\Throwable $th) {
            if (Str::contains($th->getMessage(), ['Failed', 'Mail', 'Mailer', 'MailManager'])) {
                ResponseService::warningResponse("Message send successfully. But Email not sent.");
            } else {
                ResponseService::errorResponse(trans('error_occured'));
            }
        }
    }

    private function replaceStaffPlaceholders($user, $password, $schoolSettings)
    {

        $cache = app(CachingService::class);
        $systemSettings = $cache->getSystemSettings();

        $templateContent = $schoolSettings['email-template-staff'] ?? '';
        // Define the placeholders and their replacements
        $placeholders = [
            '{full_name}' => $user->full_name,
            '{code}' => Auth::user()->school->code,
            '{email}' => $user->email,
            '{password}' => $password,
            '{school_name}' => $schoolSettings['school_name'],
            
            '{support_email}' => $schoolSettings['school_email'] ?? '',
            '{support_contact}' => $schoolSettings['school_phone'] ?? '',

            '{url}' => url('/'),

            '{android_app}' => $systemSettings['app_link'] ?? '',
            '{ios_app}' => $systemSettings['ios_app_link'] ?? '',

            // Add more placeholders as needed
        ];

        // Replace the placeholders in the template content
        foreach ($placeholders as $placeholder => $replacement) {
            $templateContent = str_replace($placeholder, $replacement, $templateContent);
        }

        return $templateContent;
    }

    public function sendApplicationRejectEmail($user, $class_name, $guardian)
    {
        if (empty($guardian->email)) {
            return;
        }
        try {
            $cache = app(CachingService::class);
            $schoolSettings = $cache->getSchoolSettings();
            $email_body = $this->replaceApplicationRejectPlaceholders($user, $class_name, $schoolSettings, $guardian);
            $data = [
                'subject'     => 'Admission Application Rejected - ' . $schoolSettings['school_name'],
                'email'       => $guardian->email,
                'email_body'  => $email_body
            ];

            Mail::send('students.email', $data, static function ($message) use ($data) {
                $message->to($data['email'])->subject($data['subject']);
            });
        } catch (\Throwable $th) {
            if (Str::contains($th->getMessage(), ['Failed', 'Mail', 'Mailer', 'MailManager'])) {
                ResponseService::warningResponse("Message send successfully. But Email not sent.");
            } else {
                ResponseService::errorResponse(trans('error_occured'));
            }
        }
    }

    private function replaceApplicationRejectPlaceholders($user, $class_name, $schoolSettings, $guardian)
    {
        $cache = app(CachingService::class);
        $systemSettings = $cache->getSystemSettings();

        $templateContent = $schoolSettings['email-template-application-reject'] ?? '';
        // Define the placeholders and their replacements
        $placeholders = [
            '{parent_name}' => $guardian->full_name,
            '{child_name}' => $user->full_name,
            '{school_name}' => $schoolSettings['school_name'],
            '{support_email}' => $schoolSettings['school_email'] ?? '',
            '{support_contact}' => $schoolSettings['school_phone'] ?? '',
            '{class}' => $class_name
            // Add more placeholders as needed
        ];

        // Replace the placeholders in the template content
        foreach ($placeholders as $placeholder => $replacement) {
            $templateContent = str_replace($placeholder, $replacement, $templateContent);
        }

        return $templateContent;
    }



    /* Backup Code for Student CreateOrUpdate
    public function createOrUpdateStudentUser($first_name, $last_name, $admission_no, $mobile, $dob, $gender, $image, $classSectionID, $admissionDate, array $extraFields = [], $rollNumber = null, $guardianID = null) {
        $password = $this->makeStudentPassword($dob);
        $userExists = $this->user->builder()->where('email', $admission_no)->first();
        if (!empty($rollNumber)) {
            $rollNumber = $this->student->builder()->select(DB::raw('max(roll_number)'))->where('class_section_id', $classSectionID)->first();
            $rollNumber = $rollNumber['max(roll_number)'];
            ++$rollNumber;
        }
        $studentUserData = array(
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'email'      => $admission_no,
            'mobile'     => $mobile,
            'dob'        => date('Y-m-d', strtotime($dob)),
            'gender'     => $gender,
        );

        $studentData = array(
            'class_section_id' => $classSectionID,
            'admission_no'     => $admission_no,
            'roll_number'      => $rollNumber,
            'guardian_id'      => $guardianID
        );


        if (!$userExists) {
            //Create Student User
            $studentUserData = array_merge($studentUserData, [
                'password'  => Hash::make($password),
                'school_id' => Auth::user()->school_id,
                'image'     => $image
            ]);
            $user = $this->user->create($studentUserData);
            $user->assignRole('Student');

            $sessionYear = $this->sessionYear->default();
            $studentData = array_merge($studentData, [
                'user_id'         => $user->id,
                'admission_date'  => date('Y-m-d', strtotime($admissionDate)),
                'session_year_id' => $sessionYear->id
            ]);
            $student = $this->student->create($studentData);

        } else {
            //Update Student User
            if ($image) {
                $studentUserData['image'] = $image;
            }
            $user = $this->user->update($userExists->id, $studentUserData);
            $student = $this->student->update($user->student->id, $studentData);
        }

        // UPSERT EXTRA FIELDS
        $extraDetails = [];
        foreach ($extraFields as $fields) {
            // IF form_field_typ is file, and it's value is empty then skip that array
            if ($fields['input_type'] == 'file' && !isset($fields['data'])) {
                continue;
            }
            $data = null;
            if (isset($fields['data'])) {
                $data = (is_array($fields['data']) ? json_encode($fields['data'], JSON_THROW_ON_ERROR) : $fields['data']);
            }
            $extraDetails[] = array(
                'id'            => $fields['id'] ?? null,
                'student_id'    => $student->id,
                'form_field_id' => $fields['form_field_id'],
                'data'          => $data,
            );
        }

        $this->extraFormFields->upsert($extraDetails, ['student_id', 'form_field_id'], ['data']);
        DB::commit();

        if (!$userExists) {
            // Send Registration Email only if user is new. Already Existing user's parent will not receive email

                $guardian = $this->user->findById($guardianID);
                $password = $this->makeParentPassword($first_name, $mobile);
                $this->sendRegistrationEmail($guardian->email, $guardian->full_name, $password, $user->full_name, $student->admission_no, $password);
        }
        return $user;
    }*/
}
