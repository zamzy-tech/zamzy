<?php
// ZAMZY Platform — Database Connection & Auto-Migration Engine
// Database: zamzy_db (Host: localhost, User: root, Password: '')

if (!function_exists('loadEnv')) {
    function loadEnv($path = __DIR__ . '/.env') {
        if (!file_exists($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || $line[0] === '#') continue;
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                $value = trim($value, '"\'');
                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv(sprintf('%s=%s', $name, $value));
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }
}
loadEnv();

if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'zamzy_db');
if (!defined('DEEPSEEK_API_KEY')) define('DEEPSEEK_API_KEY', getenv('DEEPSEEK_API_KEY') ?: 'sk-71bbb2ea1a0e45dcbf2574d6f115aac1');

// Dynamic BASE_URL and ADMIN_URL Detection Engine
if (!defined('BASE_URL')) {
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
        (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
        (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'zamzy.in') !== false)
    );
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
        define('BASE_URL', $scheme . '://' . $host . '/zz');
    } else {
        $envUrl = $_ENV['APP_URL'] ?? $_SERVER['APP_URL'] ?? getenv('APP_URL');
        define('BASE_URL', !empty($envUrl) ? rtrim($envUrl, '/') : $scheme . '://' . $host);
    }
}

if (!defined('ADMIN_URL')) {
    define('ADMIN_URL', BASE_URL . '/admin');
}

function getDbConnection() {
    static $pdo = null;
    static $failed = false;
    if ($pdo !== null) {
        return $pdo;
    }
    if ($failed) {
        return null;
    }

    try {
        // Connect directly to target database
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Auto create tables and columns if not exists
        try {
            initTables($pdo);
        } catch (Exception $e) {}

        return $pdo;
    } catch (PDOException $e) {
        // Fallback for fresh local setup where database does not exist yet
        try {
            $pdoServer = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            initTables($pdo);
            return $pdo;
        } catch (Exception $ex) {
            $failed = true;
            error_log("ZAMZY DB Connection Error: " . $ex->getMessage());
            return null;
        }
    }
}


function initTables($pdo) {
    if (!$pdo) return;
    
    // 1. Admin Users Table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_admin_users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL,
            `role` VARCHAR(20) DEFAULT 'admin',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $pdo->exec("DELETE FROM `zamzy_admin_users` WHERE `username` != 'Zamzy0205'");

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `zamzy_admin_users` WHERE `username` = 'Zamzy0205'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $defaultPass = password_hash('@Zamzy0205', PASSWORD_DEFAULT);
            $insertAdmin = $pdo->prepare("INSERT INTO `zamzy_admin_users` (`username`, `password_hash`, `name`, `email`, `role`) VALUES ('Zamzy0205', :pass, 'ZAMZY Super Admin', 'admin@zamzy.in', 'superadmin')");
            $insertAdmin->execute([':pass' => $defaultPass]);
        }
    } catch (Exception $e) {}

    // 2. Project Inquiries / Briefs Table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_inquiries` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL,
            `phone` VARCHAR(30) NOT NULL,
            `preferred_language` VARCHAR(50) DEFAULT 'English',
            `budget` VARCHAR(100) DEFAULT '₹25,000 – ₹75,000 (Custom Web & App)',
            `role` VARCHAR(100) NULL,
            `company` VARCHAR(100) NULL,
            `tier` VARCHAR(100) DEFAULT 'Tier 02 — Custom App & Web',
            `launch_window` VARCHAR(100) NULL,
            `project_type` VARCHAR(100) DEFAULT 'Custom SaaS Platform',
            `requirements` TEXT NOT NULL,
            `reference_url` VARCHAR(255) NULL,
            `status` ENUM('partial', 'new', 'contacted', 'in_progress', 'converted', 'archived') DEFAULT 'new',
            `admin_notes` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $pdo->exec("ALTER TABLE `zamzy_inquiries` ADD COLUMN `preferred_language` VARCHAR(50) DEFAULT 'English' AFTER `phone`");
        $pdo->exec("ALTER TABLE `zamzy_inquiries` ADD COLUMN `budget` VARCHAR(100) DEFAULT '₹25,000 – ₹75,000 (Custom Web & App)' AFTER `preferred_language`");
        $pdo->exec("ALTER TABLE `zamzy_inquiries` ADD COLUMN `admin_notes` TEXT NULL AFTER `status`");
        $pdo->exec("ALTER TABLE `zamzy_inquiries` MODIFY COLUMN `status` ENUM('partial','new','contacted','in_progress','converted','archived') DEFAULT 'new'");
    } catch (Exception $e) {}

    // 3. Demo Requests Table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_demo_requests` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `product_name` VARCHAR(150) NOT NULL,
            `phone` VARCHAR(30) NOT NULL,
            `email` VARCHAR(100) NOT NULL,
            `status` ENUM('pending', 'dispatched', 'contacted', 'closed') DEFAULT 'pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } catch (Exception $e) {}

    // 4. Testimonials & Client Reviews Table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_testimonials` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `client_name` VARCHAR(100) NOT NULL,
            `company_name` VARCHAR(100) NOT NULL,
            `role` VARCHAR(100) NOT NULL,
            `location` VARCHAR(100) DEFAULT 'Anna Nagar, Chennai',
            `rating` INT DEFAULT 5,
            `review_text` TEXT NOT NULL,
            `project_type` VARCHAR(100) DEFAULT 'Custom SaaS Platform',
            `is_featured` TINYINT(1) DEFAULT 1,
            `is_published` TINYINT(1) DEFAULT 1,
            `is_approved` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $pdo->exec("ALTER TABLE `zamzy_testimonials` ADD COLUMN `is_published` TINYINT(1) DEFAULT 1 AFTER `is_featured`");
        $pdo->exec("ALTER TABLE `zamzy_testimonials` ADD COLUMN `is_approved` TINYINT(1) DEFAULT 1 AFTER `is_published`");
    } catch (Exception $e) {}

    // 5. Careers & Job Openings Table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_careers_jobs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(150) NOT NULL,
            `department` VARCHAR(100) NOT NULL,
            `employment_type` VARCHAR(50) DEFAULT 'Freelance / Project-Based',
            `location` VARCHAR(100) DEFAULT 'Remote / Chennai',
            `experience_level` VARCHAR(50) DEFAULT 'College Student / Freelancer',
            `stipend_salary` VARCHAR(100) DEFAULT 'Project Commission + Milestones',
            `description` TEXT NOT NULL,
            `requirements` TEXT NOT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } catch (Exception $e) {}

    // 6. AI Chat Sessions & Messages
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_chat_sessions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `session_token` VARCHAR(64) UNIQUE NOT NULL,
            `user_name` VARCHAR(120) NULL,
            `user_phone` VARCHAR(40) NULL,
            `user_email` VARCHAR(120) NULL,
            `total_messages` INT DEFAULT 0,
            `last_message` TEXT NULL,
            `status` ENUM('lead_captured', 'in_progress', 'closed') DEFAULT 'in_progress',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_chat_messages` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `session_id` INT NOT NULL,
            `sender` ENUM('user', 'bot') NOT NULL,
            `message` TEXT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`session_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } catch (Exception $e) {}

    // 7. System Settings & Payment Gateway Credentials Table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_settings` (
            `setting_key` VARCHAR(100) PRIMARY KEY,
            `setting_value` TEXT NULL,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $defaultSettings = [
            'active_payment_gateway' => 'razorpay',
            'famgateway_api_key' => 'fam_d8694592b735b5387bfd795c361f6463c2ead4d3',
            'upi_id' => '8667702473@fam',
            'upi_name' => 'Sameer Ahamadh',
            'webinar_price' => '149',
            'webinar_title' => 'Full Stack Web Development Live Webinar',
            'razorpay_payment_link' => 'https://rzp.io/l/zamzy-webinar',
            // SMTP Settings
            'smtp_host' => 'mail.zamzy.in',
            'smtp_port' => '465',
            'smtp_secure' => 'ssl',
            'smtp_username' => 'no-reply@zamzy.in',
            'smtp_password' => 'shacartc_zamzy',
            'smtp_from_email' => 'no-reply@zamzy.in',
            'smtp_from_name' => 'ZAMZY Learning',
            // Webinar Deliverable Assets
            'webinar_schedule' => 'Saturday 27 Sep 2026 | 06:00 PM - 08:00 PM IST',
            'webinar_meeting_link' => 'https://meet.google.com/qmv-xyza-web',
            'webinar_whatsapp_link' => 'https://chat.whatsapp.com/sample-zamzy-fullstack',
            'webinar_resources' => "• Complete Full Stack Architecture Blueprint & Curriculum (PDF)\n• GitHub Repositories & Starter Kits\n• Interview Cheatsheets & Free Tooling Access",
            'webinar_email_notes' => 'Please join 5 minutes prior to the scheduled start time. Ensure you have Google Meet / Chrome installed and your laptop ready with VS Code.',
            'webinar_reminder_wa_template' => "⏳ *Payment Pending — ZAMZY Full Stack Webinar*\n\nDear *{name}*, 👋\n\nWe noticed your registration (*Code: {reg_code}*) for the *{webinar_title}* (Fee: ₹{amount}) is currently *PENDING*. Seats are filling fast, and your slot is reserved for a limited time.\n\n💡 Please reply directly to this message or contact our coordinator if you have any questions or require assistance.\n\nWarm Regards,\n*ZAMZY Academy*",
            'webinar_reminder_email_template' => ""
        ];

        $stmtSet = $pdo->prepare("INSERT INTO `zamzy_settings` (`setting_key`, `setting_value`) VALUES (:key, :val) ON DUPLICATE KEY UPDATE `setting_value` = IF(`setting_value` IS NULL OR `setting_value` = '', :val, `setting_value`)");
        foreach ($defaultSettings as $k => $v) {
            $stmtSet->execute([':key' => $k, ':val' => $v]);
        }
    } catch (Exception $e) {}

    // 8. Webinar Registrations & Payment Tracking Table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_webinar_registrations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `reg_code` VARCHAR(35) UNIQUE NOT NULL,
            `full_name` VARCHAR(120) NOT NULL,
            `phone` VARCHAR(30) NOT NULL,
            `email` VARCHAR(120) NOT NULL,
            `college_or_company` VARCHAR(150) NULL,
            `experience_level` VARCHAR(50) DEFAULT 'Beginner',
            `preferred_language` VARCHAR(50) DEFAULT 'English',
            `amount` DECIMAL(10,2) DEFAULT 96.00,
            `payment_method` VARCHAR(50) DEFAULT 'FamPay / UPI',
            `payment_status` ENUM('pending', 'completed', 'verified', 'rejected') DEFAULT 'pending',
            `utr_reference` VARCHAR(100) NULL,
            `transaction_id` VARCHAR(100) NULL,
            `raw_payment_response` TEXT NULL,
            `admin_notes` TEXT NULL,
            `email_sent` TINYINT(1) DEFAULT 0,
            `whatsapp_sent` TINYINT(1) DEFAULT 0,
            `reminded_10min` TINYINT(1) DEFAULT 0,
            `reminders_count_today` INT DEFAULT 0,
            `last_reminder_date` DATE NULL,
            `last_reminder_at` DATETIME NULL,
            `reminders_total` INT DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Ensure all required columns exist in zamzy_webinar_registrations
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
        try {
            $pdo->exec("ALTER TABLE `zamzy_webinar_registrations` ADD COLUMN `coupon_code` VARCHAR(50) NULL");
        } catch (Exception $e) {}
        try {
            $pdo->exec("ALTER TABLE `zamzy_webinar_registrations` ADD COLUMN `discount_amount` DECIMAL(10,2) DEFAULT 0.00");
        } catch (Exception $e) {}
        try {
            $pdo->exec("ALTER TABLE `zamzy_webinar_registrations` ADD COLUMN `reminded_10min` TINYINT(1) DEFAULT 0");
        } catch (Exception $e) {}
        try {
            $pdo->exec("ALTER TABLE `zamzy_webinar_registrations` ADD COLUMN `reminders_count_today` INT DEFAULT 0");
        } catch (Exception $e) {}
        try {
            $pdo->exec("ALTER TABLE `zamzy_webinar_registrations` ADD COLUMN `last_reminder_date` DATE NULL");
        } catch (Exception $e) {}
        try {
            $pdo->exec("ALTER TABLE `zamzy_webinar_registrations` ADD COLUMN `last_reminder_at` DATETIME NULL");
        } catch (Exception $e) {}
        try {
            $pdo->exec("ALTER TABLE `zamzy_webinar_registrations` ADD COLUMN `reminders_total` INT DEFAULT 0");
        } catch (Exception $e) {}
    } catch (Exception $e) {}

    // 9. Webinar Promotional Coupons Table
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_coupons` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `code` VARCHAR(50) UNIQUE NOT NULL,
            `discount_type` ENUM('fixed', 'percent', 'free') DEFAULT 'free',
            `discount_value` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `max_uses` INT DEFAULT 0,
            `used_count` INT DEFAULT 0,
            `expiry_date` DATE NULL,
            `status` ENUM('active', 'inactive') DEFAULT 'active',
            `notes` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Seed default starter coupons if table is newly created
        $cCount = $pdo->query("SELECT COUNT(*) FROM `zamzy_coupons`")->fetchColumn();
        if ($cCount == 0) {
            $pdo->exec("INSERT INTO `zamzy_coupons` (`code`, `discount_type`, `discount_value`, `max_uses`, `status`, `notes`) 
                        VALUES 
                        ('ZAMZY100', 'free', 100.00, 500, 'active', '100% Free VIP Student Access Pass'),
                        ('SAVE50', 'fixed', 50.00, 200, 'active', 'Flat ₹50 Instant Waiver')");
        }
    } catch (Exception $e) {}

        // 10. Activity & Visitor Access Logs Table (with IP Geolocation Tracking)
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_activity_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `event_type` VARCHAR(50) NOT NULL,
                `action_name` VARCHAR(150) NOT NULL,
                `user_identifier` VARCHAR(150) NULL,
                `phone` VARCHAR(40) NULL,
                `email` VARCHAR(150) NULL,
                `ip_address` VARCHAR(60) NOT NULL,
                `city` VARCHAR(100) NULL,
                `region` VARCHAR(100) NULL,
                `country` VARCHAR(100) NULL,
                `country_code` VARCHAR(10) NULL,
                `postal` VARCHAR(30) NULL,
                `latitude` VARCHAR(30) NULL,
                `longitude` VARCHAR(30) NULL,
                `org_isp` VARCHAR(150) NULL,
                `page_url` VARCHAR(255) NULL,
                `user_agent` VARCHAR(255) NULL,
                `device_type` VARCHAR(50) NULL,
                `status` VARCHAR(30) DEFAULT 'success',
                `details` TEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (`event_type`),
                INDEX (`ip_address`),
                INDEX (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        } catch (Exception $e) {}
}

if (!function_exists('getUserIp')) {
    function getUserIp() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ipKeys as $k) {
            if (!empty($_SERVER[$k])) {
                $ips = explode(',', $_SERVER[$k]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}

if (!function_exists('getIpLocation')) {
    function getIpLocation($ip) {
        $loc = [
            'city' => 'Unknown',
            'region' => 'Unknown',
            'country' => 'India',
            'country_code' => 'IN',
            'postal' => '',
            'latitude' => '',
            'longitude' => '',
            'org_isp' => ''
        ];

        if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1' || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0) {
            $loc['city'] = 'Localhost (Dev)';
            $loc['region'] = 'Telangana';
            $loc['country'] = 'India';
            $loc['country_code'] = 'IN';
            $loc['org_isp'] = 'Local Development Network';
            return $loc;
        }

        try {
            $ctx = stream_context_create(['http' => ['timeout' => 1.5]]);
            $res = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,countryCode,regionName,city,zip,lat,lon,isp", false, $ctx);
            if ($res) {
                $data = json_decode($res, true);
                if (isset($data['status']) && $data['status'] === 'success') {
                    $loc['city'] = $data['city'] ?? 'Unknown';
                    $loc['region'] = $data['regionName'] ?? 'Unknown';
                    $loc['country'] = $data['country'] ?? 'Unknown';
                    $loc['country_code'] = $data['countryCode'] ?? 'IN';
                    $loc['postal'] = $data['zip'] ?? '';
                    $loc['latitude'] = (string)($data['lat'] ?? '');
                    $loc['longitude'] = (string)($data['lon'] ?? '');
                    $loc['org_isp'] = $data['isp'] ?? '';
                }
            }
        } catch (Exception $e) {}

        return $loc;
    }
}

if (!function_exists('detectDeviceType')) {
    function detectDeviceType($ua) {
        if (empty($ua)) return 'Desktop';
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $ua)) {
            return 'Tablet';
        }
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile|mobile)/i', $ua)) {
            return 'Mobile';
        }
        return 'Desktop';
    }
}

if (!function_exists('logActivity')) {
    function logActivity($eventType, $actionName, $details = null, $userIdentifier = null, $phone = null, $email = null, $status = 'success') {
        $pdo = getDbConnection();
        if (!$pdo) return false;

        $ip = getUserIp();
        $loc = getIpLocation($ip);
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $device = detectDeviceType($ua);
        $pageUrl = ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $pageUrl = 'https://' . $pageUrl;
        } else {
            $pageUrl = 'http://' . $pageUrl;
        }

        $detailsStr = is_array($details) ? json_encode($details, JSON_UNESCAPED_UNICODE) : (string)$details;

        try {
            $stmt = $pdo->prepare("INSERT INTO `zamzy_activity_logs` 
                (`event_type`, `action_name`, `user_identifier`, `phone`, `email`, `ip_address`, `city`, `region`, `country`, `country_code`, `postal`, `latitude`, `longitude`, `org_isp`, `page_url`, `user_agent`, `device_type`, `status`, `details`) 
                VALUES 
                (:event_type, :action_name, :user_identifier, :phone, :email, :ip_address, :city, :region, :country, :country_code, :postal, :latitude, :longitude, :org_isp, :page_url, :user_agent, :device_type, :status, :details)");
            
            return $stmt->execute([
                ':event_type' => $eventType,
                ':action_name' => $actionName,
                ':user_identifier' => $userIdentifier,
                ':phone' => $phone,
                ':email' => $email,
                ':ip_address' => $ip,
                ':city' => $loc['city'],
                ':region' => $loc['region'],
                ':country' => $loc['country'],
                ':country_code' => $loc['country_code'],
                ':postal' => $loc['postal'],
                ':latitude' => $loc['latitude'],
                ':longitude' => $loc['longitude'],
                ':org_isp' => $loc['org_isp'],
                ':page_url' => substr($pageUrl, 0, 255),
                ':user_agent' => substr($ua, 0, 255),
                ':device_type' => $device,
                ':status' => $status,
                ':details' => $detailsStr
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('getSetting')) {
    function getSetting($key, $default = '') {
        if ($key === 'razorpay_key_id') {
            $envKey = getenv('RAZORPAY_KEY_ID') ?: getenv('PAYMENT_API_KEY');
            if (!empty($envKey)) return trim($envKey);
        }
        if ($key === 'razorpay_key_secret') {
            $envSecret = getenv('RAZORPAY_KEY_SECRET') ?: getenv('PAYMENT_SECRET');
            if (!empty($envSecret)) return trim($envSecret);
        }
        $pdo = getDbConnection();
        if (!$pdo) return $default;
        try {
            $stmt = $pdo->prepare("SELECT `setting_value` FROM `zamzy_settings` WHERE `setting_key` = :key LIMIT 1");
            $stmt->execute([':key' => $key]);
            $val = $stmt->fetchColumn();
            return ($val !== false && $val !== '') ? $val : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('setSetting')) {
    function setSetting($key, $value) {
        $pdo = getDbConnection();
        if (!$pdo) return false;
        try {
            $stmt = $pdo->prepare("INSERT INTO `zamzy_settings` (`setting_key`, `setting_value`) VALUES (:key, :val) ON DUPLICATE KEY UPDATE `setting_value` = :val");
            return $stmt->execute([':key' => $key, ':val' => $value]);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>

