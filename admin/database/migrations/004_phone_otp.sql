-- Phone OTP verification for owner registration
CREATE TABLE IF NOT EXISTS `phone_otps` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `phone`       VARCHAR(20)  NOT NULL,
    `otp_hash`    VARCHAR(255) NOT NULL,
    `purpose`     VARCHAR(40)  NOT NULL DEFAULT 'registration',
    `attempts`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `verified_at` DATETIME     NULL DEFAULT NULL,
    `expires_at`  DATETIME     NOT NULL,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_phone_otps_phone` (`phone`),
    KEY `idx_phone_otps_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `phone_verified_at` DATETIME NULL DEFAULT NULL AFTER `phone`;
