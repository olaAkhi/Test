-- SQL Schema for Astrology & Numerology Website

-- Create the database if it doesn't exist (optional, depends on setup)
-- CREATE DATABASE IF NOT EXISTS astronumerology_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE astronumerology_db;

-- Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `balance` DECIMAL(10, 2) DEFAULT 0.00,
    `is_active` BOOLEAN DEFAULT TRUE, -- Added for admin management
    `wants_daily_forecast` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Services Table
CREATE TABLE IF NOT EXISTS `services` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10, 2) NOT NULL,
    `type` ENUM('astrology', 'numerology', 'general') NOT NULL,
    `category` VARCHAR(100) DEFAULT NULL, -- e.g., "Natal Chart", "Compatibility", "Forecast"
    `input_fields` JSON DEFAULT NULL, -- JSON array describing required input fields like ['birth_date', 'birth_time', 'birth_place', 'name1', 'name2']
    `calculation_logic_ref` VARCHAR(100) DEFAULT NULL, -- Identifier for backend logic function/class
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Services Table (Junction table for purchased services)
CREATE TABLE IF NOT EXISTS `user_services` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `service_id` INT UNSIGNED NOT NULL,
    `purchase_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `input_data` JSON DEFAULT NULL, -- JSON object storing user-provided data for this specific service instance
    `status` ENUM('pending', 'processing', 'completed', 'failed', 'failed_generation', 'fulfilled', 'cancelled', 'refunded', 'partially_refunded') DEFAULT 'pending', -- Added refund statuses
    `result_json_data` TEXT DEFAULT NULL, -- Stores the JSON report data directly
    `admin_notes` TEXT DEFAULT NULL,
    `last_status_change_by_admin_id` INT UNSIGNED DEFAULT NULL,
    `last_status_change_at` TIMESTAMP NULL DEFAULT NULL,
    `refunded_amount` DECIMAL(10,2) DEFAULT 0.00,
    `viewed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE RESTRICT, -- Or CASCADE if services can be deleted
    FOREIGN KEY (`last_status_change_by_admin_id`) REFERENCES `admin_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daily Forecast Log (Optional, if we want to track sent forecasts)
CREATE TABLE IF NOT EXISTS `daily_forecast_log` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `forecast_date` DATE NOT NULL,
    `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `content_summary` TEXT DEFAULT NULL, -- Or a reference to the content
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `user_forecast_date` (`user_id`, `forecast_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions Table (Optional, for more detailed balance tracking)
CREATE TABLE IF NOT EXISTS `transactions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `type` ENUM('purchase', 'refund', 'deposit') NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `related_service_id` INT UNSIGNED DEFAULT NULL, -- Link to user_services if it's a purchase
    `transaction_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`related_service_id`) REFERENCES `user_services`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Placeholder for initial services data - this would be inserted via PHP script or manually
-- INSERT INTO `services` (`name`, `description`, `price`, `type`, `category`, `input_fields`, `calculation_logic_ref`) VALUES
-- ('Birth Chart (Natal Chart) Interpretation', 'Full analysis of your personality, strengths, and life path based on your date, time, and place of birth.', 25.00, 'astrology', 'Natal Chart', '["birth_date", "birth_time", "birth_place"]', 'calculateNatalChart'),
-- ('Life Path Number Reading', 'Discover your life’s main purpose and destiny based on your birth date.', 15.00, 'numerology', 'Core Numbers', '["birth_date"]', 'calculateLifePathNumber');

-- Note:
-- `input_fields` JSON example: ["birth_date", "birth_time", "birth_location", "full_name"]
-- `input_data` JSON example for a user_service: {"birth_date": "1990-01-01", "birth_time": "12:30", "birth_location": "New York, USA"}
-- `calculation_logic_ref` could be a function name, a class@method string, or an API endpoint identifier.
-- `result_data_path` could store a JSON string directly for simple results, or a path to a generated PDF/HTML file for complex reports.

ALTER TABLE `users` ADD INDEX `idx_users_email` (`email`);
ALTER TABLE `services` ADD INDEX `idx_services_type` (`type`);
ALTER TABLE `services` ADD INDEX `idx_services_category` (`category`);
ALTER TABLE `user_services` ADD INDEX `idx_user_services_user_status` (`user_id`, `status`);

-- Admin Users Table
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'super_admin') DEFAULT 'admin',
    `is_active` BOOLEAN DEFAULT TRUE,
    `last_login_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add index for admin username
ALTER TABLE `admin_users` ADD INDEX `idx_admin_users_username` (`username`);

-- Balance Audit Log Table
CREATE TABLE IF NOT EXISTS `balance_audit_log` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `admin_user_id` INT UNSIGNED NOT NULL,
    `amount_changed` DECIMAL(10, 2) NOT NULL COMMENT 'Positive for addition, negative for subtraction',
    `old_balance` DECIMAL(10, 2) NOT NULL,
    `new_balance` DECIMAL(10, 2) NOT NULL,
    `reason` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users`(`id`) ON DELETE RESTRICT -- Or SET NULL if admin can be deleted
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for balance audit log
ALTER TABLE `balance_audit_log` ADD INDEX `idx_bal_user_id` (`user_id`);
ALTER TABLE `balance_audit_log` ADD INDEX `idx_bal_admin_user_id` (`admin_user_id`);
ALTER TABLE `balance_audit_log` ADD INDEX `idx_bal_created_at` (`created_at`);

-- Order Status Log Table
CREATE TABLE IF NOT EXISTS `order_status_log` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_service_id` INT UNSIGNED NOT NULL,
    `admin_user_id` INT UNSIGNED DEFAULT NULL COMMENT 'Admin who made the change, NULL if system change',
    `old_status` VARCHAR(50) NOT NULL,
    `new_status` VARCHAR(50) NOT NULL,
    `change_reason` TEXT DEFAULT NULL COMMENT 'Reason provided by admin or system',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_service_id`) REFERENCES `user_services`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for order status log
ALTER TABLE `order_status_log` ADD INDEX `idx_osl_user_service_id` (`user_service_id`);
ALTER TABLE `order_status_log` ADD INDEX `idx_osl_admin_user_id` (`admin_user_id`);
ALTER TABLE `order_status_log` ADD INDEX `idx_osl_created_at` (`created_at`);

-- Application Settings Table
CREATE TABLE IF NOT EXISTS `app_settings` (
    `setting_key` VARCHAR(100) NOT NULL PRIMARY KEY,
    `setting_value` TEXT DEFAULT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
