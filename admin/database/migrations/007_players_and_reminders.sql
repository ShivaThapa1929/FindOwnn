-- Players section: booking reminder tracking
ALTER TABLE bookings
    ADD COLUMN IF NOT EXISTS reminder_sent_at DATETIME NULL DEFAULT NULL AFTER cancelled_at;

-- Fix walk-in customers stored with wrong role
UPDATE users SET role = 'player', status = 'active'
WHERE email LIKE '%@offline.findownn' AND role != 'player';
