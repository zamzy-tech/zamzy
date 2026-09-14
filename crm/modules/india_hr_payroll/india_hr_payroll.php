<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: India HR, Payroll & Agent Telecalling Suite
Description: Comprehensive HR suite for Admin (Attendance, Leaves, 16-slot Vault, Joining Letters, Payroll, Reports) & Dedicated Telecalling Lead Manager for Agents.
Version: 1.3.0
Author: TeHub Development Team
Requires at least: 2.3.0
*/

define('INDIA_HR_PAYROLL_MODULE_NAME', 'india_hr_payroll');

/**
 * Register activation & hooks
 */
register_activation_hook(INDIA_HR_PAYROLL_MODULE_NAME, 'india_hr_payroll_activation_hook');

function india_hr_payroll_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files & hooks
 */
hooks()->add_action('admin_init', 'india_hr_payroll_admin_init_hook');

function india_hr_payroll_admin_init_hook()
{
    $CI = &get_instance();

    if (is_admin()) {
        /**
         * ADMIN ONLY MENU: Complete HR, Payroll, Attendance & Bulk Lead System
         */
        $CI->app_menu->add_sidebar_menu_item('india-hr-payroll', [
            'name'     => 'HR & Payroll (Admin)',
            'icon'     => 'fa fa-address-card',
            'position' => 30,
        ]);

        $CI->app_menu->add_sidebar_children_item('india-hr-payroll', [
            'slug'     => 'india-hr-attendance',
            'name'     => '📅 Attendance & Leaves',
            'href'     => admin_url('india_hr_payroll/attendance'),
            'position' => 1,
        ]);

        $CI->app_menu->add_sidebar_children_item('india-hr-payroll', [
            'slug'     => 'india-hr-bulk-leads',
            'name'     => '📥 Bulk Upload & Assign Leads',
            'href'     => admin_url('india_hr_payroll/bulk_lead_import'),
            'position' => 2,
        ]);

        $CI->app_menu->add_sidebar_children_item('india-hr-payroll', [
            'slug'     => 'india-hr-agent-leads-admin',
            'name'     => '📞 Telecalling Monitor',
            'href'     => admin_url('india_hr_payroll/agent_leads'),
            'position' => 3,
        ]);

        $CI->app_menu->add_sidebar_children_item('india-hr-payroll', [
            'slug'     => 'india-hr-vault',
            'name'     => 'Document Vault (16 Slots)',
            'href'     => admin_url('india_hr_payroll/onboarding_vault'),
            'position' => 4,
        ]);

        $CI->app_menu->add_sidebar_children_item('india-hr-payroll', [
            'slug'     => 'india-hr-docs-gen',
            'name'     => 'Letter Generator (Joining/Offer)',
            'href'     => admin_url('india_hr_payroll/document_generator'),
            'position' => 5,
        ]);

        $CI->app_menu->add_sidebar_children_item('india-hr-payroll', [
            'slug'     => 'india-hr-ctc',
            'name'     => 'Salary & CTC Structures',
            'href'     => admin_url('india_hr_payroll/salary_structures'),
            'position' => 6,
        ]);

        $CI->app_menu->add_sidebar_children_item('india-hr-payroll', [
            'slug'     => 'india-hr-payroll-run',
            'name'     => 'Run Monthly Payroll',
            'href'     => admin_url('india_hr_payroll/run_payroll'),
            'position' => 7,
        ]);

        $CI->app_menu->add_sidebar_children_item('india-hr-payroll', [
            'slug'     => 'india-hr-payslips',
            'name'     => 'Payslip Registry',
            'href'     => admin_url('india_hr_payroll/payslips'),
            'position' => 8,
        ]);

        $CI->app_menu->add_sidebar_children_item('india-hr-payroll', [
            'slug'     => 'india-hr-reports',
            'name'     => 'PF / ESI / PT Reports',
            'href'     => admin_url('india_hr_payroll/pf_esi_reports'),
            'position' => 9,
        ]);

        $CI->app_menu->add_sidebar_children_item('india-hr-payroll', [
            'slug'     => 'india-hr-exit',
            'name'     => 'Exit & Relieving (F&F)',
            'href'     => admin_url('india_hr_payroll/exit_management'),
            'position' => 10,
        ]);
    } else {
        /**
         * AGENT / TELECALLER LOGIN: Telecalling Leads
         */
        $CI->app_menu->add_sidebar_menu_item('agent-telecalling-leads', [
            'name'     => '📞 Telecalling Leads',
            'icon'     => 'fa fa-phone',
            'href'     => admin_url('india_hr_payroll/agent_leads'),
            'position' => 5,
        ]);
    }
}
