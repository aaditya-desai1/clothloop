-- Select database
USE clothloop;

-- Add is_hidden column to products table
ALTER TABLE products ADD COLUMN is_hidden TINYINT(1) NOT NULL DEFAULT 0 AFTER status; 