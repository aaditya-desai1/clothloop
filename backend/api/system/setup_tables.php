<?php
/**
 * Setup Database Tables
 * 
 * This endpoint creates the database tables for PostgreSQL
 */

// Allow CORS from any origin for Vercel frontend
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

// Include environment variables
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/database.php';

// Initialize response
$response = [
    'status' => 'pending',
    'message' => 'Starting database setup...',
    'details' => [],
    'timestamp' => date('Y-m-d H:i:s')
];

try {
    // Create database connection
    $database = new Database();
    $db = $database->connect();
    
    $response['details'][] = "Connected to database successfully.";
    $response['details'][] = "Database type: " . $database->dbType;
    
    // Users table
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS users (
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
            )
        ");
        $response['details'][] = "Created users table.";
    } catch (Exception $e) {
        $response['details'][] = "Error creating users table: " . $e->getMessage();
    }
    
    // Sellers table
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS sellers (
                id SERIAL PRIMARY KEY,
                user_id INT NOT NULL,
                shop_name VARCHAR(100) NOT NULL,
                shop_description TEXT,
                address TEXT,
                rating NUMERIC(3,2) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
        $response['details'][] = "Created sellers table.";
    } catch (Exception $e) {
        $response['details'][] = "Error creating sellers table: " . $e->getMessage();
    }
    
    // Buyers table
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS buyers (
                id SERIAL PRIMARY KEY,
                user_id INT NOT NULL,
                shipping_address TEXT,
                preferences TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
        $response['details'][] = "Created buyers table.";
    } catch (Exception $e) {
        $response['details'][] = "Error creating buyers table: " . $e->getMessage();
    }
    
    // Products table
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS products (
                id SERIAL PRIMARY KEY,
                seller_id INT NOT NULL,
                name VARCHAR(100) NOT NULL,
                description TEXT NOT NULL,
                price NUMERIC(10,2) NOT NULL,
                category VARCHAR(50) NOT NULL,
                subcategory VARCHAR(50),
                size VARCHAR(20) NOT NULL,
                condition_status VARCHAR(50) NOT NULL,
                rental_period VARCHAR(50) NOT NULL,
                availability BOOLEAN DEFAULT true,
                is_featured BOOLEAN DEFAULT false,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE
            )
        ");
        $response['details'][] = "Created products table.";
    } catch (Exception $e) {
        $response['details'][] = "Error creating products table: " . $e->getMessage();
    }
    
    // Product Images table
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS product_images (
                id SERIAL PRIMARY KEY,
                product_id INT NOT NULL,
                image_url VARCHAR(255) NOT NULL,
                is_primary BOOLEAN DEFAULT false,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )
        ");
        $response['details'][] = "Created product_images table.";
    } catch (Exception $e) {
        $response['details'][] = "Error creating product_images table: " . $e->getMessage();
    }
    
    // Wishlist table
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS wishlist (
                id SERIAL PRIMARY KEY,
                buyer_id INT NOT NULL,
                product_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (buyer_id) REFERENCES buyers(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                UNIQUE (buyer_id, product_id)
            )
        ");
        $response['details'][] = "Created wishlist table.";
    } catch (Exception $e) {
        $response['details'][] = "Error creating wishlist table: " . $e->getMessage();
    }
    
    // Reviews table
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS reviews (
                id SERIAL PRIMARY KEY,
                product_id INT NOT NULL,
                buyer_id INT NOT NULL,
                rating INT NOT NULL,
                comment TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                FOREIGN KEY (buyer_id) REFERENCES buyers(id) ON DELETE CASCADE
            )
        ");
        $response['details'][] = "Created reviews table.";
    } catch (Exception $e) {
        $response['details'][] = "Error creating reviews table: " . $e->getMessage();
    }
    
    // Create test seller user
    try {
        // Check if user exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = 'seller@example.com'");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            // Create test seller
            $password = password_hash('password123', PASSWORD_DEFAULT);
            
            $db->exec("
                INSERT INTO users (name, email, password, role, status)
                VALUES ('Test Seller', 'seller@example.com', '$password', 'seller', 'active')
                RETURNING id
            ");
            
            // Get the last inserted ID
            $stmt = $db->query("SELECT lastval() as id");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $userId = $result['id'];
            
            $db->exec("
                INSERT INTO sellers (user_id, shop_name, shop_description)
                VALUES ($userId, 'Test Shop', 'This is a test shop selling high-quality clothes')
            ");
            
            $response['details'][] = "Created test seller user.";
        } else {
            $response['details'][] = "Test seller user already exists.";
        }
    } catch (Exception $e) {
        $response['details'][] = "Error creating test seller: " . $e->getMessage();
    }
    
    // Create test buyer user
    try {
        // Check if user exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = 'buyer@example.com'");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            // Create test buyer
            $password = password_hash('password123', PASSWORD_DEFAULT);
            
            $db->exec("
                INSERT INTO users (name, email, password, role, status)
                VALUES ('Test Buyer', 'buyer@example.com', '$password', 'buyer', 'active')
                RETURNING id
            ");
            
            // Get the last inserted ID
            $stmt = $db->query("SELECT lastval() as id");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $userId = $result['id'];
            
            $db->exec("
                INSERT INTO buyers (user_id, shipping_address)
                VALUES ($userId, '123 Test Street, Test City, 12345')
            ");
            
            $response['details'][] = "Created test buyer user.";
        } else {
            $response['details'][] = "Test buyer user already exists.";
        }
    } catch (Exception $e) {
        $response['details'][] = "Error creating test buyer: " . $e->getMessage();
    }
    
    $response['status'] = 'success';
    $response['message'] = 'Database setup completed successfully.';
    
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'Database setup failed: ' . $e->getMessage();
}

// Return the response
echo json_encode($response, JSON_PRETTY_PRINT); 