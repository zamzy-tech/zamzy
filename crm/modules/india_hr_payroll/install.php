<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// 1. Alter tblleads to support Telecalling campaign scripts
if (!$CI->db->field_exists('category', db_prefix() . 'leads')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'leads` ADD `category` VARCHAR(255) NULL AFTER `title`;');
}
if (!$CI->db->field_exists('main_feature', db_prefix() . 'leads')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'leads` ADD `main_feature` TEXT NULL AFTER `category`;');
}
if (!$CI->db->field_exists('pitch_script', db_prefix() . 'leads')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'leads` ADD `pitch_script` TEXT NULL AFTER `main_feature`;');
}

// 2. Employee Profile Details Table
if (!$CI->db->table_exists(db_prefix() . 'hr_employee_details')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'hr_employee_details` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `staff_id` int(11) NOT NULL,
      `father_mother_spouse_name` varchar(255) DEFAULT NULL,
      `relation_type` varchar(50) DEFAULT "Father",
      `dob` date DEFAULT NULL,
      `gender` varchar(20) DEFAULT "Male",
      `mobile_number` varchar(50) DEFAULT NULL,
      `current_address` text DEFAULT NULL,
      `permanent_address` text DEFAULT NULL,
      `designation` varchar(255) DEFAULT NULL,
      `department` varchar(255) DEFAULT NULL,
      `annual_ctc` decimal(15,2) DEFAULT 0.00,
      `probation_period` varchar(100) DEFAULT "3 Months",
      `notice_period` varchar(100) DEFAULT "30 Days",
      `working_hours` varchar(150) DEFAULT "9:30 AM - 6:30 PM (Mon - Sat)",
      `employment_terms` text DEFAULT NULL,
      `pan_number` varchar(50) DEFAULT NULL,
      `aadhaar_number` varchar(50) DEFAULT NULL,
      `pf_eligible` tinyint(1) DEFAULT 1,
      `uan_number` varchar(50) DEFAULT NULL,
      `previous_pf_number` varchar(100) DEFAULT NULL,
      `previous_member_id` varchar(100) DEFAULT NULL,
      `esic_number` varchar(50) DEFAULT NULL,
      `bank_name` varchar(150) DEFAULT NULL,
      `bank_account_no` varchar(100) DEFAULT NULL,
      `ifsc_code` varchar(50) DEFAULT NULL,
      `nominee_name` varchar(255) DEFAULT NULL,
      `nominee_relation` varchar(100) DEFAULT NULL,
      `nominee_dob` date DEFAULT NULL,
      `nominee_aadhaar` varchar(50) DEFAULT NULL,
      `emergency_contact_name` varchar(255) DEFAULT NULL,
      `emergency_contact_phone` varchar(50) DEFAULT NULL,
      `state` varchar(100) DEFAULT "Andhra Pradesh",
      `joining_date` date DEFAULT NULL,
      `relieving_date` date DEFAULT NULL,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `staff_id` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
}

// 3. Employee Documents Table (16-Slot Vault)
if (!$CI->db->table_exists(db_prefix() . 'hr_employee_documents')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'hr_employee_documents` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `staff_id` int(11) NOT NULL,
      `doc_type` varchar(100) NOT NULL,
      `file_name` varchar(255) NOT NULL,
      `file_path` varchar(255) NOT NULL,
      `notes` text DEFAULT NULL,
      `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `staff_id` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
}

// 4. Salary Structures Table
if (!$CI->db->table_exists(db_prefix() . 'hr_salary_structures')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'hr_salary_structures` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `staff_id` int(11) NOT NULL,
      `basic_salary` decimal(15,2) NOT NULL DEFAULT 0.00,
      `hra` decimal(15,2) NOT NULL DEFAULT 0.00,
      `special_allowance` decimal(15,2) NOT NULL DEFAULT 0.00,
      `other_allowances` decimal(15,2) NOT NULL DEFAULT 0.00,
      `gross_monthly` decimal(15,2) NOT NULL DEFAULT 0.00,
      `pf_applicable` tinyint(1) DEFAULT 1,
      `esi_applicable` tinyint(1) DEFAULT 1,
      `pt_applicable` tinyint(1) DEFAULT 1,
      `tds_monthly` decimal(15,2) DEFAULT 0.00,
      PRIMARY KEY (`id`),
      UNIQUE KEY `staff_id` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
}

// 5. Daily Attendance Table
if (!$CI->db->table_exists(db_prefix() . 'hr_attendance')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'hr_attendance` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `staff_id` int(11) NOT NULL,
      `date` date NOT NULL,
      `check_in` time DEFAULT NULL,
      `check_out` time DEFAULT NULL,
      `status` varchar(50) DEFAULT "Present",
      `work_hours` decimal(5,2) DEFAULT 8.00,
      `notes` text DEFAULT NULL,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `staff_date` (`staff_id`, `date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
}

// 6. Monthly Attendance Summary Table (For Payroll Processing)
if (!$CI->db->table_exists(db_prefix() . 'hr_monthly_attendance')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'hr_monthly_attendance` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `staff_id` int(11) NOT NULL,
      `month` int(2) NOT NULL,
      `year` int(4) NOT NULL,
      `total_days` int(3) NOT NULL DEFAULT 30,
      `present_days` decimal(5,1) NOT NULL DEFAULT 30.0,
      `paid_leaves` decimal(5,1) NOT NULL DEFAULT 0.0,
      `absent_days` decimal(5,1) NOT NULL DEFAULT 0.0,
      `payable_days` decimal(5,1) NOT NULL DEFAULT 30.0,
      `notes` text DEFAULT NULL,
      `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `staff_month_year` (`staff_id`, `month`, `year`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
}

// 7. Monthly Payroll Runs Table
if (!$CI->db->table_exists(db_prefix() . 'hr_payroll_runs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'hr_payroll_runs` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `month` int(2) NOT NULL,
      `year` int(4) NOT NULL,
      `status` varchar(50) DEFAULT "processed",
      `processed_by` int(11) DEFAULT NULL,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
}

// 8. Generated Payslips Table
if (!$CI->db->table_exists(db_prefix() . 'hr_payslips')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'hr_payslips` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `payroll_run_id` int(11) NOT NULL,
      `staff_id` int(11) NOT NULL,
      `month` int(2) NOT NULL,
      `year` int(4) NOT NULL,
      `payslip_number` varchar(100) NOT NULL,
      `basic` decimal(15,2) NOT NULL,
      `hra` decimal(15,2) NOT NULL,
      `special_allowance` decimal(15,2) NOT NULL,
      `gross_salary` decimal(15,2) NOT NULL,
      `pf_employee` decimal(15,2) NOT NULL,
      `pf_employer` decimal(15,2) NOT NULL,
      `esi_employee` decimal(15,2) NOT NULL,
      `esi_employer` decimal(15,2) NOT NULL,
      `professional_tax` decimal(15,2) NOT NULL,
      `tds` decimal(15,2) NOT NULL,
      `other_deductions` decimal(15,2) DEFAULT 0.00,
      `net_salary` decimal(15,2) NOT NULL,
      `attendance_days` decimal(5,1) DEFAULT 30.0,
      `leave_days` decimal(5,1) DEFAULT 0.0,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `staff_id` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
}
