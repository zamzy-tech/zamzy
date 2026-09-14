<?php
$db_host = 'localhost';
$db_name = 'shacartc_zamzy_whatsapp';
$db_user = 'shacartc_zamzy_whatsapp';
$db_pass = 'shacartc_zamzy_whatsapp';

try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Helper function to get table columns in MySQL
    function getTableColumns($pdo, $table) {
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            return [];
        }
    }

    // 1. Create settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        smtp_host VARCHAR(255),
        smtp_port INT,
        smtp_username VARCHAR(255),
        smtp_password VARCHAR(255),
        smtp_secure VARCHAR(50),
        company_name VARCHAR(255),
        company_phone VARCHAR(50),
        company_email VARCHAR(255),
        company_website VARCHAR(255),
        company_tagline VARCHAR(255),
        company_notes_default TEXT,
        admin_password VARCHAR(255),
        whatsapp_gateway_type VARCHAR(50) DEFAULT 'browser',
        whatsapp_gateway_url VARCHAR(255),
        whatsapp_gateway_token VARCHAR(255),
        whatsapp_linked_number VARCHAR(50),
        whatsapp_is_connected INT DEFAULT 0,
        razorpay_key_id VARCHAR(255) DEFAULT 'rzp_test_YOUR_KEY_HERE',
        razorpay_key_secret VARCHAR(255),
        gemini_sales_enabled INT DEFAULT 0,
        gemini_api_key VARCHAR(255) NULL,
        template_invoice_create TEXT,
        template_payment_receive TEXT,
        template_estimate TEXT,
        template_expiry_alert TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Create smtp_accounts table
    $pdo->exec("CREATE TABLE IF NOT EXISTS smtp_accounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        display_name VARCHAR(255),
        smtp_host VARCHAR(255),
        smtp_port INT,
        smtp_username VARCHAR(255),
        smtp_password VARCHAR(255),
        smtp_secure VARCHAR(50),
        is_default INT DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Create staff_users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS staff_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(150) UNIQUE,
        password VARCHAR(255),
        name VARCHAR(150),
        status VARCHAR(50) DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Create leads table
    $pdo->exec("CREATE TABLE IF NOT EXISTS leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        address TEXT,
        phone VARCHAR(50),
        status VARCHAR(50) DEFAULT 'pending',
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_by INT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");


    // 3. Create invoices table
    $pdo->exec("CREATE TABLE IF NOT EXISTS invoices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        invoice_number VARCHAR(100) UNIQUE,
        invoice_date VARCHAR(50),
        due_date VARCHAR(50),
        client_name VARCHAR(255),
        company_name VARCHAR(255),
        phone VARCHAR(50),
        emails VARCHAR(255),
        grand_total DOUBLE DEFAULT 0,
        advance_amount DOUBLE DEFAULT 0,
        pending_amount DOUBLE DEFAULT 0,
        notes TEXT,
        web_link VARCHAR(255),
        source_link VARCHAR(255),
        admin_link VARCHAR(255),
        admin_id VARCHAR(100),
        admin_pass VARCHAR(100),
        email_link VARCHAR(255),
        email_id VARCHAR(100),
        email_pass VARCHAR(100),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Check if new columns exist on invoices table, and add them dynamically
    $columns = getTableColumns($pdo, 'invoices');
    if (!in_array('sender_email', $columns)) {
        $pdo->exec("ALTER TABLE invoices ADD COLUMN sender_email VARCHAR(255);");
    }
    if (!in_array('smtp_account_id', $columns)) {
        $pdo->exec("ALTER TABLE invoices ADD COLUMN smtp_account_id INT;");
    }
    if (!in_array('is_deleted', $columns)) {
        $pdo->exec("ALTER TABLE invoices ADD COLUMN is_deleted INT DEFAULT 0;");
    }
    if (!in_array('whatsapp', $columns)) {
        $pdo->exec("ALTER TABLE invoices ADD COLUMN whatsapp VARCHAR(50);");
    }

    // Check if WhatsApp Gateway columns exist on settings table, and add them dynamically
    $columns_settings = getTableColumns($pdo, 'settings');
    if (!in_array('whatsapp_gateway_type', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN whatsapp_gateway_type VARCHAR(50) DEFAULT 'browser';");
    }
    if (!in_array('whatsapp_gateway_url', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN whatsapp_gateway_url VARCHAR(255);");
    }
    if (!in_array('whatsapp_gateway_token', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN whatsapp_gateway_token VARCHAR(255);");
    }
    if (!in_array('whatsapp_linked_number', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN whatsapp_linked_number VARCHAR(50);");
    }
    if (!in_array('whatsapp_is_connected', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN whatsapp_is_connected INT DEFAULT 0;");
    }
    if (!in_array('razorpay_key_id', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN razorpay_key_id VARCHAR(255) DEFAULT 'rzp_test_YOUR_KEY_HERE';");
    }
    if (!in_array('razorpay_key_secret', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN razorpay_key_secret VARCHAR(255);");
    }
    if (!in_array('gemini_sales_enabled', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN gemini_sales_enabled INT DEFAULT 0;");
    }
    if (!in_array('gemini_api_key', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN gemini_api_key VARCHAR(255) NULL;");
    }
    if (!in_array('template_invoice_create', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN template_invoice_create TEXT;");
    }
    if (!in_array('template_payment_receive', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN template_payment_receive TEXT;");
    }
    if (!in_array('template_estimate', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN template_estimate TEXT;");
    }
    if (!in_array('fampay_upi_id', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN fampay_upi_id VARCHAR(255) DEFAULT '8667702473@fam';");
    }
    if (!in_array('fampay_upi_name', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN fampay_upi_name VARCHAR(255) DEFAULT 'Sameer Ahamadh';");
    }
    if (!in_array('famgateway_api_key', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN famgateway_api_key VARCHAR(255) DEFAULT 'fam_d8694592b735b5387bfd795c361f6463c2ead4d3';");
    }
    if (!in_array('clients_purged_v1', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN clients_purged_v1 INT DEFAULT 0;");
    }

    // 4. Create invoice_items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS invoice_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        invoice_id INT,
        description TEXT,
        qty DOUBLE DEFAULT 0,
        price DOUBLE DEFAULT 0,
        expiry_date VARCHAR(50),
        FOREIGN KEY(invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 5. Create clients table
    $pdo->exec("CREATE TABLE IF NOT EXISTS clients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_name VARCHAR(191) UNIQUE,
        company_name VARCHAR(255),
        phone VARCHAR(50),
        whatsapp VARCHAR(50),
        emails VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $columns_clients = getTableColumns($pdo, 'clients');
    if (!in_array('whatsapp', $columns_clients)) {
        $pdo->exec("ALTER TABLE clients ADD COLUMN whatsapp VARCHAR(50);");
    }

    // 6. Create expenses table
    $pdo->exec("CREATE TABLE IF NOT EXISTS expenses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        description TEXT,
        amount DOUBLE DEFAULT 0,
        expense_date VARCHAR(50),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 7. Create api_keys table
    $pdo->exec("CREATE TABLE IF NOT EXISTS api_keys (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_name VARCHAR(255) NOT NULL,
        api_key VARCHAR(191) NOT NULL UNIQUE,
        credits INT DEFAULT 0,
        status VARCHAR(50) DEFAULT 'active',
        whatsapp_gateway_type VARCHAR(50) DEFAULT 'gateway',
        whatsapp_gateway_url VARCHAR(255),
        whatsapp_gateway_token VARCHAR(255),
        whatsapp_linked_number VARCHAR(50),
        whatsapp_is_connected INT DEFAULT 0,
        client_phone VARCHAR(50),
        login_id VARCHAR(191) UNIQUE,
        login_password VARCHAR(255),
        plain_password VARCHAR(255),
        is_trial INT DEFAULT 0,
        expiry_alerts_sent INT DEFAULT 0,
        last_expiry_alert_at DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $columns_api = getTableColumns($pdo, 'api_keys');
    if (!in_array('whatsapp_gateway_type', $columns_api)) {
        $pdo->exec("ALTER TABLE api_keys ADD COLUMN whatsapp_gateway_type VARCHAR(50) DEFAULT 'gateway';");
    }
    if (!in_array('whatsapp_gateway_url', $columns_api)) {
        $pdo->exec("ALTER TABLE api_keys ADD COLUMN whatsapp_gateway_url VARCHAR(255);");
    }
    if (!in_array('whatsapp_gateway_token', $columns_api)) {
        $pdo->exec("ALTER TABLE api_keys ADD COLUMN whatsapp_gateway_token VARCHAR(255);");
    }
    if (!in_array('whatsapp_linked_number', $columns_api)) {
        $pdo->exec("ALTER TABLE api_keys ADD COLUMN whatsapp_linked_number VARCHAR(50);");
    }
    if (!in_array('whatsapp_is_connected', $columns_api)) {
        $pdo->exec("ALTER TABLE api_keys ADD COLUMN whatsapp_is_connected INT DEFAULT 0;");
    }
    if (!in_array('client_phone', $columns_api)) {
        $pdo->exec("ALTER TABLE api_keys ADD COLUMN client_phone VARCHAR(50);");
    }
    if (!in_array('login_id', $columns_api)) {
        $pdo->exec("ALTER TABLE api_keys ADD COLUMN login_id VARCHAR(191);");
    }
    if (!in_array('login_password', $columns_api)) {
        $pdo->exec("ALTER TABLE api_keys ADD COLUMN login_password VARCHAR(255);");
    }
    if (!in_array('expiry_date', $columns_api)) {
        $pdo->exec("ALTER TABLE api_keys ADD COLUMN expiry_date VARCHAR(50);");
    }
    if (!in_array('allowed_scanners', $columns_api)) {
        $pdo->exec("ALTER TABLE api_keys ADD COLUMN allowed_scanners INT DEFAULT 1;");
    }
    if (!in_array('plain_password', $columns_api)) {
        $pdo->exec("ALTER TABLE api_keys ADD COLUMN plain_password VARCHAR(255);");
    }
    if (!in_array('is_trial', $columns_api)) {
        $pdo->exec("ALTER TABLE api_keys ADD COLUMN is_trial INT DEFAULT 0;");
    }
    if (!in_array('expiry_alerts_sent', $columns_api)) {
        $pdo->exec("ALTER TABLE api_keys ADD COLUMN expiry_alerts_sent INT DEFAULT 0;");
    }
    if (!in_array('last_expiry_alert_at', $columns_api)) {
        $pdo->exec("ALTER TABLE api_keys ADD COLUMN last_expiry_alert_at DATETIME NULL;");
    }
    if (!in_array('template_otp', $columns_api)) {
        $pdo->exec("ALTER TABLE api_keys ADD COLUMN template_otp TEXT;");
    }
    if (!in_array('template_invoice', $columns_api)) {
        $pdo->exec("ALTER TABLE api_keys ADD COLUMN template_invoice TEXT;");
    }
    if (!in_array('template_general', $columns_api)) {
        $pdo->exec("ALTER TABLE api_keys ADD COLUMN template_general TEXT;");
    }

    // 8. Create api_logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS api_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        api_key_id INT,
        message_type VARCHAR(50) NOT NULL,
        recipient_phone VARCHAR(50) NOT NULL,
        status VARCHAR(50) NOT NULL,
        response_message TEXT,
        credits_used INT DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(api_key_id) REFERENCES api_keys(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 9. Create coupons table
    $pdo->exec("CREATE TABLE IF NOT EXISTS coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(100) UNIQUE NOT NULL,
        discount_type VARCHAR(50) NOT NULL,
        discount_value DOUBLE NOT NULL,
        expiry_date VARCHAR(50),
        usage_limit INT,
        used_count INT DEFAULT 0,
        status VARCHAR(50) DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 10. Create client_payments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS client_payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT,
        payment_id VARCHAR(255),
        plan_name VARCHAR(255),
        amount DOUBLE DEFAULT 0,
        duration INT,
        extra_scanners INT,
        coupon_code VARCHAR(100),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 11. Create client_devices table
    $pdo->exec("CREATE TABLE IF NOT EXISTS client_devices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT,
        slot_number INT,
        whatsapp_linked_number VARCHAR(50),
        whatsapp_is_connected INT DEFAULT 0,
        api_key VARCHAR(191),
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY client_slot (client_id, slot_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 12. Create service_pricing table
    $pdo->exec("CREATE TABLE IF NOT EXISTS service_pricing (
        id INT AUTO_INCREMENT PRIMARY KEY,
        plan_key VARCHAR(100) UNIQUE NOT NULL,
        plan_name VARCHAR(255) NOT NULL,
        description TEXT,
        scanners_included INT DEFAULT 1,
        monthly_price DOUBLE DEFAULT 0,
        price_3_months DOUBLE DEFAULT 0,
        price_6_months DOUBLE DEFAULT 0,
        price_12_months DOUBLE DEFAULT 0,
        status VARCHAR(50) DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Seed default service pricing if empty
    $sp_count = $pdo->query("SELECT COUNT(*) FROM service_pricing")->fetchColumn();
    if ($sp_count == 0) {
        $ins_sp = $pdo->prepare("INSERT INTO service_pricing (plan_key, plan_name, description, scanners_included, monthly_price, price_3_months, price_6_months, price_12_months, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        $ins_sp->execute(['starter', 'Starter Plan', '1 Device Scanner • Unlimited SMS', 1, 799, 2097, 3594, 5988]);
        $ins_sp->execute(['business', 'Business Plan', '3 Device Scanners • Unlimited SMS', 3, 1299, 3597, 6594, 11988]);
        $ins_sp->execute(['addon_scanner', 'Extra Active Scanner Add-on', 'Cost per additional WhatsApp device scanner per month', 1, 300, 900, 1800, 3600]);
    }

    $columns_dev = getTableColumns($pdo, 'client_devices');
    if (!in_array('api_key', $columns_dev)) {
        $pdo->exec("ALTER TABLE client_devices ADD COLUMN api_key VARCHAR(191);");
    }

    // Self-seeding historical connected devices to client_devices
    $stmt_keys = $pdo->query("SELECT id, whatsapp_is_connected, whatsapp_linked_number FROM api_keys WHERE whatsapp_is_connected = 1");
    $active_keys = $stmt_keys->fetchAll();
    if ($active_keys) {
        $ins_dev = $pdo->prepare("INSERT IGNORE INTO client_devices (client_id, slot_number, whatsapp_linked_number, whatsapp_is_connected) VALUES (?, 1, ?, 1)");
        foreach ($active_keys as $ak) {
            $ins_dev->execute([$ak['id'], $ak['whatsapp_linked_number']]);
        }
    }

    // Self-seeding historical clients if clients table is empty
    // 13. Purge existing test clients as requested (one-time safe wipe)
    try {
        $check_purge = $pdo->query("SELECT clients_purged_v1 FROM settings LIMIT 1")->fetchColumn();
        if ($check_purge === false || $check_purge === 0 || $check_purge === '0' || $check_purge === null) {
            $pdo->exec("DELETE FROM client_devices WHERE 1");
            $pdo->exec("DELETE FROM client_payments WHERE 1");
            $pdo->exec("DELETE FROM api_keys WHERE 1");
            $pdo->exec("DELETE FROM clients WHERE 1");
            $pdo->exec("UPDATE settings SET clients_purged_v1 = 1 WHERE id > 0 LIMIT 1");
        }
    } catch (Exception $e) {}

    // Seed default settings if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
    if ($stmt->fetchColumn() == 0) {
        $default_password_hash = password_hash('admin123', PASSWORD_BCRYPT);
        
        $insert_settings = $pdo->prepare("INSERT INTO settings (
            smtp_host, smtp_port, smtp_username, smtp_password, smtp_secure,
            company_name, company_phone, company_email, company_website, company_tagline,
            company_notes_default, admin_password,
            fampay_upi_id, fampay_upi_name, famgateway_api_key, clients_purged_v1
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        
        $insert_settings->execute([
            'mail.zamzy.in',
            465,
            'no-reply@zamzy.in',
            'shacartc_zamzy',
            'ssl',
            'ZAMZY',
            '+91 72870 60553',
            'no-reply@zamzy.in',
            'zamzy.in',
            'ZAMZY WhatsApp Gateway Cluster',
            'Official ZAMZY automated communications & services.',
            $default_password_hash,
            '8667702473@fam',
            'Sameer Ahamadh',
            'fam_d8694592b735b5387bfd795c361f6463c2ead4d3'
        ]);
    }

    // Configure and ensure default ZAMZY SMTP account
    $stmt_zamzy_smtp = $pdo->query("SELECT COUNT(*) FROM smtp_accounts WHERE smtp_host = 'mail.zamzy.in' AND smtp_username = 'no-reply@zamzy.in'");
    if ($stmt_zamzy_smtp->fetchColumn() == 0) {
        // Reset any existing defaults and set Zamzy as active default
        $pdo->exec("UPDATE smtp_accounts SET is_default = 0");
        $insert_smtp = $pdo->prepare("INSERT INTO smtp_accounts (
            display_name, smtp_host, smtp_port, smtp_username, smtp_password, smtp_secure, is_default
        ) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert_smtp->execute([
            'ZAMZY Mailer (no-reply@zamzy.in)',
            'mail.zamzy.in',
            465,
            'no-reply@zamzy.in',
            'shacartc_zamzy',
            'ssl',
            1
        ]);
    }

    // Sync settings with current ZAMZY SMTP and FamPay defaults if empty or outdated
    $settings_check = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();
    if ($settings_check) {
        $update_fields = [];
        $update_params = [];
        
        if (empty($settings_check['company_name']) || $settings_check['company_name'] === 'The Expert Hub') {
            $update_fields[] = "company_name = ?";
            $update_params[] = 'ZAMZY';
        }
        if (empty($settings_check['company_email']) || strpos($settings_check['company_email'], 'theexperthub') !== false) {
            $update_fields[] = "company_email = ?";
            $update_params[] = 'no-reply@zamzy.in';
        }
        if (empty($settings_check['company_website']) || strpos($settings_check['company_website'], 'theexperthub') !== false) {
            $update_fields[] = "company_website = ?";
            $update_params[] = 'zamzy.in';
        }
        if (empty($settings_check['smtp_host']) || strpos($settings_check['smtp_host'], 'stableserver') !== false) {
            $update_fields[] = "smtp_host = ?";
            $update_params[] = 'mail.zamzy.in';
            $update_fields[] = "smtp_port = ?";
            $update_params[] = 465;
            $update_fields[] = "smtp_username = ?";
            $update_params[] = 'no-reply@zamzy.in';
            $update_fields[] = "smtp_password = ?";
            $update_params[] = 'shacartc_zamzy';
            $update_fields[] = "smtp_secure = ?";
            $update_params[] = 'ssl';
        }
        if (empty($settings_check['fampay_upi_id'])) {
            $update_fields[] = "fampay_upi_id = ?";
            $update_params[] = '8667702473@fam';
        }
        if (empty($settings_check['fampay_upi_name'])) {
            $update_fields[] = "fampay_upi_name = ?";
            $update_params[] = 'Sameer Ahamadh';
        }
        if (empty($settings_check['famgateway_api_key'])) {
            $update_fields[] = "famgateway_api_key = ?";
            $update_params[] = 'fam_d8694592b735b5387bfd795c361f6463c2ead4d3';
        }
        if (empty($settings_check['razorpay_key_id']) || $settings_check['razorpay_key_id'] === 'rzp_test_YOUR_KEY_HERE') {
            $update_fields[] = "razorpay_key_id = ?";
            $update_params[] = 'rzp_live_T6vpsfWvqIlyeC';
        }
        if (empty($settings_check['razorpay_key_secret'])) {
            $update_fields[] = "razorpay_key_secret = ?";
            $update_params[] = 'UOYxj3V23nOsi28LQnpI7fk9';
        }
        if (empty($settings_check['template_invoice_create'])) {
            $update_fields[] = "template_invoice_create = ?";
            $update_params[] = "*Dear {client_name},*\n\n⚡ *Invoice Summary from {company_name}* ⚡\n\n*Invoice Number:* {invoice_number}\n*Invoice Date:* {invoice_date}\n*Due Date:* {due_date}\n\n*Grand Total:* Rs {grand_total}\n*Advance Paid:* Rs {advance_amount}\n*Balance Due:* *Rs {pending_amount}*\n\nThank you for your business! If you have any questions, please feel free to reach out.";
        }
        if (empty($settings_check['template_payment_receive'])) {
            $update_fields[] = "template_payment_receive = ?";
            $update_params[] = "*Dear {client_name},*\n\n✅ *Payment Received - Thank You!* ✅\n\nWe have received your payment of *Rs {amount_paid}* in full for invoice *{invoice_number}*.\n\n*Invoice Details:*\n• *Invoice Number:* {invoice_number}\n• *Grand Total:* Rs {grand_total}\n• *Amount Paid:* Rs {amount_paid}\n• *Outstanding Balance:* Rs {pending_amount} (Fully Paid)\n\nThank you for your business! We look forward to working with you again.";
        }
        if (empty($settings_check['template_estimate'])) {
            $update_fields[] = "template_estimate = ?";
            $update_params[] = "*Dear {client_name},*\n\n📋 *New Estimate from {company_name}* 📋\n\nWe are pleased to submit our estimate *#{invoice_number}* for your review.\n\n*Estimated Total:* *Rs {grand_total}*\n\nPlease let us know if you would like to proceed or if you need any adjustments.\n\nBest regards,\n*{company_name}*";
        }
        
        if (!empty($update_fields)) {
            $update_params[] = $settings_check['id'];
            $pdo->prepare("UPDATE settings SET " . implode(", ", $update_fields) . " WHERE id = ?")->execute($update_params);
        }
    }

    // Seed default client templates in api_keys table if empty
    $pdo->exec("UPDATE api_keys SET template_otp = 'Your verification code is *{otp_code}*. This code is valid for 10 minutes. Please do not share it with anyone.' WHERE template_otp IS NULL OR template_otp = '';");
    $pdo->exec("UPDATE api_keys SET template_invoice = 'Dear {client_name}, your invoice #{invoice_number} is ready. Total Amount: Rs {grand_total}. You can view it here: {web_link}' WHERE template_invoice IS NULL OR template_invoice = '';");
    $pdo->exec("UPDATE api_keys SET template_general = '⚡ Alert: {message}' WHERE template_general IS NULL OR template_general = '';");

    // 13. Create youtubers table
    $pdo->exec("CREATE TABLE IF NOT EXISTS youtubers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(150) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        channel_id VARCHAR(100),
        channel_name VARCHAR(255),
        whatsapp_target_jid VARCHAR(100),
        whatsapp_target_name VARCHAR(255),
        last_video_id VARCHAR(50),
        template_upload TEXT,
        template_live TEXT,
        is_active TINYINT DEFAULT 1,
        whatsapp_is_connected INT DEFAULT 0,
        whatsapp_linked_number VARCHAR(50) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $columns_yt = getTableColumns($pdo, 'youtubers');
    if (!in_array('whatsapp_is_connected', $columns_yt)) {
        $pdo->exec("ALTER TABLE youtubers ADD COLUMN whatsapp_is_connected INT DEFAULT 0;");
    }
    if (!in_array('whatsapp_linked_number', $columns_yt)) {
        $pdo->exec("ALTER TABLE youtubers ADD COLUMN whatsapp_linked_number VARCHAR(50) DEFAULT NULL;");
    }
    if (!in_array('phone', $columns_yt)) {
        $pdo->exec("ALTER TABLE youtubers ADD COLUMN phone VARCHAR(50) DEFAULT NULL;");
    }

    // 14. Create youtuber_channels table
    $pdo->exec("CREATE TABLE IF NOT EXISTS youtuber_channels (
        id INT AUTO_INCREMENT PRIMARY KEY,
        youtuber_id INT NOT NULL,
        channel_id VARCHAR(100) NOT NULL,
        channel_name VARCHAR(255),
        whatsapp_target_jid VARCHAR(100),
        whatsapp_target_name VARCHAR(255),
        last_video_id VARCHAR(50),
        template_upload TEXT,
        template_live TEXT,
        is_active TINYINT DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (youtuber_id) REFERENCES youtubers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
