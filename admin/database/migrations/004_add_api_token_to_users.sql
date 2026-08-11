-- Add API token column to users table for mobile app authentication
ALTER TABLE users
ADD COLUMN api_token VARCHAR(255) NULL AFTER password,
ADD COLUMN api_token_expires_at DATETIME NULL AFTER api_token,
ADD INDEX idx_api_token (api_token);

-- Update existing users to have no token (will be generated on login)
UPDATE users SET api_token = NULL, api_token_expires_at = NULL;
