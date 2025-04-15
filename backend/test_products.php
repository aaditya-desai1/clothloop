<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'config/db_connect.php';

echo "====== ClothLoop Database Test =======\n\n";

// Test database connection
echo "Database Connection: ";
if ($conn->connect_error) {
    echo "FAILED - " . $conn->connect_error . "\n";
    exit();
} else {
    echo "SUCCESS\n\n";
}

// Check if cloth_details table exists
echo "Checking for cloth_details table: ";
$result = $conn->query("SHOW TABLES LIKE 'cloth_details'");
if ($result->num_rows > 0) {
    echo "FOUND\n\n";
    
    // Check table structure
    echo "cloth_details table structure:\n";
    $result = $conn->query("DESCRIBE cloth_details");
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")" . 
                 ($row['Null'] == 'NO' ? ' NOT NULL' : '') . 
                 ($row['Key'] == 'PRI' ? ' PRIMARY KEY' : '') . "\n";
        }
    } else {
        echo "Error getting table structure\n";
    }
    
    // Count rows
    $result = $conn->query("SELECT COUNT(*) as count FROM cloth_details");
    $count = $result->fetch_assoc()['count'];
    echo "\nTotal products in cloth_details: " . $count . "\n";
    
    // Get sample data
    if ($count > 0) {
        echo "\nSample product data:\n";
        $result = $conn->query("SELECT * FROM cloth_details LIMIT 1");
        $row = $result->fetch_assoc();
        foreach ($row as $key => $value) {
            echo "- " . $key . ": " . (strlen($value) > 30 ? substr($value, 0, 30) . "..." : $value) . "\n";
        }
    }
} else {
    echo "NOT FOUND - Please create the cloth_details table\n";
    
    // Check if products table exists as alternative
    echo "\nChecking for products table: ";
    $result = $conn->query("SHOW TABLES LIKE 'products'");
    if ($result->num_rows > 0) {
        echo "FOUND\n\n";
        
        // Check table structure
        echo "products table structure:\n";
        $result = $conn->query("DESCRIBE products");
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "- " . $row['Field'] . " (" . $row['Type'] . ")" . 
                     ($row['Null'] == 'NO' ? ' NOT NULL' : '') . 
                     ($row['Key'] == 'PRI' ? ' PRIMARY KEY' : '') . "\n";
            }
        }
        
        // Count rows
        $result = $conn->query("SELECT COUNT(*) as count FROM products");
        $count = $result->fetch_assoc()['count'];
        echo "\nTotal items in products: " . $count . "\n";
    } else {
        echo "NOT FOUND\n";
    }
}

// Check for cloth_images table
echo "\nChecking for cloth_images table: ";
$result = $conn->query("SHOW TABLES LIKE 'cloth_images'");
if ($result->num_rows > 0) {
    echo "FOUND\n";
} else {
    echo "NOT FOUND\n";
}

// Check for users table
echo "Checking for users table: ";
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows > 0) {
    echo "FOUND\n";
} else {
    echo "NOT FOUND\n";
}

// List all tables
echo "\nAll tables in database:\n";
$result = $conn->query("SHOW TABLES");
while($row = $result->fetch_row()) {
    echo "- " . $row[0] . "\n";
}

// Close connection
$conn->close();
echo "\n====== Test Complete =======\n";
?> 