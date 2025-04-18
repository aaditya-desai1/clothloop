<?php
// Set CORS headers
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

// Track execution result
$results = [];
$errors = [];

try {
    // Required files
    require_once __DIR__ . '/config/database.php';
    $results[] = "Database class loaded successfully";
    
    // Connect to database
    $database = new Database();
    $conn = $database->getConnection();
    $results[] = "Connected to database successfully";
    
    // Create tables
    createTables($conn, $results, $errors);
    
    // Insert test data
    insertTestData($conn, $results, $errors);
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Database setup completed successfully',
        'results' => $results,
        'errors' => $errors
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database setup failed: ' . $e->getMessage(),
        'results' => $results,
        'errors' => array_merge($errors, [$e->getMessage()])
    ], JSON_PRETTY_PRINT);
}

// Function to create database tables
function createTables($conn, &$results, &$errors) {
    try {
        // Create products table
        $conn->exec("
            CREATE TABLE IF NOT EXISTS products (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                seller_id INT(11) NOT NULL,
                category_id INT(11) DEFAULT 1,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                price DECIMAL(10, 2) NOT NULL,
                condition_id INT(11) DEFAULT 1,
                stock INT(11) DEFAULT 1,
                status ENUM('active', 'inactive', 'sold') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `seller_id` (`seller_id`),
                KEY `category_id` (`category_id`)
            ) ENGINE=InnoDB
        ");
        $results[] = "Created products table";
        
        // Create customer_interests table
        $conn->exec("
            CREATE TABLE IF NOT EXISTS customer_interests (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                buyer_id INT(11) NOT NULL,
                product_id INT(11) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                KEY `buyer_id` (`buyer_id`),
                KEY `product_id` (`product_id`)
            ) ENGINE=InnoDB
        ");
        $results[] = "Created customer_interests table";
        
        // Create seller_reviews table
        $conn->exec("
            CREATE TABLE IF NOT EXISTS seller_reviews (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                seller_id INT(11) NOT NULL,
                buyer_id INT(11) NOT NULL,
                rating DECIMAL(2, 1) NOT NULL,
                comment TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `seller_id` (`seller_id`),
                KEY `buyer_id` (`buyer_id`)
            ) ENGINE=InnoDB
        ");
        $results[] = "Created seller_reviews table";
        
        // Create buyers table if it doesn't exist
        $conn->exec("
            CREATE TABLE IF NOT EXISTS buyers (
                id INT(11) PRIMARY KEY,
                address VARCHAR(255),
                preferences TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB
        ");
        $results[] = "Created buyers table";
        
        // Create categories table
        $conn->exec("
            CREATE TABLE IF NOT EXISTS categories (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL,
                description VARCHAR(255),
                parent_id INT(11) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");
        $results[] = "Created categories table";
        
    } catch (PDOException $e) {
        $errors[] = "Error creating tables: " . $e->getMessage();
        throw $e;
    }
}

// Function to insert test data
function insertTestData($conn, &$results, &$errors) {
    try {
        // Add test user if not exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = 'seller@example.com'");
        $stmt->execute();
        
        $sellerId = null;
        if ($stmt->rowCount() === 0) {
            // Create test seller
            $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
            $conn->exec("
                INSERT INTO users (name, email, password, phone, role)
                VALUES ('Test Seller', 'seller@example.com', '$hashedPassword', '1234567890', 'seller')
            ");
            $sellerId = $conn->lastInsertId();
            
            // Insert seller record
            $conn->exec("
                INSERT INTO sellers (id, shop_name, description)
                VALUES ($sellerId, 'Test Shop', 'This is a test shop for demonstration')
            ");
            $results[] = "Created test seller user with ID: $sellerId";
        } else {
            $sellerId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
            $results[] = "Test seller already exists with ID: $sellerId";
        }
        
        // Add test buyer if not exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = 'buyer@example.com'");
        $stmt->execute();
        
        $buyerId = null;
        if ($stmt->rowCount() === 0) {
            // Create test buyer
            $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
            $conn->exec("
                INSERT INTO users (name, email, password, phone, role)
                VALUES ('Test Buyer', 'buyer@example.com', '$hashedPassword', '9876543210', 'buyer')
            ");
            $buyerId = $conn->lastInsertId();
            
            // Insert buyer record
            $conn->exec("
                INSERT INTO buyers (id)
                VALUES ($buyerId)
            ");
            $results[] = "Created test buyer user with ID: $buyerId";
        } else {
            $buyerId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
            $results[] = "Test buyer already exists with ID: $buyerId";
        }
        
        // Add categories if not exists
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM categories");
        $stmt->execute();
        $categoryCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($categoryCount === 0) {
            $categories = ['Men', 'Women', 'Kids', 'Accessories', 'Footwear'];
            
            foreach ($categories as $index => $category) {
                $conn->exec("
                    INSERT INTO categories (id, name, description)
                    VALUES (" . ($index + 1) . ", '$category', 'Category for $category items')
                ");
            }
            $results[] = "Created categories: " . implode(', ', $categories);
        } else {
            $results[] = "Categories already exist";
        }
        
        // Add test products if not exists
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE seller_id = ?");
        $stmt->bindParam(1, $sellerId);
        $stmt->execute();
        $productCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($productCount === 0 && $sellerId) {
            $products = [
                ['Vintage Denim Jacket', 'Classic vintage denim jacket in excellent condition', 1299.99, 1],
                ['Leather Boots', 'Genuine leather boots, lightly worn', 1899.99, 5],
                ['Cotton T-Shirt', 'Premium cotton t-shirt, brand new with tags', 699.50, 1],
                ['Summer Dress', 'Light summer dress, perfect for beach days', 999.00, 2],
                ['Winter Coat', 'Warm winter coat with faux fur lining', 2499.00, 1]
            ];
            
            foreach ($products as $index => $product) {
                $stmt = $conn->prepare("
                    INSERT INTO products (seller_id, category_id, name, description, price, condition_id)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $sellerId, 
                    $product[3], 
                    $product[0], 
                    $product[1], 
                    $product[2], 
                    1
                ]);
                $results[] = "Added product: " . $product[0];
            }
        } else {
            $results[] = "Products already exist for seller #$sellerId";
        }
        
        // Add customer interests if not exists
        if ($buyerId && $sellerId) {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM customer_interests WHERE buyer_id = ?");
            $stmt->bindParam(1, $buyerId);
            $stmt->execute();
            $interestsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($interestsCount === 0) {
                // Get product IDs
                $stmt = $conn->prepare("SELECT id FROM products WHERE seller_id = ? LIMIT 3");
                $stmt->bindParam(1, $sellerId);
                $stmt->execute();
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($products) > 0) {
                    foreach ($products as $product) {
                        $stmt = $conn->prepare("
                            INSERT INTO customer_interests (buyer_id, product_id)
                            VALUES (?, ?)
                        ");
                        $stmt->execute([$buyerId, $product['id']]);
                    }
                    $results[] = "Added customer interests for buyer #$buyerId";
                }
            } else {
                $results[] = "Customer interests already exist for buyer #$buyerId";
            }
            
            // Add seller reviews if not exists
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM seller_reviews WHERE seller_id = ?");
            $stmt->bindParam(1, $sellerId);
            $stmt->execute();
            $reviewsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($reviewsCount === 0) {
                $stmt = $conn->prepare("
                    INSERT INTO seller_reviews (seller_id, buyer_id, rating, comment)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $sellerId, 
                    $buyerId, 
                    4.5, 
                    'Great seller, fast shipping and item as described!'
                ]);
                $results[] = "Added seller review from buyer #$buyerId";
            } else {
                $results[] = "Seller reviews already exist for seller #$sellerId";
            }
        }
        
    } catch (PDOException $e) {
        $errors[] = "Error inserting test data: " . $e->getMessage();
        throw $e;
    }
} 