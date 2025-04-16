<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'config/db_connect.php';

echo "====== ClothLoop Database Setup =======\n\n";

// Function to check if a table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Function to create users table
function createUsersTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS `sellers` (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone_no VARCHAR(20),
        shop_name VARCHAR(255),
        shop_address TEXT,
        shop_location VARCHAR(255),
        shop_bio TEXT,
        shop_logo VARCHAR(255),
        verification_token VARCHAR(255),
        is_verified BOOLEAN DEFAULT 0,
        reset_token VARCHAR(255),
        reset_token_expires DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Users table created or already exists\n";
        return true;
    } else {
        echo "Error creating users table: " . $conn->error . "\n";
        return false;
    }
}

// Function to create cloth_details table
function createClothDetailsTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS cloth_details (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        seller_id INT(11) NOT NULL,
        cloth_title VARCHAR(255) NOT NULL,
        description TEXT,
        size VARCHAR(50),
        category VARCHAR(100),
        rental_price DECIMAL(10,2) NOT NULL,
        contact_number VARCHAR(20),
        whatsapp_number VARCHAR(20),
        terms_and_conditions TEXT,
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Cloth details table created or already exists\n";
        return true;
    } else {
        echo "Error creating cloth_details table: " . $conn->error . "\n";
        return false;
    }
}

// Function to create cloth_images table
function createClothImagesTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS cloth_images (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        cloth_id INT(11) NOT NULL,
        image_data MEDIUMBLOB,
        image_type VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Cloth images table created or already exists\n";
        return true;
    } else {
        echo "Error creating cloth_images table: " . $conn->error . "\n";
        return false;
    }
}

// Function to add sample data
function addSampleData($conn) {
    // Check if we have at least one user
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $userCount = $result->fetch_assoc()['count'];
    
    if ($userCount == 0) {
        echo "Adding sample users...\n";
        
        // Add sample users
        $password = password_hash('password123', PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, password, email, first_name, last_name, user_type, phone) VALUES 
                ('seller1', '$password', 'seller1@example.com', 'John', 'Doe', 'seller', '9876543210'),
                ('seller2', '$password', 'seller2@example.com', 'Jane', 'Smith', 'seller', '8765432109'),
                ('buyer1', '$password', 'buyer1@example.com', 'Alice', 'Johnson', 'buyer', '7654321098')";
        
        if ($conn->query($sql) === TRUE) {
            echo "Sample users added\n";
        } else {
            echo "Error adding sample users: " . $conn->error . "\n";
        }
    }
    
    // Check if we have any cloth details
    $result = $conn->query("SELECT COUNT(*) as count FROM cloth_details");
    $clothCount = $result->fetch_assoc()['count'];
    
    if ($clothCount == 0) {
        echo "Adding sample cloth details...\n";
        
        // Get seller IDs
        $result = $conn->query("SELECT id FROM users WHERE user_type = 'seller' LIMIT 2");
        $sellers = [];
        while ($row = $result->fetch_assoc()) {
            $sellers[] = $row['id'];
        }
        
        if (count($sellers) < 2) {
            echo "Not enough sellers found, skipping sample cloth details\n";
            return;
        }
        
        // Add sample cloth details
        $sql = "INSERT INTO cloth_details (seller_id, cloth_title, description, size, category, rental_price, contact_number, whatsapp_number, terms_and_conditions) VALUES 
                ({$sellers[0]}, 'Formal Black Suit', 'Elegant black suit for formal occasions and events', 'L', 'Formal', 499.99, '9876543210', '9876543210', 'Return in good condition'),
                ({$sellers[0]}, 'Wedding Lehenga', 'Beautiful wedding lehenga with intricate embroidery', 'M', 'Wedding', 1299.99, '9876543210', '9876543210', 'Dry clean only, security deposit required'),
                ({$sellers[1]}, 'Casual Jeans', 'Comfortable jeans for everyday wear', '32', 'Casual', 149.99, '8765432109', '8765432109', 'Wash before return'),
                ({$sellers[1]}, 'Summer Dress', 'Light and airy dress perfect for summer', 'S', 'Casual', 199.99, '8765432109', '8765432109', 'Handle with care')";
        
        if ($conn->query($sql) === TRUE) {
            echo "Sample cloth details added\n";
        } else {
            echo "Error adding sample cloth details: " . $conn->error . "\n";
        }
    }
}

// Create the database tables if they don't exist
createUsersTable($conn);
createClothDetailsTable($conn);
createClothImagesTable($conn);

// Add sample data
addSampleData($conn);

// List all tables 
echo "\nCurrent tables in database:\n";
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    echo "- " . $row[0] . "\n";
}

// Show product count
$result = $conn->query("SELECT COUNT(*) as count FROM cloth_details");
$count = $result->fetch_assoc()['count'];
echo "\nTotal products: $count\n";

// Close the connection
$conn->close();

echo "\n====== Setup Complete =======\n";
echo "You can now access the products endpoint at: http://localhost/ClothLoop/backend/utils/product_operations.php?operation=fetch_all\n";
echo "Or access the buyer dashboard at: http://localhost/ClothLoop/frontend/pages/buyer/buyer_dashboard.html\n";
?>