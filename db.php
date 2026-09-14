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
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
        define('BASE_URL', $scheme . '://' . $host . '/zz');
    } else {
        $envUrl = getenv('APP_URL');
        define('BASE_URL', !empty($envUrl) ? rtrim($envUrl, '/') : $scheme . '://' . $host);
    }
}

if (!defined('ADMIN_URL')) {
    define('ADMIN_URL', BASE_URL . '/admin');
}

function getDbConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        // Attempt database creation if user has permissions (e.g. local XAMPP)
        try {
            $pdoServer = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (Exception $e) {
            // Safe fallback on shared cPanel hosting where database is pre-created
        }
        
        // Connect directly to target database
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Auto create tables and columns if not exists
        initTables($pdo);

        return $pdo;
    } catch (PDOException $e) {
        error_log("ZAMZY DB Connection Error: " . $e->getMessage());
        return null;
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

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `zamzy_admin_users` WHERE `username` = 'admin'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $defaultPass = password_hash('zamzy@2026', PASSWORD_DEFAULT);
            $insertAdmin = $pdo->prepare("INSERT INTO `zamzy_admin_users` (`username`, `password_hash`, `name`, `email`, `role`) VALUES ('admin', :pass, 'ZAMZY Admin', 'admin@zamzy.in', 'superadmin')");
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
            'fampay_api_key' => '',
            'fampay_secret_key' => '',
            'fampay_merchant_id' => '',
            'fampay_env' => 'production',
            'upi_id' => '7287060553@ybl',
            'upi_name' => 'ZAMZY Digital Solutions',
            'webinar_price' => '96',
            'webinar_title' => 'Full Stack Web Development Live Webinar'
        ];

        $stmtSet = $pdo->prepare("INSERT IGNORE INTO `zamzy_settings` (`setting_key`, `setting_value`) VALUES (:key, :val)");
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
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } catch (Exception $e) {}
}

if (!function_exists('getSetting')) {
    function getSetting($key, $default = '') {
        $pdo = getDbConnection();
        if (!$pdo) return $default;
        try {
            $stmt = $pdo->prepare("SELECT `setting_value` FROM `zamzy_settings` WHERE `setting_key` = :key LIMIT 1");
            $stmt->execute([':key' => $key]);
            $val = $stmt->fetchColumn();
            return $val !== false ? $val : $default;
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

