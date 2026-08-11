-- Marketplace subscription plans (Venue Owner model)
-- Prefer running: php admin/setup-subscription-plans.php

ALTER TABLE `subscription_plans`
  ADD COLUMN IF NOT EXISTS `platform_fee_percent` DECIMAL(5,2) NULL DEFAULT NULL AFTER `price`;

-- Update by slug (run after setup-subscription-plans.php or use PHP script for full feature text)
