CREATE DATABASE clothloop;
USE clothloop;

-- Main users table (common information)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    user_type ENUM('buyer', 'seller') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 

-- Buyer-specific information table
CREATE TABLE buyer_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Seller-specific information table
CREATE TABLE seller_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    shop_name VARCHAR(100) NOT NULL,
    shop_address TEXT NOT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Cloth details table (linked to seller)
CREATE TABLE cloth_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    cloth_title VARCHAR(100) NOT NULL,
    description TEXT,
    size VARCHAR(20) NOT NULL,
    category VARCHAR(30) NOT NULL,
    rental_price DECIMAL(10,2) NOT NULL,
    contact_number VARCHAR(15) NOT NULL,
    whatsapp_number VARCHAR(15),
    terms_and_conditions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES seller_details(id) ON DELETE CASCADE
);

-- Table for cloth images
CREATE TABLE cloth_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cloth_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cloth_id) REFERENCES cloth_details(id) ON DELETE CASCADE
);
