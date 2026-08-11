-- ============================================================
-- Enhanced Venue Structure - Multiple Venues, Courts & Sports
-- WhatsApp Integration for Player Communication
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------
-- 1. ADD WHATSAPP TO USERS (Players)
-- ----------------------------------------------------------------
ALTER TABLE `users` 
ADD COLUMN `whatsapp_number` VARCHAR(20) NULL DEFAULT NULL AFTER `phone`,
ADD COLUMN `whatsapp_opt_in` TINYINT(1) NOT NULL DEFAULT 1 AFTER `whatsapp_number`,
ADD COLUMN `last_whatsapp_sent` DATETIME NULL DEFAULT NULL AFTER `whatsapp_opt_in`,
ADD KEY `idx_users_whatsapp` (`whatsapp_number`);

-- ----------------------------------------------------------------
-- 2. SPORTS TABLE - Define available sports
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sports` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100)  NOT NULL,
    `slug`        VARCHAR(120)  NOT NULL,
    `icon`        VARCHAR(100)  NULL DEFAULT NULL,
    `description` TEXT          NULL DEFAULT NULL,
    `is_active`   TINYINT(1)    NOT NULL DEFAULT 1,
    `sort_order`  INT           NOT NULL DEFAULT 0,
    `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_sports_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default sports
INSERT INTO `sports` (`name`, `slug`, `icon`, `is_active`, `sort_order`) VALUES
('Box Cricket', 'box-cricket', 'bi-trophy', 1, 1),
('Pickleball', 'pickleball', 'bi-circle', 1, 2),
('Football', 'football', 'bi-hexagon', 1, 3),
('Badminton', 'badminton', 'bi-star', 1, 4),
('Tennis', 'tennis', 'bi-circle-fill', 1, 5),
('Basketball', 'basketball', 'bi-square', 1, 6);

-- ----------------------------------------------------------------
-- 3. ENHANCE VENUES TABLE
-- ----------------------------------------------------------------
-- Remove type field since we'll use venue_sports mapping
ALTER TABLE `venues`
DROP COLUMN IF EXISTS `type`;

-- Add more venue details
ALTER TABLE `venues`
ADD COLUMN `contact_person` VARCHAR(120) NULL DEFAULT NULL AFTER `owner_id`,
ADD COLUMN `contact_email` VARCHAR(180) NULL DEFAULT NULL AFTER `contact_person`,
ADD COLUMN `contact_phone` VARCHAR(20) NULL DEFAULT NULL AFTER `contact_email`,
ADD COLUMN `whatsapp_number` VARCHAR(20) NULL DEFAULT NULL AFTER `contact_phone`,
ADD COLUMN `opening_time` TIME NULL DEFAULT '06:00:00' AFTER `amenities`,
ADD COLUMN `closing_time` TIME NULL DEFAULT '23:00:00' AFTER `opening_time`,
ADD COLUMN `booking_advance_days` INT NOT NULL DEFAULT 30 AFTER `closing_time`,
ADD COLUMN `cancellation_hours` INT NOT NULL DEFAULT 24 AFTER `booking_advance_days`,
ADD COLUMN `latitude` DECIMAL(10,8) NULL DEFAULT NULL AFTER `google_map_link`,
ADD COLUMN `longitude` DECIMAL(11,8) NULL DEFAULT NULL AFTER `latitude`;

-- ----------------------------------------------------------------
-- 4. VENUE_SPORTS MAPPING (Many-to-Many)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venue_sports` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `venue_id`   INT UNSIGNED NOT NULL,
    `sport_id`   INT UNSIGNED NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_venue_sport` (`venue_id`, `sport_id`),
    KEY `idx_vs_venue` (`venue_id`),
    KEY `idx_vs_sport` (`sport_id`),
    CONSTRAINT `fk_vs_venue` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_vs_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 5. COURTS TABLE - Multiple courts per venue
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `courts` (
    `id`                INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `venue_id`          INT UNSIGNED   NOT NULL,
    `sport_id`          INT UNSIGNED   NOT NULL,
    `name`              VARCHAR(150)   NOT NULL,
    `court_number`      VARCHAR(20)    NULL DEFAULT NULL,
    `description`       TEXT           NULL DEFAULT NULL,
    `surface_type`      VARCHAR(100)   NULL DEFAULT NULL COMMENT 'Artificial turf, Concrete, Wood, etc',
    `dimensions`        VARCHAR(100)   NULL DEFAULT NULL COMMENT 'e.g., 30x20 feet',
    `capacity`          INT            NULL DEFAULT NULL COMMENT 'Max players',
    `price_per_hour`    DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `featured_image`    VARCHAR(255)   NULL DEFAULT NULL,
    `amenities`         TEXT           NULL DEFAULT NULL COMMENT 'JSON array of amenities',
    `equipment_provided` TEXT          NULL DEFAULT NULL COMMENT 'JSON array of equipment',
    `status`            ENUM('active','inactive','maintenance') NOT NULL DEFAULT 'active',
    `is_indoor`         TINYINT(1)     NOT NULL DEFAULT 0,
    `has_lighting`      TINYINT(1)     NOT NULL DEFAULT 1,
    `booking_slot_duration` INT        NOT NULL DEFAULT 60 COMMENT 'Minutes per slot',
    `sort_order`        INT            NOT NULL DEFAULT 0,
    `created_at`        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        DATETIME       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_courts_venue` (`venue_id`),
    KEY `idx_courts_sport` (`sport_id`),
    KEY `idx_courts_status` (`status`),
    CONSTRAINT `fk_courts_venue` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_courts_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 6. COURT IMAGES TABLE - Multiple images per court
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `court_images` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `court_id`   INT UNSIGNED  NOT NULL,
    `image_path` VARCHAR(255)  NOT NULL,
    `caption`    VARCHAR(255)  NULL DEFAULT NULL,
    `image_type` ENUM('gallery','featured','360_view') NOT NULL DEFAULT 'gallery',
    `sort_order` INT           NOT NULL DEFAULT 0,
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cim_court` (`court_id`),
    CONSTRAINT `fk_cim_court` FOREIGN KEY (`court_id`) REFERENCES `courts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 7. UPDATE BOOKINGS TABLE - Link to courts
-- ----------------------------------------------------------------
ALTER TABLE `bookings`
ADD COLUMN `court_id` INT UNSIGNED NULL DEFAULT NULL AFTER `venue_id`,
ADD COLUMN `sport_id` INT UNSIGNED NULL DEFAULT NULL AFTER `court_id`,
ADD KEY `idx_book_court` (`court_id`),
ADD KEY `idx_book_sport` (`sport_id`);

-- Add foreign keys for new columns
ALTER TABLE `bookings`
ADD CONSTRAINT `fk_book_court` FOREIGN KEY (`court_id`) REFERENCES `courts` (`id`),
ADD CONSTRAINT `fk_book_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`);

-- ----------------------------------------------------------------
-- 8. WHATSAPP MESSAGES LOG
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `whatsapp_messages` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`      INT UNSIGNED    NOT NULL,
    `phone_number` VARCHAR(20)     NOT NULL,
    `message_type` ENUM('reminder','promotion','booking_confirmation','cancellation') NOT NULL,
    `message`      TEXT            NOT NULL,
    `status`       ENUM('pending','sent','failed','delivered') NOT NULL DEFAULT 'pending',
    `sent_by`      INT UNSIGNED    NULL DEFAULT NULL,
    `booking_id`   INT UNSIGNED    NULL DEFAULT NULL,
    `whatsapp_link` VARCHAR(500)   NULL DEFAULT NULL,
    `error_message` TEXT           NULL DEFAULT NULL,
    `sent_at`      DATETIME        NULL DEFAULT NULL,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_wa_user` (`user_id`),
    KEY `idx_wa_status` (`status`),
    KEY `idx_wa_type` (`message_type`),
    CONSTRAINT `fk_wa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 9. COURT AVAILABILITY SLOTS (Optional - for preset slots)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `court_slots` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `court_id`   INT UNSIGNED  NOT NULL,
    `day_of_week` TINYINT      NOT NULL COMMENT '0=Sunday, 1=Monday, ..., 6=Saturday',
    `start_time` TIME          NOT NULL,
    `end_time`   TIME          NOT NULL,
    `is_active`  TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_slots_court` (`court_id`),
    KEY `idx_slots_day` (`day_of_week`),
    CONSTRAINT `fk_slots_court` FOREIGN KEY (`court_id`) REFERENCES `courts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 10. PRICING RULES (Optional - dynamic pricing)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pricing_rules` (
    `id`            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `court_id`      INT UNSIGNED   NOT NULL,
    `rule_name`     VARCHAR(150)   NOT NULL,
    `day_type`      ENUM('weekday','weekend','all') NOT NULL DEFAULT 'all',
    `start_time`    TIME           NULL DEFAULT NULL,
    `end_time`      TIME           NULL DEFAULT NULL,
    `price_per_hour` DECIMAL(10,2) NOT NULL,
    `is_active`     TINYINT(1)     NOT NULL DEFAULT 1,
    `priority`      INT            NOT NULL DEFAULT 0 COMMENT 'Higher priority rules apply first',
    `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pr_court` (`court_id`),
    CONSTRAINT `fk_pr_court` FOREIGN KEY (`court_id`) REFERENCES `courts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 11. UPDATE VENUE IMAGES - Add type field
-- ----------------------------------------------------------------
ALTER TABLE `venue_images`
ADD COLUMN `image_type` ENUM('gallery','featured','cover') NOT NULL DEFAULT 'gallery' AFTER `image_path`;

-- ----------------------------------------------------------------
-- 12. WHATSAPP TEMPLATES (for quick messages)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `whatsapp_templates` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(150)  NOT NULL,
    `slug`        VARCHAR(170)  NOT NULL,
    `category`    ENUM('reminder','promotion','booking','general') NOT NULL,
    `message`     TEXT          NOT NULL COMMENT 'Use {{name}}, {{venue}}, {{date}}, {{time}} as placeholders',
    `is_active`   TINYINT(1)    NOT NULL DEFAULT 1,
    `created_by`  INT UNSIGNED  NULL DEFAULT NULL,
    `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_wt_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default templates
INSERT INTO `whatsapp_templates` (`name`, `slug`, `category`, `message`, `is_active`) VALUES
('Booking Reminder', 'booking-reminder', 'reminder', 
'Hi {{name}}! 🏏 Reminder: Your booking at {{venue}} is on {{date}} at {{time}}. See you there! - Findownn Team', 1),

('Booking Confirmation', 'booking-confirmation', 'booking',
'Hi {{name}}! ✅ Your booking is confirmed at {{venue}} on {{date}} at {{time}}. Booking ID: {{booking_id}}. Have a great game! 🎾', 1),

('New Venue Promotion', 'new-venue-promo', 'promotion',
'Hi {{name}}! 🎉 New venue alert! Check out {{venue}} in {{city}}. Book now at special prices! - Findownn', 1),

('Cancellation Notice', 'booking-cancelled', 'booking',
'Hi {{name}}, Your booking at {{venue}} on {{date}} has been cancelled. Refund will be processed in 3-5 days. - Findownn', 1);

SET FOREIGN_KEY_CHECKS = 1;
