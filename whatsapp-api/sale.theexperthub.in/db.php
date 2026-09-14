<?php
$db_host = 'localhost';
$db_name = 'shacartc_expert';
$db_user = 'shacartc_expert';
$db_pass = 'shacartc_expert';

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
        template_invoice_create TEXT,
        template_payment_receive TEXT,
        template_estimate TEXT
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
    if (!in_array('template_invoice_create', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN template_invoice_create TEXT;");
    }
    if (!in_array('template_payment_receive', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN template_payment_receive TEXT;");
    }
    if (!in_array('template_estimate', $columns_settings)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN template_estimate TEXT;");
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
    $client_count = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
    if ($client_count == 0) {
        $stmt_inv = $pdo->query("SELECT DISTINCT client_name, company_name, phone, emails FROM invoices WHERE client_name IS NOT NULL AND client_name != ''");
        $historical_clients = $stmt_inv->fetchAll();
        if ($historical_clients) {
            $insert_client = $pdo->prepare("INSERT IGNORE INTO clients (client_name, company_name, phone, emails) VALUES (?, ?, ?, ?)");
            foreach ($historical_clients as $hc) {
                $insert_client->execute([
                    $hc['client_name'],
                    $hc['company_name'],
                    $hc['phone'],
                    $hc['emails']
                ]);
            }
        }
    }

    // Seed default settings if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
    if ($stmt->fetchColumn() == 0) {
        $default_password_hash = password_hash('admin123', PASSWORD_BCRYPT);
        
        $insert_settings = $pdo->prepare("INSERT INTO settings (
            smtp_host, smtp_port, smtp_username, smtp_password, smtp_secure,
            company_name, company_phone, company_email, company_website, company_tagline,
            company_notes_default, admin_password
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $insert_settings->execute([
            's3508.bom1.stableserver.net',
            465,
            'noreply@theexperthub.in',
            'Inayah@62',
            'ssl',
            'The Expert Hub',
            '044 47873458',
            'enquiry@theexperthub.in',
            'theexperthub.in',
            'theexperthub.in | tehub.in',
            'Payment terms, bank details, or any other notes for the client...',
            $default_password_hash
        ]);
    }

    // Migration of existing SMTP credentials to smtp_accounts
    $smtp_count = $pdo->query("SELECT COUNT(*) FROM smtp_accounts")->fetchColumn();
    if ($smtp_count == 0) {
        // Fetch current credentials from settings
        $stmt = $pdo->query("SELECT smtp_host, smtp_port, smtp_username, smtp_password, smtp_secure, company_name FROM settings LIMIT 1");
        $settings_data = $stmt->fetch();
        if ($settings_data && !empty($settings_data['smtp_username'])) {
            $insert_smtp = $pdo->prepare("INSERT INTO smtp_accounts (
                display_name, smtp_host, smtp_port, smtp_username, smtp_password, smtp_secure, is_default
            ) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert_smtp->execute([
                $settings_data['company_name'] . ' (Noreply)',
                $settings_data['smtp_host'],
                $settings_data['smtp_port'],
                $settings_data['smtp_username'],
                $settings_data['smtp_password'],
                $settings_data['smtp_secure'],
                1 // Set as default
            ]);
        }
    }

    // Seed default Razorpay API details & default admin templates if missing/placeholder
    $settings_check = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();
    if ($settings_check) {
        $update_fields = [];
        $update_params = [];
        
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

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
