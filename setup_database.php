<?php
/**
 * Database Setup Script
 * 
 * This script sets up the necessary database tables for the ClothLoop application.
 * Run this script once after the database is created.
 */

// Include the database configuration
require_once __DIR__ . '/backend/config/database.php';

// Output for tracking progress
echo "Starting database setup...\n";

try {
    // Create database connection
    echo "Connecting to database...\n";
    $database = new Database();
    $db = $database->connect();
    
    echo "Connection successful!\n";
    
    // Create users table
    echo "Creating tables...\n";
    
    // Users table
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone_no VARCHAR(20),
            role ENUM('admin', 'seller', 'buyer') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            profile_photo VARCHAR(255),
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'
        )
    ");
    echo "- Users table created\n";
    
    // Sellers table
    $db->exec("
        CREATE TABLE IF NOT EXISTS sellers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            shop_name VARCHAR(100) NOT NULL,
            shop_description TEXT,
            address TEXT,
            rating DECIMAL(3,2) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "- Sellers table created\n";
    
    // Buyers table
    $db->exec("
        CREATE TABLE IF NOT EXISTS buyers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            shipping_address TEXT,
            preferences TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "- Buyers table created\n";
    
    // Products table
    $db->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            seller_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            description TEXT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            category VARCHAR(50) NOT NULL,
            subcategory VARCHAR(50),
            size VARCHAR(20) NOT NULL,
            condition_status VARCHAR(50) NOT NULL,
            rental_period VARCHAR(50) NOT NULL,
            availability BOOLEAN DEFAULT true,
            is_featured BOOLEAN DEFAULT false,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE
        )
    ");
    echo "- Products table created\n";
    
    // Product Images table
    $db->exec("
        CREATE TABLE IF NOT EXISTS product_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            image_url VARCHAR(255) NOT NULL,
            is_primary BOOLEAN DEFAULT false,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )
    ");
    echo "- Product Images table created\n";
    
    // Wishlist table
    $db->exec("
        CREATE TABLE IF NOT EXISTS wishlist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            buyer_id INT NOT NULL,
            product_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (buyer_id) REFERENCES buyers(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_wishlist (buyer_id, product_id)
        )
    ");
    echo "- Wishlist table created\n";
    
    // Reviews table
    $db->exec("
        CREATE TABLE IF NOT EXISTS reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            buyer_id INT NOT NULL,
            rating INT NOT NULL,
            comment TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (buyer_id) REFERENCES buyers(id) ON DELETE CASCADE
        )
    ");
    echo "- Reviews table created\n";
    
    // Create test user
    echo "Creating test users...\n";
    
    // Check if test seller exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = 'seller@example.com'");
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        // Create test seller
        $password = password_hash('password123', PASSWORD_DEFAULT);
        
        $db->exec("
            INSERT INTO users (name, email, password, role, status)
            VALUES ('Test Seller', 'seller@example.com', '$password', 'seller', 'active')
        ");
        
        $userId = $db->lastInsertId();
        
        $db->exec("
            INSERT INTO sellers (user_id, shop_name, shop_description)
            VALUES ($userId, 'Test Shop', 'This is a test shop selling high-quality clothes')
        ");
        
        echo "- Test seller created\n";
    } else {
        echo "- Test seller already exists\n";
    }
    
    // Check if test buyer exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = 'buyer@example.com'");
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        // Create test buyer
        $password = password_hash('password123', PASSWORD_DEFAULT);
        
        $db->exec("
            INSERT INTO users (name, email, password, role, status)
            VALUES ('Test Buyer', 'buyer@example.com', '$password', 'buyer', 'active')
        ");
        
        $userId = $db->lastInsertId();
        
        $db->exec("
            INSERT INTO buyers (user_id, shipping_address)
            VALUES ($userId, '123 Test Street, Test City, 12345')
        ");
        
        echo "- Test buyer created\n";
    } else {
        echo "- Test buyer already exists\n";
    }
    
    echo "\nDatabase setup completed successfully!\n";
    echo "You can now log in with the following test accounts:\n";
    echo "Seller: seller@example.com / password123\n";
    echo "Buyer: buyer@example.com / password123\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 