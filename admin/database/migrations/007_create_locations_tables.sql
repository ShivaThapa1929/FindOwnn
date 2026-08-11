-- Migration 007: Create States, Cities, and Partner Requests tables
-- For Findownn Partner Onboarding & Location Management

-- 1. Create states table
CREATE TABLE IF NOT EXISTS `states` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `code`       VARCHAR(10)  NOT NULL,
    `status`     ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_states_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create cities table
CREATE TABLE IF NOT EXISTS `cities` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `state_id`   INT UNSIGNED NOT NULL,
    `name`       VARCHAR(100) NOT NULL,
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `status`     ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cities_state` (`state_id`),
    CONSTRAINT `fk_cities_state` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create partner_requests table
CREATE TABLE IF NOT EXISTS `partner_requests` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `owner_name`   VARCHAR(150) NOT NULL,
    `phone`        VARCHAR(20)  NOT NULL,
    `venue_name`   VARCHAR(200) NOT NULL,
    `state`        VARCHAR(100) NOT NULL DEFAULT 'Gujarat',
    `city`         VARCHAR(100) NOT NULL DEFAULT 'Bhuj',
    `area`         VARCHAR(255) NOT NULL,
    `latitude`     DECIMAL(10,8) NULL DEFAULT NULL,
    `longitude`    DECIMAL(11,8) NULL DEFAULT NULL,
    `map_address`  TEXT NULL DEFAULT NULL,
    `sports`       TEXT NULL DEFAULT NULL,
    `comments`     TEXT NULL DEFAULT NULL,
    `status`       ENUM('pending', 'contacted', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Seed States Data
INSERT IGNORE INTO `states` (`id`, `name`, `code`, `status`) VALUES
(1, 'Gujarat', 'GJ', 'active'),
(2, 'Maharashtra', 'MH', 'active'),
(3, 'Rajasthan', 'RJ', 'active'),
(4, 'Karnataka', 'KA', 'active'),
(5, 'Delhi', 'DL', 'active');

-- 5. Seed Cities Data (Bhuj set as primary default city for Gujarat)
INSERT IGNORE INTO `cities` (`id`, `state_id`, `name`, `is_default`, `status`) VALUES
(1, 1, 'Bhuj', 1, 'active'),
(2, 1, 'Gandhidham', 0, 'active'),
(3, 1, 'Anjar', 0, 'active'),
(4, 1, 'Mundra', 0, 'active'),
(5, 1, 'Mandvi', 0, 'active'),
(6, 1, 'Ahmedabad', 0, 'active'),
(7, 1, 'Surat', 0, 'active'),
(8, 1, 'Vadodara', 0, 'active'),
(9, 1, 'Rajkot', 0, 'active'),
(10, 1, 'Jamnagar', 0, 'active');
