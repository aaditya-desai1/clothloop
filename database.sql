-- Create the buyers table
CREATE TABLE IF NOT EXISTS buyers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone_no VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create the sellers table
CREATE TABLE IF NOT EXISTS sellers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone_no VARCHAR(20) NOT NULL,
    shop_name VARCHAR(100) NOT NULL,
    shop_address TEXT NOT NULL,
    shop_location VARCHAR(100) NOT NULL,
    shop_logo VARCHAR(255) DEFAULT NULL,
    shop_bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create the cloth_details table
CREATE TABLE IF NOT EXISTS cloth_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    cloth_title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    size VARCHAR(50) NOT NULL,
    category ENUM('Men', 'Women', 'Kids') NOT NULL,
    rental_price DECIMAL(10,2) NOT NULL,
    contact_no VARCHAR(20) NOT NULL,
    whatsapp_no VARCHAR(20) NOT NULL,
    terms_conditions TEXT NOT NULL,
    cloth_photo MEDIUMBLOB NOT NULL,
    photo_type VARCHAR(50) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE
);

-- Index for faster email lookups
CREATE INDEX idx_buyers_email ON buyers(email);
CREATE INDEX idx_sellers_email ON sellers(email);

-- Index for faster searches
CREATE INDEX idx_cloth_details_seller ON cloth_details(seller_id);
CREATE INDEX idx_cloth_details_category ON cloth_details(category); 