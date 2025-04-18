<?php
/**
 * Database Initialization Script
 * Creates all required tables for ClothLoop platform
 */

// Include database connection
require_once __DIR__ . '/config/database.php';

// Enable error reporting for development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Create database connection
$database = new Database();
$conn = $database->getConnection();

// Function to execute SQL safely
function executeSql($conn, $sql) {
    try {
        $conn->exec($sql);
        return true;
    } catch (PDOException $e) {
        echo "Error executing SQL: " . $e->getMessage() . "<br>";
        return false;
    }
}

// Create Users table
$success = executeSql($conn, "
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
        status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
        INDEX (email),
        INDEX (role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Create Sellers table
if ($success) {
    $success = executeSql($conn, "
        CREATE TABLE IF NOT EXISTS sellers (
            id INT PRIMARY KEY,
            shop_name VARCHAR(100),
            description TEXT,
            address TEXT,
            rating DECIMAL(3,2) DEFAULT 0,
            total_ratings INT DEFAULT 0,
            FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}

// Create Buyers table
if ($success) {
    $success = executeSql($conn, "
        CREATE TABLE IF NOT EXISTS buyers (
            id INT PRIMARY KEY,
            address TEXT,
            latitude DECIMAL(10,8),
            longitude DECIMAL(11,8),
            FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}

// Create Categories table
if ($success) {
    $success = executeSql($conn, "
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}

// Create Products table
if ($success) {
    $success = executeSql($conn, "
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            seller_id INT NOT NULL,
            title VARCHAR(100) NOT NULL,
            description TEXT,
            category_id INT,
            size VARCHAR(20),
            occasion VARCHAR(50),
            rental_price DECIMAL(10,2) NOT NULL,
            status ENUM('available', 'rented', 'unavailable') DEFAULT 'available',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            terms TEXT,
            views INT DEFAULT 0,
            FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id),
            INDEX (status),
            INDEX (seller_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}

// Create Product images table
if ($success) {
    $success = executeSql($conn, "
        CREATE TABLE IF NOT EXISTS product_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            is_primary BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            INDEX (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}

// Create Orders table
if ($success) {
    $success = executeSql($conn, "
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            buyer_id INT NOT NULL,
            seller_id INT NOT NULL,
            product_id INT NOT NULL,
            rental_start_date DATE NOT NULL,
            rental_end_date DATE NOT NULL,
            total_price DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'confirmed', 'cancelled', 'completed', 'returned') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            notes TEXT,
            FOREIGN KEY (buyer_id) REFERENCES buyers(id) ON DELETE CASCADE,
            FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            INDEX (status),
            INDEX (buyer_id),
            INDEX (seller_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}

// Create Reviews table
if ($success) {
    $success = executeSql($conn, "
        CREATE TABLE IF NOT EXISTS reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            buyer_id INT NOT NULL,
            product_id INT NOT NULL,
            seller_id INT NOT NULL,
            rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
            review_text TEXT,
            seller_response TEXT NULL,
            response_date TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (buyer_id) REFERENCES buyers(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE,
            UNIQUE (order_id),
            INDEX (product_id),
            INDEX (seller_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}

// Create Messages table
if ($success) {
    $success = executeSql($conn, "
        CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            message TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX (sender_id, receiver_id),
            INDEX (is_read)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}

// Create Wishlist table
if ($success) {
    $success = executeSql($conn, "
        CREATE TABLE IF NOT EXISTS wishlist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            buyer_id INT NOT NULL,
            product_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (buyer_id) REFERENCES buyers(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE (buyer_id, product_id),
            INDEX (buyer_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}

// Insert default categories if successful so far
if ($success) {
    $categories = [
        ['name' => 'Formal Wear', 'description' => 'Clothing for formal occasions like weddings, galas, and business events.'],
        ['name' => 'Casual Wear', 'description' => 'Everyday comfortable clothing for casual settings.'],
        ['name' => 'Party Wear', 'description' => 'Stylish clothing for parties and celebrations.'],
        ['name' => 'Ethnic Wear', 'description' => 'Traditional and cultural clothing from various ethnicities.'],
        ['name' => 'Winter Wear', 'description' => 'Warm clothing for cold weather.'],
        ['name' => 'Summer Wear', 'description' => 'Light clothing for hot weather.'],
        ['name' => 'Accessories', 'description' => 'Fashion accessories to complement outfits.'],
        ['name' => 'Footwear', 'description' => 'Shoes, heels, boots and other footwear.']
    ];
    
    try {
        $categoryInsert = $conn->prepare("INSERT IGNORE INTO categories (name, description) VALUES (:name, :description)");
        
        foreach ($categories as $category) {
            $categoryInsert->bindParam(':name', $category['name']);
            $categoryInsert->bindParam(':description', $category['description']);
            $categoryInsert->execute();
        }
    } catch (PDOException $e) {
        echo "Error adding categories: " . $e->getMessage() . "<br>";
        $success = false;
    }
}

// Final message
if ($success) {
    echo "<h2>Database tables created successfully!</h2>";
} else {
    echo "<h2>There were errors creating the database tables. Please check the messages above.</h2>";
} 