<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection from config
require_once 'config/db_connect.php';

echo "<h1>ClothLoop Sample Data Setup</h1>";

// Function to check if a table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Function to create cloth_details table if it doesn't exist
function createClothDetailsTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS cloth_details (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        seller_id INT(11) NOT NULL DEFAULT 1,
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
        echo "<p>Cloth details table created or already exists</p>";
        return true;
    } else {
        echo "<p>Error creating cloth_details table: " . $conn->error . "</p>";
        return false;
    }
}

// Function to create cloth_images table if it doesn't exist
function createClothImagesTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS cloth_images (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        cloth_id INT(11) NOT NULL,
        image_data MEDIUMBLOB,
        image_type VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p>Cloth images table created or already exists</p>";
        return true;
    } else {
        echo "<p>Error creating cloth_images table: " . $conn->error . "</p>";
        return false;
    }
}

// Function to check if sample data already exists
function sampleDataExists($conn) {
    $result = $conn->query("SELECT COUNT(*) as count FROM cloth_details");
    if ($result && $result->num_rows > 0) {
        $count = (int) $result->fetch_assoc()['count'];
        return $count > 0;
    }
    return false;
}

// Function to add sample data
function addSampleData($conn) {
    // Skip if sample data already exists
    if (sampleDataExists($conn)) {
        echo "<p>Sample data already exists (found existing records in cloth_details)</p>";
        return true;
    }
    
    echo "<p>Adding sample products...</p>";
    
    // Sample products
    $sampleProducts = [
        [
            'title' => 'Elegant Wedding Dress',
            'description' => 'Beautiful white wedding dress with lace details and a long train. Perfect for your special day.',
            'size' => 'M',
            'category' => 'Women',
            'price' => 1499.99,
            'contact' => '9876543210',
            'whatsapp' => '9876543210',
            'terms' => 'Security deposit required. Dry clean only. Return within 7 days.'
        ],
        [
            'title' => 'Men\'s Formal Black Suit',
            'description' => 'Classic black suit for formal occasions. Includes jacket and pants.',
            'size' => 'L',
            'category' => 'Men',
            'price' => 799.99,
            'contact' => '9876543211',
            'whatsapp' => '9876543211',
            'terms' => 'Dry clean only. Return in original condition.'
        ],
        [
            'title' => 'Kids Party Dress',
            'description' => 'Colorful party dress for young girls. Comfortable and stylish.',
            'size' => '8',
            'category' => 'Kids',
            'price' => 299.99,
            'contact' => '9876543212',
            'whatsapp' => '9876543212',
            'terms' => 'Gentle wash. No stains.'
        ],
        [
            'title' => 'Traditional Saree',
            'description' => 'Elegant silk saree with golden border. Perfect for traditional occasions.',
            'size' => 'Free',
            'category' => 'Women',
            'price' => 999.99,
            'contact' => '9876543213',
            'whatsapp' => '9876543213',
            'terms' => 'Dry clean only. Security deposit required.'
        ],
        [
            'title' => 'Men\'s Sherwani',
            'description' => 'Royal looking sherwani for weddings and special occasions.',
            'size' => 'XL',
            'category' => 'Men',
            'price' => 1299.99,
            'contact' => '9876543214',
            'whatsapp' => '9876543214',
            'terms' => 'Handle with care. Return within 5 days.'
        ]
    ];
    
    // Insert sample products
    $success = true;
    foreach ($sampleProducts as $product) {
        $sql = "INSERT INTO cloth_details (
            seller_id,
            cloth_title,
            description,
            size,
            category,
            rental_price,
            contact_number,
            whatsapp_number,
            terms_and_conditions
        ) VALUES (
            1,
            '{$product['title']}',
            '{$product['description']}',
            '{$product['size']}',
            '{$product['category']}',
            {$product['price']},
            '{$product['contact']}',
            '{$product['whatsapp']}',
            '{$product['terms']}'
        )";
        
        if (!$conn->query($sql)) {
            echo "<p>Error adding sample product '{$product['title']}': " . $conn->error . "</p>";
            $success = false;
        }
    }
    
    if ($success) {
        echo "<p>Sample products added successfully!</p>";
        return true;
    } else {
        echo "<p>There were errors adding some sample products.</p>";
        return false;
    }
}

// Main setup process
echo "<h2>Setting up tables</h2>";
createClothDetailsTable($conn);
createClothImagesTable($conn);

echo "<h2>Adding sample data</h2>";
$result = addSampleData($conn);

echo "<h2>Summary</h2>";
if ($result) {
    echo "<p>Setup completed successfully! You can now view products in the ClothLoop application.</p>";
} else {
    echo "<p>Setup completed with some errors. Please check the messages above.</p>";
}

echo "<p><a href='utils/check_database.php'>Check Database Status</a></p>";
echo "<p><a href='../frontend/pages/buyer/buyer_dashboard.html'>Go to Buyer Dashboard</a></p>";

// Close connection
$conn->close();
?> 