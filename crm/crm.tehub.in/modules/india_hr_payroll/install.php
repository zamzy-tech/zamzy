<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// 1. tblhr_employee_details
if (!$CI->db->table_exists(db_prefix() . 'hr_employee_details')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'hr_employee_details` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `staff_id` INT(11) NOT NULL,
      `pan_number` VARCHAR(20) DEFAULT NULL,
      `aadhaar_number` VARCHAR(20) DEFAULT NULL,
      `uan_number` VARCHAR(30) DEFAULT NULL,
      `esic_number` VARCHAR(30) DEFAULT NULL,
      `bank_name` VARCHAR(100) DEFAULT NULL,
      `bank_account_no` VARCHAR(50) DEFAULT NULL,
      `ifsc_code` VARCHAR(20) DEFAULT NULL,
      `emergency_contact_name` VARCHAR(100) DEFAULT NULL,
      `emergency_contact_phone` VARCHAR(20) DEFAULT NULL,
      `state` VARCHAR(50) DEFAULT "Andhra Pradesh",
      `joining_date` DATE DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `staff_id` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}

// 2. tblhr_employee_documents
if (!$CI->db->table_exists(db_prefix() . 'hr_employee_documents')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'hr_employee_documents` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `staff_id` INT(11) NOT NULL,
      `doc_type` VARCHAR(50) NOT NULL,
      `file_name` VARCHAR(255) NOT NULL,
      `file_path` VARCHAR(255) NOT NULL,
      `notes` TEXT DEFAULT NULL,
      `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `staff_id` (`staff_id`),
      KEY `doc_type` (`doc_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}

// 3. tblhr_salary_structures
if (!$CI->db->table_exists(db_prefix() . 'hr_salary_structures')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'hr_salary_structures` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `staff_id` INT(11) NOT NULL,
      `basic_salary` DECIMAL(15,2) DEFAULT "0.00",
      `hra` DECIMAL(15,2) DEFAULT "0.00",
      `special_allowance` DECIMAL(15,2) DEFAULT "0.00",
      `other_allowances` DECIMAL(15,2) DEFAULT "0.00",
      `gross_monthly` DECIMAL(15,2) DEFAULT "0.00",
      `pf_applicable` TINYINT(1) DEFAULT "1",
      `esi_applicable` TINYINT(1) DEFAULT "1",
      `pt_applicable` TINYINT(1) DEFAULT "1",
      `tds_monthly` DECIMAL(15,2) DEFAULT "0.00",
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `staff_id` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}

// 4. tblhr_payroll_runs
if (!$CI->db->table_exists(db_prefix() . 'hr_payroll_runs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'hr_payroll_runs` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `month` INT(2) NOT NULL,
      `year` INT(4) NOT NULL,
      `status` VARCHAR(20) DEFAULT "processed",
      `processed_by` INT(11) NOT NULL,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}

// 5. tblhr_payslips
if (!$CI->db->table_exists(db_prefix() . 'hr_payslips')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'hr_payslips` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `payroll_run_id` INT(11) NOT NULL,
      `staff_id` INT(11) NOT NULL,
      `month` INT(2) NOT NULL,
      `year` INT(4) NOT NULL,
      `payslip_number` VARCHAR(50) NOT NULL,
      `basic` DECIMAL(15,2) DEFAULT "0.00",
      `hra` DECIMAL(15,2) DEFAULT "0.00",
      `special_allowance` DECIMAL(15,2) DEFAULT "0.00",
      `gross_salary` DECIMAL(15,2) DEFAULT "0.00",
      `pf_employee` DECIMAL(15,2) DEFAULT "0.00",
      `pf_employer` DECIMAL(15,2) DEFAULT "0.00",
      `esi_employee` DECIMAL(15,2) DEFAULT "0.00",
      `esi_employer` DECIMAL(15,2) DEFAULT "0.00",
      `professional_tax` DECIMAL(15,2) DEFAULT "0.00",
      `tds` DECIMAL(15,2) DEFAULT "0.00",
      `other_deductions` DECIMAL(15,2) DEFAULT "0.00",
      `net_salary` DECIMAL(15,2) DEFAULT "0.00",
      `attendance_days` INT(3) DEFAULT "30",
      `leave_days` INT(3) DEFAULT "0",
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `staff_id` (`staff_id`),
      KEY `payroll_run_id` (`payroll_run_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}
