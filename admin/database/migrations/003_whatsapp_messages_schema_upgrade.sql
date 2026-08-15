-- Upgrade whatsapp_messages provider column (run once on Hostinger via phpMyAdmin)
-- Safe to run: skips if recipient_number already exists

ALTER TABLE `whatsapp_messages`
  ADD COLUMN IF NOT EXISTS `recipient_number` VARCHAR(20) NULL AFTER `user_id`,
  ADD COLUMN IF NOT EXISTS `message_content` TEXT NULL AFTER `message_type`,
  ADD COLUMN IF NOT EXISTS `provider` VARCHAR(20) NOT NULL DEFAULT 'twilio' AFTER `message_content`,
  ADD COLUMN IF NOT EXISTS `provider_message_id` VARCHAR(100) NULL AFTER `provider`,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NULL AFTER `created_at`;

UPDATE `whatsapp_messages`
SET `recipient_number` = COALESCE(`recipient_number`, `phone_number`)
WHERE `recipient_number` IS NULL AND `phone_number` IS NOT NULL;

UPDATE `whatsapp_messages`
SET `message_content` = COALESCE(`message_content`, `message`)
WHERE `message_content` IS NULL AND `message` IS NOT NULL;

ALTER TABLE `whatsapp_messages`
  MODIFY COLUMN `user_id` INT(10) UNSIGNED NULL;
