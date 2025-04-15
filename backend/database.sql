CREATE DATABASE clothloop;
USE clothloop;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    user_type ENUM('buyer', 'seller') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 

CREATE TABLE cloth_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cloth_title VARCHAR(100) NOT NULL,
    description TEXT,
    size VARCHAR(20) NOT NULL,
    category VARCHAR(30) NOT NULL,
    rental_price DECIMAL(10,2) NOT NULL,
    contact_number VARCHAR(15) NOT NULL,
    whatsapp_number VARCHAR(15),
    shop_name VARCHAR(100) NOT NULL,
    shop_address TEXT NOT NULL,
    terms_and_conditions TEXT,
    location VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
