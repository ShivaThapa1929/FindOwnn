-- ============================================================
-- Findownn Admin - Complete Database Schema v1.0
-- Compatible with MySQL 5.7+ and MariaDB 10.3+
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ----------------------------------------------------------------
-- USERS
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`              VARCHAR(120)    NOT NULL,
    `email`             VARCHAR(180)    NOT NULL,
    `password`          VARCHAR(255)    NOT NULL,
    `phone`             VARCHAR(20)     NULL DEFAULT NULL,
    `role`              ENUM('super_admin','admin','venue_owner','player') NOT NULL DEFAULT 'venue_owner',
    `status`            ENUM('active','inactive','suspended')     NOT NULL DEFAULT 'active',
    `avatar`            VARCHAR(255)    NULL DEFAULT NULL,
    `email_verified_at` DATETIME        NULL DEFAULT NULL,
    `last_login_at`     DATETIME        NULL DEFAULT NULL,
    `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        DATETIME        NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`),
    KEY `idx_users_role`   (`role`),
    KEY `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- VENUES
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venues` (
    `id`                   INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `owner_id`             INT UNSIGNED   NOT NULL,
    `name`                 VARCHAR(200)   NOT NULL,
    `slug`                 VARCHAR(220)   NOT NULL,
    `type`                 ENUM('box_cricket','pickleball','football','badminton','tennis','other') NOT NULL DEFAULT 'box_cricket',
    `description`          TEXT           NULL DEFAULT NULL,
    `address`              TEXT           NULL DEFAULT NULL,
    `city`                 VARCHAR(100)   NULL DEFAULT NULL,
    `state`                VARCHAR(100)   NULL DEFAULT NULL,
    `pincode`              VARCHAR(10)    NULL DEFAULT NULL,
    `google_map_link`      VARCHAR(500)   NULL DEFAULT NULL,
    `amenities`            TEXT           NULL DEFAULT NULL,
    `price_per_hour`       DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `featured_image`       VARCHAR(255)   NULL DEFAULT NULL,
    `rating`               DECIMAL(3,2)   NOT NULL DEFAULT 0.00,
    `total_reviews`        INT UNSIGNED   NOT NULL DEFAULT 0,
    `status`               ENUM('active','inactive','suspended') NOT NULL DEFAULT 'inactive',
    `verification_status`  ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `is_verified`          TINYINT(1)     NOT NULL DEFAULT 0,
    `verified_by`          INT UNSIGNED   NULL DEFAULT NULL,
    `verified_at`          DATETIME       NULL DEFAULT NULL,
    `badge_expires_at`     DATE           NULL DEFAULT NULL,
    `verification_notes`   TEXT           NULL DEFAULT NULL,
    `created_at`           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`           DATETIME       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_venues_owner`  (`owner_id`),
    KEY `idx_venues_status` (`status`),
    KEY `idx_venues_type`   (`type`),
    KEY `idx_venues_city`   (`city`),
    CONSTRAINT `fk_venues_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- VENUE IMAGES
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venue_images` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `venue_id`   INT UNSIGNED  NOT NULL,
    `image_path` VARCHAR(255)  NOT NULL,
    `caption`    VARCHAR(255)  NULL DEFAULT NULL,
    `sort_order` INT           NOT NULL DEFAULT 0,
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_vim_venue` (`venue_id`),
    CONSTRAINT `fk_vim_venue` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- SUBSCRIPTION PLANS
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `subscription_plans` (
    `id`            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(100)   NOT NULL,
    `slug`          VARCHAR(120)   NOT NULL,
    `price`         DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `billing_cycle` ENUM('monthly','quarterly','yearly','lifetime') NOT NULL DEFAULT 'monthly',
    `description`   TEXT           NULL DEFAULT NULL,
    `features`      TEXT           NULL DEFAULT NULL,
    `max_venues`    INT UNSIGNED   NOT NULL DEFAULT 1,
    `max_images`    INT UNSIGNED   NOT NULL DEFAULT 5,
    `max_slots`     INT UNSIGNED   NOT NULL DEFAULT 10,
    `is_active`     TINYINT(1)     NOT NULL DEFAULT 1,
    `is_featured`   TINYINT(1)     NOT NULL DEFAULT 0,
    `sort_order`    INT            NOT NULL DEFAULT 0,
    `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_plans_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- SUBSCRIPTIONS
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `user_id`        INT UNSIGNED   NOT NULL,
    `plan_id`        INT UNSIGNED   NOT NULL,
    `status`         ENUM('active','expired','pending','cancelled') NOT NULL DEFAULT 'pending',
    `starts_at`      DATETIME       NULL DEFAULT NULL,
    `expires_at`     DATETIME       NULL DEFAULT NULL,
    `auto_renew`     TINYINT(1)     NOT NULL DEFAULT 0,
    `payment_id`     INT UNSIGNED   NULL DEFAULT NULL,
    `amount_paid`    DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `invoice_number` VARCHAR(50)    NULL DEFAULT NULL,
    `created_at`     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sub_user`   (`user_id`),
    KEY `idx_sub_plan`   (`plan_id`),
    KEY `idx_sub_status` (`status`),
    CONSTRAINT `fk_sub_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sub_plan` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- PAYMENTS
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
    `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `user_id`        INT UNSIGNED   NOT NULL,
    `type`           ENUM('subscription','booking','refund') NOT NULL DEFAULT 'subscription',
    `subject_id`     INT UNSIGNED   NULL DEFAULT NULL,
    `amount`         DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `currency`       VARCHAR(5)     NOT NULL DEFAULT 'INR',
    `gateway`        VARCHAR(50)    NULL DEFAULT 'razorpay',
    `gateway_txn_id` VARCHAR(200)   NULL DEFAULT NULL,
    `status`         ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    `invoice_number` VARCHAR(50)    NULL DEFAULT NULL,
    `notes`          TEXT           NULL DEFAULT NULL,
    `paid_at`        DATETIME       NULL DEFAULT NULL,
    `created_at`     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pay_user` (`user_id`),
    CONSTRAINT `fk_pay_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- BOOKINGS
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bookings` (
    `id`                INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `venue_id`          INT UNSIGNED   NOT NULL,
    `user_id`           INT UNSIGNED   NOT NULL,
    `booking_date`      DATE           NOT NULL,
    `start_time`        TIME           NOT NULL,
    `end_time`          TIME           NOT NULL,
    `total_hours`       DECIMAL(4,2)   NOT NULL DEFAULT 1.00,
    `amount`            DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `status`            ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
    `payment_status`    ENUM('pending','paid','refunded','failed')          NOT NULL DEFAULT 'pending',
    `payment_id`        INT UNSIGNED   NULL DEFAULT NULL,
    `booking_reference` VARCHAR(30)    NULL DEFAULT NULL,
    `notes`             TEXT           NULL DEFAULT NULL,
    `created_at`        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_book_venue` (`venue_id`),
    KEY `idx_book_user`  (`user_id`),
    KEY `idx_book_date`  (`booking_date`),
    CONSTRAINT `fk_book_venue` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`),
    CONSTRAINT `fk_book_user`  FOREIGN KEY (`user_id`)  REFERENCES `users`  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- REVIEWS
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reviews` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `venue_id`   INT UNSIGNED  NOT NULL,
    `user_id`    INT UNSIGNED  NOT NULL,
    `booking_id` INT UNSIGNED  NULL DEFAULT NULL,
    `rating`     TINYINT       NOT NULL DEFAULT 5,
    `review`     TEXT          NULL DEFAULT NULL,
    `status`     ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_rev_venue` (`venue_id`),
    CONSTRAINT `fk_rev_venue` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rev_user`  FOREIGN KEY (`user_id`)  REFERENCES `users`  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- NOTIFICATIONS
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
    `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`      INT UNSIGNED  NOT NULL,
    `title`        VARCHAR(255)  NOT NULL,
    `message`      TEXT          NULL DEFAULT NULL,
    `type`         VARCHAR(50)   NOT NULL DEFAULT 'info',
    `subject_type` VARCHAR(100)  NULL DEFAULT NULL,
    `subject_id`   INT UNSIGNED  NULL DEFAULT NULL,
    `is_read`      TINYINT(1)    NOT NULL DEFAULT 0,
    `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notif_user` (`user_id`),
    CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- SUPPORT TICKETS
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `support_tickets` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED  NOT NULL,
    `subject`     VARCHAR(255)  NOT NULL,
    `message`     TEXT          NOT NULL,
    `status`      ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
    `priority`    ENUM('low','medium','high','urgent')           NOT NULL DEFAULT 'medium',
    `assigned_to` INT UNSIGNED  NULL DEFAULT NULL,
    `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ticket_user`   (`user_id`),
    KEY `idx_ticket_status` (`status`),
    CONSTRAINT `fk_ticket_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- AUDIT LOGS
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED    NULL DEFAULT NULL,
    `action`     VARCHAR(100)    NOT NULL,
    `model`      VARCHAR(100)    NULL DEFAULT NULL,
    `model_id`   INT UNSIGNED    NULL DEFAULT NULL,
    `old_values` TEXT            NULL DEFAULT NULL,
    `new_values` TEXT            NULL DEFAULT NULL,
    `ip_address` VARCHAR(50)     NULL DEFAULT NULL,
    `user_agent` VARCHAR(500)    NULL DEFAULT NULL,
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_audit_user`   (`user_id`),
    KEY `idx_audit_action` (`action`),
    KEY `idx_audit_model`  (`model`, `model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- ACTIVITY LOGS
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`      INT UNSIGNED    NULL DEFAULT NULL,
    `description`  TEXT            NOT NULL,
    `type`         VARCHAR(50)     NOT NULL DEFAULT 'info',
    `subject_type` VARCHAR(100)    NULL DEFAULT NULL,
    `subject_id`   INT UNSIGNED    NULL DEFAULT NULL,
    `ip_address`   VARCHAR(50)     NULL DEFAULT NULL,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_act_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- SETTINGS
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `key`        VARCHAR(100)  NOT NULL,
    `value`      TEXT          NULL DEFAULT NULL,
    `group`      VARCHAR(50)   NOT NULL DEFAULT 'general',
    `type`       VARCHAR(30)   NOT NULL DEFAULT 'text',
    `label`      VARCHAR(200)  NULL DEFAULT NULL,
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_settings_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
