-- ============================================================
-- Database Backup: findownn_admin
-- Created: 2026-07-23 07:14:12
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


-- --------------------------------------------------------
-- Table structure for `activity_logs`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `user_role` varchar(30) DEFAULT NULL,
  `description` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'info',
  `subject_type` varchar(100) DEFAULT NULL,
  `subject_id` int(10) unsigned DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_act_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `activity_logs`
-- 36 rows

INSERT INTO `activity_logs` (`id`, `user_id`, `user_role`, `description`, `type`, `subject_type`, `subject_id`, `ip_address`, `created_at`) VALUES
('1', '1', NULL, 'Logged in from IP: ::1', 'auth', 'User', '1', '::1', '2026-06-22 21:31:04'),
('2', '1', NULL, 'Created user: shahom145@gmail.com', 'user', 'User', '6', '::1', '2026-06-22 21:34:09'),
('3', '1', NULL, 'Logged out.', 'auth', 'User', '1', '::1', '2026-06-22 21:34:15'),
('4', '6', NULL, 'Logged in from IP: ::1', 'auth', 'User', '6', '::1', '2026-06-22 21:34:28'),
('5', '6', NULL, 'Created user: techmatess.tech@gmail.com', 'user', 'User', '7', '::1', '2026-06-22 21:35:35'),
('6', '6', NULL, 'Logged out.', 'auth', 'User', '6', '::1', '2026-06-22 21:35:38'),
('7', '7', NULL, 'Logged in from IP: ::1', 'auth', 'User', '7', '::1', '2026-06-22 21:36:02'),
('8', '7', NULL, 'Logged out.', 'auth', 'User', '7', '::1', '2026-06-22 21:59:31'),
('9', '1', NULL, 'Logged in from IP: ::1', 'auth', 'User', '1', '::1', '2026-06-22 21:59:56'),
('10', '1', NULL, 'Toggled status for user: techmatess.tech@gmail.com', 'user', 'User', '7', '::1', '2026-06-22 22:03:44'),
('11', '1', NULL, 'Toggled status for user: techmatess.tech@gmail.com', 'user', 'User', '7', '::1', '2026-06-22 22:03:49'),
('12', '1', NULL, 'Toggled status for user: techmatess.tech@gmail.com', 'user', 'User', '7', '::1', '2026-06-22 22:03:50'),
('13', '1', NULL, 'Deleted user: techmatess.tech@gmail.com', 'user', 'User', '7', '::1', '2026-06-22 22:03:55'),
('14', '1', NULL, 'Logged out.', 'auth', 'User', '1', '::1', '2026-06-22 22:19:02'),
('15', '1', NULL, 'Logged in from ::1', 'auth', 'User', '1', '::1', '2026-06-22 22:23:41'),
('16', '1', NULL, 'Updated user: shahom145@gmail.com', 'user', 'User', '6', '::1', '2026-06-22 22:24:06'),
('17', '1', NULL, 'Logged out.', 'auth', 'User', '1', '::1', '2026-06-22 22:24:09'),
('18', '1', NULL, 'Logged in from ::1', 'auth', 'User', '1', '::1', '2026-06-22 22:24:52'),
('19', '1', NULL, 'Updated user: rahul@venue.com', 'user', 'User', '3', '::1', '2026-06-22 22:26:24'),
('20', '1', NULL, 'Logged out.', 'auth', 'User', '1', '::1', '2026-06-22 22:26:25'),
('21', '3', NULL, 'Logged in from ::1', 'auth', 'User', '3', '::1', '2026-06-22 22:26:33'),
('22', '3', NULL, 'Logged out.', 'auth', 'User', '3', '::1', '2026-06-22 22:43:14'),
('23', '1', NULL, 'Logged in from ::1', 'auth', 'User', '1', '::1', '2026-06-22 22:43:30'),
('24', '1', NULL, 'Logged out.', 'auth', 'User', '1', '::1', '2026-06-22 22:44:48'),
('25', '3', NULL, 'Logged in from ::1', 'auth', 'User', '3', '::1', '2026-06-22 22:45:12'),
('26', '3', NULL, 'Booking #BK-SEED-0010 status → completed', 'booking', 'Booking', '10', '::1', '2026-06-22 23:03:30'),
('27', '3', NULL, 'Logged out.', 'auth', 'User', '3', '::1', '2026-06-22 23:05:48'),
('28', '1', NULL, 'Logged in from ::1', 'auth', 'User', '1', '::1', '2026-06-22 23:06:00'),
('29', '1', NULL, 'Logged in from ::1', 'auth', 'User', '1', '::1', '2026-07-22 18:49:39'),
('30', '1', NULL, 'Logged out.', 'auth', 'User', '1', '::1', '2026-07-22 19:58:44'),
('31', '1', NULL, 'Logged in from ::1', 'auth', 'User', '1', '::1', '2026-07-22 19:59:58'),
('32', '1', NULL, 'Updated profile.', 'user', 'User', '1', '::1', '2026-07-22 20:00:28'),
('33', '1', NULL, 'Logged out.', 'auth', 'User', '1', '::1', '2026-07-22 20:00:48'),
('34', '3', NULL, 'Logged in from ::1', 'auth', 'User', '3', '::1', '2026-07-22 20:01:07'),
('35', '3', NULL, 'Updated court: Pitch 1 (5-a-side Football)', 'court', 'Court', '4', '::1', '2026-07-22 20:09:17'),
('36', '1', NULL, 'Logged in from ::1', 'auth', 'User', '1', '::1', '2026-07-23 10:22:00');


-- --------------------------------------------------------
-- Table structure for `audit_logs`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `model` varchar(100) DEFAULT NULL,
  `model_id` int(10) unsigned DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_action` (`action`),
  KEY `idx_audit_model` (`model`,`model_id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `audit_logs`
-- 41 rows

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `model`, `model_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
('1', '1', 'LOGIN', 'User', '1', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 21:31:04'),
('2', '1', 'USER_CREATED', 'User', '6', '[]', '{\"email\":\"shahom145@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 21:34:09'),
('3', '1', 'LOGOUT', 'User', '1', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 21:34:15'),
('4', '6', 'LOGIN', 'User', '6', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 21:34:28'),
('5', '6', 'USER_CREATED', 'User', '7', '[]', '{\"email\":\"techmatess.tech@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 21:35:35'),
('6', '6', 'LOGOUT', 'User', '6', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 21:35:38'),
('7', '7', 'LOGIN', 'User', '7', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 21:36:02'),
('8', '7', 'LOGOUT', 'User', '7', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 21:59:31'),
('9', '1', 'LOGIN', 'User', '1', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 21:59:56'),
('10', '1', 'USER_STATUS_TOGGLED', 'User', '7', '{\"status\":\"active\"}', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:03:44'),
('11', '1', 'USER_STATUS_TOGGLED', 'User', '7', '{\"status\":\"inactive\"}', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:03:49'),
('12', '1', 'USER_STATUS_TOGGLED', 'User', '7', '{\"status\":\"active\"}', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:03:50'),
('13', '1', 'USER_DELETED', 'User', '7', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:03:55'),
('14', '1', 'LOGOUT', 'User', '1', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:19:02'),
('15', NULL, 'FAILED_LOGIN', 'User', '0', '{\"email\":\"vaydavanikgnati@gmail.com\"}', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:22:38'),
('16', '1', 'LOGIN', 'User', '1', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:23:41'),
('17', '1', 'USER_UPDATED', 'User', '6', '{\"id\":6,\"name\":\"OM\",\"email\":\"shahom145@gmail.com\",\"password\":\"$2y$12$hQw1xGyoxRAy13.XVQfOHuQVHLyY8rKaqNguFknQCmDmNnBIAw9ny\",\"phone\":\"+917016567167\",\"role\":\"admin\",\"status\":\"active\",\"avatar\":null,\"email_verified_at\":null,\"last_login_at\":\"2026-06-22 21:34:28\",\"created_at\":\"2026-06-22 21:34:09\",\"updated_at\":\"2026-06-22 21:34:28\",\"deleted_at\":null}', '{\"name\":\"OM\",\"phone\":\"+917016567167\",\"role\":\"venue_owner\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:24:06'),
('18', '1', 'LOGOUT', 'User', '1', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:24:09'),
('19', NULL, 'LOGIN_BLOCKED_NO_SUB', 'User', '6', '{\"email\":\"SHAHOM145@GMAIL.COM\"}', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:24:22'),
('20', '1', 'LOGIN', 'User', '1', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:24:52'),
('21', '1', 'USER_UPDATED', 'User', '3', '{\"id\":3,\"name\":\"Rahul Patel\",\"email\":\"rahul@venue.com\",\"password\":\"$2y$12$HdxrPS3Oxm5cDt8v7kAbkuYDWv9Ou55lyge3Ts96AF.OvbA\\/YSh0m\",\"phone\":\"+91 98765 43210\",\"role\":\"venue_owner\",\"status\":\"active\",\"avatar\":null,\"email_verified_at\":null,\"last_login_at\":null,\"created_at\":\"2026-06-22 21:27:02\",\"updated_at\":\"2026-06-22 21:27:02\",\"deleted_at\":null}', '{\"name\":\"Rahul Patel\",\"phone\":\"+91 98765 43210\",\"role\":\"venue_owner\",\"password\":\"$2y$12$kqJXf\\/vsnLcgpp9yk5P\\/a.cgGKjGdCWjTLAUkDFCRYEDNsdItEMpi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:26:24'),
('22', '1', 'LOGOUT', 'User', '1', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:26:25'),
('23', '3', 'LOGIN', 'User', '3', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:26:33'),
('24', '3', 'LOGOUT', 'User', '3', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:43:14'),
('25', '1', 'LOGIN', 'User', '1', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:43:30'),
('26', '1', 'LOGOUT', 'User', '1', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:44:48'),
('27', '3', 'LOGIN', 'User', '3', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 22:45:12'),
('28', '3', 'BOOKING_STATUS_CHANGED', 'Booking', '10', '{\"status\":\"confirmed\"}', '{\"status\":\"completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 23:03:30'),
('29', '3', 'LOGOUT', 'User', '3', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 23:05:48'),
('30', '1', 'LOGIN', 'User', '1', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 23:06:00'),
('31', NULL, 'FAILED_LOGIN', 'User', '0', '{\"email\":\"admin@findownn.com\"}', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 18:49:26'),
('32', '1', 'LOGIN', 'User', '1', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 18:49:39'),
('33', '1', 'DATABASE_BACKUP', 'System', '0', '[]', '{\"filename\":\"findownn_admin_20260722_193653.sql\",\"size\":\"62.1 KB\",\"tables\":24}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 19:36:53'),
('34', '1', 'LOGOUT', 'User', '1', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 19:58:44'),
('35', NULL, 'FAILED_LOGIN', 'User', '0', '{\"email\":\"SHAHOM145@GMAIL.COM\"}', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 19:58:58'),
('36', NULL, 'FAILED_LOGIN', 'User', '0', '{\"email\":\"SHAHOM145@GMAIL.COM\"}', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 19:59:14'),
('37', '1', 'LOGIN', 'User', '1', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 19:59:58'),
('38', '1', 'PROFILE_UPDATED', 'User', '1', '{\"id\":1,\"name\":\"Rahul Mehta\",\"email\":\"superadmin@findownn.com\",\"password\":\"$2y$12$HdxrPS3Oxm5cDt8v7kAbkuYDWv9Ou55lyge3Ts96AF.OvbA\\/YSh0m\",\"api_token\":null,\"api_token_expires_at\":null,\"phone\":\"+91 99999 00001\",\"whatsapp_number\":null,\"whatsapp_opt_in\":1,\"last_whatsapp_sent\":null,\"role\":\"super_admin\",\"status\":\"active\",\"avatar\":null,\"email_verified_at\":null,\"last_login_at\":\"2026-07-22 19:59:58\",\"created_at\":\"2026-06-22 21:27:02\",\"updated_at\":\"2026-07-22 19:59:58\",\"deleted_at\":null}', '{\"name\":\"Om Shah\",\"phone\":\"7016567167\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 20:00:28'),
('39', '1', 'LOGOUT', 'User', '1', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 20:00:48'),
('40', '3', 'LOGIN', 'User', '3', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 20:01:07'),
('41', '1', 'LOGIN', 'User', '1', '[]', '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-23 10:22:00');


-- --------------------------------------------------------
-- Table structure for `bookings`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `venue_id` int(10) unsigned NOT NULL,
  `court_id` int(10) unsigned DEFAULT NULL,
  `sport_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `total_hours` decimal(4,2) NOT NULL DEFAULT 1.00,
  `price_per_hour` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `payment_status` enum('pending','paid','refunded','failed') NOT NULL DEFAULT 'pending',
  `payment_id` int(10) unsigned DEFAULT NULL,
  `transaction_id` varchar(120) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `booking_reference` varchar(30) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `special_requirements` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_book_venue` (`venue_id`),
  KEY `idx_book_user` (`user_id`),
  KEY `idx_book_date` (`booking_date`),
  KEY `fk_book_court` (`court_id`),
  KEY `fk_book_sport` (`sport_id`),
  CONSTRAINT `fk_book_court` FOREIGN KEY (`court_id`) REFERENCES `courts` (`id`),
  CONSTRAINT `fk_book_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`),
  CONSTRAINT `fk_book_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_book_venue` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `bookings`
-- 14 rows

INSERT INTO `bookings` (`id`, `venue_id`, `court_id`, `sport_id`, `user_id`, `booking_date`, `start_time`, `end_time`, `total_hours`, `price_per_hour`, `discount_percent`, `discount_amount`, `subtotal`, `amount`, `status`, `payment_status`, `payment_id`, `transaction_id`, `payment_method`, `booking_reference`, `notes`, `cancellation_reason`, `cancelled_at`, `special_requirements`, `created_at`, `updated_at`) VALUES
('1', '1', NULL, NULL, '1', '2026-06-23', '18:00:00', '19:00:00', '1.00', '0.00', '0.00', '0.00', '0.00', '1000.00', 'confirmed', 'paid', NULL, NULL, NULL, 'BK-SEED-0001', NULL, NULL, NULL, NULL, '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('2', '1', NULL, NULL, '1', '2026-06-24', '18:00:00', '19:00:00', '1.00', '0.00', '0.00', '0.00', '0.00', '1000.00', 'confirmed', 'paid', NULL, NULL, NULL, 'BK-SEED-0002', NULL, NULL, NULL, NULL, '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('3', '1', NULL, NULL, '1', '2026-06-25', '18:00:00', '19:00:00', '1.00', '0.00', '0.00', '0.00', '0.00', '1000.00', 'confirmed', 'paid', NULL, NULL, NULL, 'BK-SEED-0003', NULL, NULL, NULL, NULL, '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('4', '1', NULL, NULL, '1', '2026-06-26', '18:00:00', '19:00:00', '1.00', '0.00', '0.00', '0.00', '0.00', '1000.00', 'confirmed', 'paid', NULL, NULL, NULL, 'BK-SEED-0004', NULL, NULL, NULL, NULL, '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('5', '1', NULL, NULL, '1', '2026-06-27', '18:00:00', '19:00:00', '1.00', '0.00', '0.00', '0.00', '0.00', '1000.00', 'confirmed', 'paid', NULL, NULL, NULL, 'BK-SEED-0005', NULL, NULL, NULL, NULL, '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('6', '1', NULL, NULL, '1', '2026-06-28', '18:00:00', '19:00:00', '1.00', '0.00', '0.00', '0.00', '0.00', '1000.00', 'confirmed', 'paid', NULL, NULL, NULL, 'BK-SEED-0006', NULL, NULL, NULL, NULL, '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('7', '1', NULL, NULL, '1', '2026-06-29', '18:00:00', '19:00:00', '1.00', '0.00', '0.00', '0.00', '0.00', '1000.00', 'confirmed', 'paid', NULL, NULL, NULL, 'BK-SEED-0007', NULL, NULL, NULL, NULL, '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('8', '1', NULL, NULL, '1', '2026-06-30', '18:00:00', '19:00:00', '1.00', '0.00', '0.00', '0.00', '0.00', '1000.00', 'confirmed', 'paid', NULL, NULL, NULL, 'BK-SEED-0008', NULL, NULL, NULL, NULL, '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('9', '1', NULL, NULL, '1', '2026-07-01', '18:00:00', '19:00:00', '1.00', '0.00', '0.00', '0.00', '0.00', '1000.00', 'confirmed', 'paid', NULL, NULL, NULL, 'BK-SEED-0009', NULL, NULL, NULL, NULL, '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('10', '1', NULL, NULL, '1', '2026-07-02', '18:00:00', '19:00:00', '1.00', '0.00', '0.00', '0.00', '0.00', '1000.00', 'completed', 'paid', NULL, NULL, NULL, 'BK-SEED-0010', NULL, NULL, NULL, NULL, '2026-06-22 21:27:02', '2026-06-22 23:03:30'),
('11', '1', '1', '1', '1', '2026-07-22', '08:00:00', '09:00:00', '1.00', '0.00', '0.00', '0.00', '0.00', '1000.00', 'confirmed', 'paid', NULL, NULL, NULL, 'BKC0D30D55', NULL, NULL, NULL, NULL, '2026-07-22 19:24:39', '2026-07-22 19:24:39'),
('12', '1', '1', '1', '1', '2026-07-22', '18:00:00', '19:00:00', '1.00', '0.00', '0.00', '0.00', '0.00', '1000.00', 'confirmed', 'paid', NULL, NULL, NULL, 'BK72204EEA', NULL, NULL, NULL, NULL, '2026-07-22 19:24:39', '2026-07-22 19:24:39'),
('13', '1', '1', '1', '1', '2026-07-22', '19:00:00', '20:00:00', '1.00', '0.00', '0.00', '0.00', '0.00', '1000.00', 'confirmed', 'paid', NULL, NULL, NULL, 'BKA56E2325', NULL, NULL, NULL, NULL, '2026-07-22 19:24:39', '2026-07-22 19:24:39'),
('14', '1', '1', '1', '1', '2026-07-22', '20:00:00', '21:00:00', '1.00', '0.00', '0.00', '0.00', '0.00', '1000.00', 'confirmed', 'paid', NULL, NULL, NULL, 'BKA186A9C2', NULL, NULL, NULL, NULL, '2026-07-22 19:24:39', '2026-07-22 19:24:39');


-- --------------------------------------------------------
-- Table structure for `cities`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `cities`;
CREATE TABLE `cities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `state_id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cities_state` (`state_id`),
  CONSTRAINT `fk_cities_state` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `cities`
-- 10 rows

INSERT INTO `cities` (`id`, `state_id`, `name`, `is_default`, `status`, `created_at`) VALUES
('1', '1', 'Bhuj', '1', 'active', '2026-07-22 19:18:07'),
('2', '1', 'Gandhidham', '0', 'active', '2026-07-22 19:18:07'),
('3', '1', 'Anjar', '0', 'active', '2026-07-22 19:18:07'),
('4', '1', 'Mundra', '0', 'active', '2026-07-22 19:18:07'),
('5', '1', 'Mandvi', '0', 'active', '2026-07-22 19:18:07'),
('6', '1', 'Ahmedabad', '0', 'active', '2026-07-22 19:18:07'),
('7', '1', 'Surat', '0', 'active', '2026-07-22 19:18:07'),
('8', '1', 'Vadodara', '0', 'active', '2026-07-22 19:18:07'),
('9', '1', 'Rajkot', '0', 'active', '2026-07-22 19:18:07'),
('10', '1', 'Jamnagar', '0', 'active', '2026-07-22 19:18:07');


-- --------------------------------------------------------
-- Table structure for `court_images`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `court_images`;
CREATE TABLE `court_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `court_id` int(10) unsigned NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `image_type` enum('gallery','featured','360_view') NOT NULL DEFAULT 'gallery',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cim_court` (`court_id`),
  CONSTRAINT `fk_cim_court` FOREIGN KEY (`court_id`) REFERENCES `courts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `court_images`
-- 5 rows

INSERT INTO `court_images` (`id`, `court_id`, `image_path`, `caption`, `image_type`, `sort_order`, `created_at`, `updated_at`) VALUES
('1', '1', 'assets/images/venue1.jpg', 'Box Cricket Pitch', 'featured', '0', '2026-07-22 20:18:12', '2026-07-22 20:18:12'),
('2', '2', 'assets/images/venue2.jpg', 'Turf Pitch B', 'featured', '0', '2026-07-22 20:18:12', '2026-07-22 20:18:12'),
('4', '4', 'assets/images/venue3.jpg', '5-a-side Football Pitch', 'featured', '0', '2026-07-22 20:18:12', '2026-07-22 20:18:12'),
('5', '5', 'assets/images/venue1.jpg', 'Badminton Wooden Court', 'featured', '0', '2026-07-22 20:18:12', '2026-07-22 20:18:12'),
('6', '3', 'courts/6a60d8ff79188_1784731903.jpg', '', 'gallery', '0', '2026-07-22 20:21:43', '2026-07-22 20:21:43');


-- --------------------------------------------------------
-- Table structure for `court_slots`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `court_slots`;
CREATE TABLE `court_slots` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `court_id` int(10) unsigned NOT NULL,
  `day_of_week` tinyint(4) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_slots_court` (`court_id`),
  KEY `idx_slots_day` (`day_of_week`),
  CONSTRAINT `fk_slots_court` FOREIGN KEY (`court_id`) REFERENCES `courts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- No data for table `court_slots`


-- --------------------------------------------------------
-- Table structure for `courts`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `courts`;
CREATE TABLE `courts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `venue_id` int(10) unsigned NOT NULL,
  `sport_id` int(10) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `court_number` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `surface_type` varchar(100) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `price_per_hour` decimal(10,2) NOT NULL DEFAULT 0.00,
  `featured_image` varchar(255) DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `equipment_provided` text DEFAULT NULL,
  `status` enum('active','inactive','maintenance') NOT NULL DEFAULT 'active',
  `is_indoor` tinyint(1) NOT NULL DEFAULT 0,
  `has_lighting` tinyint(1) NOT NULL DEFAULT 1,
  `booking_slot_duration` int(11) NOT NULL DEFAULT 60,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_courts_venue` (`venue_id`),
  KEY `idx_courts_sport` (`sport_id`),
  KEY `idx_courts_status` (`status`),
  CONSTRAINT `fk_courts_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`),
  CONSTRAINT `fk_courts_venue` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `courts`
-- 5 rows

INSERT INTO `courts` (`id`, `venue_id`, `sport_id`, `name`, `court_number`, `description`, `surface_type`, `dimensions`, `capacity`, `price_per_hour`, `featured_image`, `amenities`, `equipment_provided`, `status`, `is_indoor`, `has_lighting`, `booking_slot_duration`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
('1', '1', '1', 'Turf Pitch A (Box Cricket)', 'C1', 'FIFA grade artificial grass turf with LED lights', 'FIFA Artificial Turf', '30x20 ft', '16', '1000.00', NULL, '[\"Floodlights\",\"Water\"]', NULL, 'active', '0', '1', '60', '0', '2026-07-22 19:48:44', '2026-07-22 19:48:44', NULL),
('2', '1', '1', 'Turf Pitch B (Box Cricket)', 'C2', 'Synthetic grass turf with practice net enclosure', 'Synthetic Turf', '30x20 ft', '16', '1000.00', NULL, '[\"Floodlights\",\"Water\"]', NULL, 'active', '0', '1', '60', '0', '2026-07-22 19:48:44', '2026-07-22 19:48:44', NULL),
('3', '2', '2', 'Court 1 (Pro Pickleball)', 'P1', 'Official acrylic hard court with professional nets', 'Acrylic Hard Court', '20x44 ft', '4', '800.00', NULL, '[\"Indoor\",\"Air Conditioned\"]', NULL, 'active', '1', '1', '60', '0', '2026-07-22 19:48:44', '2026-07-22 19:48:44', NULL),
('4', '3', '3', 'Pitch 1 (5-a-side Football)', 'F1', '50mm Shock-padded grass turf pitch', 'Padded Turf', '100x60 ft', '10', '1201.00', NULL, '[\"Floodlights\"]', '[]', 'active', '1', '1', '60', '0', '2026-07-22 19:48:44', '2026-07-22 20:09:17', NULL),
('5', '4', '4', 'Wooden Court 1 (Badminton)', 'B1', 'BWF approved teak wood court', 'Teak Wood', '44x20 ft', '4', '600.00', NULL, '[\"BWF Wooden Flooring\"]', NULL, 'active', '1', '1', '60', '0', '2026-07-22 19:48:44', '2026-07-22 19:48:44', NULL);


-- --------------------------------------------------------
-- Table structure for `notifications`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'info',
  `subject_type` varchar(100) DEFAULT NULL,
  `subject_id` int(10) unsigned DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- No data for table `notifications`


-- --------------------------------------------------------
-- Table structure for `partner_requests`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `partner_requests`;
CREATE TABLE `partner_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `owner_name` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `venue_name` varchar(200) NOT NULL,
  `state` varchar(100) NOT NULL DEFAULT 'Gujarat',
  `city` varchar(100) NOT NULL DEFAULT 'Bhuj',
  `area` varchar(255) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `map_address` text DEFAULT NULL,
  `sports` text DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `status` enum('pending','contacted','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- No data for table `partner_requests`


-- --------------------------------------------------------
-- Table structure for `payments`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `type` enum('subscription','booking','refund') NOT NULL DEFAULT 'subscription',
  `subject_id` int(10) unsigned DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(5) NOT NULL DEFAULT 'INR',
  `gateway` varchar(50) DEFAULT 'razorpay',
  `gateway_txn_id` varchar(200) DEFAULT NULL,
  `status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `invoice_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pay_user` (`user_id`),
  CONSTRAINT `fk_pay_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- No data for table `payments`


-- --------------------------------------------------------
-- Table structure for `pricing_rules`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `pricing_rules`;
CREATE TABLE `pricing_rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `court_id` int(10) unsigned NOT NULL,
  `rule_name` varchar(150) NOT NULL,
  `day_type` enum('weekday','weekend','all') NOT NULL DEFAULT 'all',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `price_per_hour` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `priority` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pr_court` (`court_id`),
  CONSTRAINT `fk_pr_court` FOREIGN KEY (`court_id`) REFERENCES `courts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- No data for table `pricing_rules`


-- --------------------------------------------------------
-- Table structure for `reviews`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `venue_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `booking_id` int(10) unsigned DEFAULT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 5,
  `review` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_rev_venue` (`venue_id`),
  KEY `fk_rev_user` (`user_id`),
  CONSTRAINT `fk_rev_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_rev_venue` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `reviews`
-- 3 rows

INSERT INTO `reviews` (`id`, `venue_id`, `user_id`, `booking_id`, `rating`, `review`, `status`, `created_at`) VALUES
('1', '1', '1', NULL, '5', 'Top notch box cricket turf in Bhuj! High quality FIFA grade grass turf and incredible LED floodlights.', 'approved', '2026-07-22 18:00:00'),
('2', '1', '2', NULL, '5', 'Excellent venue! Very clean washrooms, cold drinking water, and professional staff. Will book again!', 'approved', '2026-07-20 14:30:00'),
('3', '2', '3', NULL, '5', 'Great court for pickleball. Easy access, spacious parking area, smooth acrylic surface.', 'approved', '2026-07-21 16:00:00');


-- --------------------------------------------------------
-- Table structure for `settings`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `group` varchar(50) NOT NULL DEFAULT 'general',
  `type` varchar(30) NOT NULL DEFAULT 'text',
  `label` varchar(200) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `settings`
-- 10 rows

INSERT INTO `settings` (`id`, `key`, `value`, `group`, `type`, `label`, `created_at`, `updated_at`) VALUES
('1', 'app_name', 'Findownn Admin', 'general', 'text', 'App Name', '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('2', 'app_logo', '', 'general', 'text', 'App Logo', '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('3', 'contact_email', 'findownn@gmail.com', 'general', 'text', 'Contact Email', '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('4', 'contact_phone', '+91 98765 43210', 'general', 'text', 'Phone', '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('5', 'currency', 'INR', 'payment', 'text', 'Currency', '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('6', 'currency_symbol', '₹', 'payment', 'text', 'Currency Symbol', '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('7', 'commission_pct', '15', 'payment', 'text', 'Commission %', '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('8', 'mail_from', 'findownn@gmail.com', 'mail', 'text', 'Mail From', '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('9', 'login_attempts', '5', 'security', 'text', 'Max Login Attempts', '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('10', 'session_timeout', '120', 'security', 'text', 'Session Timeout (min)', '2026-06-22 21:27:02', '2026-06-22 21:27:02');


-- --------------------------------------------------------
-- Table structure for `sports`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `sports`;
CREATE TABLE `sports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sports_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `sports`
-- 6 rows

INSERT INTO `sports` (`id`, `name`, `slug`, `icon`, `description`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
('1', 'Box Cricket', 'box-cricket', 'bi-trophy', NULL, '1', '1', '2026-07-22 19:18:07', '2026-07-22 19:18:07'),
('2', 'Pickleball', 'pickleball', 'bi-circle', NULL, '1', '2', '2026-07-22 19:18:07', '2026-07-22 19:18:07'),
('3', 'Football', 'football', 'bi-hexagon', NULL, '1', '3', '2026-07-22 19:18:07', '2026-07-22 19:18:07'),
('4', 'Badminton', 'badminton', 'bi-star', NULL, '1', '4', '2026-07-22 19:18:07', '2026-07-22 19:18:07'),
('5', 'Tennis', 'tennis', 'bi-circle-fill', NULL, '1', '5', '2026-07-22 19:18:07', '2026-07-22 19:18:07'),
('6', 'Basketball', 'basketball', 'bi-square', NULL, '1', '6', '2026-07-22 19:18:07', '2026-07-22 19:18:07');


-- --------------------------------------------------------
-- Table structure for `states`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `states`;
CREATE TABLE `states` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_states_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `states`
-- 5 rows

INSERT INTO `states` (`id`, `name`, `code`, `status`, `created_at`) VALUES
('1', 'Gujarat', 'GJ', 'active', '2026-07-22 19:18:07'),
('2', 'Maharashtra', 'MH', 'active', '2026-07-22 19:18:07'),
('3', 'Rajasthan', 'RJ', 'active', '2026-07-22 19:18:07'),
('4', 'Karnataka', 'KA', 'active', '2026-07-22 19:18:07'),
('5', 'Delhi', 'DL', 'active', '2026-07-22 19:18:07');


-- --------------------------------------------------------
-- Table structure for `subscription_plans`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `subscription_plans`;
CREATE TABLE `subscription_plans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `billing_cycle` enum('monthly','quarterly','yearly','lifetime') NOT NULL DEFAULT 'monthly',
  `description` text DEFAULT NULL,
  `features` text DEFAULT NULL,
  `max_venues` int(10) unsigned NOT NULL DEFAULT 1,
  `max_images` int(10) unsigned NOT NULL DEFAULT 5,
  `max_slots` int(10) unsigned NOT NULL DEFAULT 10,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_plans_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `subscription_plans`
-- 4 rows

INSERT INTO `subscription_plans` (`id`, `name`, `slug`, `price`, `billing_cycle`, `description`, `features`, `max_venues`, `max_images`, `max_slots`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`) VALUES
('1', 'Free', 'free', '0.00', 'lifetime', 'Get started for free', '1 Venue
3 Images
5 Time Slots
Basic Analytics', '1', '3', '5', '1', '0', '1', '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('2', 'Basic', 'basic', '999.00', 'monthly', 'Great for small venues', '3 Venues
10 Images
20 Time Slots
Email Support', '3', '10', '20', '1', '0', '2', '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('3', 'Premium', 'premium', '2499.00', 'monthly', 'For growing businesses', '10 Venues
30 Images
50 Time Slots
Verified Badge
Priority Support', '10', '30', '50', '1', '1', '3', '2026-06-22 21:27:02', '2026-06-22 21:27:02'),
('4', 'Enterprise', 'enterprise', '7999.00', 'monthly', 'Unlimited everything', 'Unlimited Venues
Unlimited Images
API Access
Dedicated Manager
Custom Analytics', '999', '999', '999', '1', '0', '4', '2026-06-22 21:27:02', '2026-06-22 21:27:02');


-- --------------------------------------------------------
-- Table structure for `subscriptions`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE `subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `plan_id` int(10) unsigned NOT NULL,
  `status` enum('active','expired','pending','cancelled') NOT NULL DEFAULT 'pending',
  `starts_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `auto_renew` tinyint(1) NOT NULL DEFAULT 0,
  `payment_id` int(10) unsigned DEFAULT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `invoice_number` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_sub_user` (`user_id`),
  KEY `idx_sub_plan` (`plan_id`),
  KEY `idx_sub_status` (`status`),
  CONSTRAINT `fk_sub_plan` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`),
  CONSTRAINT `fk_sub_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `subscriptions`
-- 1 rows

INSERT INTO `subscriptions` (`id`, `user_id`, `plan_id`, `status`, `starts_at`, `expires_at`, `auto_renew`, `payment_id`, `amount_paid`, `invoice_number`, `created_at`, `updated_at`) VALUES
('1', '3', '3', 'active', '2026-06-22 21:27:02', '2026-07-22 21:27:02', '0', NULL, '2499.00', 'INV-SEED-0001', '2026-06-22 21:27:02', '2026-06-22 21:27:02');


-- --------------------------------------------------------
-- Table structure for `support_tickets`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `support_tickets`;
CREATE TABLE `support_tickets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ticket_user` (`user_id`),
  KEY `idx_ticket_status` (`status`),
  CONSTRAINT `fk_ticket_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- No data for table `support_tickets`


-- --------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(180) NOT NULL,
  `password` varchar(255) NOT NULL,
  `api_token` varchar(255) DEFAULT NULL,
  `api_token_expires_at` datetime DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `whatsapp_number` varchar(20) DEFAULT NULL,
  `whatsapp_opt_in` tinyint(1) NOT NULL DEFAULT 1,
  `last_whatsapp_sent` datetime DEFAULT NULL,
  `role` enum('super_admin','admin','venue_owner') NOT NULL DEFAULT 'venue_owner',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `avatar` varchar(255) DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users`
-- 7 rows

INSERT INTO `users` (`id`, `name`, `email`, `password`, `api_token`, `api_token_expires_at`, `phone`, `whatsapp_number`, `whatsapp_opt_in`, `last_whatsapp_sent`, `role`, `status`, `avatar`, `email_verified_at`, `last_login_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
('1', 'Om Shah', 'superadmin@findownn.com', '$2y$12$HdxrPS3Oxm5cDt8v7kAbkuYDWv9Ou55lyge3Ts96AF.OvbA/YSh0m', NULL, NULL, '7016567167', NULL, '1', NULL, 'super_admin', 'active', NULL, NULL, '2026-07-23 10:22:00', '2026-06-22 21:27:02', '2026-07-23 10:22:00', NULL),
('2', 'Priya Patel', 'admin@findownn.com', '$2y$12$HdxrPS3Oxm5cDt8v7kAbkuYDWv9Ou55lyge3Ts96AF.OvbA/YSh0m', NULL, NULL, '+91 99999 00002', NULL, '1', NULL, 'admin', 'active', NULL, NULL, NULL, '2026-06-22 21:27:02', '2026-07-22 19:39:59', NULL),
('3', 'Vikram Singh', 'rahul@venue.com', '$2y$12$kqJXf/vsnLcgpp9yk5P/a.cgGKjGdCWjTLAUkDFCRYEDNsdItEMpi', NULL, NULL, '+91 98765 43210', NULL, '1', NULL, 'venue_owner', 'active', NULL, NULL, '2026-07-22 20:01:07', '2026-06-22 21:27:02', '2026-07-22 20:01:07', NULL),
('4', 'Ananya Sharma', 'priya@venue.com', '$2y$12$HdxrPS3Oxm5cDt8v7kAbkuYDWv9Ou55lyge3Ts96AF.OvbA/YSh0m', NULL, NULL, '+91 98765 43211', NULL, '1', NULL, 'venue_owner', 'active', NULL, NULL, NULL, '2026-06-22 21:27:02', '2026-07-22 19:39:59', NULL),
('5', 'Amit Joshi', 'amit@venue.com', '$2y$12$HdxrPS3Oxm5cDt8v7kAbkuYDWv9Ou55lyge3Ts96AF.OvbA/YSh0m', NULL, NULL, '+91 98765 43212', NULL, '1', NULL, 'venue_owner', 'active', NULL, NULL, NULL, '2026-06-22 21:27:02', '2026-06-22 21:27:02', NULL),
('6', 'OM', 'shahom145@gmail.com', '$2y$12$hQw1xGyoxRAy13.XVQfOHuQVHLyY8rKaqNguFknQCmDmNnBIAw9ny', NULL, NULL, '+917016567167', NULL, '1', NULL, 'venue_owner', 'active', NULL, NULL, '2026-06-22 21:34:28', '2026-06-22 21:34:09', '2026-06-22 22:24:06', NULL),
('7', 'OM', 'techmatess.tech@gmail.com', '$2y$12$.ow9wdCBfrAzTJ.UpfVScuGEZJCDnSUfanS.xJ18SiUilrpGKf3k6', NULL, NULL, '+917016567167', NULL, '1', NULL, 'venue_owner', 'inactive', NULL, NULL, '2026-06-22 21:36:02', '2026-06-22 21:35:35', '2026-06-22 22:03:55', '2026-06-22 22:03:55');


-- --------------------------------------------------------
-- Table structure for `venue_images`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `venue_images`;
CREATE TABLE `venue_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `venue_id` int(10) unsigned NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `image_type` enum('gallery','featured','cover') NOT NULL DEFAULT 'gallery',
  `caption` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_vim_venue` (`venue_id`),
  CONSTRAINT `fk_vim_venue` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `venue_images`
-- 4 rows

INSERT INTO `venue_images` (`id`, `venue_id`, `image_path`, `image_type`, `caption`, `sort_order`, `created_at`, `updated_at`) VALUES
('1', '1', 'assets/images/venue1.jpg', 'featured', 'Bhuj Box Arena Turf', '0', '2026-07-22 19:48:44', '2026-07-22 19:48:44'),
('2', '1', 'assets/images/venue2.jpg', 'gallery', 'Night Floodlight View', '1', '2026-07-22 19:48:44', '2026-07-22 19:48:44'),
('4', '3', 'assets/images/venue3.jpg', 'featured', 'Kickoff Football Pitch', '0', '2026-07-22 19:48:44', '2026-07-22 19:48:44'),
('5', '2', 'venues/6a60d89c86e4e_1784731804.jpeg', 'gallery', '', '0', '2026-07-22 20:20:04', '2026-07-22 20:20:04');


-- --------------------------------------------------------
-- Table structure for `venue_sports`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `venue_sports`;
CREATE TABLE `venue_sports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `venue_id` int(10) unsigned NOT NULL,
  `sport_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_venue_sport` (`venue_id`,`sport_id`),
  KEY `idx_vs_venue` (`venue_id`),
  KEY `idx_vs_sport` (`sport_id`),
  CONSTRAINT `fk_vs_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vs_venue` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `venue_sports`
-- 4 rows

INSERT INTO `venue_sports` (`id`, `venue_id`, `sport_id`, `created_at`) VALUES
('1', '1', '1', '2026-07-22 19:48:44'),
('2', '2', '2', '2026-07-22 19:48:44'),
('3', '3', '3', '2026-07-22 19:48:44'),
('4', '4', '4', '2026-07-22 19:48:44');


-- --------------------------------------------------------
-- Table structure for `venues`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `venues`;
CREATE TABLE `venues` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int(10) unsigned NOT NULL,
  `contact_person` varchar(120) DEFAULT NULL,
  `contact_email` varchar(180) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `whatsapp_number` varchar(20) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `type` enum('box_cricket','pickleball','football','badminton','tennis','other') NOT NULL DEFAULT 'box_cricket',
  `description` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `google_map_link` varchar(500) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `opening_time` time DEFAULT '06:00:00',
  `closing_time` time DEFAULT '23:00:00',
  `booking_advance_days` int(11) NOT NULL DEFAULT 30,
  `cancellation_hours` int(11) NOT NULL DEFAULT 24,
  `price_per_hour` decimal(10,2) NOT NULL DEFAULT 0.00,
  `featured_image` varchar(255) DEFAULT NULL,
  `rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `total_reviews` int(10) unsigned NOT NULL DEFAULT 0,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'inactive',
  `verification_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_by` int(10) unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `badge_expires_at` date DEFAULT NULL,
  `verification_notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_venues_owner` (`owner_id`),
  KEY `idx_venues_status` (`status`),
  KEY `idx_venues_type` (`type`),
  KEY `idx_venues_city` (`city`),
  CONSTRAINT `fk_venues_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `venues`
-- 4 rows

INSERT INTO `venues` (`id`, `owner_id`, `contact_person`, `contact_email`, `contact_phone`, `whatsapp_number`, `name`, `slug`, `type`, `description`, `address`, `city`, `state`, `pincode`, `google_map_link`, `latitude`, `longitude`, `amenities`, `opening_time`, `closing_time`, `booking_advance_days`, `cancellation_hours`, `price_per_hour`, `featured_image`, `rating`, `total_reviews`, `status`, `verification_status`, `is_verified`, `verified_by`, `verified_at`, `badge_expires_at`, `verification_notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
('1', '3', NULL, 'findownn@gmail.com', '+91 98765 43210', '+919876543210', 'Bhuj Box Arena', 'bhuj-box-arena', 'box_cricket', 'Premier box cricket arena in Bhuj featuring FIFA-certified artificial grass turf, high-intensity LED floodlights, and netted enclosures.', 'Near Jubilee Ground, Station Road', 'Bhuj', 'Gujarat', '370001', 'https://maps.google.com/?q=23.2420,69.6669', '23.24200000', '69.66690000', '[\"Floodlights\",\"Parking\",\"Water\",\"Restroom\",\"Changing Room\",\"First Aid\"]', '06:00:00', '23:00:00', '30', '24', '1000.00', 'assets/images/venue1.jpg', '5.00', '2', 'active', 'approved', '1', NULL, NULL, NULL, NULL, '2026-06-22 21:27:02', '2026-07-22 19:48:44', NULL),
('2', '3', NULL, 'findownn@gmail.com', '+91 98765 43210', '+919876543210', 'Champion Pickleball', 'champion-pickleball', 'pickleball', 'State-of-the-art indoor pickleball facility in Bhuj. Features official BWF dimension courts, non-slip acrylic flooring, and climate control.', 'College Road, Near Kutch University', 'Bhuj', 'Gujarat', '370001', 'https://maps.google.com/?q=23.2500,69.6700', '23.24200000', '69.66690000', '[\"Indoor Arena\",\"Air Conditioned\",\"Parking\",\"Water\",\"Restroom\"]', '07:00:00', '22:00:00', '30', '24', '800.00', 'assets/images/venue2.jpg', '5.00', '1', 'active', 'approved', '1', NULL, NULL, NULL, NULL, '2026-06-22 21:27:02', '2026-07-22 19:48:44', NULL),
('3', '3', NULL, 'findownn@gmail.com', '+91 98765 43210', '+919876543210', 'Kickoff Football Turf', 'kickoff-football-turf', 'box_cricket', 'High-octane 5-a-side football turf in Bhuj with 50mm shock-padded FIFA grass turf and rebound fencing.', 'Mundra Road, Opp. Reliance Smart', 'Bhuj', 'Gujarat', '370001', 'https://maps.google.com/?q=23.2300,69.6500', '23.24200000', '69.66690000', '[\"Floodlights\",\"Parking\",\"Water\",\"Changing Room\"]', '06:00:00', '23:00:00', '30', '24', '1200.00', 'assets/images/venue3.jpg', '0.00', '0', 'active', 'pending', '0', NULL, NULL, NULL, NULL, '2026-06-22 21:27:02', '2026-07-22 19:48:44', NULL),
('4', '3', NULL, 'findownn@gmail.com', '+91 98765 43210', '+919876543210', 'Smash & Play Badminton', 'smash-and-play-badminton', 'pickleball', 'Professional wooden court badminton complex in Bhuj with BWF approved teak wood flooring.', 'RTO Relocation Site, Near Hill Garden', 'Bhuj', 'Gujarat', '370001', 'https://maps.google.com/?q=23.2600,69.6800', '23.24200000', '69.66690000', '[\"Indoor Arena\",\"BWF Wooden Flooring\",\"Drinking Water\",\"Restroom\"]', '06:00:00', '22:00:00', '30', '24', '600.00', 'assets/images/venue1.jpg', '0.00', '0', 'active', 'approved', '1', NULL, NULL, NULL, NULL, '2026-06-22 21:27:02', '2026-07-22 19:48:44', NULL);


-- --------------------------------------------------------
-- Table structure for `whatsapp_messages`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `whatsapp_messages`;
CREATE TABLE `whatsapp_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `message_type` enum('reminder','promotion','booking_confirmation','cancellation') NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','sent','failed','delivered') NOT NULL DEFAULT 'pending',
  `sent_by` int(10) unsigned DEFAULT NULL,
  `booking_id` int(10) unsigned DEFAULT NULL,
  `whatsapp_link` varchar(500) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_wa_user` (`user_id`),
  KEY `idx_wa_status` (`status`),
  KEY `idx_wa_type` (`message_type`),
  CONSTRAINT `fk_wa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- No data for table `whatsapp_messages`


-- --------------------------------------------------------
-- Table structure for `whatsapp_templates`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `whatsapp_templates`;
CREATE TABLE `whatsapp_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `slug` varchar(170) NOT NULL,
  `category` enum('reminder','promotion','booking','general') NOT NULL,
  `message` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wt_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `whatsapp_templates`
-- 4 rows

INSERT INTO `whatsapp_templates` (`id`, `name`, `slug`, `category`, `message`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
('1', 'Booking Reminder', 'booking-reminder', 'reminder', 'Hi {{name}}! Reminder: Your booking at {{venue}} is on {{date}} at {{time}}. - Findownn Team', '1', NULL, '2026-07-22 19:18:07', '2026-07-22 19:18:07'),
('2', 'Booking Confirmation', 'booking-confirmation', 'booking', 'Hi {{name}}! Your booking is confirmed at {{venue}} on {{date}} at {{time}}. Booking ID: {{booking_id}}.', '1', NULL, '2026-07-22 19:18:07', '2026-07-22 19:18:07'),
('3', 'New Venue Promotion', 'new-venue-promo', 'promotion', 'Hi {{name}}! New venue alert! Check out {{venue}} in {{city}}. Book now! - Findownn', '1', NULL, '2026-07-22 19:18:07', '2026-07-22 19:18:07'),
('4', 'Cancellation Notice', 'booking-cancelled', 'booking', 'Hi {{name}}, Your booking at {{venue}} on {{date}} has been cancelled. Refund in 3-5 days. - Findownn', '1', NULL, '2026-07-22 19:18:07', '2026-07-22 19:18:07');

SET FOREIGN_KEY_CHECKS = 1;

-- Backup completed: 2026-07-23 07:14:13
