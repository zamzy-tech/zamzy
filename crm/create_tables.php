<?php
define('BASEPATH', true);
require('application/config/app-config.php');

$conn = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Create tbllead_loan_details table
$sql1 = "CREATE TABLE IF NOT EXISTS `tbllead_loan_details` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `lead_id` INT NOT NULL UNIQUE,
    `profession_type` VARCHAR(50) DEFAULT 'salary',
    `loan_type` VARCHAR(100) DEFAULT NULL,
    `mother_name` VARCHAR(255) DEFAULT NULL,
    `co_applicant_name` VARCHAR(255) DEFAULT NULL,
    `co_applicant_mother_name` VARCHAR(255) DEFAULT NULL,
    `co_applicant_mobile` VARCHAR(20) DEFAULT NULL,
    `co_applicant_email` VARCHAR(100) DEFAULT NULL,
    `co_applicant_address` TEXT DEFAULT NULL,
    `ref1_name` VARCHAR(255) DEFAULT NULL,
    `ref1_phone` VARCHAR(20) DEFAULT NULL,
    `ref2_name` VARCHAR(255) DEFAULT NULL,
    `ref2_phone` VARCHAR(20) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql1) === TRUE) {
    echo "Table tbllead_loan_details created successfully<br>\n";
} else {
    echo "Error creating table tbllead_loan_details: " . $conn->error . "<br>\n";
}

// 2. Create tbllead_loan_documents table
$sql2 = "CREATE TABLE IF NOT EXISTS `tbllead_loan_documents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `lead_id` INT NOT NULL,
    `document_type` VARCHAR(100) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `uploaded_by_staff` INT DEFAULT NULL,
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql2) === TRUE) {
    echo "Table tbllead_loan_documents created successfully<br>\n";
} else {
    echo "Error creating table tbllead_loan_documents: " . $conn->error . "<br>\n";
}

// 2b. Create tbllead_loan_status_history table
$sql3 = "CREATE TABLE IF NOT EXISTS `tbllead_loan_status_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `lead_id` INT NOT NULL,
    `old_status` VARCHAR(100) DEFAULT NULL,
    `new_status` VARCHAR(100) DEFAULT NULL,
    `changed_by` VARCHAR(255) DEFAULT NULL,
    `proof_path` VARCHAR(255) DEFAULT NULL,
    `changed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql3) === TRUE) {
    echo "Table tbllead_loan_status_history created successfully<br>\n";
} else {
    echo "Error creating table tbllead_loan_status_history: " . $conn->error . "<br>\n";
}

// 3. Insert/ensure lead statuses

$statuses = [
    ['name' => 'Printed', 'color' => '#3b82f6', 'statusorder' => 8],
    ['name' => 'Hand to Kiran', 'color' => '#a855f7', 'statusorder' => 9],
    ['name' => 'Hand to Bank', 'color' => '#6366f1', 'statusorder' => 10],
    ['name' => 'Need to Reupload', 'color' => '#f97316', 'statusorder' => 11],
    ['name' => 'Bank Approved', 'color' => '#22c55e', 'statusorder' => 12]
];

foreach ($statuses as $st) {
    $name = $conn->real_escape_string($st['name']);
    $color = $st['color'];
    $order = $st['statusorder'];
    
    // check if exists
    $check = $conn->query("SELECT id FROM tblleads_status WHERE name = '$name'");
    if ($check->num_rows == 0) {
        $sql = "INSERT INTO tblleads_status (name, color, statusorder) VALUES ('$name', '$color', $order)";
        if ($conn->query($sql) === TRUE) {
            echo "Status '$name' added successfully<br>\n";
        } else {
            echo "Error adding status '$name': " . $conn->error . "<br>\n";
        }
    } else {
        echo "Status '$name' already exists<br>\n";
    }
}

$conn->close();
echo "Database migration finished!";
