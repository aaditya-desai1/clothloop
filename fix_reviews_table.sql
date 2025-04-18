-- Check if the product_reviews table exists, if not create it
CREATE TABLE IF NOT EXISTS product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    buyer_id INT,
    rating INT NOT NULL,
    created_at DATETIME NOT NULL
);

-- Check if the review column exists, if not add it
ALTER TABLE product_reviews ADD COLUMN IF NOT EXISTS review TEXT NOT NULL;

-- If your MySQL version doesn't support IF NOT EXISTS for columns, use this alternative approach:
-- First, check if the column exists
SET @exists = 0;
SELECT 1 INTO @exists FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'clothloop' AND TABLE_NAME = 'product_reviews' AND COLUMN_NAME = 'review';

-- If column doesn't exist, add it
SET @query = IF(@exists = 0, 'ALTER TABLE product_reviews ADD COLUMN review TEXT NOT NULL', 'SELECT "Column already exists"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt; 