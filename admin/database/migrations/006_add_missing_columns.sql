-- Migration 006: Add missing columns & indexes for mobile API and admin features
-- Safe to run multiple times (uses IF NOT EXISTS / IGNORE)

-- 1. bookings: add transaction_id, cancellation_reason, cancelled_at, payment_method, special_requirements
ALTER TABLE `bookings`
    ADD COLUMN IF NOT EXISTS `transaction_id`       VARCHAR(120)  NULL AFTER `payment_id`,
    ADD COLUMN IF NOT EXISTS `payment_method`       VARCHAR(50)   NULL AFTER `transaction_id`,
    ADD COLUMN IF NOT EXISTS `cancellation_reason`  TEXT          NULL AFTER `notes`,
    ADD COLUMN IF NOT EXISTS `cancelled_at`         DATETIME      NULL AFTER `cancellation_reason`,
    ADD COLUMN IF NOT EXISTS `special_requirements` TEXT          NULL AFTER `cancelled_at`;

-- 2. venues: ensure google_map_link and whatsapp_number columns exist
ALTER TABLE `venues`
    ADD COLUMN IF NOT EXISTS `google_map_link`   VARCHAR(500) NULL AFTER `address`,
    ADD COLUMN IF NOT EXISTS `whatsapp_number`   VARCHAR(20)  NULL AFTER `phone`;

-- 3. courts: ensure surface_type column exists
ALTER TABLE `courts`
    ADD COLUMN IF NOT EXISTS `surface_type` VARCHAR(60) NULL AFTER `capacity`;

-- 4. sports: ensure icon, description, color, and is_featured columns exist
ALTER TABLE `sports`
    ADD COLUMN IF NOT EXISTS `description` TEXT NULL AFTER `name`,
    ADD COLUMN IF NOT EXISTS `icon`        VARCHAR(100) NULL AFTER `slug`,
    ADD COLUMN IF NOT EXISTS `color`       VARCHAR(20) NOT NULL DEFAULT '#22c55e' AFTER `icon`,
    ADD COLUMN IF NOT EXISTS `is_featured`  TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`;

-- 5. users: ensure deleted_at for soft-delete is indexed
ALTER TABLE `users`
    ADD INDEX IF NOT EXISTS `idx_users_deleted_at` (`deleted_at`);

-- 6. activity_logs: ensure user_id, user_name, user_role columns exist for admin reporting
ALTER TABLE `activity_logs`
    ADD COLUMN IF NOT EXISTS `user_role` VARCHAR(30) NULL AFTER `user_id`;
