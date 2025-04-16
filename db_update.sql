-- Make sure the shop_logo column exists if not add it
ALTER TABLE sellers ADD COLUMN IF NOT EXISTS shop_logo VARCHAR(255) NULL;

-- Check if shop_location column exists, if not add it
ALTER TABLE sellers ADD COLUMN IF NOT EXISTS shop_location VARCHAR(100) NULL;

-- Make sure the shop_name, shop_address, shop_bio fields exist
ALTER TABLE sellers ADD COLUMN IF NOT EXISTS shop_name VARCHAR(255) NULL;
ALTER TABLE sellers ADD COLUMN IF NOT EXISTS shop_address TEXT NULL;
ALTER TABLE sellers ADD COLUMN IF NOT EXISTS shop_bio TEXT NULL;

-- Create upload directory if it doesn't exist
-- This needs to be run as a PHP script separately 