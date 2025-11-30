-- WorkNPay Database Schema
-- Mobile-first e-commerce platform connecting customers with skilled workers
-- Version: 1.0
-- Date: 2025-11-21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `worknpay`
--
CREATE DATABASE IF NOT EXISTS `worknpay` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `worknpay`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
-- Core user table for all user types (customers, workers, admins)
--

CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_phone` varchar(20) NOT NULL,
  `user_role` tinyint NOT NULL COMMENT '1=Customer, 2=Worker, 3=Admin',
  `user_country` varchar(50) DEFAULT 'Ghana',
  `user_city` varchar(50) DEFAULT NULL,
  `user_address` text,
  `user_image` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_email` (`user_email`),
  KEY `idx_user_role` (`user_role`),
  KEY `idx_user_email` (`user_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
-- Categories for services offered (Phase 1: Gadget Repair, Electrical, Plumbing, Tutoring)
--

CREATE TABLE `service_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `category_description` text,
  `category_icon` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_categories`
--

INSERT INTO `service_categories` (`category_id`, `category_name`, `category_description`, `category_icon`, `is_active`, `created_at`) VALUES
(1, 'Gadget Repair', 'Repair services for phones, laptops, tablets and other electronic devices', 'icon-gadget.png', 1, CURRENT_TIMESTAMP),
(2, 'Electrical Services', 'Electrical wiring, appliance repairs, and installations', 'icon-electrical.png', 1, CURRENT_TIMESTAMP),
(3, 'Plumbing', 'Plumbing repairs, installations, and maintenance', 'icon-plumbing.png', 1, CURRENT_TIMESTAMP),
(4, 'Tutoring', 'Academic tutoring for various subjects', 'icon-tutoring.png', 1, CURRENT_TIMESTAMP);

-- --------------------------------------------------------

--
-- Table structure for table `worker_profiles`
-- Extended profile information for workers
--

CREATE TABLE `worker_profiles` (
  `profile_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `bio` text,
  `skills` text COMMENT 'Comma-separated list of skills',
  `experience_years` int DEFAULT 0,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `service_radius_km` int DEFAULT 10 COMMENT 'Service area radius in kilometers',
  `id_number` varchar(50) DEFAULT NULL COMMENT 'National ID or verification number',
  `id_verified` tinyint(1) DEFAULT 0,
  `background_check_status` enum('pending','approved','rejected','not_requested') DEFAULT 'not_requested',
  `verification_badge` tinyint(1) DEFAULT 0,
  `total_jobs_completed` int DEFAULT 0,
  `average_rating` decimal(3,2) DEFAULT 0.00,
  `total_earnings` decimal(10,2) DEFAULT 0.00,
  `available_balance` decimal(10,2) DEFAULT 0.00,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`profile_id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_worker_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
-- Services offered by workers
--

CREATE TABLE `services` (
  `service_id` int NOT NULL AUTO_INCREMENT,
  `worker_id` int NOT NULL,
  `category_id` int NOT NULL,
  `service_title` varchar(200) NOT NULL,
  `service_description` text,
  `base_price` decimal(10,2) NOT NULL,
  `price_unit` enum('per_hour','per_job','per_item') DEFAULT 'per_job',
  `estimated_duration` int DEFAULT NULL COMMENT 'Estimated duration in minutes',
  `service_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`service_id`),
  KEY `worker_id` (`worker_id`),
  KEY `category_id` (`category_id`),
  KEY `idx_service_active` (`is_active`),
  CONSTRAINT `fk_service_worker` FOREIGN KEY (`worker_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_service_category` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
-- Service bookings/appointments
--

CREATE TABLE `bookings` (
  `booking_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `worker_id` int NOT NULL,
  `service_id` int NOT NULL,
  `booking_reference` varchar(50) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `service_address` text NOT NULL,
  `customer_notes` text,
  `estimated_price` decimal(10,2) NOT NULL,
  `final_price` decimal(10,2) DEFAULT NULL,
  `booking_status` enum('pending','accepted','rejected','in_progress','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','refunded','released') DEFAULT 'pending',
  `completion_date` timestamp NULL DEFAULT NULL,
  `completion_photos` text COMMENT 'JSON array of photo URLs',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_id`),
  UNIQUE KEY `booking_reference` (`booking_reference`),
  KEY `customer_id` (`customer_id`),
  KEY `worker_id` (`worker_id`),
  KEY `service_id` (`service_id`),
  KEY `idx_booking_status` (`booking_status`),
  KEY `idx_booking_date` (`booking_date`),
  CONSTRAINT `fk_booking_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_worker` FOREIGN KEY (`worker_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
-- Payment transactions with escrow support
--

CREATE TABLE `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `worker_id` int NOT NULL,
  `payment_reference` varchar(100) NOT NULL COMMENT 'Paystack transaction reference',
  `amount` decimal(10,2) NOT NULL COMMENT 'Total amount in GHS',
  `customer_commission` decimal(10,2) NOT NULL COMMENT '7% commission from customer',
  `worker_commission` decimal(10,2) NOT NULL COMMENT '5% commission from worker',
  `worker_payout` decimal(10,2) NOT NULL COMMENT 'Amount worker receives after commission',
  `currency` varchar(3) DEFAULT 'GHS',
  `payment_method` varchar(50) DEFAULT NULL COMMENT 'mobile_money, card, bank_transfer',
  `payment_status` enum('pending','successful','failed','refunded') DEFAULT 'pending',
  `escrow_status` enum('held','released','refunded') DEFAULT 'held',
  `escrow_release_date` timestamp NULL DEFAULT NULL,
  `auto_release_date` timestamp NULL DEFAULT NULL COMMENT '24 hours after completion',
  `paystack_response` text COMMENT 'JSON response from Paystack',
  `payment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `payment_reference` (`payment_reference`),
  KEY `booking_id` (`booking_id`),
  KEY `customer_id` (`customer_id`),
  KEY `worker_id` (`worker_id`),
  KEY `idx_escrow_status` (`escrow_status`),
  KEY `idx_payment_status` (`payment_status`),
  CONSTRAINT `fk_payment_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payment_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_payment_worker` FOREIGN KEY (`worker_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payouts`
-- Worker payout requests and tracking
--

CREATE TABLE `payouts` (
  `payout_id` int NOT NULL AUTO_INCREMENT,
  `worker_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payout_type` enum('instant','next_day','accumulated') DEFAULT 'next_day',
  `payout_fee` decimal(10,2) DEFAULT 0.00 COMMENT '2% for instant, 0% for next-day',
  `net_amount` decimal(10,2) NOT NULL COMMENT 'Amount after fees',
  `payout_method` varchar(50) DEFAULT NULL COMMENT 'mobile_money, bank_transfer',
  `account_details` text COMMENT 'JSON with account information',
  `payout_status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `payout_reference` varchar(100) DEFAULT NULL,
  `requested_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`payout_id`),
  KEY `worker_id` (`worker_id`),
  KEY `idx_payout_status` (`payout_status`),
  CONSTRAINT `fk_payout_worker` FOREIGN KEY (`worker_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
-- Customer reviews and ratings for workers
--

CREATE TABLE `reviews` (
  `review_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `worker_id` int NOT NULL,
  `rating` tinyint NOT NULL COMMENT '1-5 stars',
  `review_text` text,
  `review_photos` text COMMENT 'JSON array of photo URLs',
  `is_verified` tinyint(1) DEFAULT 1 COMMENT 'Verified purchase',
  `worker_response` text,
  `worker_response_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `booking_id` (`booking_id`),
  KEY `customer_id` (`customer_id`),
  KEY `worker_id` (`worker_id`),
  KEY `idx_rating` (`rating`),
  CONSTRAINT `fk_review_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_review_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_review_worker` FOREIGN KEY (`worker_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `chk_rating` CHECK ((`rating` >= 1 AND `rating` <= 5))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
-- In-app messaging between customers and workers
--

CREATE TABLE `messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int DEFAULT NULL,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `message_text` text NOT NULL,
  `message_type` enum('text','image','system') DEFAULT 'text',
  `attachment_url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `booking_id` (`booking_id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_message_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_message_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_message_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `disputes`
-- Dispute resolution system
--

CREATE TABLE `disputes` (
  `dispute_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `worker_id` int NOT NULL,
  `dispute_reason` enum('service_not_completed','poor_quality','overcharged','damaged_property','other') NOT NULL,
  `dispute_description` text NOT NULL,
  `evidence_photos` text COMMENT 'JSON array of photo URLs',
  `dispute_status` enum('open','under_review','resolved','closed') DEFAULT 'open',
  `resolution` text,
  `resolved_by` int DEFAULT NULL COMMENT 'Admin user_id',
  `resolution_outcome` enum('refund_customer','pay_worker','partial_refund','no_action') DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`dispute_id`),
  KEY `booking_id` (`booking_id`),
  KEY `customer_id` (`customer_id`),
  KEY `worker_id` (`worker_id`),
  KEY `resolved_by` (`resolved_by`),
  KEY `idx_dispute_status` (`dispute_status`),
  CONSTRAINT `fk_dispute_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dispute_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dispute_worker` FOREIGN KEY (`worker_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dispute_resolver` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
-- System notifications for users
--

CREATE TABLE `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `notification_type` enum('booking','payment','message','review','dispute','system') NOT NULL,
  `notification_title` varchar(200) NOT NULL,
  `notification_message` text NOT NULL,
  `related_id` int DEFAULT NULL COMMENT 'Related booking_id, payment_id, etc',
  `is_read` tinyint(1) DEFAULT 0,
  `is_sent` tinyint(1) DEFAULT 0 COMMENT 'For SMS/push notifications',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_notification_type` (`notification_type`),
  CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `worker_availability`
-- Worker schedule and availability
--

CREATE TABLE `worker_availability` (
  `availability_id` int NOT NULL AUTO_INCREMENT,
  `worker_id` int NOT NULL,
  `day_of_week` tinyint NOT NULL COMMENT '0=Sunday, 1=Monday, ..., 6=Saturday',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`availability_id`),
  KEY `worker_id` (`worker_id`),
  KEY `idx_day_of_week` (`day_of_week`),
  CONSTRAINT `fk_availability_worker` FOREIGN KEY (`worker_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `chk_day_of_week` CHECK ((`day_of_week` >= 0 AND `day_of_week` <= 6))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction_logs`
-- Audit trail for all financial transactions
--

CREATE TABLE `transaction_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `transaction_type` enum('payment','payout','commission','refund','fee') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `balance_before` decimal(10,2) NOT NULL,
  `balance_after` decimal(10,2) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_transaction_type` (`transaction_type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Insert default admin user
-- Password: admin123 (hashed with bcrypt)
--

INSERT INTO `users` (`user_id`, `user_name`, `user_email`, `user_password`, `user_phone`, `user_role`, `user_country`, `user_city`, `is_verified`, `is_active`) VALUES
(1, 'System Admin', 'admin@worknpay.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+233000000000', 3, 'Ghana', 'Accra', 1, 1);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
