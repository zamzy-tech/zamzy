-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 13, 2026 at 09:30 PM
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
-- Database: `shacartc_expert`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `api_key` varchar(191) NOT NULL,
  `credits` int(11) DEFAULT 0,
  `status` varchar(50) DEFAULT 'active',
  `whatsapp_gateway_type` varchar(50) DEFAULT 'gateway',
  `whatsapp_gateway_url` varchar(255) DEFAULT NULL,
  `whatsapp_gateway_token` varchar(255) DEFAULT NULL,
  `whatsapp_linked_number` varchar(50) DEFAULT NULL,
  `whatsapp_is_connected` int(11) DEFAULT 0,
  `client_phone` varchar(50) DEFAULT NULL,
  `login_id` varchar(191) DEFAULT NULL,
  `login_password` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expiry_date` varchar(50) DEFAULT NULL,
  `allowed_scanners` int(11) DEFAULT 1,
  `template_otp` text DEFAULT NULL,
  `template_invoice` text DEFAULT NULL,
  `template_general` text DEFAULT NULL,
  `chatbot_enabled` int(11) DEFAULT 1,
  `plain_password` varchar(255) DEFAULT NULL,
  `is_trial` int(11) DEFAULT 0,
  `expiry_alerts_sent` int(11) DEFAULT 0,
  `last_expiry_alert_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_keys`
--

INSERT INTO `api_keys` (`id`, `client_name`, `api_key`, `credits`, `status`, `whatsapp_gateway_type`, `whatsapp_gateway_url`, `whatsapp_gateway_token`, `whatsapp_linked_number`, `whatsapp_is_connected`, `client_phone`, `login_id`, `login_password`, `created_at`, `expiry_date`, `allowed_scanners`, `template_otp`, `template_invoice`, `template_general`, `chatbot_enabled`, `plain_password`, `is_trial`, `expiry_alerts_sent`, `last_expiry_alert_at`) VALUES
(3, 'sameer', 'teh_api_baaecfed0e63926cdc4cdfcb28ed22ad', -1, 'active', 'gateway', NULL, NULL, '+917287060553', 1, '8667702473', '8667702473', '$2y$10$U3KZKuSFCELkCX4ns0NsBuCZUAwROCVjOHvwgKrBYTk.lxsOzSbG.', '2026-06-28 12:03:59', '2027-09-11', 3, 'Your verification code is *{otp_code}*. This code is valid for 10 minutes. Please do not share it with anyone.', 'Dear {client_name}, your invoice #{invoice_number} is ready. Total Amount: Rs {grand_total}. You can view it here: {web_link}', '⚡ Alert: {message}', 1, '123456', 0, 0, NULL),
(4, 'Nooryak Technologies', 'teh_api_47dbc4f2285eeadfcdc8b60edc25f4ae', 0, 'active', 'gateway', NULL, NULL, NULL, 0, '6374913298', '6374913298', '$2y$10$fub0l8WeOmEe4mQdxMJhMuCVGAiTw3yIztot/DMJx0e85qFZkRrwi', '2026-06-28 14:36:04', '2026-06-29', 1, 'Your verification code is *{otp_code}*. This code is valid for 10 minutes. Please do not share it with anyone.', 'Dear {client_name}, your invoice #{invoice_number} is ready. Total Amount: Rs {grand_total}. You can view it here: {web_link}', '{message}', 1, NULL, 0, 0, NULL),
(5, 'yaseer', '235e1a68e4e9add6a53e61bbe5f31568', 0, 'active', 'gateway', NULL, NULL, '+918939220422', 0, '9150137159', '9150137159', '$2y$10$EBN4PW.VtsRB5rCJwrz/F.aOZm4gEE7U38C3LnvsbYdQANDhT2u0q', '2026-06-29 11:29:31', '2026-06-30', 1, 'Your verification code is *{otp_code}*. This code is valid for 10 minutes. Please do not share it with anyone.', 'Dear {client_name}, your invoice #{invoice_number} is ready. Total Amount: Rs {grand_total}. You can view it here: {web_link}', '{message}', 1, '123456', 0, 0, NULL),
(6, 'nooryak technologies', '544e86838c5c75aaa9bf171a9a4c566c', 0, 'active', 'gateway', NULL, NULL, NULL, 0, '9360157880', '9360157880', '$2y$10$TN.pGIsl88NCQwRGtBsqO.w.bQ1bpoJfm/idwnQWFWUmQVEm2rM36', '2026-07-10 08:13:05', '2026-07-11 08:13:05', 1, 'Your verification code is *{otp_code}*. This code is valid for 10 minutes. Please do not share it with anyone.', 'Dear {client_name}, your invoice #{invoice_number} is ready. Total Amount: Rs {grand_total}. You can view it here: {web_link}', '{message}', 1, NULL, 0, 0, NULL),
(7, 'yaseer', '2afb55e8bbc3d8d6ca018fd19592a4ee', -1, 'active', 'gateway', NULL, NULL, '+917604955818', 0, '9490524010', '9490524010', '$2y$10$JH9hm500QwqyC4iikuVKxeUuGNzpA9QkrxfwiiGsYOKTeUDvSmw.6', '2026-07-14 04:11:58', '2027-07-30', 8, 'Your verification code is *{otp_code}*. This code is valid for 10 minutes. Please do not share it with anyone.', 'Dear {client_name}, your invoice #{invoice_number} is ready. Total Amount: Rs {grand_total}. You can view it here: {web_link}', '{message}', 1, '123456', 0, 0, NULL),
(8, 'mosin', '2bd902d9d5d57632dbd740888f93588d', 0, 'active', 'gateway', NULL, NULL, NULL, 0, '7010726030', '7010726030', '$2y$10$P1bNYt93Dr5f5nKaywfCZ.KSvUnEL/0yeDQ/2UchrGacXewCIuEem', '2026-07-14 07:07:28', '2026-07-15 07:07:28', 1, 'Your verification code is *{otp_code}*. This code is valid for 10 minutes. Please do not share it with anyone.', 'Dear {client_name}, your invoice #{invoice_number} is ready. Total Amount: Rs {grand_total}. You can view it here: {web_link}', '{message}', 1, 'bahad@123', 1, 0, NULL),
(9, 'abdul bahad', 'b5c7c13a4e2dcd8da55753924a053ab6', 0, 'active', 'gateway', NULL, NULL, NULL, 0, '9095111682', '9095111682', '$2y$10$S8WVmLLrOlMF4EHr0hFpKuXhg2mBUg9.Ul8ZwgpsN5dv07LuViwkm', '2026-07-15 11:19:25', '2026-07-16 11:19:25', 1, 'Your verification code is *{otp_code}*. This code is valid for 10 minutes. Please do not share it with anyone.', 'Dear {client_name}, your invoice #{invoice_number} is ready. Total Amount: Rs {grand_total}. You can view it here: {web_link}', '{message}', 1, 'bahad@123', 1, 0, NULL),
(11, 'ajith', 'teh_api_a9f9dfa5b39c87241df7a042a35a711c', -1, 'active', 'gateway', NULL, NULL, '+919150137159', 1, '7397222208', '7397222208', '$2y$10$N40fesziP/BI7GOlqlGqU.sQ5wSXT5GSEI46IcYDEKYB6CD3/qKvy', '2026-08-11 17:11:50', '2027-08-11', 1, 'Your verification code is *{otp_code}*. This code is valid for 10 minutes. Please do not share it with anyone.', 'Dear {client_name}, your invoice #{invoice_number} is ready. Total Amount: Rs {grand_total}. You can view it here: {web_link}', '⚡ Alert: {message}', 1, 'A2208', 0, 0, NULL),
(12, 'sai krishna', 'b0b306dc4bf090c19f85c584906a967c', -1, 'active', 'gateway', NULL, NULL, '+919912312902', 0, '8106653373', '8106653373', '$2y$10$CFU1ruKNFTZBJmIVpw.UyOKRVJzkLQX0LJQrXe9ArvIpeHVCuAuOu', '2026-08-20 12:38:04', '2027-08-24', 1, 'Your verification code is *{otp_code}*. This code is valid for 10 minutes. Please do not share it with anyone.', 'Dear {client_name}, your invoice #{invoice_number} is ready. Total Amount: Rs {grand_total}. You can view it here: {web_link}', '{message}', 1, NULL, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `api_logs`
--

CREATE TABLE `api_logs` (
  `id` int(11) NOT NULL,
  `api_key_id` int(11) DEFAULT NULL,
  `message_type` varchar(50) NOT NULL,
  `recipient_phone` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `response_message` text DEFAULT NULL,
  `credits_used` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_logs`
--

INSERT INTO `api_logs` (`id`, `api_key_id`, `message_type`, `recipient_phone`, `status`, `response_message`, `credits_used`, `created_at`) VALUES
(1, 4, 'otp', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 07:20:08'),
(2, 4, 'otp', '919876543210', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 07:44:54'),
(3, 4, 'otp', '919876543210', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 07:45:04'),
(4, 4, 'otp', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 07:46:22'),
(5, 4, 'general', '08667702473', 'failed', 'HTTP Request error: Operation timed out after 20004 milliseconds with 0 bytes received', 0, '2026-06-29 09:32:25'),
(6, 4, 'general', '08667702473', 'failed', 'HTTP Request error: Operation timed out after 20001 milliseconds with 0 bytes received', 0, '2026-06-29 09:33:18'),
(7, 4, 'general', '918887702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 09:35:51'),
(8, 4, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 09:43:48'),
(9, 4, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 09:57:38'),
(10, 4, 'promotion', '918887702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:14:29'),
(11, 4, 'promotion', '918887702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:14:29'),
(12, 4, 'promotion', '918887702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:14:29'),
(13, 4, 'promotion', '918887702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:14:30'),
(14, 4, 'promotion', '918887702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:14:30'),
(15, 4, 'promotion', '918887702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:14:30'),
(16, 4, 'general', '918887702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:14:57'),
(17, 4, 'promotion', '918887702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:14:57'),
(18, 4, 'otp', '918887702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:14:58'),
(19, 4, 'invoice', '918887702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:14:58'),
(20, 4, 'report', '918887702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:14:58'),
(21, 4, 'promotion', '918887702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:21:26'),
(22, 4, 'promotion', '918887702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:21:26'),
(23, 4, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:24:05'),
(24, 4, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:27:06'),
(25, 4, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:27:24'),
(26, 4, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:28:03'),
(27, 4, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:34:24'),
(28, 4, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:45:39'),
(29, 4, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:53:24'),
(30, 4, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:53:49'),
(31, 4, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 10:57:36'),
(32, 4, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 11:00:27'),
(33, 4, 'general', '08667702473', 'failed', 'HTTP Request error: Operation timed out after 20001 milliseconds with 0 bytes received', 0, '2026-06-29 11:05:53'),
(34, 4, 'general', '08667702473', 'failed', 'HTTP Request error: Operation timed out after 20003 milliseconds with 0 bytes received', 0, '2026-06-29 11:06:43'),
(35, 4, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 11:09:46'),
(36, 4, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-06-29 11:16:40'),
(37, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-10 08:22:26'),
(38, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-10 08:23:33'),
(39, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-10 08:29:07'),
(40, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-10 09:47:32'),
(41, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-10 10:00:54'),
(42, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-10 10:10:17'),
(43, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-10 10:14:26'),
(44, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-10 10:15:13'),
(45, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-10 10:15:58'),
(46, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-10 10:34:22'),
(47, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-10 10:37:40'),
(48, 6, 'otp', '916374913298', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-10 10:52:21'),
(49, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-10 11:01:50'),
(50, 6, 'otp', '916374913298', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-10 11:17:30'),
(51, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-10 11:31:50'),
(52, 6, 'otp', '916374913298', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-10 11:50:34'),
(53, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-10 14:57:15'),
(54, 6, 'otp', '917871687174', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-11 13:20:28'),
(55, 6, 'otp', '917871687174', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-11 13:21:17'),
(56, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-11 18:19:35'),
(57, 6, 'general', '1234567890', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-11 22:43:06'),
(58, 6, 'general', '2348148329050', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-11 22:43:52'),
(59, 6, 'otp', '919360157880', 'failed', 'Gateway returned HTTP 503: {\"success\":false,\"error\":\"WhatsApp session \\\"9360157880\\\" is not connected. Please scan QR first.\"}', 0, '2026-07-12 06:41:32'),
(60, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 08:19:39'),
(61, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 08:21:42'),
(62, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 08:23:08'),
(63, 6, 'otp', '917871687174', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 08:32:54'),
(64, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 09:28:45'),
(65, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 09:34:46'),
(66, 6, 'general', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 09:37:27'),
(67, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 10:05:31'),
(68, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 10:08:28'),
(69, 6, 'otp', '9109360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 10:16:15'),
(70, 6, 'otp', '9109360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 10:17:03'),
(71, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 10:17:28'),
(72, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 10:19:28'),
(73, 6, 'general', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 10:21:41'),
(74, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 10:52:57'),
(75, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 10:56:54'),
(76, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 11:00:11'),
(77, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 11:12:09'),
(78, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 11:41:33'),
(79, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 11:43:04'),
(80, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 11:44:58'),
(81, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 11:47:53'),
(82, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 11:53:48'),
(83, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 12:04:20'),
(84, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 12:05:19'),
(85, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 12:06:27'),
(86, 6, 'general', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 12:07:55'),
(87, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 13:36:43'),
(88, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 13:37:24'),
(89, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 13:37:46'),
(90, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 13:41:58'),
(91, 6, 'otp', '966596375166', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 13:44:57'),
(92, 6, 'general', '966596375166', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-12 13:48:52'),
(93, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-13 06:45:20'),
(94, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-13 06:46:32'),
(95, 6, 'general', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-13 06:48:07'),
(96, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-13 07:14:49'),
(97, 6, 'general', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-13 07:16:25'),
(98, 6, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-13 08:14:32'),
(99, 6, 'general', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-13 08:16:19'),
(100, 6, 'otp', '917854475789', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-14 06:32:57'),
(101, 8, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-14 07:13:00'),
(102, 8, 'general', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-14 07:16:34'),
(103, 8, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-14 12:57:08'),
(104, 8, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-14 12:59:21'),
(105, 8, 'general', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-14 13:00:48'),
(106, 8, 'otp', '91700770351', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 02:25:38'),
(107, 8, 'otp', '917200770351', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 02:26:22'),
(108, 8, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 02:54:53'),
(109, 8, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 02:56:40'),
(110, 8, 'general', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 02:59:32'),
(111, 8, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 03:55:59'),
(112, 8, 'general', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 03:59:52'),
(113, 8, 'otp', '917871687174', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 10:47:35'),
(114, 8, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 10:51:00'),
(115, 8, 'otp', '917871687174', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 10:54:14'),
(116, 8, 'otp', '917871687174', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 10:54:27'),
(117, 8, 'otp', '966596375166', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 10:57:16'),
(118, 8, 'otp', '919095111682', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 10:59:47'),
(119, 8, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 11:02:31'),
(120, 9, 'otp', '919095111682', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 11:37:38'),
(121, 9, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 11:39:17'),
(122, 9, 'otp', '919095111682', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 11:40:46'),
(123, 9, 'otp', '919360157880', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 11:41:06'),
(124, 9, 'otp', '917871687174', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-15 11:42:57'),
(231, 7, 'general', '9150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-30 20:11:13'),
(232, 7, 'general', '9150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-30 20:11:28'),
(233, 7, 'general', '9150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-30 20:11:28'),
(234, 7, 'otp', '9150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-30 21:13:42'),
(235, 7, 'general', '9150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-30 21:13:52'),
(236, 7, 'general', '9150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-07-30 21:13:52'),
(238, 7, 'otp', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-03 07:33:46'),
(239, 7, 'otp', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-03 07:34:16'),
(240, 7, 'otp', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-03 07:35:21'),
(241, 7, 'otp', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-03 07:39:08'),
(243, 7, 'otp', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-03 08:02:00'),
(244, 7, 'invoice', '917397222208', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-03 09:03:43'),
(245, 7, 'invoice', '917397222208', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-03 09:08:26'),
(246, 7, 'invoice', '917397222208', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-03 09:08:57'),
(247, 7, 'otp', '919100867467', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-03 10:06:30'),
(248, 7, 'invoice', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-03 13:47:23'),
(249, 7, 'otp', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-03 16:10:29'),
(251, 7, 'otp', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-04 14:17:40'),
(252, 7, 'otp', '917200770351', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-04 14:22:09'),
(258, 7, 'otp', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-05 11:05:53'),
(264, 7, 'otp', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-09 07:26:16'),
(265, 7, 'otp', '918939220422', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-09 07:28:14'),
(266, 7, 'otp', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-09 12:56:42'),
(267, 7, 'otp', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-09 12:59:11'),
(268, 7, 'otp', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-09 13:02:18'),
(269, 7, 'invoice', '8667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-11 10:38:52'),
(270, 7, 'invoice', '8667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-11 10:50:45'),
(271, 7, 'invoice', '9150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-11 19:04:15'),
(272, 7, 'invoice', '8610869633', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-12 02:47:41'),
(273, 7, 'invoice', '9150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-12 03:43:12'),
(274, 7, 'otp', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-12 04:48:55'),
(275, 7, 'otp', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-12 08:29:19'),
(276, 11, 'invoice', '8939220422', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-13 09:04:34'),
(277, 11, 'general', '8939220422', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-13 09:48:46'),
(278, 11, 'general', '8939220422', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-13 09:56:27'),
(279, 11, 'general', '9790977532', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-13 09:59:07'),
(282, 7, 'otp', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-14 14:16:50'),
(283, 7, 'otp', '919912312902', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-14 14:18:23'),
(291, 7, 'otp', '917793968485', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-17 14:55:36'),
(292, 7, 'otp', '917871687174', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-17 14:56:53'),
(293, 7, 'otp', '917604955818', 'failed', 'Gateway returned HTTP 503: {\"success\":false,\"error\":\"WhatsApp session \\\"9490524010\\\" is not connected. Please scan QR first.\"}', 0, '2026-08-19 06:10:13'),
(294, 7, 'otp', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-19 06:11:25'),
(295, 7, 'otp', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-19 06:45:04'),
(296, 7, 'otp', '917604955818', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-19 06:54:03'),
(297, 7, 'otp', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-19 06:58:15'),
(298, 11, 'general', '8939220422', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-19 10:12:25'),
(299, 7, 'general', '919059297755', 'failed', 'Gateway returned HTTP 503: {\"success\":false,\"error\":\"WhatsApp session \\\"9490524010\\\" is not connected. Please scan QR first.\"}', 0, '2026-08-19 10:12:43'),
(300, 11, 'general', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-19 10:13:07'),
(301, 11, 'general', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-19 10:13:25'),
(302, 11, 'general', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-19 10:16:16'),
(303, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-19 10:25:46'),
(304, 7, 'general', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-19 10:26:01'),
(305, 7, 'general', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-19 10:26:36'),
(306, 7, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-19 10:40:11'),
(307, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-19 10:40:25'),
(308, 7, 'general', '917604955818', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-21 07:17:00'),
(309, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-21 07:17:42'),
(310, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-21 07:17:42'),
(311, 7, 'general', '917604955818', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-21 07:35:08'),
(312, 7, 'general', '917604955818', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-21 07:36:38'),
(313, 12, 'general', '917604955818', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-24 08:39:51'),
(314, 12, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-24 08:50:28'),
(315, 12, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-24 08:52:05'),
(316, 12, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-24 09:04:12'),
(317, 12, 'general', '601163983287', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-24 11:39:59'),
(318, 12, 'general', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-25 06:37:35'),
(319, 12, 'invoice', '918939220422', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-25 06:39:03'),
(320, 12, 'report', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-25 06:39:34'),
(321, 12, 'invoice', '918939220422', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-25 06:42:20'),
(322, 12, 'general', '918939220422', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-25 06:45:05'),
(323, 12, 'general', '918939220422', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-25 07:08:38'),
(324, 12, 'general', '918939220422', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-25 07:10:30'),
(325, 12, 'report', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-25 07:15:37'),
(326, 12, 'report', '918008779995', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-25 07:17:34'),
(327, 12, 'general', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-25 07:19:54'),
(328, 12, 'general', '919150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-25 07:20:01'),
(329, 12, 'general', '918939220422', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-25 07:20:10'),
(330, 12, 'general', '918939220422', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-25 07:21:45'),
(331, 12, 'general', '918939220422', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-25 07:24:13'),
(332, 12, 'general', '918939220422', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-25 07:29:09'),
(333, 7, 'report', '9150137159', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-25 08:35:01'),
(334, 7, 'report', '7793968485', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-25 08:41:51'),
(335, 7, 'general', '917604955818', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-26 08:45:25'),
(336, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-26 08:45:51'),
(337, 7, 'general', '917604955818', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-26 12:16:41'),
(338, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-26 12:17:22'),
(339, 7, 'general', '919888478140', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 06:57:02'),
(340, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 06:57:14'),
(341, 7, 'general', '918019880269', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 08:24:49'),
(342, 7, 'general', '918698187526', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 09:41:21'),
(343, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 09:42:16'),
(344, 7, 'general', '917411856662', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 11:16:07'),
(345, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 11:16:19'),
(346, 7, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 11:22:12'),
(347, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 11:22:33'),
(348, 7, 'general', '919109990139', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 12:18:07'),
(349, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 12:18:45'),
(350, 7, 'general', '919884341970', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 12:35:50'),
(351, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 12:36:02'),
(352, 7, 'general', '918688862502', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 15:42:48'),
(353, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 15:43:06'),
(354, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 16:57:57'),
(355, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 16:59:41'),
(356, 7, 'general', '917411856662', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 20:16:52'),
(357, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-27 20:17:01'),
(358, 7, 'general', '919705486119', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-28 01:52:39'),
(359, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-28 01:53:02'),
(360, 7, 'general', '919073065688', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-28 13:30:07'),
(361, 7, 'general', '919246414650', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-28 15:19:37'),
(362, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-28 15:19:47'),
(363, 7, 'general', '919591790240', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-28 16:50:58'),
(364, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-28 16:51:09'),
(365, 7, 'general', '918308862664', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-28 19:07:31'),
(366, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-28 19:07:42'),
(367, 7, 'general', '919101073571', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-28 19:39:06'),
(368, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-28 19:39:19'),
(369, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-29 04:19:49'),
(370, 7, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-29 04:21:55'),
(371, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-29 04:22:11'),
(372, 7, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-29 10:23:27'),
(373, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-29 10:23:36'),
(374, 7, 'general', '918686311300', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-29 17:25:52'),
(375, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-29 17:26:03'),
(376, 7, 'general', '918884278722', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-29 22:41:04'),
(377, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-29 22:41:17'),
(378, 7, 'general', '919884444331', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-30 01:22:02'),
(379, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-30 01:22:24'),
(380, 7, 'general', '919630000400', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-30 03:51:14'),
(381, 7, 'general', '919392861668', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-30 03:58:27'),
(382, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-30 03:58:54'),
(383, 7, 'general', '916289780881', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-30 04:12:51'),
(384, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-30 04:13:41'),
(385, 7, 'general', '919948989008', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-30 13:55:28'),
(386, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-30 13:57:19'),
(387, 7, 'general', '918109109770', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-30 16:25:47'),
(388, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-30 16:26:11'),
(389, 7, 'general', '918453879174', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-30 17:11:41'),
(390, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-30 17:11:47'),
(391, 7, 'general', '919392755522', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-30 21:27:35'),
(392, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-30 21:28:05'),
(393, 7, 'general', '919830541742', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 01:46:04'),
(394, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 01:46:34'),
(395, 7, 'general', '919538370165', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 03:14:12'),
(396, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 03:14:22'),
(397, 7, 'general', '917675064668', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 03:58:13'),
(398, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 03:58:43'),
(399, 7, 'general', '919490958306', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 05:20:31'),
(400, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 05:20:42'),
(401, 7, 'general', '919746015255', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 07:25:55'),
(402, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 07:26:05'),
(403, 7, 'general', '917989461090', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 08:55:45'),
(404, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 08:56:07'),
(405, 7, 'general', '919986134476', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 09:23:44'),
(406, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 09:24:13'),
(407, 7, 'general', '917030980028', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 09:24:33'),
(408, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 09:24:43'),
(409, 7, 'general', '919392780968', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 11:10:25'),
(410, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 11:10:47'),
(411, 12, 'general', '919392780968', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 11:10:48'),
(412, 7, 'general', '917604955818', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 11:20:03'),
(413, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 11:20:18'),
(414, 12, 'general', '917604955818', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 11:20:19'),
(415, 7, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 11:59:10'),
(416, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 11:59:31'),
(417, 12, 'general', '918667702473', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 11:59:31'),
(418, 7, 'general', '919003247077', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 13:38:03'),
(419, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 13:38:12'),
(420, 12, 'general', '919003247077', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 13:38:13'),
(421, 7, 'general', '919010734020', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 13:49:19'),
(422, 7, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 13:49:32'),
(423, 12, 'general', '919010734020', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-08-31 13:49:32'),
(424, 12, 'general', '917218857043', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 05:29:51'),
(425, 12, 'general', '919609444338', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 06:26:21'),
(426, 12, 'general', '917488933005', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 06:29:31'),
(427, 12, 'general', '919899902178', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 09:37:47'),
(428, 12, 'general', '919780762998', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 09:37:47'),
(429, 12, 'general', '919439632701', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 09:37:48'),
(430, 12, 'general', '919893272200', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 09:37:48'),
(431, 12, 'general', '917620082326', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 09:37:49'),
(432, 12, 'general', '918595916797', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 09:37:49'),
(433, 12, 'general', '919811091287', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 09:37:50'),
(434, 12, 'general', '918076771678', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 09:37:51'),
(435, 11, 'general', '919059297755', 'failed', 'Gateway returned HTTP 503: {\"success\":false,\"error\":\"WhatsApp session \\\"7397222208\\\" is not connected. Please scan QR first.\"}', 0, '2026-09-03 13:10:07'),
(436, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 13:21:37'),
(437, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 13:22:06'),
(438, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 13:24:48'),
(439, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 13:24:48'),
(440, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 13:25:01'),
(441, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 13:25:01'),
(442, 12, 'general', '120363430973934103', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 13:36:43'),
(443, 12, 'general', '120363430973934103', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 13:37:23'),
(444, 12, 'general', '120363430973934103@g.us', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 13:37:54'),
(445, 12, 'general', '120363430973934103@g.us', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 13:37:55'),
(446, 12, 'general', '120363430973934103@g.us', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 13:38:03'),
(447, 12, 'general', '120363430973934103@g.us', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 13:38:04'),
(448, 12, 'general', '120363430973934103@g.us', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 13:39:52'),
(449, 12, 'general', '120363430973934103@g.us', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-03 13:42:13'),
(450, 11, 'general', '8925193345', 'failed', 'Gateway returned HTTP 503: {\"success\":false,\"error\":\"WhatsApp session \\\"7397222208\\\" is not connected. Please scan QR first.\"}', 0, '2026-09-03 14:50:13'),
(451, 12, 'general', '917604955818', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-05 09:26:48'),
(452, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-05 09:27:13'),
(453, 12, 'general', '919785636869', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-05 11:36:09'),
(454, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-05 11:36:33'),
(455, 12, 'general', '919535210441', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-05 11:47:50'),
(456, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-05 11:48:09'),
(457, 12, 'general', '918278708472', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-05 12:07:16'),
(458, 12, 'general', '919247869824', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-05 12:07:45'),
(459, 12, 'general', '918278708472', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-05 12:08:00'),
(460, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-05 12:08:15'),
(461, 12, 'general', '918601851794', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-05 12:17:55'),
(462, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-05 12:18:15'),
(463, 12, 'general', '918108832855', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:29'),
(464, 12, 'general', '919907777711', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:30'),
(465, 12, 'general', '919527872658', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:31'),
(466, 12, 'general', '919831864705', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:32'),
(467, 12, 'general', '919550210365', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:33'),
(468, 12, 'general', '919785636869', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:33'),
(469, 12, 'general', '919535210441', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:34'),
(470, 12, 'general', '919731596954', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:36'),
(471, 12, 'general', '917568773683', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:37'),
(472, 12, 'general', '919740009282', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:38'),
(473, 12, 'general', '919247869824', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:38'),
(474, 12, 'general', '918278708472', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:39'),
(475, 12, 'general', '919987972866', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:40'),
(476, 12, 'general', '919768319999', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:41'),
(477, 12, 'general', '918601851794', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:41'),
(478, 12, 'general', '919908379977', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:42'),
(479, 12, 'general', '919910999149', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:43'),
(480, 12, 'general', '919611134142', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:44'),
(481, 12, 'general', '917755992280', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:45'),
(482, 12, 'general', '919841595053', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:46'),
(483, 12, 'general', '919341195858', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 04:42:47'),
(484, 12, 'general', '919612724045', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 10:30:04'),
(485, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 10:30:21'),
(486, 12, 'general', '919612724045', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 10:31:27'),
(487, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 10:31:39'),
(488, 12, 'general', '919399929961', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 11:02:05'),
(489, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 11:02:09'),
(490, 12, 'general', '919399929961', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 11:03:26'),
(491, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 11:03:30'),
(492, 12, 'general', '919679617825', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 20:23:07'),
(493, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-06 20:23:20'),
(494, 12, 'general', '918970251275', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-07 02:22:54'),
(495, 12, 'general', '918970251275', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-07 02:23:26'),
(496, 12, 'general', '918970251275', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-07 02:24:02'),
(497, 12, 'general', '919600620437', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-07 08:31:38'),
(498, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-07 08:31:48'),
(499, 12, 'general', '916362403324', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-07 08:37:35'),
(500, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-07 08:37:57'),
(501, 12, 'general', '120363430973934103@g.us', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-07 11:10:45'),
(502, 12, 'general', '917021003009', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-08 03:54:45'),
(503, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-08 03:54:52'),
(504, 12, 'general', '919612724045', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-08 05:33:50'),
(505, 12, 'general', '919399929961', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-08 05:33:50'),
(506, 12, 'general', '919121494385', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-08 05:33:51'),
(507, 12, 'general', '917264920057', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-08 05:33:52'),
(508, 12, 'general', '917077128127', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-08 05:33:53'),
(509, 12, 'general', '919490079087', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-08 05:33:54'),
(510, 11, 'general', '9094883274', 'failed', 'Gateway returned HTTP 503: {\"success\":false,\"error\":\"WhatsApp session \\\"7397222208\\\" is not connected. Please scan QR first.\"}', 0, '2026-09-08 06:01:36'),
(511, 12, 'general', '916297590014', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-08 15:05:48'),
(512, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-08 15:06:01'),
(513, 12, 'general', '919440310282', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-09 07:39:05'),
(514, 12, 'general', '918970251275', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-09 07:39:06'),
(515, 12, 'general', '919885534933', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-09 07:39:07'),
(516, 12, 'general', '919813440527', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-09 07:39:08'),
(517, 12, 'general', '919679617825', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-09 07:39:08'),
(518, 12, 'general', '918600473843', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-10 03:46:17'),
(519, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-10 03:46:32'),
(520, 12, 'general', '918341828435', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-10 17:55:03'),
(521, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-10 17:55:32'),
(522, 12, 'general', '918341828435', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-11 02:03:29'),
(523, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-11 02:03:49'),
(524, 12, 'general', '919771596431', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-11 03:47:58'),
(525, 12, 'general', '919059297755', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-11 03:48:21'),
(526, 12, 'general', '919931253118', 'success', 'Message successfully sent via WhatsApp Gateway.', 0, '2026-09-11 06:47:14'),
(527, 12, 'general', '919550978527', 'failed', 'Gateway returned HTTP 503: {\"success\":false,\"error\":\"WhatsApp session \\\"8106653373\\\" is not connected. Please scan QR first.\"}', 0, '2026-09-12 03:10:58');

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_rules`
--

CREATE TABLE `chatbot_rules` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `keyword` varchar(100) NOT NULL,
  `reply_text` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `image_url` varchar(255) DEFAULT NULL,
  `buttons_json` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chatbot_rules`
--

INSERT INTO `chatbot_rules` (`id`, `client_id`, `keyword`, `reply_text`, `created_at`, `image_url`, `buttons_json`) VALUES
(5, 4, 'test', 'Test Reply', '2026-06-29 13:16:14', NULL, NULL),
(8, 4, 'sam', 'sameer ahamath', '2026-06-29 13:43:40', NULL, NULL),
(20, 0, 'hi', 'Welcome to *THE EXPERT HUB*!\r\n\r\nHow can we assist you today? Please reply with one of the numbers or keywords below to get instant information:\r\n\r\n1. Services - View our core tech development services\r\n2. Sales - Details about our School Management System\r\n3. IVR - View our interactive IVR systems\r\n4. SMS - Information on our Bulk WhatsApp SMS services\r\n5. Website - Visit our main domains\r\n6. Contact - Get our direct contact info', '2026-06-29 15:23:55', 'https://2fa.tehub.in/whatsapp.png', NULL),
(21, 0, 'hello', 'Welcome to *THE EXPERT HUB*!\r\n\r\nHow can we assist you today? Please reply with one of the numbers or keywords below to get instant information:\r\n\r\n1. Services - View our core tech development services\r\n2. Sales - Details about our School Management System\r\n3. IVR - View our interactive IVR systems\r\n4. SMS - Information on our Bulk WhatsApp SMS services\r\n5. Website - Visit our main domains\r\n6. Contact - Get our direct contact info', '2026-06-29 15:23:55', 'https://2fa.tehub.in/whatsapp.png', NULL),
(22, 0, 'menu', 'Welcome to *THE EXPERT HUB*!\r\n\r\nHow can we assist you today? Please reply with one of the numbers or keywords below to get instant information:\r\n\r\n1. Services - View our core tech development services\r\n2. Sales - Details about our School Management System\r\n3. IVR - View our interactive IVR systems\r\n4. SMS - Information on our Bulk WhatsApp SMS services\r\n5. Website - Visit our main domains\r\n6. Contact - Get our direct contact info', '2026-06-29 15:23:55', 'https://2fa.tehub.in/whatsapp.png', NULL),
(23, 0, 'services', '💻 *Our Tech Services - THE EXPERT HUB* 💻\r\n\r\nWe specialize in delivering premium custom software solutions:\r\n• 🌐 Website Development\r\n• 📱 Mobile App Development\r\n• 🧩 Custom Browser Extensions\r\n• 📞 Interactive IVR Systems\r\n• 📊 Bespoke CRM Systems\r\n• ...and many more!\r\n\r\nReply *6* or *contact* to speak to a specialist about your project.', '2026-06-29 15:23:55', 'https://2fa.tehub.in/whatsapp.png', NULL),
(24, 0, '1', '💻 *Our Tech Services - THE EXPERT HUB* 💻\r\n\r\nWe specialize in delivering premium custom software solutions:\r\n• 🌐 Website Development\r\n• 📱 Mobile App Development\r\n• 🧩 Custom Browser Extensions\r\n• 📞 Interactive IVR Systems\r\n• 📊 Bespoke CRM Systems\r\n• ...and many more!\r\n\r\nReply *6* or *contact* to speak to a specialist about your project.', '2026-06-29 15:23:55', 'https://2fa.tehub.in/whatsapp.png', NULL),
(25, 0, 'sales', '💼 *Our Sales Products - THE EXPERT HUB* 💼\r\n\r\nHere are our ready-to-deploy enterprise solutions:\r\n\r\n🏫 *School Management Software*\r\nManage school administration, billing, and communication.\r\n🌐 *Domain*: school.tehub.in\r\n\r\n📞 *IVR Call Systems*\r\nStreamline customer calls with our cloud-hosted IVR portal.\r\n🌐 *Domain*: https://ivr.tehub.in/\r\n\r\n💬 *Bulk WhatsApp SMS Gateway*\r\nReach customers instantly with our secure SMS API.\r\n🌐 *Domain*: 2fa.tehub.in\r\n\r\nReply *6* or *contact* to request a live demo or get pricing details for any of these products!', '2026-06-29 15:23:55', 'https://2fa.tehub.in/whatsapp.png', NULL),
(26, 0, 'school', '💼 *Our Sales Products - THE EXPERT HUB* 💼\r\n\r\nHere are our ready-to-deploy enterprise solutions:\r\n\r\n🏫 *School Management Software*\r\nManage school administration, billing, and communication.\r\n🌐 *Domain*: school.tehub.in\r\n\r\n📞 *IVR Call Systems*\r\nStreamline customer calls with our cloud-hosted IVR portal.\r\n🌐 *Domain*: https://ivr.tehub.in/\r\n\r\n💬 *Bulk WhatsApp SMS Gateway*\r\nReach customers instantly with our secure SMS API.\r\n🌐 *Domain*: 2fa.tehub.in\r\n\r\nReply *6* or *contact* to request a live demo or get pricing details for any of these products!', '2026-06-29 15:23:55', 'https://2fa.tehub.in/whatsapp.png', NULL),
(27, 0, '2', '💼 *Our Sales Products - THE EXPERT HUB* 💼\r\n\r\nHere are our ready-to-deploy enterprise solutions:\r\n\r\n🏫 *School Management Software*\r\nManage school administration, billing, and communication.\r\n🌐 *Domain*: school.tehub.in\r\n\r\n📞 *IVR Call Systems*\r\nStreamline customer calls with our cloud-hosted IVR portal.\r\n🌐 *Domain*: https://ivr.tehub.in/\r\n\r\n💬 *Bulk WhatsApp SMS Gateway*\r\nReach customers instantly with our secure SMS API.\r\n🌐 *Domain*: 2fa.tehub.in\r\n\r\nReply *6* or *contact* to request a live demo or get pricing details for any of these products!', '2026-06-29 15:23:55', 'https://2fa.tehub.in/whatsapp.png', NULL),
(28, 0, 'ivr', '📞 *Interactive Voice Response (IVR) Systems* 📞\r\n\r\nStreamline your customer support and business calls with our premium, cloud-hosted IVR systems.\r\n\r\n🌐 *Access Portal*: https://ivr.tehub.in/\r\n\r\nReply *6* or *contact* to configure a custom flow for your business.', '2026-06-29 15:23:55', 'https://2fa.tehub.in/whatsapp.png', NULL),
(29, 0, '3', '📞 *Interactive Voice Response (IVR) Systems* 📞\r\n\r\nStreamline your customer support and business calls with our premium, cloud-hosted IVR systems.\r\n\r\n🌐 *Access Portal*: https://ivr.tehub.in/\r\n\r\nReply *6* or *contact* to configure a custom flow for your business.', '2026-06-29 15:23:55', 'https://2fa.tehub.in/whatsapp.png', NULL),
(30, 0, 'sms', '💬 *Bulk WhatsApp & SMS Services* 💬\r\n\r\nReach thousands of customers instantly with our secure, high-speed Bulk WhatsApp SMS Gateway.\r\n\r\n🌐 *Access Portal*: 2fa.tehub.in\r\n\r\nGet started with a free trial today!', '2026-06-29 15:23:55', 'https://2fa.tehub.in/whatsapp.png', NULL),
(31, 0, '4', '💬 *Bulk WhatsApp & SMS Services* 💬\r\n\r\nReach thousands of customers instantly with our secure, high-speed Bulk WhatsApp SMS Gateway.\r\n\r\n🌐 *Access Portal*: 2fa.tehub.in\r\n\r\nGet started with a free trial today!', '2026-06-29 15:23:55', 'https://2fa.tehub.in/whatsapp.png', NULL),
(32, 0, 'website', '🌐 *Our Web Portals - THE EXPERT HUB* 🌐\r\n\r\nVisit our official websites to explore our services and company details:\r\n• tehub.in\r\n• theexperthub.in', '2026-06-29 15:23:55', 'https://2fa.tehub.in/whatsapp.png', NULL),
(33, 0, '5', '🌐 *Our Web Portals - THE EXPERT HUB* 🌐\r\n\r\nVisit our official websites to explore our services and company details:\r\n• tehub.in\r\n• theexperthub.in', '2026-06-29 15:23:55', 'https://2fa.tehub.in/whatsapp.png', NULL),
(34, 0, 'contact', '📞 *Contact THE EXPERT HUB* 📞\r\n\r\nGet in touch with us directly for enquiries and support:\r\n• 📱 *Call / WhatsApp*: +91 89392 20422\r\n• ✉️ *Email*: enquiry@theexperthub.in\r\n\r\nWe look forward to partnering with you!', '2026-06-29 15:23:55', 'https://2fa.tehub.in/whatsapp.png', NULL),
(35, 0, '6', '📞 *Contact THE EXPERT HUB* 📞\r\n\r\nGet in touch with us directly for enquiries and support:\r\n• 📱 *Call / WhatsApp*: +91 89392 20422\r\n• ✉️ *Email*: enquiry@theexperthub.in\r\n\r\nWe look forward to partnering with you!', '2026-06-29 15:23:55', 'https://2fa.tehub.in/whatsapp.png', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `client_name` varchar(191) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `whatsapp` varchar(50) DEFAULT NULL,
  `emails` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_devices`
--

CREATE TABLE `client_devices` (
  `id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `slot_number` int(11) DEFAULT NULL,
  `whatsapp_linked_number` varchar(50) DEFAULT NULL,
  `whatsapp_is_connected` int(11) DEFAULT 0,
  `api_key` varchar(191) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_devices`
--

INSERT INTO `client_devices` (`id`, `client_id`, `slot_number`, `whatsapp_linked_number`, `whatsapp_is_connected`, `api_key`, `updated_at`) VALUES
(3, 1, 1, '', 0, NULL, '2026-06-28 10:30:19'),
(5, 2, 1, NULL, 0, 'teh_api_50be936b9e0679ac20dd0517ddda1cbb', '2026-06-28 12:02:17'),
(448, 5, 1, '', 0, '235e1a68e4e9add6a53e61bbe5f31568', '2026-07-05 13:44:54'),
(721, 4, 1, NULL, 0, 'teh_api_47dbc4f2285eeadfcdc8b60edc25f4ae', '2026-07-14 05:00:44'),
(862, 6, 1, '', 0, '544e86838c5c75aaa9bf171a9a4c566c', '2026-07-14 06:51:48'),
(1446, 8, 1, '', 0, '2bd902d9d5d57632dbd740888f93588d', '2026-07-15 11:05:57'),
(1538, 9, 1, NULL, 0, 'b5c7c13a4e2dcd8da55753924a053ab6', '2026-07-17 07:41:13'),
(3142, 7, 2, NULL, 0, 'teh_api_d3414731b68831323cbc56e264cb800f', '2026-07-30 19:53:20'),
(3143, 7, 3, NULL, 0, 'teh_api_c203c4b8bf5e16f4ffb5cfcba1c48d29', '2026-07-30 19:53:20'),
(3144, 7, 4, NULL, 0, 'teh_api_8e9051a5f1ac76c7b1ad1f84e772a725', '2026-07-30 19:53:20'),
(3145, 7, 5, NULL, 0, 'teh_api_0699c7cded809a49c75df8ce4e9fbdfd', '2026-07-30 19:53:20'),
(3146, 7, 6, NULL, 0, 'teh_api_5d030f672dd51e45829531e396f55b72', '2026-07-30 19:53:20'),
(3147, 7, 7, NULL, 0, 'teh_api_5627e238265f3678e0666dc28ab0195a', '2026-07-30 19:53:20'),
(3148, 7, 8, NULL, 0, 'teh_api_69c08df22a62791334081550d868b7a6', '2026-07-30 19:53:20'),
(11670, 10, 1, '', 0, NULL, '2026-08-17 15:23:13'),
(11782, 11, 1, '+919150137159', 1, NULL, '2026-08-19 10:12:06'),
(21335, 3, 2, NULL, 0, 'teh_api_a36a7b2ad836bf51780bc2f7c9dd4211', '2026-09-11 08:52:34'),
(21336, 3, 3, NULL, 0, 'teh_api_744b7af4d7acbeff63be17fdd11583e3', '2026-09-11 08:52:34'),
(21349, 3, 1, '+917287060553', 1, NULL, '2026-09-11 08:55:58'),
(21801, 7, 1, '', 0, NULL, '2026-09-12 11:43:02'),
(22315, 12, 1, '', 0, NULL, '2026-09-12 18:49:16');

-- --------------------------------------------------------

--
-- Table structure for table `client_payments`
--

CREATE TABLE `client_payments` (
  `id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `payment_id` varchar(255) DEFAULT NULL,
  `plan_name` varchar(255) DEFAULT NULL,
  `amount` double DEFAULT 0,
  `duration` int(11) DEFAULT NULL,
  `extra_scanners` int(11) DEFAULT NULL,
  `coupon_code` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_payments`
--

INSERT INTO `client_payments` (`id`, `client_id`, `payment_id`, `plan_name`, `amount`, `duration`, `extra_scanners`, `coupon_code`, `created_at`) VALUES
(1, 10, 'pay_TFjbwVuW6H69vg', 'Starter Plan (1 Scanner Base) (0 Add-on Scanners)', 2519.4, 3, 0, 'SAMEER40', '2026-07-20 11:07:33'),
(2, 7, 'FREE_COUPON_QITXHAAID', 'Business Plan (3 Scanner Base) (5 Add-on Scanners)', 0, 12, 5, 'SAVW100', '2026-07-30 19:53:16'),
(3, 12, 'FREE_COUPON_L7YRO2BGF', 'Starter Plan (1 Scanner Base) (0 Add-on Scanners)', 0, 12, 0, 'SAM123', '2026-08-24 08:30:41'),
(4, 3, 'FREE_COUPON_V3T8ZOL60', 'Business Plan (3 Scanner Base) (0 Add-on Scanners)', 0, 12, 0, 'SAM123', '2026-09-11 08:52:32');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `discount_type` varchar(50) NOT NULL,
  `discount_value` double NOT NULL,
  `expiry_date` varchar(50) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `status` varchar(50) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount_type`, `discount_value`, `expiry_date`, `usage_limit`, `used_count`, `status`, `created_at`) VALUES
(5, 'SAMEER40', 'percentage', 40, '2026-07-31', 2, 1, 'active', '2026-07-20 05:43:44'),
(6, 'SAVW100', 'percentage', 100, NULL, 1, 1, 'active', '2026-07-30 19:51:48'),
(8, 'SAM123', 'percentage', 100, '2029-06-05', NULL, 1, 'active', '2026-09-11 08:52:12');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` double DEFAULT 0,
  `expense_date` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `invoice_date` varchar(50) DEFAULT NULL,
  `due_date` varchar(50) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `emails` varchar(255) DEFAULT NULL,
  `grand_total` double DEFAULT 0,
  `advance_amount` double DEFAULT 0,
  `pending_amount` double DEFAULT 0,
  `notes` text DEFAULT NULL,
  `web_link` varchar(255) DEFAULT NULL,
  `source_link` varchar(255) DEFAULT NULL,
  `admin_link` varchar(255) DEFAULT NULL,
  `admin_id` varchar(100) DEFAULT NULL,
  `admin_pass` varchar(100) DEFAULT NULL,
  `email_link` varchar(255) DEFAULT NULL,
  `email_id` varchar(100) DEFAULT NULL,
  `email_pass` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `sender_email` varchar(255) DEFAULT NULL,
  `smtp_account_id` int(11) DEFAULT NULL,
  `is_deleted` int(11) DEFAULT 0,
  `whatsapp` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `qty` double DEFAULT 0,
  `price` double DEFAULT 0,
  `expiry_date` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `name`, `address`, `phone`, `status`, `updated_at`, `created_by`, `created_at`) VALUES
(1, 'John Doe', '123 Street Name', '919876543210', 'pending', '2026-07-05 08:50:57', 1, '2026-07-05 08:50:57'),
(2, 'Jane Smith', '456 Avenue', '919876543211', 'pending', '2026-07-05 08:50:57', 1, '2026-07-05 08:50:57'),
(3, 'yaseett', '', '9150137159', 'no_response', '2026-07-05 15:35:44', 1, '2026-07-05 08:51:11'),
(4, 'John Doe', '123 Street Name', '919876543210', 'pending', '2026-07-05 09:04:41', 1, '2026-07-05 09:04:41'),
(5, 'Jane Smith', '456 Avenue', '919876543211', 'pending', '2026-07-05 09:04:41', 1, '2026-07-05 09:04:41'),
(6, 'John Doe', '123 Street Name', '919876543210', 'pending', '2026-07-05 09:04:55', 1, '2026-07-05 09:04:55'),
(7, 'Jane Smith', '456 Avenue', '919876543211', 'pending', '2026-07-05 09:04:55', 1, '2026-07-05 09:04:55'),
(8, 'ORCHIDS The International School - CBSE School in Pulianthope', 'Sri Sushwani Matha Jain Vidyalaya, 11, Kuttithambiran St, Bhogipalayam, Pulianthope, Chennai, Greater Chennai, Tamil Nadu 600012, India', '919999431999', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(9, 'The Shri Ram Universal School, Perambur - CBSE School in Chennai', '1, New Farrance Rd, Buckingham Carnatic Mills, Perambur, Chennai, Greater Chennai, Tamil Nadu 600012, India', '917397752935', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(10, 'Little Millennium Preschool - Vyasarpadi, Chennai', '14, Stephenson Rd, nearby Rams Mahal, Perambur, Vyasar Nagar Colony, Vyasarpadi, Chennai, Greater Chennai, Tamil Nadu 600039, India', '919500124365', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(11, 'Elements International', '6/48, Secretariat Colony 1st St, Vyasar Nagar Colony, Vyasarpadi, Chennai, Greater Chennai, Tamil Nadu 600039, India', '918111051110', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(12, 'Hi5 PreSchool | PlaySchool | Binny Gardens, North Town, Perambur', 'G02, Tower 10, North Town, Stephenson Rd, Binny Garden, Perambur, Chennai, Greater Chennai, Tamil Nadu 600012, India', '917305351075', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(13, 'Princess Matriculation Higher Secondary School', '37X6+VPX, 110, Pulianthope High Road, Pattalam, Choolai, Chennai, Greater Chennai, Tamil Nadu 600012, India', '914426672671', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(14, 'Narayana eTechno (CBSE) School - Pattalam', 'No 7, EVK Sampath Ln, opposite Fire Station, Periamet, Vepery, Choolai, Chennai, Greater Chennai, Tamil Nadu 600007, India', '9118001023344', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(15, 'Everwin Vidhyashram', '32, Perambur High Rd, Shanthi Nagar, Arundati Nagar, Perambur, Chennai, Greater Chennai, Tamil Nadu 600012, India', '919445959595', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(16, 'KIDZEE MKB NAGAR PRESCHOOL AND DAYCARE CENTER', 'No. 38, Venkatesapuram Colony 3rd Cross St, NEAR MKB NAGAR BSNL EXCHANGE, MKB Nagar, Mahakavi Bharathi Nagar, Vyasarpadi, Chennai, Greater Chennai, Tamil Nadu 600039, India', '919840609501', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(17, 'Little Elly - Preschool in Perambur, Chennai', '39/28, Selva Vinayagar Koil Street, Chinnaiyan Colony, Perambur, Chennai, Greater Chennai, Tamil Nadu 600011, India', '918047590308', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(18, 'Voc Vidiyalayaa Matriculation Higher Secondary School', '24/66, B.B.Road, 2nd St, Vyasarpadi, Chennai, Tamil Nadu 600039, India', '914425511470', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(19, 'V. S. Nethaji Matriculation Higher Secondary School', 'AA Rd, Kasturi Bai Gandhi Nagar, Perambur, Chennai, Greater Chennai, Tamil Nadu 600039, India', '914425512813', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(20, 'Aathichudi International Pre-School', '4764+RP2, Street Number 6, Santhi Nagar, Vyasar Nagar Colony, Perambur, Chennai, Greater Chennai, Tamil Nadu 600039, India', '919940032718', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(21, 'AJEETHA DRIVING SCHOOL', 'Sri balaji apmt, 9, BB Rd, Chinnaiyan Colony, Perambur, Chennai, Greater Chennai, Tamil Nadu 600011, India', '919380708817', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(22, 'Chennai primary school CB road', '26/82, 26/82, Secretariat Colony 1st St, Hari Narayanpuram, Old Washermanpet, Chennai, Greater Chennai, Tamil Nadu 600021, India', '919673854110', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(23, 'Terapanth Jain Vidyalaya Matriculation Higher Secondary School', '32, Vadamalai Street, Sowcarpet, George Town, Chennai, Greater Chennai, Tamil Nadu 600001, India', '914425296933', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(24, 'Sri Ramakrishna Math National School', '46, Basin Bridge Rd, Kondithope, Moolakothalam, Chennai, Greater Chennai, Tamil Nadu 600001, India', '914425205765', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(25, 'White Gold Montessori School', '29, Perambur High Rd, Shanthi Nagar, Perambur, Jamalia, Chennai, Greater Chennai, Tamil Nadu 600012, India', '914447652750', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(26, 'Kaligi Ranganathan Montford Matric Hr. Sec. School', 'Kannabiraan Koil St, Chinnaiyan Colony, Perambur, Chennai, Greater Chennai, Tamil Nadu 600011, India', '914425514508', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(27, 'Sana Smart Matriculation School', '13/3b, perambur high road, Secretariat Colony 1st St, Jamalia, Chennai, Tamil Nadu 600012, India', '914445587693', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(28, 'Jamalia Higher Secondary School', '47, Perambur High Rd, Shanthi Nagar, Arundati Nagar, Perambur, Chennai, Greater Chennai, Tamil Nadu 600012, India', '914426625818', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(29, 'Little Stars Montessori Play School ( best play school in perambur / daycare Centre / tuition )', '1st Street, Bharathi Road, Perambur, Chennai, Tamil Nadu 600011, India', '919566051922', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(30, 'KRM School for Special Children', '10, Bharathi Road, Chinnaiyan Colony, Perambur, Chennai, Greater Chennai, Tamil Nadu 600011, India', '919042111150', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(31, 'Mount Safa Nursery And Primary School', '118, Perambur High Rd, Shanthi Nagar, Arundati Nagar, Jamalia, Chennai, Greater Chennai, Tamil Nadu 600012, India', '917550092828', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(32, 'KRM Public School', '15, Chinnaiyan Colony, Perambur, Chennai, Greater Chennai, Tamil Nadu 600011, India', '914428278796', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(33, 'Sana Model School', '13, Shanthi Nagar, Desia Colony, Jamalia, Chennai, Greater Chennai, Tamil Nadu 600012, India', '919551699400', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(34, 'Seventh Day Adventist Matriculation Higher Secondary School', 'No.15, New Magazine Rd, Kennedy Nagar, Vyasarpadi, Chennai, Greater Chennai, Tamil Nadu 600039, India', '919840219309', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(35, 'Gnanodaya Higher Secondary School', '4782+4J7, Melpatti Ponnappa St, Kakkanji Colony, Chinnaiyan Colony, Perambur, Chennai, Greater Chennai, Tamil Nadu 600039, India', '919003235211', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(36, 'Dr Kalam Juniors pre school', '24, Madurai St, Chinnaiyan Colony, Perambur, Chennai, Greater Chennai, Tamil Nadu 600011, India', '916381107454', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(37, 'Ebeneser High School', 'No. 2, Nelvayal Nagar, Perambur, Chennai, Tamil Nadu 600011, India', '914425514322', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(38, 'CHS THIRUVENKADASAMY', '4, Thiruvengada Swamy St, Bhogipalayam, Pulianthope, Chennai, Greater Chennai, Tamil Nadu 600012, India', '914426671819', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(39, 'DEVI ANNAI NANJIL GANESH NURSERY & PRIMARY SCHOOL', '#57, BB Road, 4th Lane, Vyasarpadi, Chennai, Tamil Nadu 600039, India', '919444016436', 'interested', '2026-07-06 05:21:26', 2, '2026-07-06 05:06:40'),
(40, 'M K Driving School', '57/27, Veera Chetty Street, Grey Nagar, Pulianthope, Chennai, Greater Chennai, Tamil Nadu 600012, India', '919841558776', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(41, 'Narayana eTechno (CBSE) School - Wall Tax Road', 'No 66-67, Mint Street, Basin Bridge Rd, nearby ESIC Branch Office, Old Washermanpet, Chennai, Tamil Nadu 600021, India', '9118001023344', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(42, 'EuroKids Preschool in Sowcarpet, Chennai', '33/62, Venkatrama Iyer St, Kondithope, George Town, Chennai, Greater Chennai, Tamil Nadu 600001, India', '917299991166', 'not_interested', '2026-07-06 05:18:43', 2, '2026-07-06 05:06:40'),
(43, 'BCS Jain Matriculation Higher Secondary School', '14/28, 1724, Ramanuja Iyer St, Mottai Garden, Washermanpet, Chennai, Greater Chennai, Tamil Nadu 600021, India', '919090977000', 'no_response', '2026-07-06 05:17:08', 2, '2026-07-06 05:06:40'),
(44, 'KC Sankaralinga Nadar Higher Secondary School', '60, PAN Rajarathinam Salai 7th Ln, GA Nagar, Old Washermanpet, Chennai, Greater Chennai, Tamil Nadu 600021, India', '914425951423', 'pending', '2026-07-06 05:06:40', 2, '2026-07-06 05:06:40'),
(45, 'ORCHIDS The International School - CBSE School in Pulianthope', 'Sri Sushwani Matha Jain Vidyalaya, 11, Kuttithambiran St, Bhogipalayam, Pulianthope, Chennai, Greater Chennai, Tamil Nadu 600012, India', '919999431999', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(46, 'The Shri Ram Universal School, Perambur - CBSE School in Chennai', '1, New Farrance Rd, Buckingham Carnatic Mills, Perambur, Chennai, Greater Chennai, Tamil Nadu 600012, India', '917397752935', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(47, 'Little Millennium Preschool - Vyasarpadi, Chennai', '14, Stephenson Rd, nearby Rams Mahal, Perambur, Vyasar Nagar Colony, Vyasarpadi, Chennai, Greater Chennai, Tamil Nadu 600039, India', '919500124365', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(48, 'Elements International', '6/48, Secretariat Colony 1st St, Vyasar Nagar Colony, Vyasarpadi, Chennai, Greater Chennai, Tamil Nadu 600039, India', '918111051110', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(49, 'Hi5 PreSchool | PlaySchool | Binny Gardens, North Town, Perambur', 'G02, Tower 10, North Town, Stephenson Rd, Binny Garden, Perambur, Chennai, Greater Chennai, Tamil Nadu 600012, India', '917305351075', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(50, 'Princess Matriculation Higher Secondary School', '37X6+VPX, 110, Pulianthope High Road, Pattalam, Choolai, Chennai, Greater Chennai, Tamil Nadu 600012, India', '914426672671', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(51, 'Narayana eTechno (CBSE) School - Pattalam', 'No 7, EVK Sampath Ln, opposite Fire Station, Periamet, Vepery, Choolai, Chennai, Greater Chennai, Tamil Nadu 600007, India', '9118001023344', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(52, 'Everwin Vidhyashram', '32, Perambur High Rd, Shanthi Nagar, Arundati Nagar, Perambur, Chennai, Greater Chennai, Tamil Nadu 600012, India', '919445959595', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(53, 'KIDZEE MKB NAGAR PRESCHOOL AND DAYCARE CENTER', 'No. 38, Venkatesapuram Colony 3rd Cross St, NEAR MKB NAGAR BSNL EXCHANGE, MKB Nagar, Mahakavi Bharathi Nagar, Vyasarpadi, Chennai, Greater Chennai, Tamil Nadu 600039, India', '919840609501', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(54, 'Little Elly - Preschool in Perambur, Chennai', '39/28, Selva Vinayagar Koil Street, Chinnaiyan Colony, Perambur, Chennai, Greater Chennai, Tamil Nadu 600011, India', '918047590308', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(55, 'Voc Vidiyalayaa Matriculation Higher Secondary School', '24/66, B.B.Road, 2nd St, Vyasarpadi, Chennai, Tamil Nadu 600039, India', '914425511470', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(56, 'V. S. Nethaji Matriculation Higher Secondary School', 'AA Rd, Kasturi Bai Gandhi Nagar, Perambur, Chennai, Greater Chennai, Tamil Nadu 600039, India', '914425512813', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(57, 'Aathichudi International Pre-School', '4764+RP2, Street Number 6, Santhi Nagar, Vyasar Nagar Colony, Perambur, Chennai, Greater Chennai, Tamil Nadu 600039, India', '919940032718', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(58, 'AJEETHA DRIVING SCHOOL', 'Sri balaji apmt, 9, BB Rd, Chinnaiyan Colony, Perambur, Chennai, Greater Chennai, Tamil Nadu 600011, India', '919380708817', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(59, 'Chennai primary school CB road', '26/82, 26/82, Secretariat Colony 1st St, Hari Narayanpuram, Old Washermanpet, Chennai, Greater Chennai, Tamil Nadu 600021, India', '919673854110', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(60, 'Terapanth Jain Vidyalaya Matriculation Higher Secondary School', '32, Vadamalai Street, Sowcarpet, George Town, Chennai, Greater Chennai, Tamil Nadu 600001, India', '914425296933', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(61, 'Sri Ramakrishna Math National School', '46, Basin Bridge Rd, Kondithope, Moolakothalam, Chennai, Greater Chennai, Tamil Nadu 600001, India', '914425205765', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(62, 'White Gold Montessori School', '29, Perambur High Rd, Shanthi Nagar, Perambur, Jamalia, Chennai, Greater Chennai, Tamil Nadu 600012, India', '914447652750', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(63, 'Kaligi Ranganathan Montford Matric Hr. Sec. School', 'Kannabiraan Koil St, Chinnaiyan Colony, Perambur, Chennai, Greater Chennai, Tamil Nadu 600011, India', '914425514508', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(64, 'Sana Smart Matriculation School', '13/3b, perambur high road, Secretariat Colony 1st St, Jamalia, Chennai, Tamil Nadu 600012, India', '914445587693', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(65, 'Jamalia Higher Secondary School', '47, Perambur High Rd, Shanthi Nagar, Arundati Nagar, Perambur, Chennai, Greater Chennai, Tamil Nadu 600012, India', '914426625818', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(66, 'Little Stars Montessori Play School ( best play school in perambur / daycare Centre / tuition )', '1st Street, Bharathi Road, Perambur, Chennai, Tamil Nadu 600011, India', '919566051922', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(67, 'KRM School for Special Children', '10, Bharathi Road, Chinnaiyan Colony, Perambur, Chennai, Greater Chennai, Tamil Nadu 600011, India', '919042111150', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(68, 'Mount Safa Nursery And Primary School', '118, Perambur High Rd, Shanthi Nagar, Arundati Nagar, Jamalia, Chennai, Greater Chennai, Tamil Nadu 600012, India', '917550092828', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(69, 'KRM Public School', '15, Chinnaiyan Colony, Perambur, Chennai, Greater Chennai, Tamil Nadu 600011, India', '914428278796', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(70, 'Sana Model School', '13, Shanthi Nagar, Desia Colony, Jamalia, Chennai, Greater Chennai, Tamil Nadu 600012, India', '919551699400', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(71, 'Seventh Day Adventist Matriculation Higher Secondary School', 'No.15, New Magazine Rd, Kennedy Nagar, Vyasarpadi, Chennai, Greater Chennai, Tamil Nadu 600039, India', '919840219309', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(72, 'Gnanodaya Higher Secondary School', '4782+4J7, Melpatti Ponnappa St, Kakkanji Colony, Chinnaiyan Colony, Perambur, Chennai, Greater Chennai, Tamil Nadu 600039, India', '919003235211', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(73, 'Dr Kalam Juniors pre school', '24, Madurai St, Chinnaiyan Colony, Perambur, Chennai, Greater Chennai, Tamil Nadu 600011, India', '916381107454', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(74, 'Ebeneser High School', 'No. 2, Nelvayal Nagar, Perambur, Chennai, Tamil Nadu 600011, India', '914425514322', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(75, 'CHS THIRUVENKADASAMY', '4, Thiruvengada Swamy St, Bhogipalayam, Pulianthope, Chennai, Greater Chennai, Tamil Nadu 600012, India', '914426671819', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(76, 'DEVI ANNAI NANJIL GANESH NURSERY & PRIMARY SCHOOL', '#57, BB Road, 4th Lane, Vyasarpadi, Chennai, Tamil Nadu 600039, India', '919444016436', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(77, 'M K Driving School', '57/27, Veera Chetty Street, Grey Nagar, Pulianthope, Chennai, Greater Chennai, Tamil Nadu 600012, India', '919841558776', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(78, 'Narayana eTechno (CBSE) School - Wall Tax Road', 'No 66-67, Mint Street, Basin Bridge Rd, nearby ESIC Branch Office, Old Washermanpet, Chennai, Tamil Nadu 600021, India', '9118001023344', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(79, 'EuroKids Preschool in Sowcarpet, Chennai', '33/62, Venkatrama Iyer St, Kondithope, George Town, Chennai, Greater Chennai, Tamil Nadu 600001, India', '917299991166', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(80, 'BCS Jain Matriculation Higher Secondary School', '14/28, 1724, Ramanuja Iyer St, Mottai Garden, Washermanpet, Chennai, Greater Chennai, Tamil Nadu 600021, India', '919090977000', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14'),
(81, 'KC Sankaralinga Nadar Higher Secondary School', '60, PAN Rajarathinam Salai 7th Ln, GA Nagar, Old Washermanpet, Chennai, Greater Chennai, Tamil Nadu 600021, India', '914425951423', 'pending', '2026-07-06 05:33:14', 2, '2026-07-06 05:33:14');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','Active','Completed','Cancelled') DEFAULT 'Pending',
  `payment_method` varchar(50) DEFAULT 'Gateway',
  `payment_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `short_description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_prefix` varchar(50) DEFAULT 'From ₹',
  `duration_info` varchar(100) DEFAULT '',
  `features` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `title`, `short_description`, `price`, `price_prefix`, `duration_info`, `features`, `status`, `created_at`) VALUES
(1, 'MVP & Automation', 'For early-stage startups needing a functional prototype to showcase to investors, or businesses seeking custom workflow scripting and integrations.', 10000.00, 'From ₹', '1 week', '1 Lead architect · 1 developer\nFully functional application prototype\nClean TypeScript backend & Next.js frontend\nCustom workflow integrations (Zapier, Make, custom APIs)\nZero-downtime cloud hosting setup (Vercel / AWS)\n3-month code warranty & updates', 'active', '2026-06-29 13:23:50'),
(2, 'Custom App & Web', 'Our core tier — custom web platforms, native or cross-platform mobile apps (Flutter / React Native), high-performance databases, and custom API systems built for scaling.', 25000.00, 'From ₹', '1-2 weeks', '1 Project lead · 2 developers · 1 DevOps\nProduction-grade web or mobile app codebase\nAutomated unit and integration testing pipelines\nAdvanced cloud config (AWS / GCP / Docker)\nFull database architecture & schema migrations\n12-month hosting maintenance & backups\nComprehensive API docs & system runbooks', 'active', '2026-06-29 13:23:50'),
(3, 'Enterprise Partnership', 'A long-term engineering partnership. THE EXPERT HUB functions as your dedicated engineering and product squad, delivering weekly sprints and feature updates.', 75000.00, 'From ₹', 'retainer scale', 'Dedicated lead engineer, frontend, backend, & QA\nContinuous integration & deployment (CI/CD)\nPriority SLA on bug fixes and incident response\nBi-weekly sprint planning & demo presentations\nComprehensive security audits & code reviews\nFull access to private packages & shared modules\n24/7 server health and load monitoring', 'active', '2026-06-29 13:23:50');

-- --------------------------------------------------------

--
-- Table structure for table `service_pricing`
--

CREATE TABLE `service_pricing` (
  `id` int(11) NOT NULL,
  `plan_key` varchar(100) NOT NULL,
  `plan_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `scanners_included` int(11) DEFAULT 1,
  `monthly_price` double DEFAULT 0,
  `price_3_months` double DEFAULT 0,
  `price_6_months` double DEFAULT 0,
  `price_12_months` double DEFAULT 0,
  `status` varchar(50) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_pricing`
--

INSERT INTO `service_pricing` (`id`, `plan_key`, `plan_name`, `description`, `scanners_included`, `monthly_price`, `price_3_months`, `price_6_months`, `price_12_months`, `status`, `created_at`) VALUES
(1, 'starter', 'Starter Plan', '1 Device Scanner • Unlimited SMS', 1, 1599, 4199, 7799, 14399, 'active', '2026-06-28 12:58:27'),
(2, 'business', 'Business Plan', '3 Device Scanners • Unlimited SMS', 3, 3599, 9999, 18999, 35999, 'active', '2026-06-28 12:58:27'),
(3, 'addon_scanner', 'Extra Active Scanner Add-on', 'Cost per additional WhatsApp device scanner per month', 1, 1200, 3000, 5500, 10000, 'active', '2026-06-28 12:58:27');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `smtp_host` varchar(255) DEFAULT NULL,
  `smtp_port` int(11) DEFAULT NULL,
  `smtp_username` varchar(255) DEFAULT NULL,
  `smtp_password` varchar(255) DEFAULT NULL,
  `smtp_secure` varchar(50) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `company_phone` varchar(50) DEFAULT NULL,
  `company_email` varchar(255) DEFAULT NULL,
  `company_website` varchar(255) DEFAULT NULL,
  `company_tagline` varchar(255) DEFAULT NULL,
  `company_notes_default` text DEFAULT NULL,
  `admin_password` varchar(255) DEFAULT NULL,
  `whatsapp_gateway_type` varchar(50) DEFAULT 'browser',
  `whatsapp_gateway_url` varchar(255) DEFAULT NULL,
  `whatsapp_gateway_token` varchar(255) DEFAULT NULL,
  `whatsapp_linked_number` varchar(50) DEFAULT NULL,
  `whatsapp_is_connected` int(11) DEFAULT 0,
  `razorpay_key_id` varchar(255) DEFAULT 'rzp_test_YOUR_KEY_HERE',
  `razorpay_key_secret` varchar(255) DEFAULT NULL,
  `template_invoice_create` text DEFAULT NULL,
  `template_payment_receive` text DEFAULT NULL,
  `template_estimate` text DEFAULT NULL,
  `chatbot_enabled` int(11) DEFAULT 1,
  `gemini_sales_enabled` int(11) DEFAULT 0,
  `gemini_api_key` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `smtp_host`, `smtp_port`, `smtp_username`, `smtp_password`, `smtp_secure`, `company_name`, `company_phone`, `company_email`, `company_website`, `company_tagline`, `company_notes_default`, `admin_password`, `whatsapp_gateway_type`, `whatsapp_gateway_url`, `whatsapp_gateway_token`, `whatsapp_linked_number`, `whatsapp_is_connected`, `razorpay_key_id`, `razorpay_key_secret`, `template_invoice_create`, `template_payment_receive`, `template_estimate`, `chatbot_enabled`, `gemini_sales_enabled`, `gemini_api_key`) VALUES
(1, 's3508.bom1.stableserver.net', 465, 'noreply@theexperthub.in', 'Inayah@62', 'ssl', 'The Expert Hub', '044 47873458', 'enquiry@theexperthub.in', 'theexperthub.in', 'theexperthub.in | tehub.in', 'Payment terms, bank details, or any other notes for the client...', '$2y$10$Ds5ZEfnY03BLwMvPHqmhse3PZ4YFXQNRSeCd/ivhAgKA.hf/f9VoS', 'gateway', 'https://2fa.tehub.in/whatsapp/send', '', NULL, 0, 'rzp_live_T6vpsfWvqIlyeC', 'UOYxj3V23nOsi28LQnpI7fk9', '*Dear {client_name},*\n\n⚡ *Invoice Summary from {company_name}* ⚡\n\n*Invoice Number:* {invoice_number}\n*Invoice Date:* {invoice_date}\n*Due Date:* {due_date}\n\n*Grand Total:* Rs {grand_total}\n*Advance Paid:* Rs {advance_amount}\n*Balance Due:* *Rs {pending_amount}*\n\nThank you for your business! If you have any questions, please feel free to reach out.', '*Dear {client_name},*\n\n✅ *Payment Received - Thank You!* ✅\n\nWe have received your payment of *Rs {amount_paid}* in full for invoice *{invoice_number}*.\n\n*Invoice Details:*\n• *Invoice Number:* {invoice_number}\n• *Grand Total:* Rs {grand_total}\n• *Amount Paid:* Rs {amount_paid}\n• *Outstanding Balance:* Rs {pending_amount} (Fully Paid)\n\nThank you for your business! We look forward to working with you again.', '*Dear {client_name},*\n\n📋 *New Estimate from {company_name}* 📋\n\nWe are pleased to submit our estimate *#{invoice_number}* for your review.\n\n*Estimated Total:* *Rs {grand_total}*\n\nPlease let us know if you would like to proceed or if you need any adjustments.\n\nBest regards,\n*{company_name}*', 1, 1, 'AQ.Ab8RN6JImPDDRsBTp1wbgNNB7-Dk_LpzIgiIe3qTWLpZ0kWWyQ');

-- --------------------------------------------------------

--
-- Table structure for table `smtp_accounts`
--

CREATE TABLE `smtp_accounts` (
  `id` int(11) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `smtp_host` varchar(255) DEFAULT NULL,
  `smtp_port` int(11) DEFAULT NULL,
  `smtp_username` varchar(255) DEFAULT NULL,
  `smtp_password` varchar(255) DEFAULT NULL,
  `smtp_secure` varchar(50) DEFAULT NULL,
  `is_default` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `smtp_accounts`
--

INSERT INTO `smtp_accounts` (`id`, `display_name`, `smtp_host`, `smtp_port`, `smtp_username`, `smtp_password`, `smtp_secure`, `is_default`) VALUES
(1, 'The Expert Hub (Noreply)', 's3508.bom1.stableserver.net', 465, 'noreply@theexperthub.in', 'Inayah@62', 'ssl', 1);

-- --------------------------------------------------------

--
-- Table structure for table `staff_users`
--

CREATE TABLE `staff_users` (
  `id` int(11) NOT NULL,
  `username` varchar(150) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_users`
--

INSERT INTO `staff_users` (`id`, `username`, `password`, `name`, `status`, `created_at`) VALUES
(1, 'SUGUNA', '$2y$10$BLCGExvSrR7R0bMTeEaSPu85C5JNNTgWBW/JKWp09jaavkTBcT1iK', 'SUGUNA PERUMAL', 'active', '2026-07-05 08:21:50'),
(2, 'tehub', '$2y$10$.bmX9dJaTAaVUricPaJUC.ozThX0MMgZECRKKHyDHt0SczWUGw/My', 'tehub', 'active', '2026-07-06 05:03:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','client') DEFAULT 'client',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin@tehub.com', '$2y$10$ImEo9psezus8gas8rsnnteS2.qgtmVVnAWW2Gaw2Z9Vxa2INaXT..', 'admin', '2026-06-29 13:23:50');

-- --------------------------------------------------------

--
-- Table structure for table `youtubers`
--

CREATE TABLE `youtubers` (
  `id` int(11) NOT NULL,
  `username` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `channel_id` varchar(100) DEFAULT NULL,
  `channel_name` varchar(255) DEFAULT NULL,
  `whatsapp_target_jid` varchar(100) DEFAULT NULL,
  `whatsapp_target_name` varchar(255) DEFAULT NULL,
  `last_video_id` varchar(50) DEFAULT NULL,
  `template_upload` text DEFAULT NULL,
  `template_live` text DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `whatsapp_is_connected` int(11) DEFAULT 0,
  `whatsapp_linked_number` varchar(50) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `youtubers`
--

INSERT INTO `youtubers` (`id`, `username`, `password`, `channel_id`, `channel_name`, `whatsapp_target_jid`, `whatsapp_target_name`, `last_video_id`, `template_upload`, `template_live`, `is_active`, `created_at`, `whatsapp_is_connected`, `whatsapp_linked_number`, `phone`) VALUES
(1, 'youtuber', '$2y$10$Ad/FTmC9nk64Dlog7nFOyeLHQw1LlX6bZYv5b.kjS1iRO6.QWvnCy', 'UC6yJ_VGVy0Dz3O3n6wHyhTQ', 'Sha Cart', '120363426187610725@g.us', 'Tehub', '1YWAqmXQZv4', '🎥 *New Video Alert!*\r\n\r\n{title}\r\n\r\nWatch now: {url}', '🔴 *We are LIVE now!*\r\n\r\n{title}\r\n\r\nJoin the stream here: {url}', 1, '2026-07-05 16:55:39', 1, '+919150137159', NULL),
(2, '90', '$2y$10$X6bzFAFbQVaC1qrwXrFsOuhNFrvD/xnhJErRZRdZUApBuxEfS.Iia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-06 05:52:13', 0, NULL, '8939220422');

-- --------------------------------------------------------

--
-- Table structure for table `youtuber_channels`
--

CREATE TABLE `youtuber_channels` (
  `id` int(11) NOT NULL,
  `youtuber_id` int(11) NOT NULL,
  `channel_id` varchar(100) NOT NULL,
  `channel_name` varchar(255) DEFAULT NULL,
  `whatsapp_target_jid` varchar(100) DEFAULT NULL,
  `whatsapp_target_name` varchar(255) DEFAULT NULL,
  `last_video_id` varchar(50) DEFAULT NULL,
  `template_upload` text DEFAULT NULL,
  `template_live` text DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `youtuber_channels`
--

INSERT INTO `youtuber_channels` (`id`, `youtuber_id`, `channel_id`, `channel_name`, `whatsapp_target_jid`, `whatsapp_target_name`, `last_video_id`, `template_upload`, `template_live`, `is_active`, `created_at`) VALUES
(1, 1, 'UC6yJ_VGVy0Dz3O3n6wHyhTQ', 'Sha Cart', '120363426187610725@g.us', 'Tehub', 'p381ZSlrNe0', '🎥 *New Video Alert!*\r\n\r\n{title}\r\n\r\nWatch now: {url}', '🔴 *We are LIVE now!*\r\n\r\n{title}\r\n\r\nJoin the stream here: {url}', 1, '2026-07-06 04:30:27');

-- --------------------------------------------------------

--
-- Table structure for table `youtube_history`
--

CREATE TABLE `youtube_history` (
  `id` int(11) NOT NULL,
  `youtuber_id` int(11) NOT NULL,
  `video_id` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `youtube_history`
--

INSERT INTO `youtube_history` (`id`, `youtuber_id`, `video_id`, `title`, `type`, `status`, `sent_at`) VALUES
(1, 1, '1YWAqmXQZv4', '🚀 Exciting News! Introducing TEHUB Restaurant The Ultimate POS &  Software for Food Businesses!', 'test_upload', 'sent', '2026-07-05 17:39:56'),
(2, 1, '1YWAqmXQZv4', '🚀 Exciting News! Introducing TEHUB Restaurant The Ultimate POS &  Software for Food Businesses!', 'test_upload', 'sent', '2026-07-05 17:40:11'),
(3, 1, '1YWAqmXQZv4', '🚀 Exciting News! Introducing TEHUB Restaurant The Ultimate POS &  Software for Food Businesses!', 'test_upload', 'sent', '2026-07-05 17:46:54'),
(4, 1, '1YWAqmXQZv4', '🚀 Exciting News! Introducing TEHUB Restaurant The Ultimate POS &  Software for Food Businesses!', 'test_upload', 'sent', '2026-07-05 17:58:56'),
(5, 1, '1YWAqmXQZv4', '🚀 Exciting News! Introducing TEHUB Restaurant The Ultimate POS &  Software for Food Businesses!', 'test_upload', 'sent', '2026-07-05 18:00:27'),
(6, 1, '1YWAqmXQZv4', '🚀 Exciting News! Introducing TEHUB Restaurant The Ultimate POS &  Software for Food Businesses!', 'test_upload', 'sent', '2026-07-06 04:09:18'),
(7, 1, '1YWAqmXQZv4', '🚀 Exciting News! Introducing TEHUB Restaurant The Ultimate POS &  Software for Food Businesses!', 'test_upload', 'sent', '2026-07-06 04:13:00'),
(8, 1, '1YWAqmXQZv4', '🚀 Exciting News! Introducing TEHUB Restaurant The Ultimate POS &  Software for Food Businesses!', 'test_upload', 'sent', '2026-07-06 04:19:01'),
(9, 1, '1YWAqmXQZv4', '🚀 Exciting News! Introducing TEHUB Restaurant The Ultimate POS &  Software for Food Businesses!', 'test_upload', 'sent', '2026-07-06 04:21:54'),
(10, 1, '1YWAqmXQZv4', '🚀 Exciting News! Introducing TEHUB Restaurant The Ultimate POS &  Software for Food Businesses!', 'test_upload', 'sent', '2026-07-06 04:26:06'),
(11, 1, '1YWAqmXQZv4', '🚀 Exciting News! Introducing TEHUB Restaurant The Ultimate POS &  Software for Food Businesses!', 'test_upload', 'sent', '2026-07-06 04:34:36'),
(12, 1, 'p381ZSlrNe0', '🚀 Exciting News! Introducing the Ultimate All-in-One School Management System ✨', 'upload', 'sent', '2026-07-06 04:39:07'),
(13, 1, 'p381ZSlrNe0', '🚀 Exciting News! Introducing the Ultimate All-in-One School Management System ✨', 'test_upload', 'sent', '2026-07-06 05:41:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_key` (`api_key`),
  ADD UNIQUE KEY `login_id` (`login_id`);

--
-- Indexes for table `api_logs`
--
ALTER TABLE `api_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `api_key_id` (`api_key_id`);

--
-- Indexes for table `chatbot_rules`
--
ALTER TABLE `chatbot_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `client_name` (`client_name`);

--
-- Indexes for table `client_devices`
--
ALTER TABLE `client_devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `client_slot` (`client_id`,`slot_number`);

--
-- Indexes for table `client_payments`
--
ALTER TABLE `client_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_pricing`
--
ALTER TABLE `service_pricing`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plan_key` (`plan_key`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `smtp_accounts`
--
ALTER TABLE `smtp_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff_users`
--
ALTER TABLE `staff_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `youtubers`
--
ALTER TABLE `youtubers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `youtuber_channels`
--
ALTER TABLE `youtuber_channels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `youtuber_id` (`youtuber_id`);

--
-- Indexes for table `youtube_history`
--
ALTER TABLE `youtube_history`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `api_logs`
--
ALTER TABLE `api_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=528;

--
-- AUTO_INCREMENT for table `chatbot_rules`
--
ALTER TABLE `chatbot_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_devices`
--
ALTER TABLE `client_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22321;

--
-- AUTO_INCREMENT for table `client_payments`
--
ALTER TABLE `client_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `service_pricing`
--
ALTER TABLE `service_pricing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `smtp_accounts`
--
ALTER TABLE `smtp_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff_users`
--
ALTER TABLE `staff_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `youtubers`
--
ALTER TABLE `youtubers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `youtuber_channels`
--
ALTER TABLE `youtuber_channels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `youtube_history`
--
ALTER TABLE `youtube_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `api_logs`
--
ALTER TABLE `api_logs`
  ADD CONSTRAINT `api_logs_ibfk_1` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `youtuber_channels`
--
ALTER TABLE `youtuber_channels`
  ADD CONSTRAINT `youtuber_channels_ibfk_1` FOREIGN KEY (`youtuber_id`) REFERENCES `youtubers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
