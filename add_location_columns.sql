USE clothloop;

-- Add latitude and longitude columns if they don't exist
ALTER TABLE sellers 
ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,8) NULL AFTER address, 
ADD COLUMN IF NOT EXISTS longitude DECIMAL(11,8) NULL AFTER latitude; 