<?php
/**
 * Database Tables Setup Script
 * Creates the missing tables required for ClothLoop
 */

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$dbname = "clothloop";

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Setting up missing tables for ClothLoop...</h2>";
    
    // Check if users table exists, if not create it
    // Otherwise, we'll skip since it already exists
    $checkTable = $conn->prepare("SHOW TABLES LIKE 'users'");
    $checkTable->execute();
    
    if ($checkTable->rowCount() == 0) {
        // Create users table if it doesn't exist
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone_no VARCHAR(20) DEFAULT NULL,
            role ENUM('admin', 'seller', 'buyer') NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            profile_photo VARCHAR(255) DEFAULT NULL,
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'
        ) ENGINE=InnoDB";
        
        $conn->exec($sql);
        echo "Table 'users' created successfully <br>";
    } else {
        echo "Table 'users' already exists <br>";
    }
    
    // Create categories table if it doesn't exist (no foreign key dependencies)
    $sql = "CREATE TABLE IF NOT EXISTS categories (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        description TEXT,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    
    $conn->exec($sql);
    echo "Table 'categories' created or already exists successfully <br>";
    
    // Create products table after users and categories
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        seller_id INT(11) NOT NULL,
        category_id INT(11),
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        image_url VARCHAR(255),
        status ENUM('active', 'inactive', 'pending') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB";
    
    $conn->exec($sql);
    echo "Table 'products' created or already exists successfully <br>";
    
    // Create seller_reviews table
    $sql = "CREATE TABLE IF NOT EXISTS seller_reviews (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        seller_id INT(11) NOT NULL,
        buyer_id INT(11) NOT NULL,
        rating DECIMAL(3, 1) NOT NULL,
        review TEXT,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB";
    
    $conn->exec($sql);
    echo "Table 'seller_reviews' created or already exists successfully <br>";
    
    // Create product_reviews table
    $sql = "CREATE TABLE IF NOT EXISTS product_reviews (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        product_id INT(11) NOT NULL,
        buyer_id INT(11) NOT NULL,
        rating DECIMAL(3, 1) NOT NULL,
        review TEXT,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB";
    
    $conn->exec($sql);
    echo "Table 'product_reviews' created or already exists successfully <br>";
    
    // Create customer_interests table
    $sql = "CREATE TABLE IF NOT EXISTS customer_interests (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        buyer_id INT(11) NOT NULL,
        product_id INT(11) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB";
    
    $conn->exec($sql);
    echo "Table 'customer_interests' created or already exists successfully <br>";
    
    // Create product_images table
    $sql = "CREATE TABLE IF NOT EXISTS product_images (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        product_id INT(11) NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        is_primary BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB";
    
    $conn->exec($sql);
    echo "Table 'product_images' created or already exists successfully <br>";
    
    echo "<br><strong>Database setup completed successfully!</strong>";
    echo "<br><a href='/ClothLoop/frontend/pages/seller/seller_dashboard.html'>Go back to Seller Dashboard</a>";
    
} catch(PDOException $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red;'>";
    echo "<h3>Error setting up database tables:</h3>";
    echo $e->getMessage();
    echo "</div>";
}

$conn = null; 