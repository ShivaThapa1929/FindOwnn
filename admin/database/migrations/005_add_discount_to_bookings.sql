-- Add discount and pricing fields to bookings table
ALTER TABLE `bookings` 
ADD COLUMN `price_per_hour` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `total_hours`,
ADD COLUMN `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `price_per_hour`,
ADD COLUMN `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `discount_percent`,
ADD COLUMN `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `discount_amount`;

-- Update existing bookings with court prices
UPDATE bookings b
JOIN courts c ON b.court_id = c.id
SET b.price_per_hour = c.price_per_hour,
    b.subtotal = b.amount
WHERE b.court_id IS NOT NULL;

-- For bookings without court_id, use venue price
UPDATE bookings b
JOIN venues v ON b.venue_id = v.id
SET b.price_per_hour = v.price_per_hour,
    b.subtotal = b.amount
WHERE b.court_id IS NULL;
