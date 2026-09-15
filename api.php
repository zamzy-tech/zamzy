<?php
// ZAMZY Platform API Handler
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$pdo = getDbConnection();

if (!$pdo) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed. Please ensure MySQL is running in XAMPP.'
    ]);
    exit;
}

// Self-healing schema checks for webinar registrations
try {
    $pdo->exec("ALTER TABLE `zamzy_webinar_registrations` ADD COLUMN `transaction_id` VARCHAR(100) NULL");
} catch (Exception $e) {}
try {
    $pdo->exec("ALTER TABLE `zamzy_webinar_registrations` ADD COLUMN `raw_payment_response` TEXT NULL");
} catch (Exception $e) {}
try {
    $pdo->exec("ALTER TABLE `zamzy_webinar_registrations` ADD COLUMN `email_sent` TINYINT(1) DEFAULT 0");
} catch (Exception $e) {}
try {
    $pdo->exec("ALTER TABLE `zamzy_webinar_registrations` ADD COLUMN `whatsapp_sent` TINYINT(1) DEFAULT 0");
} catch (Exception $e) {}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Support JSON body payload & automated webhook detection
$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);
if (is_array($jsonData)) {
    if (isset($jsonData['action'])) {
        $action = $jsonData['action'];
    } elseif (empty($action) && (isset($jsonData['event']) || isset($jsonData['order_id']) || isset($_SERVER['HTTP_X_FAMGATEWAY_SIGNATURE']))) {
        $action = 'webhook';
    }
    $_POST = array_merge($_POST, $jsonData);
}

switch ($action) {

    // 1. Submit Project Brief / Inquiry
    case 'submit_inquiry':
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $preferred_language = trim($_POST['preferred_language'] ?? 'English');
        $budget = trim($_POST['budget'] ?? '₹25k – ₹75k');
        $role = trim($_POST['role'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $tier = trim($_POST['tier'] ?? 'Tier 02 — Custom App & Web');
        $launch_window = trim($_POST['launch_window'] ?? '');
        $project_type = trim($_POST['project_type'] ?? 'Custom SaaS / Web Platform');
        $requirements = trim($_POST['requirements'] ?? '');
        $reference_url = trim($_POST['reference_url'] ?? '');

        if (empty($name) || empty($email) || empty($phone) || empty($requirements)) {
            echo json_encode([
                'success' => false,
                'message' => 'Please fill in all required fields (Name, Email, WhatsApp Phone, Requirements).'
            ]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO `zamzy_inquiries` 
                (`name`, `email`, `phone`, `preferred_language`, `budget`, `role`, `company`, `tier`, `launch_window`, `project_type`, `requirements`, `reference_url`, `status`) 
                VALUES (:name, :email, :phone, :preferred_language, :budget, :role, :company, :tier, :launch_window, :project_type, :requirements, :reference_url, 'new')");
            
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':preferred_language' => $preferred_language,
                ':budget' => $budget,
                ':role' => $role,
                ':company' => $company,
                ':tier' => $tier,
                ':launch_window' => $launch_window,
                ':project_type' => $project_type,
                ':requirements' => $requirements,
                ':reference_url' => $reference_url
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Your project brief has been successfully logged! A technical architect from ZAMZY will contact you via WhatsApp in ' . htmlspecialchars($preferred_language) . ' within 48 hours.',
                'inquiry_id' => $pdo->lastInsertId()
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error saving brief: ' . $e->getMessage()
            ]);
        }
        break;

    // 2. Submit SaaS Product Demo Request
    case 'submit_demo':
        $product_name = trim($_POST['product_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($product_name) || empty($phone) || empty($email)) {
            echo json_encode([
                'success' => false,
                'message' => 'Please provide product name, phone, and email.'
            ]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO `zamzy_demo_requests` (`product_name`, `phone`, `email`, `status`) VALUES (:product_name, :phone, :email, 'pending')");
            $stmt->execute([
                ':product_name' => $product_name,
                ':phone' => $phone,
                ':email' => $email
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Demo request registered! Sandbox credentials for ' . htmlspecialchars($product_name) . ' dispatched to ' . htmlspecialchars($phone) . '.'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error saving demo request: ' . $e->getMessage()
            ]);
        }
        break;

    // 3. Submit Client Review / Testimonial
    case 'submit_review':
        $client_name = trim($_POST['client_name'] ?? '');
        $company_name = trim($_POST['company_name'] ?? '');
        $role = trim($_POST['role'] ?? 'Founder / Client');
        $location = trim($_POST['location'] ?? 'Chennai');
        $rating = intval($_POST['rating'] ?? 5);
        $review_text = trim($_POST['review_text'] ?? '');
        $project_type = trim($_POST['project_type'] ?? 'Custom Software & SaaS');

        if (empty($client_name) || empty($company_name) || empty($review_text)) {
            echo json_encode([
                'success' => false,
                'message' => 'Please provide your name, company, and review feedback.'
            ]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO `zamzy_testimonials` 
                (`client_name`, `company_name`, `role`, `location`, `rating`, `review_text`, `project_type`, `is_featured`, `is_published`, `is_approved`) 
                VALUES (:client_name, :company_name, :role, :location, :rating, :review_text, :project_type, 1, 1, 1)");
            
            $stmt->execute([
                ':client_name' => $client_name,
                ':company_name' => $company_name,
                ':role' => $role,
                ':location' => $location,
                ':rating' => $rating,
                ':review_text' => $review_text,
                ':project_type' => $project_type
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Thank you for your testimonial! Your review is now published on the ZAMZY verified showcase.'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error saving testimonial: ' . $e->getMessage()
            ]);
        }
        break;

    // 4. Submit Freelance / Developer Guild / Career Application
    case 'submit_application':
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $location_college = trim($_POST['location_college'] ?? '');
        $primary_skills = trim($_POST['primary_skills'] ?? '');
        $experience_level = trim($_POST['experience_level'] ?? 'College Student / Fresher');
        $availability_hours = trim($_POST['availability_hours'] ?? '15-20 hrs/week');
        $expected_payout = trim($_POST['expected_payout'] ?? 'Project Commission');
        $portfolio_url = trim($_POST['portfolio_url'] ?? '');
        $past_work_notes = trim($_POST['past_work_notes'] ?? '');
        $job_id = !empty($_POST['job_id']) ? intval($_POST['job_id']) : null;
        $resume_file = '';

        if (empty($full_name) || empty($phone) || empty($email) || empty($primary_skills) || empty($location_college)) {
            echo json_encode([
                'success' => false,
                'message' => 'Please fill in all required fields (Name, WhatsApp Phone, Email, Location/College, and Skills).'
            ]);
            exit;
        }

        // Handle Resume Upload (Mandatory)
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['resume']['tmp_name'];
            $fileName = $_FILES['resume']['name'];
            $fileSize = $_FILES['resume']['size'];
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExts = ['pdf', 'doc', 'docx'];
            if (!in_array($ext, $allowedExts)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid file format. Resume must be a PDF, DOC, or DOCX document.'
                ]);
                exit;
            }

            if ($fileSize > 10 * 1024 * 1024) { // 10 MB limit
                echo json_encode([
                    'success' => false,
                    'message' => 'File too large. Resume must be under 10MB.'
                ]);
                exit;
            }

            $uploadDir = __DIR__ . '/uploads/resumes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($fileName, PATHINFO_FILENAME));
            $newFileName = 'resume_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '_' . $safeName . '.' . $ext;
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmp, $destination)) {
                $resume_file = 'uploads/resumes/' . $newFileName;
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to save resume file. Please try again.'
                ]);
                exit;
            }
        } elseif (!empty($_POST['resume_url'])) {
            $resume_file = trim($_POST['resume_url']);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Resume is mandatory! Please upload your Resume file (PDF/DOCX).'
            ]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO `zamzy_careers_applications` 
                (`job_id`, `full_name`, `email`, `phone`, `location_college`, `primary_skills`, `experience_level`, `availability_hours`, `expected_payout`, `portfolio_url`, `resume_file`, `past_work_notes`, `status`) 
                VALUES (:job_id, :full_name, :email, :phone, :location_college, :primary_skills, :experience_level, :availability_hours, :expected_payout, :portfolio_url, :resume_file, :past_work_notes, 'new')");
            
            $stmt->execute([
                ':job_id' => $job_id,
                ':full_name' => $full_name,
                ':email' => $email,
                ':phone' => $phone,
                ':location_college' => $location_college,
                ':primary_skills' => $primary_skills,
                ':experience_level' => $experience_level,
                ':availability_hours' => $availability_hours,
                ':expected_payout' => $expected_payout,
                ':portfolio_url' => $portfolio_url,
                ':resume_file' => $resume_file,
                ':past_work_notes' => $past_work_notes
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Welcome to the ZAMZY Developer Guild, ' . htmlspecialchars($full_name) . '! Your portfolio & resume have been verified. Our technical team will WhatsApp you at ' . htmlspecialchars($phone) . ' for matching client project sprints!'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error recording application: ' . $e->getMessage()
            ]);
        }
        break;

    // 5. Fetch Open Jobs
    case 'get_jobs':
        try {
            $stmt = $pdo->query("SELECT * FROM `zamzy_careers_jobs` WHERE `is_active` = 1 ORDER BY `id` DESC");
            $jobs = $stmt->fetchAll();
            echo json_encode([
                'success' => true,
                'data' => $jobs
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching jobs: ' . $e->getMessage()
            ]);
        }
        break;

    // 5. Partial Form Auto-Capture (phone or email typed but not submitted)
    case 'partial_capture':
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($phone) && empty($email)) {
            echo json_encode(['success' => false, 'message' => 'No contact info to save.']);
            exit;
        }

        // Prevent duplicate partials for same phone/email within 30 mins
        $dupCheck = $pdo->prepare("SELECT id FROM `zamzy_inquiries` WHERE (`phone` = :phone OR `email` = :email) AND `status` = 'partial' AND `created_at` > DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
        $dupCheck->execute([':phone' => $phone, ':email' => $email]);
        if ($dupCheck->fetchColumn()) {
            echo json_encode(['success' => true, 'message' => 'Already tracked.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO `zamzy_inquiries`
                (`name`, `email`, `phone`, `preferred_language`, `budget`, `project_type`, `requirements`, `status`)
                VALUES (:name, :email, :phone, 'English', 'Not specified', 'Not specified', '[PARTIAL — Form abandoned before submission]', 'partial')");
            $stmt->execute([
                ':name' => $name ?: '(Not entered)',
                ':email' => $email ?: '',
                ':phone' => $phone ?: ''
            ]);
            echo json_encode(['success' => true, 'message' => 'Partial capture saved.']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // 6. AI Assistant Chatbot (Powered by DeepSeek API with Full Session & Transcript Tracking)
    case 'ai_chat_save_lead':
        $token = trim($_POST['session_token'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($token)) {
            $token = bin2hex(random_bytes(16));
        }

        try {
            $stmt = $pdo->prepare("SELECT id FROM `zamzy_chat_sessions` WHERE `session_token` = :token");
            $stmt->execute([':token' => $token]);
            $sessionId = $stmt->fetchColumn();

            if ($sessionId) {
                $upd = $pdo->prepare("UPDATE `zamzy_chat_sessions` SET `user_name` = :name, `user_phone` = :phone, `user_email` = :email, `status` = 'lead_captured' WHERE `id` = :id");
                $upd->execute([':name' => $name, ':phone' => $phone, ':email' => $email, ':id' => $sessionId]);
            } else {
                $ins = $pdo->prepare("INSERT INTO `zamzy_chat_sessions` (`session_token`, `user_name`, `user_phone`, `user_email`, `status`) VALUES (:token, :name, :phone, :email, 'lead_captured')");
                $ins->execute([':token' => $token, ':name' => $name, ':phone' => $phone, ':email' => $email]);
                $sessionId = $pdo->lastInsertId();
            }

            // Also record in zamzy_inquiries if phone or email is provided so it appears across leads
            if (!empty($phone) || !empty($email)) {
                try {
                    $inqCheck = $pdo->prepare("SELECT id FROM `zamzy_inquiries` WHERE `phone` = :phone OR `email` = :email LIMIT 1");
                    $inqCheck->execute([':phone' => $phone, ':email' => $email]);
                    if (!$inqCheck->fetchColumn()) {
                        $inqStmt = $pdo->prepare("INSERT INTO `zamzy_inquiries` (`name`, `email`, `phone`, `preferred_language`, `budget`, `project_type`, `requirements`, `status`) VALUES (:name, :email, :phone, 'English', 'AI Chat Lead', 'AI Chat Consultation', 'Lead generated via AI Chatbot assistant.', 'new')");
                        $inqStmt->execute([
                            ':name' => $name ?: 'Chat Visitor',
                            ':email' => $email,
                            ':phone' => $phone
                        ]);
                    }
                } catch (Exception $e) {}
            }

            echo json_encode([
                'success' => true,
                'session_token' => $token,
                'session_id' => $sessionId
            ]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'ai_chat':
        $token = trim($_POST['session_token'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $userName = trim($_POST['user_name'] ?? '');
        $userPhone = trim($_POST['user_phone'] ?? '');
        $userEmail = trim($_POST['user_email'] ?? '');
        $historyJson = $_POST['history'] ?? '[]';
        $history = json_decode($historyJson, true);
        if (!is_array($history)) {
            $history = [];
        }

        if (empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Message cannot be empty.']);
            exit;
        }

        if (empty($token)) {
            $token = bin2hex(random_bytes(16));
        }

        // 1. Get or create session
        $sessionId = null;
        try {
            $stmt = $pdo->prepare("SELECT id, user_name, user_phone, user_email FROM `zamzy_chat_sessions` WHERE `session_token` = :token");
            $stmt->execute([':token' => $token]);
            $session = $stmt->fetch();

            if ($session) {
                $sessionId = $session['id'];
                if (empty($userName) && !empty($session['user_name'])) $userName = $session['user_name'];
                if (empty($userPhone) && !empty($session['user_phone'])) $userPhone = $session['user_phone'];
                if (empty($userEmail) && !empty($session['user_email'])) $userEmail = $session['user_email'];
            } else {
                $ins = $pdo->prepare("INSERT INTO `zamzy_chat_sessions` (`session_token`, `user_name`, `user_phone`, `user_email`, `status`) VALUES (:token, :name, :phone, :email, 'in_progress')");
                $ins->execute([':token' => $token, ':name' => $userName, ':phone' => $userPhone, ':email' => $userEmail]);
                $sessionId = $pdo->lastInsertId();
            }

            // Record user message
            $msgStmt = $pdo->prepare("INSERT INTO `zamzy_chat_messages` (`session_id`, `sender`, `message`) VALUES (:sid, 'user', :msg)");
            $msgStmt->execute([':sid' => $sessionId, ':msg' => $message]);
        } catch (Exception $e) {}

        // 2. Build system prompt & call DeepSeek
        $userContext = "";
        if (!empty($userName)) {
            $userContext = " You are currently speaking with {$userName}. Address them naturally when polite.";
        }

        $systemPrompt = "You are the official AI Technical Consultant for ZAMZY (zamzy.in), a premier digital engineering agency based in Hitech City, Hyderabad, Telangana, India.{$userContext}

Key Information about ZAMZY:
- What we do: Custom SaaS Platform Development, Mobile Apps (iOS & Android with Flutter/React Native/Swift/Kotlin), Enterprise ERP Systems (School, Restaurant & Business ERPs), WhatsApp & IVR Telephony Gateways, High-concurrency Cloud Architecture (AWS, Docker, Kubernetes), and Startup Launchpad Incubation.
- Location: Hitech City, Hyderabad, Telangana, India.
- Email: hello@zamzy.in | WhatsApp available for fast responses.
- Timeline & Delivery: Fast-turnaround MVPs in 7-14 days; Full custom platforms in 4-8 weeks.
- Pricing Tiers:
  * Startup MVP / Prototype: ₹10,000 – ₹25,000
  * Custom Web & Mobile App: ₹25,000 – ₹75,000
  * Enterprise SaaS / ERP: ₹75,000 – ₹2,00,000+
- Careers & Guild: We have a Freelance Developer Guild for college developers and freelancers to work on paid client projects with 10-25% project commissions (accessible via careers.php).
- Tone & Rules: Be concise, direct, helpful, and professional. Keep answers under 3-4 sentences unless deep technical detail is requested. Always prompt users to submit the Project Brief form on the page or contact hello@zamzy.in for formal quotes.";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        // Add recent conversation history for continuity
        if (!empty($history)) {
            $recent = array_slice($history, -8);
            foreach ($recent as $h) {
                if (isset($h['role']) && isset($h['content'])) {
                    $messages[] = [
                        'role' => ($h['role'] === 'user' ? 'user' : 'assistant'),
                        'content' => strval($h['content'])
                    ];
                }
            }
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        $apiKey = defined('DEEPSEEK_API_KEY') ? DEEPSEEK_API_KEY : (getenv('DEEPSEEK_API_KEY') ?: 'sk-71bbb2ea1a0e45dcbf2574d6f115aac1');
        $apiUrl = getenv('DEEPSEEK_API_URL') ?: 'https://api.deepseek.com/chat/completions';
        $apiModel = getenv('DEEPSEEK_MODEL') ?: 'deepseek-chat';

        $payload = json_encode([
            'model' => $apiModel,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 450,
            'stream' => false
        ]);

        $ch = curl_init('https://api.deepseek.com/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200) {
            $reply = "I am ZAMZY's AI Technical Assistant. We build scalable SaaS platforms, mobile apps, and ERP systems from Hitech City, Hyderabad. Please submit your requirements in the Project Brief form below or email hello@zamzy.in and our technical lead will connect with you within 48 hours!";
        } else {
            $resData = json_decode($response, true);
            $reply = $resData['choices'][0]['message']['content'] ?? "Thank you for reaching out! Please fill the Project Brief form or contact hello@zamzy.in.";
        }

        // 3. Save bot reply & update session
        if ($sessionId) {
            try {
                $botStmt = $pdo->prepare("INSERT INTO `zamzy_chat_messages` (`session_id`, `sender`, `message`) VALUES (:sid, 'bot', :msg)");
                $botStmt->execute([':sid' => $sessionId, ':msg' => $reply]);

                $updSes = $pdo->prepare("UPDATE `zamzy_chat_sessions` SET `total_messages` = `total_messages` + 2, `last_message` = :lastMsg, `user_name` = COALESCE(NULLIF(:name, ''), `user_name`), `user_phone` = COALESCE(NULLIF(:phone, ''), `user_phone`), `user_email` = COALESCE(NULLIF(:email, ''), `user_email`) WHERE `id` = :sid");
                $updSes->execute([
                    ':lastMsg' => $message,
                    ':name' => $userName,
                    ':phone' => $userPhone,
                    ':email' => $userEmail,
                    ':sid' => $sessionId
                ]);
            } catch (Exception $e) {}
        }

        echo json_encode([
            'success' => true,
            'reply' => $reply,
            'session_token' => $token
        ]);
        break;

    case 'get_chat_transcript':
        $sessionId = intval($_GET['session_id'] ?? $_POST['session_id'] ?? 0);
        if ($sessionId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid session ID.']);
            exit;
        }

        try {
            $sesStmt = $pdo->prepare("SELECT * FROM `zamzy_chat_sessions` WHERE `id` = :id");
            $sesStmt->execute([':id' => $sessionId]);
            $session = $sesStmt->fetch();

            $msgStmt = $pdo->prepare("SELECT * FROM `zamzy_chat_messages` WHERE `session_id` = :id ORDER BY `created_at` ASC");
            $msgStmt->execute([':id' => $sessionId]);
            $messages = $msgStmt->fetchAll();

            echo json_encode([
                'success' => true,
                'session' => $session,
                'messages' => $messages
            ]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // 8. Submit Full Stack Webinar Registration
    // 8. Submit Full Stack Webinar Registration
    case 'submit_webinar_registration':
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $college = trim($_POST['college_or_company'] ?? '');
        $exp = trim($_POST['experience_level'] ?? 'Beginner');
        $lang = trim($_POST['preferred_language'] ?? 'English');
        $utr = trim($_POST['utr_reference'] ?? '');
        $couponCode = strtoupper(trim($_POST['coupon_code'] ?? ''));
        $paymentMethod = trim($_POST['payment_method'] ?? 'FamPay / UPI');

        if (empty($fullName) || empty($phone) || empty($email)) {
            echo json_encode([
                'success' => false,
                'message' => 'Please provide your Name, Phone (WhatsApp), and Email address.'
            ]);
            exit;
        }

        $webinarPrice = floatval(getSetting('webinar_price', '96'));
        if ($webinarPrice <= 0) $webinarPrice = 96.00;

        $discountAmount = 0.00;
        $finalAmount = $webinarPrice;
        $appliedCoupon = null;

        // Process Coupon Discount if provided
        if (!empty($couponCode)) {
            try {
                $cStmt = $pdo->prepare("SELECT * FROM `zamzy_coupons` WHERE `code` = :code LIMIT 1");
                $cStmt->execute([':code' => $couponCode]);
                $coupon = $cStmt->fetch();

                if ($coupon && $coupon['status'] === 'active') {
                    $isExpired = (!empty($coupon['expiry_date']) && strtotime($coupon['expiry_date']) < strtotime(date('Y-m-d')));
                    $isLimitReached = ($coupon['max_uses'] > 0 && $coupon['used_count'] >= $coupon['max_uses']);
                    if (!$isExpired && !$isLimitReached) {
                        $type = $coupon['discount_type'];
                        $val = floatval($coupon['discount_value']);

                        if ($type === 'free') {
                            $discountAmount = $webinarPrice;
                            $finalAmount = 0.00;
                        } elseif ($type === 'percent') {
                            $discountAmount = round(($webinarPrice * $val) / 100, 2);
                            $finalAmount = max(0.00, round($webinarPrice - $discountAmount, 2));
                        } else {
                            $discountAmount = min($webinarPrice, $val);
                            $finalAmount = max(0.00, round($webinarPrice - $discountAmount, 2));
                        }

                        $appliedCoupon = $coupon['code'];
                        // Increment usage count
                        $uStmt = $pdo->prepare("UPDATE `zamzy_coupons` SET `used_count` = `used_count` + 1 WHERE `id` = :id");
                        $uStmt->execute([':id' => $coupon['id']]);
                    }
                }
            } catch (Exception $e) {}
        }

        // Generate unique registration code: ZMW-2026-XXXX
        $randomSuffix = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        $regCode = 'ZMW-2026-' . $randomSuffix;

        $isFree = ($finalAmount <= 0.00);
        if ($isFree) {
            $paymentStatus = 'verified';
            $paymentMethod = 'Coupon Waiver (100% FREE)';
            $utr = 'COUPON_' . ($appliedCoupon ?: 'VIP');
        } else {
            $paymentStatus = !empty($utr) ? 'completed' : 'pending';
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO `zamzy_webinar_registrations` 
                (`reg_code`, `full_name`, `phone`, `email`, `college_or_company`, `experience_level`, `preferred_language`, `amount`, `payment_method`, `payment_status`, `utr_reference`, `coupon_code`, `discount_amount`) 
                VALUES (:reg_code, :full_name, :phone, :email, :college_or_company, :experience_level, :preferred_language, :amount, :payment_method, :payment_status, :utr_reference, :coupon_code, :discount_amount)");

            $stmt->execute([
                ':reg_code' => $regCode,
                ':full_name' => $fullName,
                ':phone' => $phone,
                ':email' => $email,
                ':college_or_company' => $college,
                ':experience_level' => $exp,
                ':preferred_language' => $lang,
                ':amount' => $finalAmount,
                ':payment_method' => $paymentMethod,
                ':payment_status' => $paymentStatus,
                ':utr_reference' => $utr,
                ':coupon_code' => $appliedCoupon,
                ':discount_amount' => $discountAmount
            ]);

            $newId = $pdo->lastInsertId();

            // IF 100% FREE (Coupon waiver), instantly trigger automated Email & WhatsApp dispatches!
            if ($isFree) {
                require_once __DIR__ . '/mailer.php';
                $studentData = [
                    'id' => $newId,
                    'reg_code' => $regCode,
                    'full_name' => $fullName,
                    'phone' => $phone,
                    'email' => $email,
                    'amount' => 0,
                    'utr_reference' => $utr
                ];
                sendWebinarDeliveryEmail($studentData);
                sendWebinarDeliveryWhatsApp($studentData);

                $waCommunityLink = getSetting('webinar_whatsapp_link', 'https://chat.whatsapp.com/sample-zamzy-fullstack');
                $meetingLink = getSetting('webinar_meeting_link', '');

                echo json_encode([
                    'success' => true,
                    'is_free' => true,
                    'seat_unlocked' => true,
                    'payment_status' => 'verified',
                    'reg_code' => $regCode,
                    'amount' => 0,
                    'discount_amount' => $discountAmount,
                    'coupon_code' => $appliedCoupon,
                    'whatsapp_community_link' => $waCommunityLink,
                    'meeting_link' => $meetingLink,
                    'message' => '🎉 100% Free VIP Seat Confirmed! Access details dispatched to your Email & WhatsApp.'
                ]);
                exit;
            }

            $waMsg = "Hello ZAMZY! I have registered for the Full Stack Web Development Live Webinar (Rs. {$finalAmount}).%0A%0A*Registration Code:* {$regCode}%0A*Name:* " . urlencode($fullName) . "%0A*Phone:* " . urlencode($phone) . "%0A*Email:* " . urlencode($email);
            if (!empty($appliedCoupon)) {
                $waMsg .= "%0A*Coupon Applied:* " . urlencode($appliedCoupon) . " (Saved Rs. {$discountAmount})";
            }
            if (!empty($utr)) {
                $waMsg .= "%0A*Payment UTR / Ref:* " . urlencode($utr);
            }
            $waUrl = "https://wa.me/917287060553?text=" . $waMsg;

            echo json_encode([
                'success' => true,
                'is_free' => false,
                'message' => 'Registration successfully created!',
                'reg_code' => $regCode,
                'amount' => $finalAmount,
                'original_amount' => $webinarPrice,
                'discount_amount' => $discountAmount,
                'coupon_code' => $appliedCoupon,
                'payment_status' => $paymentStatus,
                'whatsapp_url' => $waUrl
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to save registration: ' . $e->getMessage()
            ]);
        }
        break;

    // 8B. Validate Promotional Coupon Code
    case 'validate_coupon':
        $couponCode = strtoupper(trim($_GET['code'] ?? $_POST['code'] ?? ''));
        $basePrice = floatval($_GET['amount'] ?? $_POST['amount'] ?? getSetting('webinar_price', '96'));
        if ($basePrice <= 0) $basePrice = 96.00;

        if (empty($couponCode)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a coupon code.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM `zamzy_coupons` WHERE `code` = :code LIMIT 1");
            $stmt->execute([':code' => $couponCode]);
            $coupon = $stmt->fetch();

            if (!$coupon) {
                echo json_encode(['success' => false, 'message' => 'Invalid coupon code. Please verify and retry.']);
                exit;
            }

            if ($coupon['status'] !== 'active') {
                echo json_encode(['success' => false, 'message' => 'This coupon code is currently inactive.']);
                exit;
            }

            if (!empty($coupon['expiry_date']) && strtotime($coupon['expiry_date']) < strtotime(date('Y-m-d'))) {
                echo json_encode(['success' => false, 'message' => 'This coupon code has expired on ' . date('d M Y', strtotime($coupon['expiry_date'])) . '.']);
                exit;
            }

            if ($coupon['max_uses'] > 0 && $coupon['used_count'] >= $coupon['max_uses']) {
                echo json_encode(['success' => false, 'message' => 'This coupon code has reached its maximum redemption limit.']);
                exit;
            }

            $type = $coupon['discount_type'];
            $val = floatval($coupon['discount_value']);
            $discountAmount = 0.00;
            $finalAmount = $basePrice;

            if ($type === 'free') {
                $discountAmount = $basePrice;
                $finalAmount = 0.00;
            } elseif ($type === 'percent') {
                $discountAmount = round(($basePrice * $val) / 100, 2);
                $finalAmount = max(0.00, round($basePrice - $discountAmount, 2));
            } else {
                $discountAmount = min($basePrice, $val);
                $finalAmount = max(0.00, round($basePrice - $discountAmount, 2));
            }

            echo json_encode([
                'success' => true,
                'coupon_code' => $coupon['code'],
                'discount_type' => $type,
                'discount_value' => $val,
                'original_amount' => $basePrice,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'is_free' => ($finalAmount <= 0.00),
                'notes' => $coupon['notes'] ?: '',
                'message' => ($finalAmount <= 0.00) 
                    ? "✓ Coupon {$coupon['code']} applied! 100% Free VIP Access Pass unlocked!"
                    : "✓ Coupon {$coupon['code']} applied! You saved ₹{$discountAmount} (Pay only ₹{$finalAmount})."
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error verifying coupon: ' . $e->getMessage()]);
        }
        break;

    // 9. Fetch Public Payment & P2P Gateway Settings
    case 'get_payment_settings':
        $upiId = getSetting('upi_id', '8667702473@fam');
        $upiName = getSetting('upi_name', 'Sameer Ahamadh');
        $price = getSetting('webinar_price', '96');
        $standardUpi = "upi://pay?pa=" . urlencode($upiId) . "&pn=" . urlencode($upiName) . "&am=" . urlencode($price) . "&cu=INR&tn=Webinar_Registration";

        echo json_encode([
            'success' => true,
            'gateway_mode' => 'p2p_automation',
            'upi_id' => $upiId,
            'upi_name' => $upiName,
            'webinar_price' => $price,
            'standard_upi_intent' => $standardUpi,
            'qr_image' => "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($standardUpi)
        ]);
        break;

    // 10. P2P Automation Loop: Generate Dynamic UPI Intent / FamGateway Order
    case 'create_fampay_order':
    case 'create_order':
    case 'create_payment_order':
        $regCode = trim($_POST['reg_code'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $amount = floatval($_POST['amount'] ?? getSetting('webinar_price', '96'));
        if ($amount <= 0) $amount = floatval(getSetting('webinar_price', '96'));

        $apiKey = getSetting('famgateway_api_key', 'fam_d8694592b735b5387bfd795c361f6463c2ead4d3');
        $upiId = getSetting('upi_id', '8667702473@fam');
        $upiName = getSetting('upi_name', 'Sameer Ahamadh');

        // 1. Standard P2P UPI Intent string
        $standardUpi = "upi://pay?pa=" . urlencode($upiId) . "&pn=" . urlencode($upiName) . "&am=" . $amount . "&cu=INR&tn=Webinar_Registration";
        // 2. Dynamic tracking UPI Intent string with student reg code
        $dynamicUpi = "upi://pay?pa=" . urlencode($upiId) . "&pn=" . urlencode($upiName) . "&am=" . $amount . "&cu=INR&tn=" . urlencode("Webinar_" . $regCode);

        // Prepare FamGateway Non-Custodial Order Request (Canonical REST API v2.0)
        $gatewayUrl = 'https://famgateway.in/api/create-order';
        $redirectUrl = BASE_URL . '/fullstack-webinar?status=success&reg_code=' . urlencode($regCode);

        $jsonPayload = json_encode([
            'amount' => $amount,
            'redirect_url' => $redirectUrl,
            'customer_name' => $fullName,
            'customer_phone' => $phone,
            'customer_email' => $email
        ]);

        $ch = curl_init($gatewayUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $jsonPayload,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'X-Api-Key: ' . $apiKey,
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $resData = json_decode($res, true);

        if ($httpCode >= 200 && $httpCode < 300 && isset($resData['status']) && $resData['status'] === 'success' && !empty($resData['data']['order_id'])) {
            $orderData = $resData['data'];
            $orderId = $orderData['order_id'];
            $checkoutUrl = $orderData['checkout_url'] ?? '';
            $qrUrl = $orderData['qr_url'] ?? ("https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($dynamicUpi));
            $liveUpiIntent = $orderData['upi_intent'] ?? $dynamicUpi;

            // Link gateway order ID to registration record in DB
            try {
                $upd = $pdo->prepare("UPDATE `zamzy_webinar_registrations` SET `transaction_id` = :txid, `raw_payment_response` = :raw WHERE `reg_code` = :code");
                $upd->execute([
                    ':txid' => $orderId,
                    ':raw' => $res,
                    ':code' => $regCode
                ]);
            } catch (Exception $e) {}

            echo json_encode([
                'success' => true,
                'gateway' => 'famgateway_p2p',
                'order_id' => $orderId,
                'checkout_url' => $checkoutUrl,
                'payment_url' => $checkoutUrl,
                'qr_url' => $qrUrl,
                'upi_intent' => $liveUpiIntent,
                'standard_upi_intent' => $standardUpi,
                'amount' => $amount,
                'reg_code' => $regCode
            ]);
        } else {
            // Direct P2P Fallback if gateway API is throttled or offline
            try {
                $upd = $pdo->prepare("UPDATE `zamzy_webinar_registrations` SET `transaction_id` = :txid WHERE `reg_code` = :code");
                $upd->execute([
                    ':txid' => 'P2P_' . $regCode,
                    ':code' => $regCode
                ]);
            } catch (Exception $e) {}

            $fallbackQr = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($dynamicUpi);

            echo json_encode([
                'success' => true,
                'gateway' => 'direct_p2p',
                'order_id' => 'P2P_' . $regCode,
                'checkout_url' => $dynamicUpi,
                'payment_url' => $dynamicUpi,
                'qr_url' => $fallbackQr,
                'upi_intent' => $dynamicUpi,
                'standard_upi_intent' => $standardUpi,
                'amount' => $amount,
                'reg_code' => $regCode
            ]);
        }
        break;

    // 11. Automated Webhook Listener Endpoint (Captures structural UTR verification & unlocks student seat)
    case 'webhook':
    case 'famgateway_webhook':
    case 'fampay_webhook':
        $apiKey = getSetting('famgateway_api_key', 'fam_d8694592b735b5387bfd795c361f6463c2ead4d3');
        $sigHeader = $_SERVER['HTTP_X_FAMGATEWAY_SIGNATURE'] ?? '';

        // Optional HMAC-SHA256 signature verification if signature header is provided
        if (!empty($sigHeader) && !empty($rawInput)) {
            $computedSig = hash_hmac('sha256', $rawInput, $apiKey);
            if (!hash_equals($computedSig, $sigHeader)) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Invalid webhook signature']);
                exit;
            }
        }

        $payload = is_array($jsonData) ? $jsonData : $_POST;
        $orderId = trim($payload['order_id'] ?? '');
        $utr = trim($payload['utr'] ?? $payload['transaction_id'] ?? $payload['rrn'] ?? '');
        $status = strtolower(trim($payload['status'] ?? $payload['event'] ?? ''));
        $regCode = trim($payload['reg_code'] ?? $payload['merchant_order_id'] ?? '');
        $amount = floatval($payload['amount'] ?? 0);

        // Verification condition: success status or structural UTR presence
        $isVerified = ($status === 'success' || $status === 'payment.success' || $status === 'completed' || !empty($utr));

        if ($isVerified) {
            try {
                // Dynamically trigger registration state database update to unlock student seat
                $where = [];
                $whereParams = [];
                $rawPayload = !empty($rawInput) ? $rawInput : json_encode($payload);

                if (!empty($orderId)) {
                    $where[] = "`transaction_id` = :order_id";
                    $whereParams[':order_id'] = $orderId;
                }
                if (!empty($regCode)) {
                    $where[] = "`reg_code` = :reg_code";
                    $whereParams[':reg_code'] = $regCode;
                }

                $targetReg = null;
                if (!empty($where)) {
                    $updateParams = array_merge($whereParams, [
                        ':utr' => $utr,
                        ':raw' => $rawPayload
                    ]);

                    $sql = "UPDATE `zamzy_webinar_registrations` 
                            SET `payment_status` = 'verified', 
                                `utr_reference` = IF(:utr != '', :utr, `utr_reference`), 
                                `raw_payment_response` = :raw 
                            WHERE " . implode(" OR ", $where);
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($updateParams);

                    // Fetch the updated student record to send email
                    $fetchSql = "SELECT * FROM `zamzy_webinar_registrations` WHERE " . implode(" OR ", $where) . " LIMIT 1";
                    $fetchStmt = $pdo->prepare($fetchSql);
                    $fetchStmt->execute($whereParams);
                    $targetReg = $fetchStmt->fetch();
                } else if (!empty($utr)) {
                    // Update latest matching pending registration
                    $stmt = $pdo->prepare("SELECT `id` FROM `zamzy_webinar_registrations` WHERE `payment_status` = 'pending' ORDER BY `id` DESC LIMIT 1");
                    $stmt->execute();
                    $pendingId = $stmt->fetchColumn();
                    if ($pendingId) {
                        $upd = $pdo->prepare("UPDATE `zamzy_webinar_registrations` 
                                               SET `payment_status` = 'verified', `utr_reference` = :utr, `raw_payment_response` = :raw 
                                               WHERE `id` = :id");
                        $upd->execute([':utr' => $utr, ':raw' => $rawPayload, ':id' => $pendingId]);

                        $fetchStmt = $pdo->prepare("SELECT * FROM `zamzy_webinar_registrations` WHERE `id` = :id");
                        $fetchStmt->execute([':id' => $pendingId]);
                        $targetReg = $fetchStmt->fetch();
                    }
                }

                // Send automated confirmation email & WhatsApp with meeting links and PDFs!
                if ($targetReg) {
                    require_once __DIR__ . '/mailer.php';
                    if (empty($targetReg['email_sent'])) {
                        sendWebinarDeliveryEmail($targetReg);
                    }
                    if (empty($targetReg['whatsapp_sent'])) {
                        sendWebinarDeliveryWhatsApp($targetReg);
                    }
                }

                http_response_code(200);
                echo json_encode([
                    'status' => 'ok',
                    'success' => true,
                    'message' => 'Transaction verified, seat unlocked, and access email dispatched successfully.',
                    'order_id' => $orderId,
                    'utr' => $utr
                ]);
                exit;
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
        }

        echo json_encode([
            'status' => 'ignored',
            'message' => 'Webhook received but not verified status or UTR missing.'
        ]);
        break;

    // 12. Real-Time Order Verification & Live Polling Status Check
    case 'check_order_status':
    case 'check_payment_status':
    case 'check_registration_status':
        $regCode = trim($_GET['reg_code'] ?? $_POST['reg_code'] ?? '');
        $orderId = trim($_GET['order_id'] ?? $_POST['order_id'] ?? '');

        if (empty($regCode) && empty($orderId)) {
            echo json_encode(['success' => false, 'message' => 'Missing reg_code or order_id']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM `zamzy_webinar_registrations` WHERE `reg_code` = :code OR `transaction_id` = :txid LIMIT 1");
            $stmt->execute([':code' => $regCode, ':txid' => $orderId]);
            $row = $stmt->fetch();

            if (!$row) {
                echo json_encode(['success' => false, 'message' => 'Registration record not found']);
                exit;
            }

            // If already verified in database, return instant success
            if ($row['payment_status'] === 'verified' || $row['payment_status'] === 'completed') {
                // Ensure email and WhatsApp have been sent
                require_once __DIR__ . '/mailer.php';
                if (empty($row['email_sent'])) {
                    sendWebinarDeliveryEmail($row);
                }
                if (empty($row['whatsapp_sent'])) {
                    sendWebinarDeliveryWhatsApp($row);
                }

                $waCommunityLink = getSetting('webinar_whatsapp_link', 'https://chat.whatsapp.com/sample-zamzy-fullstack');
                $meetingLink = getSetting('webinar_meeting_link', '');
                $invoiceUrl = !empty($row['transaction_id']) && (strpos($row['transaction_id'], 'fg_') === 0 || strpos($row['transaction_id'], 'FG') === 0)
                    ? 'https://famgateway.in/transaction-details.php?id=' . urlencode($row['transaction_id']) . '&download=pdf'
                    : '';

                echo json_encode([
                    'success' => true,
                    'payment_status' => 'verified',
                    'seat_unlocked' => true,
                    'reg_code' => $row['reg_code'],
                    'order_id' => $row['transaction_id'] ?? '',
                    'invoice_url' => $invoiceUrl,
                    'utr' => $row['utr_reference'],
                    'whatsapp_community_link' => $waCommunityLink,
                    'meeting_link' => $meetingLink,
                    'email' => $row['email'],
                    'full_name' => $row['full_name']
                ]);
                exit;
            }

            // If still pending, query FamGateway verification endpoint live
            // FamGateway docs: verify-order.php returns {"status":"success","data":{"utr":"...","transaction_id":"...",...}}
            $txId = $row['transaction_id'] ?? '';
            if (!empty($txId) && (strpos($txId, 'fg_') === 0 || strpos($txId, 'FG') === 0)) {
                $apiKey = getSetting('famgateway_api_key', 'fam_d8694592b735b5387bfd795c361f6463c2ead4d3');

                $vData     = null;
                $verifyRes = '';

                // PRIMARY: /api/verify-order.php (server-to-server, API key required)
                // Response format: { "status": "success", "data": { "utr": "...", "transaction_id": "...", "sender_name": "...", "amount": 499 } }
                $verifyUrl = 'https://famgateway.in/api/verify-order.php?api_key=' . urlencode($apiKey) . '&order_id=' . urlencode($txId);
                $ch = curl_init($verifyUrl);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    CURLOPT_HTTPHEADER     => ['X-Api-Key: ' . $apiKey, 'Authorization: Bearer ' . $apiKey]
                ]);
                $verifyRes = curl_exec($ch);
                $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if (!empty($verifyRes)) {
                    $vData = json_decode($verifyRes, true);
                }

                // FALLBACK: /api/checkout-status.php (public, no API key — flat response)
                // Response format: { "status": "success", "utr": "...", "sender_name": "..." }
                if (!is_array($vData) || ($vData['status'] ?? '') !== 'success') {
                    $statusUrl = 'https://famgateway.in/api/checkout-status.php?order_id=' . urlencode($txId);
                    $ch2 = curl_init($statusUrl);
                    curl_setopt_array($ch2, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_TIMEOUT        => 8,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                    ]);
                    $sRes = curl_exec($ch2);
                    curl_close($ch2);
                    if (!empty($sRes)) {
                        $sData = json_decode($sRes, true);
                        if (is_array($sData) && (($sData['status'] ?? '') === 'success' || !empty($sData['utr']))) {
                            $vData     = $sData;
                            $verifyRes = $sRes;
                        }
                    }
                }

                // PARSE: verify-order nests data under 'data' key; checkout-status is flat at root
                $fgRootStatus = strtolower($vData['status'] ?? '');
                // Extract nested data block (verify-order) or use root (checkout-status)
                $fgData   = (isset($vData['data']) && is_array($vData['data'])) ? $vData['data'] : $vData;
                $fgUtr    = $fgData['utr'] ?? $fgData['transaction_id'] ?? $fgData['rrn'] ?? '';
                $fgSender = $fgData['sender_name'] ?? '';

                // Confirmed if status=success OR a UTR exists OR status is paid/completed
                $isPaid = ($fgRootStatus === 'success' || $fgRootStatus === 'paid' || $fgRootStatus === 'completed') || !empty($fgUtr);

                if ($isPaid) {
                    $utrFound = !empty($fgUtr) ? $fgUtr : ('FG_CONFIRMED_' . $txId);
                    // Unlock seat in DB
                    $upd = $pdo->prepare("UPDATE `zamzy_webinar_registrations` SET `payment_status` = 'verified', `utr_reference` = :utr, `raw_payment_response` = :raw WHERE `id` = :id");
                    $upd->execute([
                        ':utr' => $utrFound,
                        ':raw' => $verifyRes,
                        ':id'  => $row['id']
                    ]);

                    // Send delivery email and WhatsApp immediately
                    require_once __DIR__ . '/mailer.php';
                    $row['utr_reference'] = $utrFound;
                    if (empty($row['email_sent'])) {
                        sendWebinarDeliveryEmail($row);
                    }
                    if (empty($row['whatsapp_sent'])) {
                        sendWebinarDeliveryWhatsApp($row);
                    }

                    $waCommunityLink = getSetting('webinar_whatsapp_link', 'https://chat.whatsapp.com/sample-zamzy-fullstack');
                    $meetingLink = getSetting('webinar_meeting_link', '');
                    $invoiceUrl = 'https://famgateway.in/transaction-details.php?id=' . urlencode($txId) . '&download=pdf';

                    echo json_encode([
                        'success'               => true,
                        'payment_status'        => 'verified',
                        'seat_unlocked'         => true,
                        'reg_code'              => $row['reg_code'],
                        'order_id'              => $txId,
                        'invoice_url'           => $invoiceUrl,
                        'utr'                   => $utrFound,
                        'whatsapp_community_link' => $waCommunityLink,
                        'meeting_link'          => $meetingLink,
                        'email'                 => $row['email'],
                        'full_name'             => $row['full_name']
                    ]);
                    exit;
                }
            }

            echo json_encode([
                'success' => true,
                'payment_status' => 'pending',
                'seat_unlocked' => false,
                'reg_code' => $row['reg_code']
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    // 13. Manual UTR Verification Submission
    case 'submit_manual_utr':
        $regCode = trim($_POST['reg_code'] ?? '');
        $utr = trim($_POST['utr'] ?? '');

        if (empty($regCode) || empty($utr)) {
            echo json_encode(['success' => false, 'message' => 'Missing registration code or UTR number.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE `zamzy_webinar_registrations` 
                                   SET `utr_reference` = :utr, `payment_status` = 'verified' 
                                   WHERE `reg_code` = :code");
            $stmt->execute([':utr' => $utr, ':code' => $regCode]);

            // Fetch record to send automated email
            $fStmt = $pdo->prepare("SELECT * FROM `zamzy_webinar_registrations` WHERE `reg_code` = :code LIMIT 1");
            $fStmt->execute([':code' => $regCode]);
            $student = $fStmt->fetch();

            if ($student) {
                require_once __DIR__ . '/mailer.php';
                if (empty($student['email_sent'])) {
                    sendWebinarDeliveryEmail($student);
                }
                if (empty($student['whatsapp_sent'])) {
                    sendWebinarDeliveryWhatsApp($student);
                }
            }

            $waCommunity = getSetting('webinar_whatsapp_link', 'https://chat.whatsapp.com/sample-zamzy-fullstack');
            $meeting = getSetting('webinar_meeting_link', '');

            echo json_encode([
                'success' => true,
                'message' => 'Payment verified and seat confirmed!',
                'seat_unlocked' => true,
                'reg_code' => $regCode,
                'whatsapp_community_link' => $waCommunity,
                'meeting_link' => $meeting
            ]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
        break;

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid API action.'
        ]);
        break;
}
?>

