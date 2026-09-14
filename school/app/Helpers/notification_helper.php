<?php

use App\Models\User;
use App\Services\CachingService;
use Google\Client;

function send_notification($user, $title, $body, $type, $customData = []) {
    // Send WhatsApp notification if enabled per-school
    try {
        $usersData = User::whereIn('id', $user)->where('mobile', '!=', '')->get();
        $usersBySchool = $usersData->groupBy('school_id');

        foreach ($usersBySchool as $schoolId => $schoolUsers) {
            if (empty($schoolId)) {
                continue;
            }

            // Check if WhatsApp notification is enabled for this school
            $whatsapp_status = app(CachingService::class)->getSchoolSettings('whatsapp_status', $schoolId);
            if ($whatsapp_status === '' || $whatsapp_status === null) {
                $whatsapp_status = 1;
            }
            if ($whatsapp_status == 1) {
                // Send WhatsApp messages
                foreach ($schoolUsers as $u) {
                    $phone = $u->mobile;
                    
                    // Format the message body
                    $whatsappMsg = $body;
                    
                    // Append PDF download links for Fees type notifications
                    if ($type === 'Fees') {
                        try {
                            $student = null;
                            if ($u->hasRole('Student')) {
                                $student = \App\Models\Students::where('user_id', $u->id)->first();
                            } elseif ($u->hasRole('Parent')) {
                                $student = $u->guardianRelationChild()->first();
                            } else {
                                $student = \App\Models\Students::where('user_id', $u->id)->first();
                                if (!$student) {
                                    $student = \App\Models\Students::where('guardian_id', $u->id)->first();
                                }
                            }

                            if ($student) {
                                $latestPayment = \App\Models\FeesPaid::where('student_id', $student->user_id)
                                    ->orderBy('id', 'desc')
                                    ->first();
                                if ($latestPayment) {
                                    $receiptUrl = \Illuminate\Support\Facades\URL::signedRoute('public.receipt.pdf', [
                                        'school_id' => $schoolId,
                                        'id' => $latestPayment->id
                                    ]);
                                    $statementUrl = \Illuminate\Support\Facades\URL::signedRoute('public.statement.pdf', [
                                        'school_id' => $schoolId,
                                        'student_id' => $student->user_id
                                    ]);

                                    $whatsappMsg .= "\n\n📄 *Download Receipt:* " . $receiptUrl;
                                }
                            }
                        } catch (\Throwable $ex) {
                            \Illuminate\Support\Facades\Log::error("Failed to append PDF links to WhatsApp notification: " . $ex->getMessage());
                        }
                    }

                    if (!empty($title) && $title !== $body) {
                        $whatsappMsg = "*" . $title . "*\n\n" . $whatsappMsg;
                    }

                    // Send via cURL
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://sms.tehub.in/api/campaign/send-single');
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                        'userId' => 'school_' . $schoolId,
                        'phone' => $phone,
                        'message' => $whatsappMsg
                    ]));

                    $res = curl_exec($ch);
                    
                    if ($res === false) {
                        \Illuminate\Support\Facades\Log::error("WhatsApp send failed for school " . $schoolId . ", phone " . $phone . ". Error: " . curl_error($ch));
                    } else {
                        $resDecoded = json_decode($res, true);
                        if (isset($resDecoded['error'])) {
                            \Illuminate\Support\Facades\Log::error("WhatsApp API returned error for school " . $schoolId . ", phone " . $phone . ": " . $resDecoded['error']);
                        }
                    }
                    curl_close($ch);
                }
            }
        }
    } catch (\Throwable $th) {
        \Illuminate\Support\Facades\Log::error("WhatsApp Notification helper error: " . $th->getMessage());
    }

    $FcmTokens = User::where('fcm_id', '!=', '')->whereIn('id', $user)->get()->pluck('fcm_id');

    $cache = app(CachingService::class);


    $project_id = $cache->getSystemSettings('firebase_project_id');
    $url = 'https://fcm.googleapis.com/v1/projects/' . $project_id . '/messages:send';

    $access_token = getAccessToken();
   
    foreach ($FcmTokens as $FcmToken) {

        $data = [
            "message" => [
                "token" => $FcmToken,
                "notification" => [
                    "title" => $title,
                    "body" => $body
                ],
                "data" => [
                    "title" => $title,
                    "body" => $body,
                    "type" => $type,
                    ...$customData
                ],
                "android" => [
                    "notification"=> [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        "sound" => "default"  // This is for Android sound
                    ],
                    "priority" => "high"
                   
                ],
                "apns" => [
                    "headers" => [
                        "apns-priority" => "10" // Set APNs priority to 10 (high) for immediate delivery
                    ],
                    "payload" => [
                        "aps" => [
                            "alert" => [
                                "title" => $title,
                                "body" => $body,
                            ],
                            "type" => $type,
                            ...$customData,
                            "sound" => "default",  // This is for iOS sound
                            "mutable-content" => 1, 
                            "content-available" => 1
                        ]
                    ]
                ]
            ]
        ];

        $encodedData = json_encode($data);
       
        $headers = [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);

        // Execute post
        $result = curl_exec($ch);
        // dd($result);
        if ($result == FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);
    }    
}

function getAccessToken()
{
    $cache = app(CachingService::class);

    $file_name = $cache->getSystemSettings('firebase_service_file');
    $data = explode("storage/", $file_name ?? '');
    $file_name = end($data);

    $file_path = base_path('public/storage/'. $file_name);

    $client = new Client();
    $client->setAuthConfig($file_path);
    $client->setScopes(['https://www.googleapis.com/auth/firebase.messaging']);
    $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

    return $accessToken;
}
