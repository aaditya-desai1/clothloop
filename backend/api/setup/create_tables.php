<?php

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Max-Age: 3600");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Set content type
header('Content-Type: application/json');

/**
 * Database Tables Setup Script
 * Creates all necessary tables for the ClothLoop application based on clothloop_updates.sql
 */

// Required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/api_utils.php';

// Response object
$response = [
    'status' => 'success',
    'message' => 'Setup initiated',
    'tables_created' => [],
    'tables_failed' => [],
    'errors' => []
];

try {
    // Get database connection
    $database = new Database();
    $conn = $database->connect();
    $dbType = $database->dbType;
    
    // Log start of setup
    error_log("Starting table setup for database type: " . $dbType);
    
    // Create tables based on database type
    if ($dbType === 'pgsql') {
        createPostgreSQLTables($conn, $response);
    } else {
        createMySQLTables($conn, $response);
    }
    
    // Insert demo data
    insertDemoData($conn, $dbType, $response);
    
    // Return success response
    $response['message'] = 'Setup completed successfully';
    echo json_encode($response);
    
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'Setup failed: ' . $e->getMessage();
    $response['errors'][] = $e->getMessage();
    error_log("Setup failed: " . $e->getMessage());
    
    echo json_encode($response);
}

/**
 * Create tables for MySQL database
 * Based on clothloop_updates.sql
 * 
 * @param PDO $conn Database connection
 * @param array &$response Response array
 */
function createMySQLTables($conn, &$response) {
    try {
        // Enable foreign key constraints
        $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        // Users table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS users (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                phone_no VARCHAR(20) DEFAULT NULL,
                role ENUM('admin','seller','buyer') NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                profile_photo VARCHAR(255) DEFAULT NULL,
                status ENUM('active','inactive','suspended') DEFAULT 'active'
            )");
            $response['tables_created'][] = 'users';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'users';
            $response['errors'][] = "Error creating users table: " . $e->getMessage();
            error_log("Error creating users table: " . $e->getMessage());
        }
        
        // Buyers table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS buyers (
                id INT(11) NOT NULL PRIMARY KEY,
                address TEXT DEFAULT NULL,
                latitude DECIMAL(10,8) DEFAULT NULL,
                longitude DECIMAL(11,8) DEFAULT NULL,
                shipping_address VARCHAR(255) DEFAULT NULL,
                profile_photo VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE
            )");
            $response['tables_created'][] = 'buyers';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'buyers';
            $response['errors'][] = "Error creating buyers table: " . $e->getMessage();
            error_log("Error creating buyers table: " . $e->getMessage());
        }
        
        // Sellers table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS sellers (
                id INT(11) NOT NULL PRIMARY KEY,
                shop_name VARCHAR(100) DEFAULT NULL,
                description TEXT DEFAULT NULL,
                address TEXT DEFAULT NULL,
                latitude DECIMAL(10,8) DEFAULT NULL,
                longitude DECIMAL(11,8) DEFAULT NULL,
                avg_rating DECIMAL(3,2) DEFAULT 0.00,
                total_reviews INT(11) DEFAULT 0,
                profile_photo VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE
            )");
            $response['tables_created'][] = 'sellers';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'sellers';
            $response['errors'][] = "Error creating sellers table: " . $e->getMessage();
            error_log("Error creating sellers table: " . $e->getMessage());
        }
        
        // Categories table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS categories (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL UNIQUE,
                description TEXT DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                parent_id INT(11) DEFAULT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
            )");
            $response['tables_created'][] = 'categories';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'categories';
            $response['errors'][] = "Error creating categories table: " . $e->getMessage();
            error_log("Error creating categories table: " . $e->getMessage());
        }
        
        // Products table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS products (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                seller_id INT(11) NOT NULL,
                title VARCHAR(100) NOT NULL,
                description TEXT DEFAULT NULL,
                category_id INT(11) DEFAULT NULL,
                size VARCHAR(20) DEFAULT NULL,
                occasion VARCHAR(50) DEFAULT NULL,
                rental_price DECIMAL(10,2) NOT NULL,
                status ENUM('available','rented','unavailable') DEFAULT 'available',
                is_hidden TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                terms TEXT DEFAULT NULL,
                views INT(11) DEFAULT 0,
                FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
            )");
            $response['tables_created'][] = 'products';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'products';
            $response['errors'][] = "Error creating products table: " . $e->getMessage();
            error_log("Error creating products table: " . $e->getMessage());
        }
        
        // Product images table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS product_images (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                product_id INT(11) NOT NULL,
                image_path VARCHAR(255) NOT NULL,
                is_primary TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )");
            $response['tables_created'][] = 'product_images';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'product_images';
            $response['errors'][] = "Error creating product_images table: " . $e->getMessage();
            error_log("Error creating product_images table: " . $e->getMessage());
        }
        
        // Product reviews table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS product_reviews (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                product_id INT(11) NOT NULL,
                buyer_id INT(11) DEFAULT NULL,
                rating DECIMAL(3,1) NOT NULL,
                review TEXT DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE
            )");
            $response['tables_created'][] = 'product_reviews';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'product_reviews';
            $response['errors'][] = "Error creating product_reviews table: " . $e->getMessage();
            error_log("Error creating product_reviews table: " . $e->getMessage());
        }
        
        // Customer interests table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS customer_interests (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                buyer_id INT(11) NOT NULL,
                product_id INT(11) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )");
            $response['tables_created'][] = 'customer_interests';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'customer_interests';
            $response['errors'][] = "Error creating customer_interests table: " . $e->getMessage();
            error_log("Error creating customer_interests table: " . $e->getMessage());
        }
        
        // Wishlist table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS wishlist (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                buyer_id INT(11) NOT NULL,
                product_id INT(11) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY buyer_product (buyer_id, product_id),
                FOREIGN KEY (buyer_id) REFERENCES buyers(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )");
            $response['tables_created'][] = 'wishlist';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'wishlist';
            $response['errors'][] = "Error creating wishlist table: " . $e->getMessage();
            error_log("Error creating wishlist table: " . $e->getMessage());
        }
        
        // Seller notifications table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS seller_notifications (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                seller_id INT(11) NOT NULL,
                product_id INT(11) DEFAULT NULL,
                message TEXT NOT NULL,
                type ENUM('info','warning','restriction','success') NOT NULL DEFAULT 'info',
                is_read TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL,
                FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
            )");
            $response['tables_created'][] = 'seller_notifications';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'seller_notifications';
            $response['errors'][] = "Error creating seller_notifications table: " . $e->getMessage();
            error_log("Error creating seller_notifications table: " . $e->getMessage());
        }
    } catch (Exception $e) {
        $response['errors'][] = "General error in MySQL table creation: " . $e->getMessage();
        error_log("General error in MySQL table creation: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Create tables for PostgreSQL database
 * Based on clothloop_updates.sql but adapted for PostgreSQL
 * 
 * @param PDO $conn Database connection
 * @param array &$response Response array
 */
function createPostgreSQLTables($conn, &$response) {
    try {
        // Users table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                phone_no VARCHAR(20),
                role VARCHAR(20) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                profile_photo VARCHAR(255),
                status VARCHAR(20) DEFAULT 'active'
            )");
            
            // Create enum-like constraint for role
            $conn->exec("
                DO $$
                BEGIN
                    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'users_role_check') THEN
                        ALTER TABLE users ADD CONSTRAINT users_role_check
                        CHECK (role IN ('admin', 'seller', 'buyer'));
                    END IF;
                END $$;
            ");
            
            // Create enum-like constraint for status
            $conn->exec("
                DO $$
                BEGIN
                    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'users_status_check') THEN
                        ALTER TABLE users ADD CONSTRAINT users_status_check
                        CHECK (status IN ('active', 'inactive', 'suspended'));
                    END IF;
                END $$;
            ");
            
            $response['tables_created'][] = 'users';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'users';
            $response['errors'][] = "Error creating users table: " . $e->getMessage();
            error_log("Error creating users table: " . $e->getMessage());
        }
        
        // Buyers table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS buyers (
                id INTEGER PRIMARY KEY,
                address TEXT,
                latitude DECIMAL(10,8),
                longitude DECIMAL(11,8),
                shipping_address VARCHAR(255),
                profile_photo VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE
            )");
            $response['tables_created'][] = 'buyers';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'buyers';
            $response['errors'][] = "Error creating buyers table: " . $e->getMessage();
            error_log("Error creating buyers table: " . $e->getMessage());
        }
        
        // Sellers table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS sellers (
                id INTEGER PRIMARY KEY,
                shop_name VARCHAR(100),
                description TEXT,
                address TEXT,
                latitude DECIMAL(10,8),
                longitude DECIMAL(11,8),
                avg_rating DECIMAL(3,2) DEFAULT 0.00,
                total_reviews INTEGER DEFAULT 0,
                profile_photo VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE
            )");
            $response['tables_created'][] = 'sellers';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'sellers';
            $response['errors'][] = "Error creating sellers table: " . $e->getMessage();
            error_log("Error creating sellers table: " . $e->getMessage());
        }
        
        // Categories table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS categories (
                id SERIAL PRIMARY KEY,
                name VARCHAR(50) NOT NULL UNIQUE,
                description TEXT,
                parent_id INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
            )");
            $response['tables_created'][] = 'categories';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'categories';
            $response['errors'][] = "Error creating categories table: " . $e->getMessage();
            error_log("Error creating categories table: " . $e->getMessage());
        }
        
        // Products table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS products (
                id SERIAL PRIMARY KEY,
                seller_id INTEGER NOT NULL,
                title VARCHAR(100) NOT NULL,
                description TEXT,
                category_id INTEGER,
                size VARCHAR(20),
                occasion VARCHAR(50),
                rental_price DECIMAL(10,2) NOT NULL,
                status VARCHAR(20) DEFAULT 'available',
                is_hidden BOOLEAN NOT NULL DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                terms TEXT,
                views INTEGER DEFAULT 0,
                FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
            )");
            
            // Create enum-like constraint for status
            $conn->exec("
                DO $$
                BEGIN
                    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'products_status_check') THEN
                        ALTER TABLE products ADD CONSTRAINT products_status_check
                        CHECK (status IN ('available', 'unavailable', 'rented'));
                    END IF;
                END $$;
            ");
            
            $response['tables_created'][] = 'products';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'products';
            $response['errors'][] = "Error creating products table: " . $e->getMessage();
            error_log("Error creating products table: " . $e->getMessage());
        }
        
        // Product images table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS product_images (
                id SERIAL PRIMARY KEY,
                product_id INTEGER NOT NULL,
                image_path VARCHAR(255) NOT NULL,
                is_primary BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )");
            $response['tables_created'][] = 'product_images';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'product_images';
            $response['errors'][] = "Error creating product_images table: " . $e->getMessage();
            error_log("Error creating product_images table: " . $e->getMessage());
        }
        
        // Product reviews table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS product_reviews (
                id SERIAL PRIMARY KEY,
                product_id INTEGER NOT NULL,
                buyer_id INTEGER,
                rating DECIMAL(3,1) NOT NULL,
                review TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE
            )");
            $response['tables_created'][] = 'product_reviews';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'product_reviews';
            $response['errors'][] = "Error creating product_reviews table: " . $e->getMessage();
            error_log("Error creating product_reviews table: " . $e->getMessage());
        }
        
        // Customer interests table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS customer_interests (
                id SERIAL PRIMARY KEY,
                buyer_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )");
            $response['tables_created'][] = 'customer_interests';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'customer_interests';
            $response['errors'][] = "Error creating customer_interests table: " . $e->getMessage();
            error_log("Error creating customer_interests table: " . $e->getMessage());
        }
        
        // Wishlist table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS wishlist (
                id SERIAL PRIMARY KEY,
                buyer_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE (buyer_id, product_id),
                FOREIGN KEY (buyer_id) REFERENCES buyers(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )");
            $response['tables_created'][] = 'wishlist';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'wishlist';
            $response['errors'][] = "Error creating wishlist table: " . $e->getMessage();
            error_log("Error creating wishlist table: " . $e->getMessage());
        }
        
        // Seller notifications table
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS seller_notifications (
                id SERIAL PRIMARY KEY,
                seller_id INTEGER NOT NULL,
                product_id INTEGER,
                message TEXT NOT NULL,
                type VARCHAR(20) NOT NULL DEFAULT 'info',
                is_read BOOLEAN NOT NULL DEFAULT FALSE,
                created_at TIMESTAMP NOT NULL,
                FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
            )");
            
            // Create enum-like constraint for notification type
            $conn->exec("
                DO $$
                BEGIN
                    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'seller_notifications_type_check') THEN
                        ALTER TABLE seller_notifications ADD CONSTRAINT seller_notifications_type_check
                        CHECK (type IN ('info', 'warning', 'restriction', 'success'));
                    END IF;
                END $$;
            ");
            
            $response['tables_created'][] = 'seller_notifications';
        } catch (Exception $e) {
            $response['tables_failed'][] = 'seller_notifications';
            $response['errors'][] = "Error creating seller_notifications table: " . $e->getMessage();
            error_log("Error creating seller_notifications table: " . $e->getMessage());
        }
        
        // Add indexes to improve performance
        try {
            $conn->exec("CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id)");
            $conn->exec("CREATE INDEX IF NOT EXISTS idx_products_status ON products(status)");
            $conn->exec("CREATE INDEX IF NOT EXISTS idx_users_role ON users(role)");
            $conn->exec("CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)");
            $response['details'][] = "Created indexes for performance";
        } catch (Exception $e) {
            $response['errors'][] = "Error creating indexes: " . $e->getMessage();
            error_log("Error creating indexes: " . $e->getMessage());
        }
    } catch (Exception $e) {
        $response['errors'][] = "General error in PostgreSQL table creation: " . $e->getMessage();
        error_log("General error in PostgreSQL table creation: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Insert demo data into tables
 * 
 * @param PDO $conn Database connection
 * @param string $dbType Database type (mysql or pgsql)
 * @param array &$response Response array
 */
function insertDemoData($conn, $dbType, &$response) {
    try {
        // Insert demo categories
        $checkCategories = $conn->query("SELECT COUNT(*) FROM categories");
        if ($checkCategories->fetchColumn() == 0) {
            $demoCategories = [
                ['name' => 'Men', 'description' => 'Men\'s Clothing'],
                ['name' => 'Women', 'description' => 'Women\'s Clothing'],
                ['name' => 'Kids', 'description' => 'Kids Clothing'],
                ['name' => 'Ethnic', 'description' => 'Ethnic Wear'],
                ['name' => 'Formal', 'description' => 'Formal Wear']
            ];
            
            $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            foreach ($demoCategories as $category) {
                $stmt->execute([$category['name'], $category['description']]);
            }
            
            $response['message'] .= ' Categories created.';
        }
        
        // Insert demo users if none exist
        $checkUsers = $conn->query("SELECT COUNT(*) FROM users");
        if ($checkUsers->fetchColumn() == 0) {
            // Create a test seller
            $hashedPassword = password_hash('testpassword', PASSWORD_DEFAULT);
            
            // Insert test seller
            $conn->beginTransaction();
            
            try {
                // Insert into users table
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute(['Test Seller', 'seller@test.com', $hashedPassword, 'seller', 'active']);
                
                // Get user ID based on database type
                if ($dbType === 'pgsql') {
                    $userId = $conn->lastInsertId('users_id_seq');
                } else {
                    $userId = $conn->lastInsertId();
                }
                
                // Insert into sellers table
                $stmt = $conn->prepare("INSERT INTO sellers (id, shop_name, address, description) VALUES (?, ?, ?, ?)");
                $stmt->execute([$userId, 'Test Shop', '123 Test Street', 'This is a test seller account']);
                
                $conn->commit();
                
                $response['message'] .= ' Test seller created.';
                
                // Create a test buyer
                $conn->beginTransaction();
                
                // Insert into users table
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute(['Test Buyer', 'buyer@test.com', $hashedPassword, 'buyer', 'active']);
                
                // Get user ID based on database type
                if ($dbType === 'pgsql') {
                    $userId = $conn->lastInsertId('users_id_seq');
                } else {
                    $userId = $conn->lastInsertId();
                }
                
                // Insert into buyers table
                $stmt = $conn->prepare("INSERT INTO buyers (id, shipping_address) VALUES (?, ?)");
                $stmt->execute([$userId, '456 Test Avenue']);
                
                $conn->commit();
                
                $response['message'] .= ' Test buyer created.';
                
            } catch (Exception $e) {
                $conn->rollBack();
                $response['errors'][] = "Error creating test users: " . $e->getMessage();
                error_log("Error creating test users: " . $e->getMessage());
            }
        }
    } catch (Exception $e) {
        $response['errors'][] = "Error inserting demo data: " . $e->getMessage();
        error_log("Error inserting demo data: " . $e->getMessage());
    }
} 