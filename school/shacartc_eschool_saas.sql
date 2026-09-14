-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 13, 2026 at 09:34 PM
-- Server version: 10.6.28-MariaDB
-- PHP Version: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shacartc_eschool_saas`
--

-- --------------------------------------------------------

--
-- Table structure for table `addons`
--

CREATE TABLE `addons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `price` double(64,4) NOT NULL,
  `feature_id` bigint(20) UNSIGNED NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 => Inactive, 1 => Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addons`
--

INSERT INTO `addons` (`id`, `name`, `price`, `feature_id`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Whatsapp Auto Message', 1.0000, 9, 0, '2026-06-15 10:53:52', '2026-06-15 10:53:57', '2026-06-15 10:53:57');

-- --------------------------------------------------------

--
-- Table structure for table `addon_subscriptions`
--

CREATE TABLE `addon_subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `feature_id` bigint(20) UNSIGNED NOT NULL,
  `price` double(64,4) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0 => Discontinue next billing, 1 => Continue',
  `payment_transaction_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `extra_school_datas`
--

CREATE TABLE `extra_school_datas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_inquiry_id` bigint(20) UNSIGNED DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `form_field_id` bigint(20) UNSIGNED NOT NULL,
  `data` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(191) NOT NULL,
  `description` text NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `title`, `description`, `school_id`, `created_at`, `updated_at`) VALUES
(1, 'What is the School Management System?', 'Our School Management System is a comprehensive platform designed to automate and streamline school operations, including admissions, attendance, fees, examinations, communication, and reporting.', NULL, '2026-06-15 10:32:51', '2026-06-15 10:32:51'),
(2, 'Who can use this platform?', 'The platform is designed for school owners, administrators, principals, teachers, students, and parents, with role-based access for each user.', NULL, '2026-06-15 10:32:59', '2026-06-15 10:32:59'),
(3, 'Can multiple schools use the system?', 'Yes. Our platform supports a multi-school architecture, allowing multiple schools to operate independently under a single system managed by a Super Admin.', NULL, '2026-06-15 10:33:09', '2026-06-15 10:33:09'),
(4, 'Is the system cloud-based?', 'Yes. The platform is fully cloud-based, allowing users to access it anytime and from anywhere with an internet connection.', NULL, '2026-06-15 10:33:22', '2026-06-15 10:33:22'),
(5, 'Does the system support online admissions?', 'Yes. Schools can create and manage online admission forms, application reviews, and enrollment processes through the platform.', NULL, '2026-06-15 10:33:31', '2026-06-15 10:33:31'),
(6, 'Can parents access student information?', 'Yes. Parents can view attendance, academic performance, homework, fee details, notices, and other important updates through the parent portal.', NULL, '2026-06-15 10:33:41', '2026-06-15 10:33:41'),
(7, 'Does the system include fee management?', 'Yes. The platform provides complete fee management, including fee collection, online payments, invoices, receipts, and financial reports.', NULL, '2026-06-15 10:33:51', '2026-06-15 10:33:51'),
(8, 'Is there a mobile application available?', 'Yes. Our mobile application allows students, parents, teachers, and administrators to stay connected and access essential information on the go.', NULL, '2026-06-15 10:34:01', '2026-06-15 10:34:01'),
(9, 'How secure is the platform?', 'We use advanced security measures, encrypted data storage, secure authentication, and regular backups to ensure data protection and privacy.', NULL, '2026-06-15 10:34:13', '2026-06-15 10:34:13'),
(10, 'Can schools customize their settings?', 'Yes. Each school can customize its profile, academic structure, grading system, notifications, and operational settings.', NULL, '2026-06-15 10:34:21', '2026-06-15 10:34:21'),
(11, 'Does the system provide attendance management?', 'Yes. Schools can track student and staff attendance, generate reports, and monitor attendance records in real time.', NULL, '2026-06-15 10:34:30', '2026-06-15 10:34:30'),
(12, 'Can the platform generate reports?', 'Absolutely. The system provides detailed academic, financial, attendance, admission, and administrative reports for better decision-making.', NULL, '2026-06-15 10:34:37', '2026-06-15 10:34:37'),
(13, 'Do you provide technical support?', 'Yes. Our support team is available to assist schools with onboarding, training, troubleshooting, and ongoing technical support.', NULL, '2026-06-15 10:34:44', '2026-06-15 10:34:44'),
(14, 'How can a school get started?', 'Schools can register on the platform, select a suitable subscription plan, and begin managing their operations through a centralized dashboard.', NULL, '2026-06-15 10:34:51', '2026-06-15 10:34:51'),
(15, 'Does the system support multiple branches?', 'Yes. Schools with multiple branches or campuses can manage all locations from a single account while maintaining separate records.', NULL, '2026-06-15 10:35:00', '2026-06-15 10:35:00'),
(16, 'Can teachers manage classes and exams?', 'Yes. Teachers can manage class schedules, assignments, attendance, examinations, grades, and student performance records.', NULL, '2026-06-15 10:35:08', '2026-06-15 10:35:08'),
(17, 'Is training provided for new schools?', 'Yes. We provide onboarding assistance, user training, and documentation to ensure a smooth implementation process.', NULL, '2026-06-15 10:35:16', '2026-06-15 10:35:16'),
(18, 'Can data be exported from the system?', 'Yes. Schools can export reports, student records, attendance data, financial information, and other reports when needed.', NULL, '2026-06-15 10:35:24', '2026-06-15 10:35:24'),
(19, 'Why choose our School Management System?', 'Our platform offers a secure, scalable, user-friendly, and feature-rich solution that helps schools improve efficiency, communication, and overall management with low budget.', NULL, '2026-06-15 10:35:47', '2026-06-15 10:35:47');

-- --------------------------------------------------------

--
-- Table structure for table `features`
--

CREATE TABLE `features` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `is_default` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 => No, 1 => Yes',
  `status` int(11) NOT NULL DEFAULT 1,
  `required_vps` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `features`
--

INSERT INTO `features` (`id`, `name`, `is_default`, `status`, `required_vps`, `created_at`, `updated_at`) VALUES
(1, 'Student Management', 1, 1, 0, '2026-06-11 05:31:18', '2026-06-11 05:31:47'),
(2, 'Academics Management', 1, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47'),
(3, 'Slider Management', 0, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47'),
(4, 'Teacher Management', 1, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47'),
(5, 'Session Year Management', 1, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47'),
(6, 'Holiday Management', 0, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47'),
(7, 'Timetable Management', 0, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47'),
(8, 'Attendance Management', 0, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47'),
(9, 'Exam Management', 0, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47'),
(10, 'Lesson Management', 0, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47'),
(11, 'Assignment Management', 0, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47'),
(12, 'Announcement Management', 0, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47'),
(13, 'Staff Management', 0, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47'),
(14, 'Expense Management', 0, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47'),
(15, 'Staff Leave Management', 0, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47'),
(16, 'Fees Management', 0, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47'),
(17, 'School Gallery Management', 0, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47'),
(18, 'ID Card - Certificate Generation', 0, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47'),
(19, 'Website Management', 0, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47'),
(20, 'Chat Module', 0, 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47');

-- --------------------------------------------------------

--
-- Table structure for table `feature_sections`
--

CREATE TABLE `feature_sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(191) NOT NULL,
  `heading` varchar(191) DEFAULT NULL,
  `rank` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feature_section_lists`
--

CREATE TABLE `feature_section_lists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `feature_section_id` bigint(20) UNSIGNED NOT NULL,
  `feature` varchar(191) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `modal_type` varchar(191) NOT NULL,
  `modal_id` bigint(20) UNSIGNED NOT NULL,
  `file_name` varchar(1024) DEFAULT NULL,
  `file_thumbnail` varchar(1024) DEFAULT NULL,
  `type` tinytext NOT NULL COMMENT '1 = File Upload, 2 = Youtube Link, 3 = Video Upload, 4 = Other Link',
  `file_url` varchar(1024) NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `form_fields`
--

CREATE TABLE `form_fields` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(128) NOT NULL,
  `type` varchar(128) NOT NULL COMMENT 'text,number,textarea,dropdown,checkbox,radio,fileupload',
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `default_values` text DEFAULT NULL COMMENT 'values of radio,checkbox,dropdown,etc',
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rank` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `form_fields`
--

INSERT INTO `form_fields` (`id`, `name`, `type`, `is_required`, `default_values`, `school_id`, `rank`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'new', 'text', 1, NULL, NULL, 1, '2026-06-15 11:11:33', '2026-06-15 11:11:55', '2026-06-15 11:11:55'),
(2, 'DOMAIN TYPE', 'radio', 0, '[\"DEFAULT\",\"CUSTUM\"]', NULL, 1, '2026-06-15 11:55:06', '2026-06-15 11:55:57', '2026-06-15 11:55:57');

-- --------------------------------------------------------

--
-- Table structure for table `guidances`
--

CREATE TABLE `guidances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `link` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(512) NOT NULL,
  `code` varchar(64) NOT NULL,
  `file` varchar(512) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1=>active',
  `is_rtl` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `name`, `code`, `file`, `status`, `is_rtl`, `created_at`, `updated_at`) VALUES
(1, 'English', 'en', 'en.json', 1, 0, '2026-06-11 05:31:47', '2026-06-11 05:31:47');

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `school_name` varchar(191) NOT NULL,
  `city` varchar(191) NOT NULL,
  `students_count` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `source` varchar(50) NOT NULL DEFAULT 'google_ads',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `name`, `phone`, `email`, `school_name`, `city`, `students_count`, `message`, `source`, `created_at`, `updated_at`) VALUES
(1, 'YASEER ARAFATH', '9150137159', 'nihaanshah062@gmail.com', 'BRILLIANT CHILDRENS ACADEMY', 'chennai', '100-300', 'SFGSRD', 'google_ads', '2026-06-24 09:45:35', '2026-06-24 09:45:35'),
(2, 'yaseer', '9150137159', 'nihaanshah062@gmail.com', 'YASEER', 'Chennai', '100-300', 'DRHERD5', 'google_ads', '2026-06-24 09:49:10', '2026-06-24 09:49:10');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2022_04_01_091033_create_permission_tables', 1),
(6, '2022_04_01_105826_all_tables', 1),
(7, '2023_11_16_134449_version1-0-1', 1),
(8, '2023_12_07_120054_version1_1_0', 1),
(9, '2024_01_30_092228_version1_2_0', 1),
(10, '2024_03_12_173521_version1_3_0', 1),
(11, '2024_05_21_094714_version1_3_2', 1),
(12, '2024_07_21_093657_version1_4_0', 1),
(13, '2024_08_08_094709_version1_4_1', 1),
(14, '2024_10_17_112347_version1_5_0', 1),
(15, '2025_01_22_102146_version1_5_2', 1),
(16, '2025_04_10_100137_version1_5_4', 1),
(17, '2026_06_15_120000_add_domain_fields_to_school_inquiries_table', 2),
(18, '2026_06_16_130000_add_aadhar_pic_to_users_table', 3),
(19, '2026_06_16_160000_add_mother_fields_to_users_table', 4),
(20, '2026_06_24_100000_create_leads_table', 5);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1);

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) DEFAULT NULL,
  `description` varchar(191) DEFAULT NULL,
  `tagline` varchar(191) DEFAULT NULL,
  `student_charge` double(8,2) NOT NULL,
  `staff_charge` double(8,2) NOT NULL,
  `days` int(11) NOT NULL DEFAULT 1,
  `type` int(11) NOT NULL DEFAULT 1 COMMENT '0 => Prepaid, 1 => Postpaid',
  `no_of_students` int(11) NOT NULL DEFAULT 0,
  `no_of_staffs` int(11) NOT NULL DEFAULT 0,
  `charges` double(64,4) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 => Unpublished, 1 => Published',
  `is_trial` int(11) NOT NULL DEFAULT 0,
  `highlight` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 => No, 1 => Yes',
  `rank` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `description`, `tagline`, `student_charge`, `staff_charge`, `days`, `type`, `no_of_students`, `no_of_staffs`, `charges`, `status`, `is_trial`, `highlight`, `rank`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Pro', 'Unlimited Features', 'Best plan for school', 0.02, 0.02, 90, 1, 0, 0, 0.0000, 0, 0, 0, 1, '2026-06-11 05:33:54', '2026-06-15 10:42:56', '2026-06-15 10:42:56'),
(2, 'Trial Package', 'Perfect for schools looking to experience digital transformation. This trial package provides temporary access to key TEH SMS modules, allowing administrators, teachers, and staff to evaluate', NULL, 0.00, 0.00, 30, 1, 0, 0, 0.0000, 1, 1, 1, -1, '2026-06-15 10:09:09', '2026-06-15 10:09:09', NULL),
(3, 'Monthly Pro Pack', 'Access all essential school management tools in one powerful platform. The Monthly Pro plan is designed for schools that need complete control over academics, administration, communication.', 'Complete School Management Solution for Growing Institutions', 0.00, 0.00, 30, 0, 0, 0, 1200.0000, 1, 0, 1, 1, '2026-06-15 10:46:49', '2026-06-15 11:10:03', NULL),
(4, 'Quarterly Pro Pack', 'Access all essential school management tools in one powerful platform. The Quarterly Pro plan is designed for schools that need complete control over academics, administration, communication.', 'Complete School Management Solution for Growing Institutions', 0.00, 0.00, 90, 0, 0, 0, 3300.0000, 1, 0, 1, 2, '2026-06-15 11:05:12', '2026-06-15 11:10:03', NULL),
(5, 'Halferly Pro Pack', 'Access all essential school management tools in one powerful platform. The Halferly Pro plan is designed for schools that need complete control over academics, administration, communication.', 'Complete School Management Solution for Growing Institutions', 0.00, 0.00, 180, 0, 0, 0, 6000.0000, 1, 0, 1, 3, '2026-06-15 11:06:49', '2026-06-15 11:10:03', NULL),
(6, 'yearly pro pack', 'Access all essential school management tools in one powerful platform. The yearly pro plan is designed for schools that need complete control over academics, administration, communication, a', 'Complete School Management Solution for Growing Institutions', 0.00, 0.00, 365, 0, 0, 0, 10000.0000, 1, 0, 1, 4, '2026-06-15 11:07:42', '2026-06-15 11:10:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `package_features`
--

CREATE TABLE `package_features` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `package_id` bigint(20) UNSIGNED NOT NULL,
  `feature_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `package_features`
--

INSERT INTO `package_features` (`id`, `package_id`, `feature_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(2, 1, 2, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(3, 1, 3, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(4, 1, 4, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(5, 1, 5, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(6, 1, 6, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(7, 1, 7, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(8, 1, 8, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(9, 1, 9, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(10, 1, 10, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(11, 1, 11, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(12, 1, 12, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(13, 1, 13, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(14, 1, 14, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(15, 1, 15, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(16, 1, 16, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(17, 1, 17, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(18, 1, 18, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(19, 1, 19, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(20, 1, 20, '2026-06-11 05:33:54', '2026-06-11 05:33:54'),
(21, 2, 1, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(22, 2, 2, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(23, 2, 4, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(24, 2, 5, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(25, 2, 14, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(26, 2, 15, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(27, 2, 16, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(28, 2, 17, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(29, 2, 18, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(30, 2, 19, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(31, 2, 13, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(32, 2, 12, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(33, 2, 11, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(34, 2, 10, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(35, 2, 9, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(36, 2, 8, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(37, 2, 7, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(38, 2, 6, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(39, 2, 3, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(40, 2, 20, '2026-06-15 10:09:09', '2026-06-15 10:09:09'),
(41, 3, 2, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(42, 3, 5, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(43, 3, 1, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(44, 3, 4, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(45, 3, 12, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(46, 3, 11, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(47, 3, 8, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(48, 3, 20, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(49, 3, 9, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(50, 3, 14, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(51, 3, 16, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(52, 3, 6, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(53, 3, 18, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(54, 3, 10, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(55, 3, 17, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(56, 3, 3, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(57, 3, 15, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(58, 3, 13, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(59, 3, 7, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(60, 3, 19, '2026-06-15 10:46:49', '2026-06-15 11:08:39'),
(141, 4, 2, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(142, 4, 5, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(143, 4, 1, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(144, 4, 4, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(145, 4, 12, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(146, 4, 11, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(147, 4, 8, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(148, 4, 20, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(149, 4, 9, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(150, 4, 14, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(151, 4, 16, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(152, 4, 6, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(153, 4, 18, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(154, 4, 10, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(155, 4, 17, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(156, 4, 3, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(157, 4, 15, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(158, 4, 13, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(159, 4, 7, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(160, 4, 19, '2026-06-15 11:05:12', '2026-06-15 11:09:06'),
(181, 5, 2, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(182, 5, 5, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(183, 5, 1, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(184, 5, 4, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(185, 5, 12, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(186, 5, 11, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(187, 5, 8, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(188, 5, 20, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(189, 5, 9, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(190, 5, 14, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(191, 5, 16, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(192, 5, 6, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(193, 5, 18, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(194, 5, 10, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(195, 5, 17, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(196, 5, 3, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(197, 5, 15, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(198, 5, 13, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(199, 5, 7, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(200, 5, 19, '2026-06-15 11:06:49', '2026-06-15 11:09:25'),
(201, 6, 2, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(202, 6, 5, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(203, 6, 1, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(204, 6, 4, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(205, 6, 12, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(206, 6, 11, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(207, 6, 8, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(208, 6, 20, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(209, 6, 9, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(210, 6, 14, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(211, 6, 16, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(212, 6, 6, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(213, 6, 18, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(214, 6, 10, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(215, 6, 17, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(216, 6, 3, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(217, 6, 15, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(218, 6, 13, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(219, 6, 7, '2026-06-15 11:07:42', '2026-06-15 11:09:43'),
(220, 6, 19, '2026-06-15 11:07:42', '2026-06-15 11:09:43');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_configurations`
--

CREATE TABLE `payment_configurations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payment_method` varchar(191) NOT NULL,
  `api_key` varchar(191) NOT NULL,
  `secret_key` varchar(191) NOT NULL,
  `webhook_secret_key` varchar(191) NOT NULL,
  `bank_name` varchar(191) DEFAULT NULL,
  `account_name` varchar(191) DEFAULT NULL,
  `account_no` varchar(191) DEFAULT NULL,
  `currency_code` varchar(128) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0 - Disabled, 1 - Enabled',
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_configurations`
--

INSERT INTO `payment_configurations` (`id`, `payment_method`, `api_key`, `secret_key`, `webhook_secret_key`, `bank_name`, `account_name`, `account_no`, `currency_code`, `status`, `school_id`, `created_at`, `updated_at`) VALUES
(1, 'Stripe', '', '', '', '', '', '', '', 0, NULL, '2026-06-11 13:01:43', '2026-06-11 13:01:43'),
(2, 'Razorpay', 'rzp_live_StdtXsDIHOBBgI', 'KZiPqSqrtPaP75iJr91KGU7s', 'Inayah@62', '', '', '', '', 1, NULL, '2026-06-11 13:01:43', '2026-06-15 09:25:48'),
(3, 'Paystack', '', '', '', '', '', '', '', 0, NULL, '2026-06-11 13:01:44', '2026-06-11 13:01:44'),
(4, 'Flutterwave', '', '', '', '', '', '', '', 0, NULL, '2026-06-11 13:01:44', '2026-06-11 13:01:44');

-- --------------------------------------------------------

--
-- Table structure for table `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `amount` double(64,2) NOT NULL,
  `payment_gateway` varchar(191) NOT NULL,
  `order_id` varchar(191) DEFAULT NULL COMMENT 'order_id / payment_intent_id',
  `payment_id` varchar(191) DEFAULT NULL,
  `payment_signature` varchar(191) DEFAULT NULL,
  `payment_status` enum('failed','succeed','pending') NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_transactions`
--

INSERT INTO `payment_transactions` (`id`, `user_id`, `amount`, `payment_gateway`, `order_id`, `payment_id`, `payment_signature`, `payment_status`, `school_id`, `created_at`, `updated_at`) VALUES
(1, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:27:02', '2026-07-04 23:27:02'),
(2, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:27:12', '2026-07-04 23:27:12'),
(3, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:27:21', '2026-07-04 23:27:21'),
(4, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:27:21', '2026-07-04 23:27:21'),
(5, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:27:22', '2026-07-04 23:27:22'),
(6, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:27:22', '2026-07-04 23:27:22'),
(7, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:27:22', '2026-07-04 23:27:22'),
(8, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:35:17', '2026-07-04 23:35:17'),
(9, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:35:20', '2026-07-04 23:35:20'),
(10, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:35:23', '2026-07-04 23:35:23'),
(11, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:35:24', '2026-07-04 23:35:24'),
(12, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:35:24', '2026-07-04 23:35:24'),
(13, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:35:24', '2026-07-04 23:35:24'),
(14, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:35:24', '2026-07-04 23:35:24'),
(15, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:35:24', '2026-07-04 23:35:24'),
(16, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:35:31', '2026-07-04 23:35:31'),
(17, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:35:32', '2026-07-04 23:35:32'),
(18, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:35:32', '2026-07-04 23:35:32'),
(19, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:35:33', '2026-07-04 23:35:33'),
(20, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:35:33', '2026-07-04 23:35:33'),
(21, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:35:33', '2026-07-04 23:35:33'),
(22, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:35:33', '2026-07-04 23:35:33'),
(23, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:35:33', '2026-07-04 23:35:33'),
(24, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:37:19', '2026-07-04 23:37:19'),
(25, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:37:21', '2026-07-04 23:37:21'),
(26, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:37:22', '2026-07-04 23:37:22'),
(27, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:37:22', '2026-07-04 23:37:22'),
(28, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:37:23', '2026-07-04 23:37:23'),
(29, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:37:23', '2026-07-04 23:37:23'),
(30, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:37:24', '2026-07-04 23:37:24'),
(31, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:37:26', '2026-07-04 23:37:26'),
(32, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:37:26', '2026-07-04 23:37:26'),
(33, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:37:27', '2026-07-04 23:37:27'),
(34, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:37:30', '2026-07-04 23:37:30'),
(35, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:37:32', '2026-07-04 23:37:32'),
(36, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:37:32', '2026-07-04 23:37:32'),
(37, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:37:32', '2026-07-04 23:37:32'),
(38, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:37:33', '2026-07-04 23:37:33'),
(39, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:37:33', '2026-07-04 23:37:33'),
(40, 43, 1.00, 'Razorpay', NULL, NULL, NULL, 'pending', 16, '2026-07-04 23:37:33', '2026-07-04 23:37:33');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'role-list', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(2, 'role-create', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(3, 'role-edit', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(4, 'role-delete', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(5, 'language-list', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(6, 'language-create', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(7, 'language-edit', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(8, 'language-delete', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(9, 'schools-list', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(10, 'schools-create', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(11, 'schools-edit', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(12, 'schools-delete', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(13, 'package-list', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(14, 'package-create', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(15, 'package-edit', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(16, 'package-delete', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(17, 'addons-list', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(18, 'addons-create', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(19, 'addons-edit', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(20, 'addons-delete', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(21, 'guidance-list', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(22, 'guidance-create', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(23, 'guidance-edit', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(24, 'guidance-delete', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(25, 'system-setting-manage', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(26, 'fcm-setting-create', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(27, 'email-setting-create', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(28, 'privacy-policy', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(29, 'contact-us', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(30, 'about-us', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(31, 'terms-condition', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(32, 'app-settings', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(33, 'subscription-view', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(34, 'staff-list', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(35, 'staff-create', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(36, 'staff-edit', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(37, 'staff-delete', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(38, 'faqs-list', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(39, 'faqs-create', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(40, 'faqs-edit', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(41, 'faqs-delete', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(42, 'fcm-setting-manage', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(43, 'front-site-setting', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(44, 'payment-settings', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(45, 'subscription-settings', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(46, 'subscription-change-bills', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(47, 'school-terms-condition', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(48, 'subscription-bill-payment', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(49, 'web-settings', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(50, 'email-template', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(51, 'custom-school-email', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(52, 'database-backup', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(53, 'school-custom-field-list', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(54, 'school-custom-field-create', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(55, 'school-custom-field-edit', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46'),
(56, 'school-custom-field-delete', 'web', '2026-06-11 05:31:46', '2026-06-11 05:31:46');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(191) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `custom_role` tinyint(1) NOT NULL DEFAULT 1,
  `editable` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `school_id`, `custom_role`, `editable`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'web', NULL, 0, 0, '2026-06-11 05:31:46', '2026-06-11 05:31:46');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1);

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `address` varchar(191) NOT NULL,
  `support_phone` varchar(191) NOT NULL,
  `support_email` varchar(191) NOT NULL,
  `tagline` varchar(191) NOT NULL,
  `logo` varchar(191) NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'user_id',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 => Deactivate, 1 => Active',
  `domain` varchar(191) DEFAULT NULL,
  `database_name` varchar(191) DEFAULT NULL,
  `code` varchar(191) DEFAULT NULL,
  `domain_type` varchar(191) DEFAULT 'default',
  `type` varchar(191) DEFAULT 'custom',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`id`, `name`, `address`, `support_phone`, `support_email`, `tagline`, `logo`, `admin_id`, `status`, `domain`, `database_name`, `code`, `domain_type`, `type`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'School 1', 'Bhuj', '1234567890', 'school1@gmail.com', 'We Provide Best Education', 'school/logo.png', 2, 1, 'school1', 'shacartc_eschool_saas_1_school', 'SCH202412', 'default', 'custom', '2026-06-11 05:32:18', '2026-06-15 09:28:25', '2026-06-15 09:28:25'),
(2, 'School 2', 'Bhuj', '1234567890', 'school2@gmail.com', 'We Provide Best Education', 'school/logo.png', 3, 1, NULL, 'shacartc_eschool_saas_2_school', NULL, 'default', 'custom', '2026-06-11 05:32:18', '2026-06-15 09:28:28', '2026-06-15 09:28:28'),
(3, 'BRILLIANT CHILDRENS ACADEMY', 'Kasumuru, Venkatachalam Mandal,\r\nNellore District, Andhra Pradesh - 524320', '9491920982', 'SCHOOL@TEHUB.IN', 'Give Your Child the Best Foundation for Success', 'super-admin/school/6a2ab232118e63.381792891781183026.jpeg', 4, 1, NULL, 'shacartc_eschool_saas_3_brilliant', 'SCH20262', 'default', 'custom', '2026-06-11 13:03:46', '2026-06-15 09:28:40', '2026-06-15 09:28:40'),
(6, 'BRILLIANT CHILDRENS ACADEMY', 'no 20\r\nchoolai', '09150137159', 'SCHOOL62@TEHUB.IN', 'new', 'super-admin/school/6a2ac0c79b51e4.810249081781186759.jpeg', 7, 1, 'NEW', 'shacartc_eschool_saas_6_brilliant', 'SCH20264', 'default', 'custom', '2026-06-11 14:05:59', '2026-06-15 09:28:35', '2026-06-15 09:28:35'),
(7, 'BRILLIANT CHILDRENS ACADEMY', 'NO. 9-297, KASMUR, VENKATACHALAM, NELLORE - 524320', '7416410369', 'BCA@BRILLIANTBCA.COM', 'Enroll Your Child for a Bright Future', 'super-admin/school/6a2fc103441aa1.698993961781514499.png', 8, 1, 'brilliantbca.com', 'shacartc_eschool_saas_7_brilliant', 'SCH20267', 'custom', 'custom', '2026-06-11 14:22:04', '2026-06-16 10:05:33', NULL),
(10, 'BRILLIANT CHILDRENS ACADEMY', 'KASMUR,NELLORE', '917416410369', 'ADMIN@BRILLIANTBCA.COM', 'EVERY STUDENT RULE THEIR LIFE WITH EDUCATION', 'super-admin/school/6a2e81fd711b76.241826021781432829.jpeg', 37, 1, 'brilliant-childrens-academy', 'shacartc_eschool_saas_1_brilliant', 'TEH20261', 'default', 'custom', '2026-06-14 12:04:57', '2026-06-15 09:28:46', '2026-06-15 09:28:46'),
(11, 'BRILLIANT CHILDRENS ACADEMY', 'Kasumuru, Venkatachalam Mandal,\nNellore District, Andhra Pradesh - 524320', '9491920982', 'admin@brilliantbca.in', 'Give Your Child the Best Foundation for Success', 'super-admin/school/6a2ab232118e63.381792891781183026.jpeg', 38, 1, 'brilliant-childrens-academy-2', 'shacartc_eschool_saas_2_brilliant', 'TEH20262', 'default', 'custom', '2026-06-14 16:02:47', '2026-06-15 13:22:17', '2026-06-15 13:22:17'),
(15, 'Mohammad Yaseer Arafath', '2nd Floor, No 20', '08939220422', 'yas.araf62@gmail.com', 'Choollaipallam, KK Nagar', 'no_image_available.jpg', 42, 0, 'news', 'shacartc_eschool_saas_15_mohammad', 'SCH20268', 'default', 'custom', '2026-07-04 21:51:04', '2026-07-04 21:55:54', '2026-07-04 21:55:54'),
(16, 'YASEER ARAFATH', 'no 20', '09150137159', 'nihaanshah062@gmail.com', 'choolai', 'no_image_available.jpg', 43, 1, 'b1', 'shacartc_eschool_saas_16_yaseer', 'SCH20268', 'default', 'custom', '2026-07-04 21:56:14', '2026-07-04 21:56:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `school_inquiries`
--

CREATE TABLE `school_inquiries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_name` varchar(191) NOT NULL,
  `school_address` varchar(191) NOT NULL,
  `school_phone` varchar(191) NOT NULL,
  `school_email` varchar(191) NOT NULL,
  `school_tagline` varchar(191) NOT NULL,
  `domain` varchar(191) DEFAULT NULL,
  `domain_type` varchar(50) DEFAULT 'default',
  `date` date NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_settings`
--

CREATE TABLE `school_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `data` text NOT NULL,
  `type` varchar(191) DEFAULT NULL COMMENT 'datatype like string , file etc',
  `school_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_settings`
--

INSERT INTO `school_settings` (`id`, `name`, `data`, `type`, `school_id`) VALUES
(1, 'app_link', 'https://school.tehub.in/downloads/Tehub_School_Parent.apk', 'string', 1),
(2, 'app_link', 'https://school.tehub.in/downloads/Tehub_School_Parent.apk', 'string', 2),
(3, 'app_link', 'https://school.tehub.in/downloads/Tehub_School_Parent.apk', 'string', 3),
(4, 'app_link', 'https://school.tehub.in/downloads/Tehub_School_Parent.apk', 'string', 6),
(5, 'app_link', 'https://school.tehub.in/downloads/Tehub_School_Parent.apk', 'string', 7),
(6, 'app_link', 'https://school.tehub.in/downloads/Tehub_School_Parent.apk', 'string', 10),
(7, 'app_link', 'https://school.tehub.in/downloads/Tehub_School_Parent.apk', 'string', 11),
(9, 'teacher_app_link', 'https://school.tehub.in/downloads/Tehub_School_Staff.apk', 'string', 1),
(11, 'teacher_app_link', 'https://school.tehub.in/downloads/Tehub_School_Staff.apk', 'string', 2),
(13, 'teacher_app_link', 'https://school.tehub.in/downloads/Tehub_School_Staff.apk', 'string', 3),
(15, 'teacher_app_link', 'https://school.tehub.in/downloads/Tehub_School_Staff.apk', 'string', 6),
(17, 'teacher_app_link', 'https://school.tehub.in/downloads/Tehub_School_Staff.apk', 'string', 7),
(19, 'teacher_app_link', 'https://school.tehub.in/downloads/Tehub_School_Staff.apk', 'string', 10),
(21, 'teacher_app_link', 'https://school.tehub.in/downloads/Tehub_School_Staff.apk', 'string', 11),
(24, 'admin_app_link', 'https://school.tehub.in/downloads/Tehub_School_Admin.apk', 'string', 1),
(27, 'admin_app_link', 'https://school.tehub.in/downloads/Tehub_School_Admin.apk', 'string', 2),
(30, 'admin_app_link', 'https://school.tehub.in/downloads/Tehub_School_Admin.apk', 'string', 3),
(33, 'admin_app_link', 'https://school.tehub.in/downloads/Tehub_School_Admin.apk', 'string', 6),
(36, 'admin_app_link', 'https://school.tehub.in/downloads/Tehub_School_Admin.apk', 'string', 7),
(39, 'admin_app_link', 'https://school.tehub.in/downloads/Tehub_School_Admin.apk', 'string', 10),
(42, 'admin_app_link', 'https://school.tehub.in/downloads/Tehub_School_Admin.apk', 'string', 11);

-- --------------------------------------------------------

--
-- Table structure for table `staffs`
--

CREATE TABLE `staffs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `qualification` varchar(512) DEFAULT NULL,
  `salary` double NOT NULL DEFAULT 0,
  `joining_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_support_schools`
--

CREATE TABLE `staff_support_schools` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `package_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `student_charge` double(8,4) NOT NULL,
  `staff_charge` double(8,4) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `package_type` int(11) NOT NULL DEFAULT 1 COMMENT '0 => Prepaid, 1 => Postpaid',
  `no_of_students` int(11) NOT NULL DEFAULT 0,
  `no_of_staffs` int(11) NOT NULL DEFAULT 0,
  `charges` double(64,4) NOT NULL DEFAULT 0.0000,
  `billing_cycle` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `school_id`, `package_id`, `name`, `student_charge`, `staff_charge`, `start_date`, `end_date`, `package_type`, `no_of_students`, `no_of_staffs`, `charges`, `billing_cycle`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Pro', 0.0200, 0.0200, '2026-06-11', '2026-09-08', 1, 0, 0, 0.0000, 90, '2026-06-11 07:02:30', '2026-06-11 07:02:30'),
(2, 2, 1, 'Pro', 0.0200, 0.0200, '2026-06-11', '2026-09-08', 1, 0, 0, 0.0000, 90, '2026-06-11 07:02:50', '2026-06-11 07:02:50'),
(3, 7, 1, 'Pro', 0.0200, 0.0200, '2026-06-11', '2026-06-15', 1, 0, 0, 0.0000, 5, '2026-06-11 14:33:18', '2026-07-04 21:51:17'),
(4, 7, 6, 'yearly pro pack', 0.0000, 0.0000, '2026-06-15', '2027-06-14', 1, 0, 0, 10000.0000, 365, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(5, 15, 2, 'Trial Package', 0.0000, 0.0000, '2026-07-04', '2026-08-03', 1, 0, 0, 0.0000, 30, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(6, 16, 2, 'Trial Package', 0.0000, 0.0000, '2026-07-04', '2026-08-02', 1, 0, 0, 0.0000, 30, '2026-07-04 16:46:29', '2026-07-04 23:17:11');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_bills`
--

CREATE TABLE `subscription_bills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(191) DEFAULT NULL,
  `amount` double(64,4) NOT NULL,
  `total_student` bigint(20) NOT NULL,
  `total_staff` bigint(20) NOT NULL,
  `payment_transaction_id` bigint(20) UNSIGNED DEFAULT NULL,
  `due_date` date NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_bills`
--

INSERT INTO `subscription_bills` (`id`, `subscription_id`, `description`, `amount`, `total_student`, `total_staff`, `payment_transaction_id`, `due_date`, `school_id`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, 0.0000, 2, 2, NULL, '2026-06-17', 7, '2026-06-15 13:21:46', '2026-06-15 13:21:46');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_features`
--

CREATE TABLE `subscription_features` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `feature_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_features`
--

INSERT INTO `subscription_features` (`id`, `subscription_id`, `feature_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(2, 1, 2, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(3, 1, 3, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(4, 1, 4, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(5, 1, 5, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(6, 1, 6, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(7, 1, 7, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(8, 1, 8, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(9, 1, 9, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(10, 1, 10, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(11, 1, 11, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(12, 1, 12, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(13, 1, 13, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(14, 1, 14, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(15, 1, 15, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(16, 1, 16, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(17, 1, 17, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(18, 1, 18, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(19, 1, 19, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(20, 1, 20, '2026-06-11 07:02:30', '2026-06-15 10:48:11'),
(21, 2, 1, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(22, 2, 2, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(23, 2, 3, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(24, 2, 4, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(25, 2, 5, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(26, 2, 6, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(27, 2, 7, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(28, 2, 8, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(29, 2, 9, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(30, 2, 10, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(31, 2, 11, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(32, 2, 12, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(33, 2, 13, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(34, 2, 14, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(35, 2, 15, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(36, 2, 16, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(37, 2, 17, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(38, 2, 18, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(39, 2, 19, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(40, 2, 20, '2026-06-11 07:02:50', '2026-06-15 10:48:11'),
(121, 4, 2, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(122, 4, 5, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(123, 4, 1, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(124, 4, 4, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(125, 4, 12, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(126, 4, 11, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(127, 4, 8, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(128, 4, 20, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(129, 4, 9, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(130, 4, 14, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(131, 4, 16, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(132, 4, 6, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(133, 4, 18, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(134, 4, 10, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(135, 4, 17, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(136, 4, 3, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(137, 4, 15, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(138, 4, 13, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(139, 4, 7, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(140, 4, 19, '2026-06-15 13:21:46', '2026-06-15 13:21:46'),
(141, 5, 1, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(142, 5, 2, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(143, 5, 3, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(144, 5, 4, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(145, 5, 5, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(146, 5, 6, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(147, 5, 7, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(148, 5, 8, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(149, 5, 9, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(150, 5, 10, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(151, 5, 11, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(152, 5, 12, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(153, 5, 13, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(154, 5, 14, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(155, 5, 15, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(156, 5, 16, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(157, 5, 17, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(158, 5, 18, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(159, 5, 19, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(160, 5, 20, '2026-07-04 16:46:29', '2026-07-04 16:46:29'),
(261, 6, 1, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(262, 6, 2, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(263, 6, 3, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(264, 6, 4, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(265, 6, 5, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(266, 6, 6, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(267, 6, 7, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(268, 6, 8, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(269, 6, 9, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(270, 6, 10, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(271, 6, 11, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(272, 6, 12, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(273, 6, 13, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(274, 6, 14, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(275, 6, 15, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(276, 6, 16, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(277, 6, 17, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(278, 6, 18, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(279, 6, 19, '2026-07-04 17:58:34', '2026-07-04 17:58:34'),
(280, 6, 20, '2026-07-04 17:58:34', '2026-07-04 17:58:34');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `data` text NOT NULL,
  `type` varchar(191) DEFAULT NULL COMMENT 'datatype like string , file etc'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `name`, `data`, `type`) VALUES
(1, 'hero_title_1', 'Experience seamless learning and school management with THE EXPERT HUB\'s powerful tools for students, parents, and educators.', 'text'),
(2, 'hero_title_2', 'Learn Smarter. Achieve More. Grow with TEHUB', 'text'),
(3, 'about_us_title', 'Empowering Schools Through Smart Technology', 'text'),
(4, 'about_us_heading', 'Why Choose THE EXPERT HUB?', 'text'),
(5, 'about_us_description', 'THE EXPERT HUB is a comprehensive School Management System designed to simplify and modernize educational administration. Our platform seamlessly connects schools, teachers, students, and parents through powerful digital tools that streamline daily operations, enhance communication, and improve academic management. From admissions and attendance to examinations, fees, and performance tracking, THE EXPERT HUB provides everything educational institutions need to operate efficiently. With a focus on innovation, reliability, and user-friendly experiences, we help schools save time, increase productivity, and deliver better educational outcomes.', 'text'),
(6, 'about_us_points', 'Affordable price,Easy to manage admin panel,Data Security', 'text'),
(7, 'custom_package_status', '1', 'text'),
(8, 'custom_package_description', 'Tailor your experience with our custom package options. From personalized services to bespoke solutions, we offer flexibility to meet your unique needs.', 'text'),
(9, 'download_our_app_description', 'Admin Portal\r\nManage your entire school ecosystem efficiently with powerful administrative tools and real-time reporting.\r\n\r\nTeacher App\r\nTrack attendance, manage academics, assign homework, and communicate seamlessly with students and parents.\r\n\r\nParent App\r\nMonitor attendance, results, fees, homework, and school updates to stay actively involved in your child\'s education.\r\n\r\nTEH SMS — Connecting Administrators, Teachers, Parents, and Students Through Smart Technology.', 'text'),
(10, 'theme_primary_color', '#56cc99', 'text'),
(11, 'theme_secondary_color', '#215679', 'text'),
(12, 'theme_secondary_color_1', '#38a3a5', 'text'),
(13, 'theme_primary_background_color', '#f2f5f7', 'text'),
(14, 'theme_text_secondary_color', '#5c788c', 'text'),
(15, 'tag_line', 'Transform School Management With THE EXPERT HUB SMS', 'text'),
(16, 'mobile', '04447873458', 'text'),
(17, 'hero_description', 'Experience the future of education with our THE EXPERT HUB platform. Streamline attendance, assignments, exams, and more. Elevate your school\'s efficiency and engagement.', 'text'),
(18, 'display_school_logos', '1', 'text'),
(19, 'display_counters', '1', 'text'),
(20, 'email_template_school_registration', '&lt;p&gt;Dear {school_admin_name},&lt;/p&gt; &lt;p&gt;Welcome to {system_name}!&lt;/p&gt; &lt;p&gt;We are excited to have you as part of our educational community. Below are your registration details to access the system:&lt;/p&gt; &lt;hr&gt; &lt;p&gt;&lt;strong&gt;School Name:&lt;/strong&gt; {school_name}&lt;/p&gt; &lt;p&gt;&lt;strong&gt;System URL:&lt;/strong&gt; {url}&lt;/p&gt; &lt;p&gt;&lt;strong&gt;Your Login Credentials:&lt;/strong&gt;&lt;/p&gt; &lt;ul&gt; &lt;li&gt;&lt;strong&gt;Email:&lt;/strong&gt; {email}&lt;/li&gt; &lt;li&gt;&lt;strong&gt;Password:&lt;/strong&gt; {password}&lt;/li&gt; &lt;li&gt;&lt;strong&gt;School Code:&lt;/strong&gt; {code}&lt;/li&gt; &lt;/ul&gt; &lt;hr&gt; &lt;p&gt;&lt;strong&gt;Please follow these steps to complete your registration:&lt;/strong&gt;&lt;/p&gt; &lt;ol&gt; &lt;li&gt;Click on the system URL provided above.&lt;/li&gt; &lt;li&gt;Enter your email and password.&lt;/li&gt; &lt;li&gt;Follow the instructions to complete your profile setup.&lt;/li&gt; &lt;/ol&gt; &lt;p&gt;&lt;strong&gt;Important:&lt;/strong&gt;&lt;/p&gt; &lt;ul&gt; &lt;li&gt;For security reasons, please change your password after your first login.&lt;/li&gt; &lt;li&gt;If you encounter any issues during the registration process, please do not hesitate to contact our support team at {support_email} or call {contact}.&lt;/li&gt; &lt;/ul&gt; &lt;p&gt;Thank you for choosing {system_name}. We are committed to providing you with the best educational tools and resources.&lt;/p&gt; &lt;p&gt;Best regards,&lt;/p&gt; &lt;p&gt;{super_admin_name}&lt;br&gt;{system_name}&lt;br&gt;{support_email}&lt;br&gt;{url}&lt;/p&gt; &lt;br&gt; &lt;p&gt;&lt;strong&gt;This email was auto-generated, so don&#039;t reply.&lt;/strong&gt;&lt;/p&gt;', 'text'),
(21, 'system_version', '1.5.4', 'string'),
(23, 'email_template_two_factor_authentication_code', '&lt;p&gt;Dear {school_admin_name},&lt;/p&gt; &lt;p&gt;Welcome to {system_name}!&lt;/p&gt; &lt;p&gt;We are excited to have you as part of our educational community. To enhance the security of your account, we have enabled Two-Factor Authentication (2FA) for your login.&lt;/p&gt; &lt;p&gt;&lt;strong&gt;Your Verification Code:&lt;/strong&gt;&lt;/p&gt; &lt;p&gt;&lt;strong&gt;{verification_code}&lt;/strong&gt;&lt;/p&gt; &lt;p&gt;This verification code is required to complete your login process. Please enter the code within the next {expiration_time} minutes. If the code expires, you can request a new one by following the same process.&lt;/p&gt; &lt;hr&gt; &lt;p&gt;&lt;strong&gt;Important:&lt;/strong&gt;&lt;/p&gt; &lt;ul&gt; &lt;li&gt;If you did not request this verification code, please contact our support team immediately at {support_email} or call {support_contact} to secure your account.&lt;/li&gt; &lt;li&gt;For additional security, ensure that no one else has access to your email or device when retrieving your verification code.&lt;/li&gt; &lt;/ul&gt; &lt;p&gt;If you have any issues with the 2FA process or need assistance, our support team is ready to help at {support_email} or {support_contact}.&lt;/p&gt; &lt;p&gt;Thank you for taking extra steps to secure your account. We appreciate your commitment to keeping your information safe.&lt;/p&gt; &lt;p&gt;Best regards,&lt;/p&gt; &lt;p&gt;{super_admin_name}&lt;br&gt;{system_name}&lt;br&gt;{support_email}&lt;br&gt;{url}&lt;/p&gt; &lt;br&gt; &lt;p&gt;&lt;strong&gt;This email was auto-generated, so please do not reply.&lt;/strong&gt;&lt;/p&gt;', 'text'),
(24, 'school_inquiry', '0', 'string'),
(25, 'file_upload_size_limit', '100', 'string'),
(26, 'wizard_checkMark', '1', 'integer'),
(27, 'system_settings_wizard_checkMark', '1', 'integer'),
(28, 'notification_settings_wizard_checkMark', '1', 'integer'),
(29, 'email_settings_wizard_checkMark', '1', 'integer'),
(30, 'verify_email_wizard_checkMark', '1', 'integer'),
(31, 'email_template_settings_wizard_checkMark', '1', 'integer'),
(32, 'payment_settings_wizard_checkMark', '1', 'integer'),
(33, 'third_party_api_settings_wizard_checkMark', '1', 'integer'),
(34, 'time_zone', 'Asia/Kolkata', 'string'),
(35, 'date_format', 'd-m-Y', 'date'),
(36, 'time_format', 'h:i A', 'time'),
(37, 'theme_color', '#22577A', 'string'),
(38, 'session_year', '1', 'string'),
(39, 'email_verified', '1', 'string'),
(40, 'subscription_alert', '7', 'integer'),
(41, 'currency_code', 'INR', 'string'),
(42, 'currency_symbol', '₹', 'string'),
(43, 'additional_billing_days', '2', 'integer'),
(44, 'system_name', 'THE EXPERT HUB SMS', 'string'),
(45, 'address', 'NS COMPLEX, NO 20, CHOOLLAIPALLAM, MGR NAGAR, CHENNAI - 600078', 'string'),
(46, 'billing_cycle_in_days', '30', 'integer'),
(47, 'current_plan_expiry_warning_days', '7', 'integer'),
(48, 'front_site_theme_color', '#e9f9f3', 'text'),
(49, 'primary_color', '#3ccb9b', 'text'),
(50, 'secondary_color', '#245a7f', 'text'),
(51, 'short_description', 'THE EXPERT HUB - Manage Your School', 'text'),
(52, 'facebook', 'https://www.tehub.in', 'text'),
(53, 'instagram', 'https://www.youtube.com', 'text'),
(54, 'linkedin', 'https://www.linkedin.com/in/mohammad-yaseer-arafath/', 'text'),
(55, 'footer_text', '<p>&copy; <a title=\"THE EXPERT HUB \" href=\"https://wwW.tehub.in\" target=\"_blank\" rel=\"noopener\">TEHUB</a>. All Rights Reserved</p>', 'text'),
(56, 'tagline', 'We Provide the best Education', 'text'),
(57, 'super_admin_name', 'Super Admin', 'text'),
(59, 'web_maintenance', '', 'string'),
(90, 'mail_mailer', 'smtp', 'string'),
(91, 'mail_host', 'mail.tehub.in', 'string'),
(92, 'mail_port', '465', 'string'),
(93, 'mail_username', 'school@tehub.in', 'string'),
(94, 'mail_password', 'admin@2026', 'string'),
(95, 'mail_encryption', 'ssl', 'string'),
(96, 'mail_send_from', 'school@tehub.in', 'string'),
(98, 'school_code_prefix', 'SCH', 'string'),
(119, 'school_reject_template', '', 'string'),
(123, 'school_prefix', 'SCH', 'text'),
(124, 'firebase_project_id', 'teh-sms', 'string'),
(137, 'app_link', 'https://play.google.com/store/apps/details?id=dz.creadev.bcafamily&hl=en_IN', 'string'),
(138, 'ios_app_link', '', 'string'),
(139, 'app_version', '16', 'string'),
(140, 'ios_app_version', '', 'string'),
(141, 'force_app_update', '0', 'string'),
(142, 'app_maintenance', '0', 'string'),
(143, 'teacher_app_link', 'https://school.tehub.in/downloads/Tehub_School_Staff.apk', 'string'),
(144, 'teacher_ios_app_link', '', 'string'),
(145, 'teacher_app_version', '16', 'string'),
(146, 'teacher_ios_app_version', '', 'string'),
(147, 'teacher_force_app_update', '0', 'string'),
(148, 'teacher_app_maintenance', '0', 'string'),
(149, 'horizontal_logo', 'super-admin/system-settings/logo.png', 'file'),
(150, 'vertical_logo', 'super-admin/system-settings/logo.png', 'file'),
(151, 'favicon', 'super-admin/system-settings/logo.png', 'file'),
(152, 'login_page_logo', 'super-admin/system-settings/logo.png', 'file'),
(204, 'trial_days', '30', 'text'),
(205, 'student_limit', '50000', 'text'),
(206, 'staff_limit', '5000', 'text'),
(211, 'admin_app_link', 'https://school.tehub.in/downloads/Tehub_School_Admin.apk', 'string'),
(212, 'admin_ios_app_link', '', 'string'),
(213, 'admin_app_version', '16', 'string'),
(214, 'admin_ios_app_version', '', 'string'),
(215, 'admin_force_app_update', '0', 'string'),
(216, 'admin_app_maintenance', '0', 'string'),
(230, 'contact_us', '&lt;h3 data-section-id=&quot;15a5db4&quot; data-start=&quot;0&quot; data-end=&quot;14&quot;&gt;Contact Us&lt;/h3&gt;\n&lt;p data-start=&quot;16&quot; data-end=&quot;50&quot;&gt;&lt;strong data-start=&quot;16&quot; data-end=&quot;50&quot;&gt;Need Help? We&#039;re Here for You!&lt;/strong&gt;&lt;/p&gt;\n&lt;p data-start=&quot;52&quot; data-end=&quot;294&quot;&gt;If you have any questions, concerns, or need assistance using the TEH SMS Parent App, our support team is ready to help. Whether it&#039;s related to student information, attendance, fees, examinations, or app access, feel free to reach out to us.&lt;/p&gt;\n&lt;p data-start=&quot;296&quot; data-end=&quot;319&quot;&gt;&lt;strong data-start=&quot;296&quot; data-end=&quot;319&quot;&gt;Contact Information&lt;/strong&gt;&lt;/p&gt;\n&lt;p data-start=&quot;321&quot; data-end=&quot;418&quot;&gt;📞 &lt;strong data-start=&quot;324&quot; data-end=&quot;334&quot;&gt;Phone:&lt;/strong&gt; 044 47873458&lt;br data-start=&quot;350&quot; data-end=&quot;353&quot;&gt;📧 &lt;strong data-start=&quot;356&quot; data-end=&quot;366&quot;&gt;Email:&lt;/strong&gt; &lt;a class=&quot;decorated-link cursor-pointer&quot; rel=&quot;noopener&quot; data-start=&quot;367&quot; data-end=&quot;385&quot;&gt;school@tehub.in&lt;/a&gt;&lt;br data-start=&quot;385&quot; data-end=&quot;388&quot;&gt;🌐 &lt;strong data-start=&quot;391&quot; data-end=&quot;403&quot;&gt;Website:&lt;/strong&gt; &lt;a class=&quot;decorated-link cursor-pointer&quot; target=&quot;_new&quot; rel=&quot;noopener&quot; data-start=&quot;404&quot; data-end=&quot;418&quot;&gt;www.school.tehub.in&lt;/a&gt;&lt;/p&gt;\n&lt;p data-start=&quot;420&quot; data-end=&quot;437&quot;&gt;&lt;strong data-start=&quot;420&quot; data-end=&quot;437&quot;&gt;Support Hours&lt;/strong&gt;&lt;/p&gt;\n&lt;p data-start=&quot;439&quot; data-end=&quot;516&quot;&gt;🕒 Monday &amp;ndash; Saturday: 9:00 AM &amp;ndash; 6:00 PM&lt;br data-start=&quot;478&quot; data-end=&quot;481&quot;&gt;🚫 Sunday &amp;amp; Public Holidays: Closed&lt;/p&gt;\n&lt;p data-start=&quot;518&quot; data-end=&quot;533&quot;&gt;&lt;strong data-start=&quot;518&quot; data-end=&quot;533&quot;&gt;For Parents &amp;amp; Teachers&lt;/strong&gt;&lt;/p&gt;\n&lt;p data-start=&quot;535&quot; data-end=&quot;755&quot;&gt;If you experience login issues or notice incorrect student information, please contact your school administration first. For technical assistance with the mobile application, our support team will be happy to assist you.&lt;/p&gt;\n&lt;p data-start=&quot;757&quot; data-end=&quot;952&quot;&gt;&lt;strong data-start=&quot;757&quot; data-end=&quot;783&quot;&gt;Your feedback matters!&lt;/strong&gt;&lt;br data-start=&quot;783&quot; data-end=&quot;786&quot;&gt;We continuously improve TEH SMS to provide a better experience for schools, parents, teachers, and students. Feel free to share your suggestions and feedback with us.&lt;/p&gt;\n&lt;p data-start=&quot;954&quot; data-end=&quot;1022&quot; data-is-last-node=&quot;&quot; data-is-only-node=&quot;&quot;&gt;&lt;strong data-start=&quot;954&quot; data-end=&quot;1022&quot; data-is-last-node=&quot;&quot;&gt;TEH SMS &amp;ndash; Connecting Schools, Parents, and Students Efficiently.&lt;/strong&gt;&lt;/p&gt;', 'string'),
(231, 'about_us', '&lt;p&gt;&lt;strong&gt;About TEH SMS&lt;/strong&gt;&lt;/p&gt;\n&lt;p&gt;TEH SMS (The Expert Hub School Management System) is a comprehensive digital platform designed to simplify and streamline school administration, communication, and academic management. Our goal is to connect schools, parents, teachers, and students through a secure and user-friendly system that enhances efficiency and transparency.&lt;/p&gt;\n&lt;p&gt;With TEH SMS, parents can easily monitor their child&#039;s academic progress, attendance, examination results, homework, fee details, announcements, and other important school activities anytime and anywhere. The platform is built to improve communication between schools and families while reducing administrative workload.&lt;/p&gt;\n&lt;p&gt;We are committed to providing innovative technology solutions that help educational institutions manage their daily operations effectively and deliver a better learning experience for students.&lt;/p&gt;\n&lt;p&gt;&lt;strong&gt;Contact Information&lt;/strong&gt;&lt;/p&gt;\n&lt;p&gt;📞 Phone: 044 47873458&lt;/p&gt;\n&lt;p&gt;📧 Email: &lt;a href=&quot;mailto:school@tehub.in&quot;&gt;school@tehub.in&lt;/a&gt;&lt;/p&gt;\n&lt;p&gt;🌐 Website: &lt;a href=&quot;http://www.school.tehub.in/&quot;&gt;www.school.tehub.in&lt;/a&gt;&lt;/p&gt;\n&lt;p&gt;🌐 Corporate Website: &lt;a href=&quot;http://www.tehub.in/&quot;&gt;www.tehub.in&lt;/a&gt;&lt;/p&gt;\n&lt;p&gt;Thank you for choosing TEH SMS as your trusted school management solution.&lt;/p&gt;', 'string'),
(232, 'privacy_policy', '&lt;div class=&quot;qMYqUG_convSearchResultHighlightRoot&quot;&gt;\n&lt;div class=&quot;&quot; data-turn-id-container=&quot;request-WEB:c27746b1-de7e-44a1-b149-46c06464cf40-43&quot; data-is-intersecting=&quot;true&quot;&gt;\n&lt;section class=&quot;text-token-text-primary w-full focus:outline-none has-data-writing-block:pointer-events-none [&amp;amp;:has([data-writing-block])&amp;gt;*]:pointer-events-auto R6Vx5W_threadScrollVars scroll-mb-[calc(var(--scroll-root-safe-area-inset-bottom,0px)+var(--thread-response-height))] scroll-mt-[calc(var(--header-height)+min(200px,max(70px,20svh)))]&quot; dir=&quot;auto&quot; data-turn-id=&quot;request-WEB:c27746b1-de7e-44a1-b149-46c06464cf40-43&quot; data-turn-id-container=&quot;request-WEB:c27746b1-de7e-44a1-b149-46c06464cf40-43&quot; data-testid=&quot;conversation-turn-26&quot; data-scroll-anchor=&quot;false&quot; data-turn=&quot;assistant&quot;&gt;\n&lt;div class=&quot;text-base my-auto mx-auto pb-10 [--thread-content-margin:var(--thread-content-margin-xs,calc(var(--spacing)*4))] @w-sm/main:[--thread-content-margin:var(--thread-content-margin-sm,calc(var(--spacing)*6))] @w-lg/main:[--thread-content-margin:var(--thread-content-margin-lg,calc(var(--spacing)*16))] px-(--thread-content-margin)&quot;&gt;\n&lt;div class=&quot;[--thread-content-max-width:40rem] @w-lg/main:[--thread-content-max-width:48rem] mx-auto max-w-(--thread-content-max-width) flex-1 group/turn-messages focus-visible:outline-hidden relative flex w-full min-w-0 flex-col agent-turn&quot; data-conversation-screenshot-content=&quot;&quot;&gt;\n&lt;div class=&quot;flex max-w-full flex-col gap-4 grow&quot;&gt;\n&lt;div class=&quot;min-h-8 text-message relative flex w-full flex-col items-end gap-2 text-start break-words whitespace-normal outline-none keyboard-focused:focus-ring [.text-message+&amp;amp;]:mt-1&quot; dir=&quot;auto&quot; tabindex=&quot;0&quot; data-message-author-role=&quot;assistant&quot; data-message-id=&quot;d328ccdd-6198-441a-9b9d-c7fbe8a06082&quot; data-message-model-slug=&quot;gpt-5-5&quot; data-turn-start-message=&quot;true&quot;&gt;\n&lt;div class=&quot;flex w-full flex-col gap-1 empty:hidden&quot;&gt;\n&lt;div class=&quot;markdown prose dark:prose-invert wrap-break-word w-full dark markdown-new-styling&quot;&gt;\n&lt;h3 data-section-id=&quot;13qgxxh&quot; data-start=&quot;84&quot; data-end=&quot;109&quot;&gt;System Privacy Policy&lt;/h3&gt;\n&lt;p data-start=&quot;111&quot; data-end=&quot;585&quot;&gt;TEH SMS respects the privacy of all users and is committed to protecting the information stored within the platform. We collect and process data only for the purpose of providing school management services, improving system performance, and ensuring secure access to authorized users. We do not sell or share personal information with unauthorized third parties. All data is protected using industry-standard security measures and is accessible only to authorized personnel.&lt;/p&gt;\n&lt;hr data-start=&quot;587&quot; data-end=&quot;590&quot;&gt;\n&lt;p data-start=&quot;1161&quot; data-end=&quot;1659&quot;&gt;&amp;nbsp;&lt;/p&gt;\n&lt;p data-start=&quot;1661&quot; data-end=&quot;1684&quot;&gt;&lt;strong data-start=&quot;1661&quot; data-end=&quot;1684&quot;&gt;Contact Information&lt;/strong&gt;&lt;/p&gt;\n&lt;p data-start=&quot;1686&quot; data-end=&quot;1807&quot; data-is-last-node=&quot;&quot; data-is-only-node=&quot;&quot;&gt;📞 Phone: 044 47873458&lt;br data-start=&quot;1708&quot; data-end=&quot;1711&quot;&gt;📧 Email: &lt;a class=&quot;decorated-link cursor-pointer&quot; rel=&quot;noopener&quot; data-start=&quot;1721&quot; data-end=&quot;1736&quot;&gt;school@tehub.in&lt;/a&gt;&lt;br data-start=&quot;1736&quot; data-end=&quot;1739&quot;&gt;🌐 Website: &lt;a class=&quot;decorated-link&quot; href=&quot;http://www.school.tehub.in&quot; target=&quot;_new&quot; rel=&quot;noopener&quot; data-start=&quot;1751&quot; data-end=&quot;1770&quot;&gt;www.school.tehub.in&lt;/a&gt;&lt;br data-start=&quot;1770&quot; data-end=&quot;1773&quot;&gt;🌐 Corporate Website: &lt;a class=&quot;decorated-link&quot; href=&quot;http://www.tehub.in&quot; target=&quot;_new&quot; rel=&quot;noopener&quot; data-start=&quot;1795&quot; data-end=&quot;1807&quot; data-is-last-node=&quot;&quot;&gt;www.tehub.in&lt;/a&gt;&lt;/p&gt;\n&lt;/div&gt;\n&lt;/div&gt;\n&lt;/div&gt;\n&lt;/div&gt;\n&lt;div class=&quot;z-0 flex min-h-[46px] justify-start&quot;&gt;&amp;nbsp;&lt;/div&gt;\n&lt;div class=&quot;mt-3 w-full empty:hidden&quot;&gt;\n&lt;div class=&quot;text-center&quot;&gt;&amp;nbsp;&lt;/div&gt;\n&lt;/div&gt;\n&lt;/div&gt;\n&lt;/div&gt;\n&lt;/section&gt;\n&lt;/div&gt;\n&lt;/div&gt;\n&lt;div class=&quot;pointer-events-none -mt-px h-px translate-y-[calc(var(--scroll-root-safe-area-inset-bottom)-14*var(--spacing))]&quot; aria-hidden=&quot;true&quot;&gt;&amp;nbsp;&lt;/div&gt;', 'string'),
(233, 'teacher_staff_privacy_policy', '&lt;h3 data-section-id=&quot;j7z8it&quot; data-start=&quot;592&quot; data-end=&quot;624&quot;&gt;Teacher/Staff Privacy Policy&lt;/h3&gt;\n&lt;p data-start=&quot;626&quot; data-end=&quot;1119&quot;&gt;TEH SMS collects and stores teacher and staff information solely for school administration purposes, including attendance, payroll, leave management, academic activities, and communication. Personal information is accessible only to authorized school administrators and designated staff members. We maintain appropriate security measures to protect employee data and ensure confidentiality. Staff information will never be shared with unauthorized parties without consent or legal requirement.&lt;/p&gt;\n&lt;p data-start=&quot;111&quot; data-end=&quot;585&quot;&gt;&amp;nbsp;&lt;/p&gt;\n&lt;hr data-start=&quot;587&quot; data-end=&quot;590&quot;&gt;\n&lt;p data-start=&quot;1661&quot; data-end=&quot;1684&quot;&gt;&lt;strong data-start=&quot;1661&quot; data-end=&quot;1684&quot;&gt;Contact Information&lt;/strong&gt;&lt;/p&gt;\n&lt;div class=&quot;flex max-w-full flex-col gap-4 grow&quot;&gt;\n&lt;div class=&quot;min-h-8 text-message relative flex w-full flex-col items-end gap-2 text-start break-words whitespace-normal outline-none keyboard-focused:focus-ring [.text-message+&amp;amp;]:mt-1&quot; dir=&quot;auto&quot; tabindex=&quot;0&quot; data-message-author-role=&quot;assistant&quot; data-message-id=&quot;d328ccdd-6198-441a-9b9d-c7fbe8a06082&quot; data-message-model-slug=&quot;gpt-5-5&quot; data-turn-start-message=&quot;true&quot;&gt;\n&lt;div class=&quot;flex w-full flex-col gap-1 empty:hidden&quot;&gt;\n&lt;div class=&quot;markdown prose dark:prose-invert wrap-break-word w-full dark markdown-new-styling&quot;&gt;\n&lt;p data-start=&quot;1686&quot; data-end=&quot;1807&quot; data-is-last-node=&quot;&quot; data-is-only-node=&quot;&quot;&gt;📞 Phone: 044 47873458&lt;br data-start=&quot;1708&quot; data-end=&quot;1711&quot;&gt;📧 Email: &lt;a class=&quot;decorated-link cursor-pointer&quot; rel=&quot;noopener&quot; data-start=&quot;1721&quot; data-end=&quot;1736&quot;&gt;school@tehub.in&lt;/a&gt;&lt;br data-start=&quot;1736&quot; data-end=&quot;1739&quot;&gt;🌐 Website: &lt;a class=&quot;decorated-link&quot; href=&quot;http://www.school.tehub.in&quot; target=&quot;_new&quot; rel=&quot;noopener&quot; data-start=&quot;1751&quot; data-end=&quot;1770&quot;&gt;www.school.tehub.in&lt;/a&gt;&lt;br data-start=&quot;1770&quot; data-end=&quot;1773&quot;&gt;🌐 Corporate Website: &lt;a class=&quot;decorated-link&quot; href=&quot;http://www.tehub.in&quot; target=&quot;_new&quot; rel=&quot;noopener&quot; data-start=&quot;1795&quot; data-end=&quot;1807&quot; data-is-last-node=&quot;&quot;&gt;www.tehub.in&lt;/a&gt;&lt;/p&gt;\n&lt;/div&gt;\n&lt;/div&gt;\n&lt;/div&gt;\n&lt;/div&gt;\n&lt;div class=&quot;z-0 flex min-h-[46px] justify-start&quot;&gt;&amp;nbsp;&lt;/div&gt;', 'string'),
(234, 'student_parent_privacy_policy', '&lt;div class=&quot;flex max-w-full flex-col gap-4 grow&quot;&gt;\n&lt;div class=&quot;min-h-8 text-message relative flex w-full flex-col items-end gap-2 text-start break-words whitespace-normal outline-none keyboard-focused:focus-ring [.text-message+&amp;amp;]:mt-1&quot; dir=&quot;auto&quot; tabindex=&quot;0&quot; data-message-author-role=&quot;assistant&quot; data-message-id=&quot;d328ccdd-6198-441a-9b9d-c7fbe8a06082&quot; data-message-model-slug=&quot;gpt-5-5&quot; data-turn-start-message=&quot;true&quot;&gt;\n&lt;div class=&quot;flex w-full flex-col gap-1 empty:hidden&quot;&gt;\n&lt;div class=&quot;markdown prose dark:prose-invert wrap-break-word w-full dark markdown-new-styling&quot;&gt;\n&lt;h3 data-section-id=&quot;7dt122&quot; data-start=&quot;1126&quot; data-end=&quot;1159&quot;&gt;Student/Parent Privacy Policy&lt;/h3&gt;\n&lt;p data-start=&quot;1161&quot; data-end=&quot;1659&quot;&gt;TEH SMS is committed to safeguarding student and parent information. Data such as student records, attendance, examination results, fee details, and parent contact information is collected only to support educational and administrative activities. Access to this information is restricted to authorized school personnel and the respective parent accounts. We do not sell or misuse personal data and continuously work to maintain a secure and reliable environment for students, parents, and schools.&lt;/p&gt;\n&lt;p data-start=&quot;111&quot; data-end=&quot;585&quot;&gt;&amp;nbsp;&lt;/p&gt;\n&lt;hr data-start=&quot;587&quot; data-end=&quot;590&quot;&gt;\n&lt;p data-start=&quot;1661&quot; data-end=&quot;1684&quot;&gt;&lt;strong data-start=&quot;1661&quot; data-end=&quot;1684&quot;&gt;Contact Information&lt;/strong&gt;&lt;/p&gt;\n&lt;p data-start=&quot;1686&quot; data-end=&quot;1807&quot; data-is-last-node=&quot;&quot; data-is-only-node=&quot;&quot;&gt;📞 Phone: 044 47873458&lt;br data-start=&quot;1708&quot; data-end=&quot;1711&quot;&gt;📧 Email: &lt;a class=&quot;decorated-link cursor-pointer&quot; rel=&quot;noopener&quot; data-start=&quot;1721&quot; data-end=&quot;1736&quot;&gt;school@tehub.in&lt;/a&gt;&lt;br data-start=&quot;1736&quot; data-end=&quot;1739&quot;&gt;🌐 Website: &lt;a class=&quot;decorated-link&quot; href=&quot;http://www.school.tehub.in&quot; target=&quot;_new&quot; rel=&quot;noopener&quot; data-start=&quot;1751&quot; data-end=&quot;1770&quot;&gt;www.school.tehub.in&lt;/a&gt;&lt;br data-start=&quot;1770&quot; data-end=&quot;1773&quot;&gt;🌐 Corporate Website: &lt;a class=&quot;decorated-link&quot; href=&quot;http://www.tehub.in&quot; target=&quot;_new&quot; rel=&quot;noopener&quot; data-start=&quot;1795&quot; data-end=&quot;1807&quot; data-is-last-node=&quot;&quot;&gt;www.tehub.in&lt;/a&gt;&lt;/p&gt;\n&lt;/div&gt;\n&lt;/div&gt;\n&lt;/div&gt;\n&lt;/div&gt;\n&lt;div class=&quot;z-0 flex min-h-[46px] justify-start&quot;&gt;&amp;nbsp;&lt;/div&gt;', 'string'),
(235, 'refund_cancellation', '&lt;h3&gt;Refund &amp;amp; Cancellation Policy&lt;/h3&gt;\n&lt;p&gt;Subscription fees paid for TEH SMS services are generally non-refundable once the subscription has been activated. Schools may cancel their subscription renewal at any time before the next billing cycle. In exceptional cases involving duplicate payments or technical billing errors, refund requests may be reviewed and processed at the discretion of TEH SMS management. Refund requests must be submitted in writing to &lt;strong&gt;&lt;a href=&quot;mailto:school@tehub.in&quot;&gt;school@tehub.in&lt;/a&gt;&lt;/strong&gt; within 7 days of the transaction date. Approved refunds will be processed through the original payment method within a reasonable period.&lt;/p&gt;\n&lt;p&gt;&amp;nbsp;&lt;/p&gt;\n&lt;hr&gt;\n&lt;p&gt;&lt;strong&gt;Contact Information&lt;/strong&gt;&lt;/p&gt;\n&lt;p&gt;📞 Phone: 044 47873458&lt;br&gt;📧 Email: &lt;a href=&quot;mailto:school@tehub.in&quot;&gt;school@tehub.in&lt;/a&gt;&lt;br&gt;🌐 Website: &lt;a href=&quot;http://www.school.tehub.in/&quot;&gt;www.school.tehub.in&lt;/a&gt;&lt;br&gt;🌐 Corporate Website: &lt;a href=&quot;http://www.tehub.in/&quot;&gt;www.tehub.in&lt;/a&gt;&lt;/p&gt;', 'string'),
(236, 'teacher_terms_condition', '&lt;h3&gt;Teacher Terms &amp;amp; Conditions&lt;/h3&gt;\n&lt;p&gt;Teachers and staff members are authorized to use TEH SMS for academic and administrative purposes only. Users must ensure that student records, attendance, grades, and other information entered into the system are accurate and up to date. Teachers are responsible for protecting confidential information and must not share sensitive data with unauthorized individuals. Any misuse of the platform or violation of data privacy standards may lead to access restrictions and disciplinary action.&lt;/p&gt;\n&lt;p&gt;&amp;nbsp;&lt;/p&gt;\n&lt;hr&gt;\n&lt;p&gt;&lt;strong&gt;Contact Information&lt;/strong&gt;&lt;/p&gt;\n&lt;p&gt;📞 Phone: 044 47873458&lt;br&gt;📧 Email: &lt;a href=&quot;mailto:school@tehub.in&quot;&gt;school@tehub.in&lt;/a&gt;&lt;br&gt;🌐 Website: &lt;a href=&quot;http://www.school.tehub.in/&quot;&gt;www.school.tehub.in&lt;/a&gt;&lt;br&gt;🌐 Corporate Website: &lt;a href=&quot;http://www.tehub.in/&quot;&gt;www.tehub.in&lt;/a&gt;&lt;/p&gt;', 'string'),
(237, 'student_terms_condition', '&lt;h3&gt;Student Terms &amp;amp; Conditions&lt;/h3&gt;\n&lt;p&gt;Students using TEH SMS must use the platform only for educational purposes. Students are responsible for maintaining the confidentiality of their account credentials and must not share access with others. Any misuse of the platform, including unauthorized access, inappropriate communication, or attempts to alter records, may result in account suspension and disciplinary action by the institution. Students are expected to follow their school&#039;s policies while using the platform.&lt;/p&gt;\n&lt;p&gt;&amp;nbsp;&lt;/p&gt;\n&lt;hr&gt;\n&lt;p&gt;&lt;strong&gt;Contact Information&lt;/strong&gt;&lt;/p&gt;\n&lt;p&gt;📞 Phone: 044 47873458&lt;br&gt;📧 Email: &lt;a href=&quot;mailto:school@tehub.in&quot;&gt;school@tehub.in&lt;/a&gt;&lt;br&gt;🌐 Website: &lt;a href=&quot;http://www.school.tehub.in/&quot;&gt;www.school.tehub.in&lt;/a&gt;&lt;br&gt;🌐 Corporate Website: &lt;a href=&quot;http://www.tehub.in/&quot;&gt;www.tehub.in&lt;/a&gt;&lt;/p&gt;', 'string'),
(238, 'terms_condition', '&lt;h3&gt;Terms &amp;amp; Conditions&lt;/h3&gt;\n&lt;p&gt;By using TEH SMS, schools, administrators, teachers, students, and parents agree to comply with all applicable laws and platform guidelines. Users are responsible for maintaining the confidentiality of their login credentials and ensuring that information entered into the system is accurate. TEH SMS reserves the right to modify, suspend, or discontinue services when necessary. Unauthorized access, misuse of data, or activities that compromise system security are strictly prohibited.&lt;/p&gt;\n&lt;hr&gt;\n&lt;p&gt;&lt;strong&gt;Contact Information&lt;/strong&gt;&lt;/p&gt;\n&lt;p&gt;📞 Phone: 044 47873458&lt;br&gt;📧 Email: &lt;a href=&quot;mailto:school@tehub.in&quot;&gt;school@tehub.in&lt;/a&gt;&lt;br&gt;🌐 Website: &lt;a href=&quot;http://www.school.tehub.in/&quot;&gt;www.school.tehub.in&lt;/a&gt;&lt;br&gt;🌐 Corporate Website: &lt;a href=&quot;http://www.tehub.in/&quot;&gt;www.tehub.in&lt;/a&gt;&lt;/p&gt;', 'string'),
(239, 'home_image', 'super-admin/system-settings/6a2f866912ad45.556164051781499497.png', 'file'),
(240, 'hero_title_2_image', 'super-admin/system-settings/6a2f866d6512b5.175776001781499501.png', 'file'),
(241, 'about_us_image', 'super-admin/system-settings/6a2f866dcd9e63.867937071781499501.png', 'file'),
(242, 'download_our_app_image', 'super-admin/system-settings/6a2f866e24d5a2.224170821781499502.png', 'file'),
(289, 'facebook_name', 'Website', 'text'),
(290, 'facebook_icon', 'fab fa-website', 'text'),
(291, 'instagram_name', 'youtube', 'text'),
(292, 'instagram_icon', 'fab fa-youtube', 'text'),
(293, 'linkedin_name', 'LinkedIn', 'text'),
(294, 'linkedin_icon', 'fab fa-linkedin', 'text');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(128) NOT NULL,
  `last_name` varchar(128) NOT NULL,
  `mobile` varchar(191) DEFAULT NULL,
  `email` varchar(191) NOT NULL,
  `password` varchar(191) NOT NULL,
  `gender` varchar(16) DEFAULT NULL,
  `image` varchar(512) DEFAULT NULL,
  `aadhar_pic` varchar(191) DEFAULT NULL,
  `mother_name` varchar(191) DEFAULT NULL,
  `mother_mobile` varchar(191) DEFAULT NULL,
  `mother_image` varchar(512) DEFAULT NULL,
  `mother_aadhar_pic` varchar(191) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `current_address` varchar(191) DEFAULT NULL,
  `permanent_address` varchar(191) DEFAULT NULL,
  `occupation` varchar(128) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `reset_request` tinyint(4) NOT NULL DEFAULT 0,
  `fcm_id` varchar(1024) DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `language` varchar(191) NOT NULL DEFAULT 'en',
  `remember_token` varchar(100) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `two_factor_enabled` tinyint(4) NOT NULL DEFAULT 1,
  `two_factor_secret` varchar(191) DEFAULT NULL,
  `two_factor_expires_at` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `mobile`, `email`, `password`, `gender`, `image`, `aadhar_pic`, `mother_name`, `mother_mobile`, `mother_image`, `mother_aadhar_pic`, `dob`, `current_address`, `permanent_address`, `occupation`, `status`, `reset_request`, `fcm_id`, `school_id`, `language`, `remember_token`, `email_verified_at`, `two_factor_enabled`, `two_factor_secret`, `two_factor_expires_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'super', 'admin', '', 'superadmin@gmail.com', '$2y$10$X6yg6MhegVwZjUzNpl5bcuS7jvozByf4g27Fq6dalpSJ/aMlAJOny', 'male', 'logo.svg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'en', NULL, '2026-06-11 05:32:03', 0, NULL, NULL, '2026-06-11 05:32:03', '2026-06-15 10:40:35', NULL),
(2, 'School 1', 'Demo 1', '1234567890', 'school1@gmail.com', '$2y$10$R8tEaURo1c8S7si.VJ7XbuEbV5OXHNNjVxqG7sr6xVUiLjv.za4Cy', 'male', 'users/school_admin.png', NULL, NULL, NULL, NULL, NULL, NULL, 'Bhuj', 'Bhuj', NULL, 1, 0, NULL, 1, 'en', NULL, NULL, 1, NULL, NULL, '2026-06-11 05:32:18', '2026-06-15 09:28:25', '2026-06-15 09:28:25'),
(3, 'School 2', 'Demo 2', '1234567890', 'school2@gmail.com', '$2y$10$CUZkoBTft5kNWjb58i6L6.xMs.fBZHEolO8dBdmpP.JIrBiGKN66q', 'male', 'users/school_admin.png', NULL, NULL, NULL, NULL, NULL, NULL, 'Bhuj', 'Bhuj', NULL, 1, 0, NULL, 2, 'en', NULL, NULL, 1, NULL, NULL, '2026-06-11 05:32:18', '2026-06-15 09:28:28', '2026-06-15 09:28:28'),
(4, 'School', 'Admin', '9491920982', 'SCHOOL@TEHUB.IN', '$2y$10$7UlUQB1AWaEi.AhTD9bq.evGpiXKPULyrUbdlEdTBb8sgGT5reVxS', NULL, 'super-admin/user/6a2ab2322b3812.170173831781183026.jpeg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 3, 'en', NULL, NULL, 1, NULL, NULL, '2026-06-11 13:03:46', '2026-06-15 09:28:40', '2026-06-15 09:28:40'),
(7, 'School', 'Admin', '09150137159', 'SCHOOL62@TEHUB.IN', '$2y$10$H86ZYI0FjwJ0E3nVWyX.Ue1XdpNvJQniiB37mdrYBeXWqjtQHVUju', NULL, 'super-admin/user/6a2ac0c7c2c085.313626231781186759.jpeg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 6, 'en', NULL, NULL, 1, NULL, NULL, '2026-06-11 14:05:59', '2026-06-15 09:28:35', '2026-06-15 09:28:35'),
(8, 'Brilliant', 'Childrens Academy', '7416410369', 'bca@brilliantbca.com', '$2y$10$ORM7QXXLYIrYq6SlT080xOdLuzBNQ2NXcBToaU08eJ8LC/eEP8bF2', 'male', '7/user/6a30d3201363c0.350088591781584672.png', NULL, NULL, NULL, NULL, NULL, '2002-01-01', 'NO. 9-297, KASMUR, VENKATACHALAM, NELLORE - 524320', 'NO. 9-297, KASMUR, VENKATACHALAM, NELLORE - 524320', NULL, 1, 0, NULL, 7, 'en', NULL, '2026-06-11 14:25:31', 1, NULL, NULL, '2026-06-11 14:22:04', '2026-06-16 10:07:52', NULL),
(37, 'School', 'Admin', '917416410369', 'ADMIN@BRILLIANTBCA.COM', '$2y$10$a9P9qCF2mOUdkEG0E4wP0ORVCjqDASKCsZWUe/FJT7facgOhAjhlu', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 10, 'en', NULL, '2026-06-14 13:12:10', 1, NULL, NULL, '2026-06-14 12:04:57', '2026-06-15 09:28:46', '2026-06-15 09:28:46'),
(38, 'School', 'Admin', '9491920982', 'admin@brilliantbca.in', '$2y$10$Y4X19yjnMFTCaJ0DooQfYunV89DSU6Nut7qkF48KSZ/6m.oxsY5Qe', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 11, 'en', NULL, '2026-06-14 16:02:47', 0, NULL, NULL, '2026-06-14 16:02:47', '2026-06-15 13:22:17', '2026-06-15 13:22:17'),
(42, 'School', 'Admin', '08939220422', 'yas.araf62@gmail.com', '$2y$10$ZMnT9JZbJhYShPPzlTOdhOYxFIxVhbGIVnNxiDgxT2TqskJcP/tjC', NULL, 'dummy_logo.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 15, 'en', NULL, '2026-07-04 21:51:40', 1, NULL, NULL, '2026-07-04 21:51:04', '2026-07-04 21:55:54', '2026-07-04 21:55:54'),
(43, 'School', 'Admin', '09150137159', 'nihaanshah062@gmail.com', '$2y$10$EouAte9u4vE/5ltQyEwJZOtzP846mS2NCwx1sCr97UK1z3g7nAmXi', NULL, 'dummy_logo.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 16, 'en', NULL, '2026-07-04 21:56:48', 1, NULL, NULL, '2026-07-04 21:56:14', '2026-07-04 21:56:14', NULL),
(44, '', '', NULL, 'budaklzcrew@gmail.com', '$2y$10$0gtEmAnzMagBoh6aetnA2uFL0sxBILQz4sBov/vtuNmwmEaoCzxtS', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'en', NULL, NULL, 1, NULL, NULL, '2026-08-03 15:01:57', '2026-08-03 15:01:57', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addons`
--
ALTER TABLE `addons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `addons_feature_id_unique` (`feature_id`);

--
-- Indexes for table `addon_subscriptions`
--
ALTER TABLE `addon_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addon_subscriptions_school_id_foreign` (`school_id`),
  ADD KEY `addon_subscriptions_feature_id_foreign` (`feature_id`),
  ADD KEY `addon_subscriptions_subscription_id_foreign` (`subscription_id`),
  ADD KEY `addon_subscriptions_payment_transaction_id_foreign` (`payment_transaction_id`);

--
-- Indexes for table `extra_school_datas`
--
ALTER TABLE `extra_school_datas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `extra_school_datas_school_inquiry_id_foreign` (`school_inquiry_id`),
  ADD KEY `extra_school_datas_school_id_foreign` (`school_id`),
  ADD KEY `extra_school_datas_form_field_id_foreign` (`form_field_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `faqs_school_id_foreign` (`school_id`);

--
-- Indexes for table `features`
--
ALTER TABLE `features`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feature_sections`
--
ALTER TABLE `feature_sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feature_section_lists`
--
ALTER TABLE `feature_section_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `feature_section_lists_feature_section_id_foreign` (`feature_section_id`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `files_modal_type_modal_id_index` (`modal_type`,`modal_id`),
  ADD KEY `files_school_id_foreign` (`school_id`);

--
-- Indexes for table `form_fields`
--
ALTER TABLE `form_fields`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`,`school_id`),
  ADD KEY `form_fields_school_id_foreign` (`school_id`);

--
-- Indexes for table `guidances`
--
ALTER TABLE `guidances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `languages_code_unique` (`code`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `package_features`
--
ALTER TABLE `package_features`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique` (`package_id`,`feature_id`),
  ADD KEY `package_features_package_id_index` (`package_id`),
  ADD KEY `package_features_feature_id_index` (`feature_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `payment_configurations`
--
ALTER TABLE `payment_configurations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_configurations_school_id_foreign` (`school_id`);

--
-- Indexes for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_transactions_school_id_foreign` (`school_id`),
  ADD KEY `payment_transactions_user_id_foreign` (`user_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_school_id_unique` (`name`,`guard_name`,`school_id`),
  ADD KEY `roles_school_id_foreign` (`school_id`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schools_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `school_inquiries`
--
ALTER TABLE `school_inquiries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `school_settings`
--
ALTER TABLE `school_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `school_settings_name_school_id_unique` (`name`,`school_id`),
  ADD KEY `school_settings_school_id_foreign` (`school_id`);

--
-- Indexes for table `staffs`
--
ALTER TABLE `staffs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staffs_user_id_foreign` (`user_id`);

--
-- Indexes for table `staff_support_schools`
--
ALTER TABLE `staff_support_schools`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_school` (`user_id`,`school_id`),
  ADD KEY `staff_support_schools_school_id_foreign` (`school_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscriptions_school_id_foreign` (`school_id`),
  ADD KEY `subscriptions_package_id_foreign` (`package_id`);

--
-- Indexes for table `subscription_bills`
--
ALTER TABLE `subscription_bills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subscription_bill` (`subscription_id`,`school_id`),
  ADD KEY `subscription_bills_school_id_foreign` (`school_id`),
  ADD KEY `subscription_bills_payment_transaction_id_foreign` (`payment_transaction_id`);

--
-- Indexes for table `subscription_features`
--
ALTER TABLE `subscription_features`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique` (`subscription_id`,`feature_id`),
  ADD KEY `subscription_features_feature_id_foreign` (`feature_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `system_settings_name_unique` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_school_id_foreign` (`school_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addons`
--
ALTER TABLE `addons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `addon_subscriptions`
--
ALTER TABLE `addon_subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `extra_school_datas`
--
ALTER TABLE `extra_school_datas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `features`
--
ALTER TABLE `features`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `feature_sections`
--
ALTER TABLE `feature_sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feature_section_lists`
--
ALTER TABLE `feature_section_lists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `form_fields`
--
ALTER TABLE `form_fields`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `guidances`
--
ALTER TABLE `guidances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `package_features`
--
ALTER TABLE `package_features`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=302;

--
-- AUTO_INCREMENT for table `payment_configurations`
--
ALTER TABLE `payment_configurations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `school_inquiries`
--
ALTER TABLE `school_inquiries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `school_settings`
--
ALTER TABLE `school_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `staffs`
--
ALTER TABLE `staffs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_support_schools`
--
ALTER TABLE `staff_support_schools`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `subscription_bills`
--
ALTER TABLE `subscription_bills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `subscription_features`
--
ALTER TABLE `subscription_features`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=281;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=371;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addons`
--
ALTER TABLE `addons`
  ADD CONSTRAINT `addons_feature_id_foreign` FOREIGN KEY (`feature_id`) REFERENCES `features` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `addon_subscriptions`
--
ALTER TABLE `addon_subscriptions`
  ADD CONSTRAINT `addon_subscriptions_feature_id_foreign` FOREIGN KEY (`feature_id`) REFERENCES `features` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `addon_subscriptions_payment_transaction_id_foreign` FOREIGN KEY (`payment_transaction_id`) REFERENCES `payment_transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `addon_subscriptions_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `addon_subscriptions_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `extra_school_datas`
--
ALTER TABLE `extra_school_datas`
  ADD CONSTRAINT `extra_school_datas_form_field_id_foreign` FOREIGN KEY (`form_field_id`) REFERENCES `form_fields` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `extra_school_datas_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `extra_school_datas_school_inquiry_id_foreign` FOREIGN KEY (`school_inquiry_id`) REFERENCES `school_inquiries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `faqs`
--
ALTER TABLE `faqs`
  ADD CONSTRAINT `faqs_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feature_section_lists`
--
ALTER TABLE `feature_section_lists`
  ADD CONSTRAINT `feature_section_lists_feature_section_id_foreign` FOREIGN KEY (`feature_section_id`) REFERENCES `feature_sections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `form_fields`
--
ALTER TABLE `form_fields`
  ADD CONSTRAINT `form_fields_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `package_features`
--
ALTER TABLE `package_features`
  ADD CONSTRAINT `package_features_feature_id_foreign` FOREIGN KEY (`feature_id`) REFERENCES `features` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `package_features_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_configurations`
--
ALTER TABLE `payment_configurations`
  ADD CONSTRAINT `payment_configurations_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `payment_transactions_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schools`
--
ALTER TABLE `schools`
  ADD CONSTRAINT `schools_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `school_settings`
--
ALTER TABLE `school_settings`
  ADD CONSTRAINT `school_settings_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staffs`
--
ALTER TABLE `staffs`
  ADD CONSTRAINT `staffs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_support_schools`
--
ALTER TABLE `staff_support_schools`
  ADD CONSTRAINT `staff_support_schools_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_support_schools_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptions_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscription_bills`
--
ALTER TABLE `subscription_bills`
  ADD CONSTRAINT `subscription_bills_payment_transaction_id_foreign` FOREIGN KEY (`payment_transaction_id`) REFERENCES `payment_transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscription_bills_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscription_bills_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscription_features`
--
ALTER TABLE `subscription_features`
  ADD CONSTRAINT `subscription_features_feature_id_foreign` FOREIGN KEY (`feature_id`) REFERENCES `features` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscription_features_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
