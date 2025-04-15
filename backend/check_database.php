<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to JSON
header('Content-Type: application/json');

// Database connection parameters
$db_host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "clothloop";

// Information to gather
$info = [
    'php_version' => PHP_VERSION,
    'server' => $_SERVER['SERVER_SOFTWARE'],
    'database' => [
        'connection' => false,
        'tables' => [],
        'cloth_details_exists' => false,
        'cloth_details_columns' => [],
        'users_exists' => false,
        'users_columns' => [],
        'cloth_images_exists' => false,
        'cloth_images_columns' => [],
        'product_count' => 0
    ],
    'errors' => []
];

try {
    // Create connection
    $conn = new mysqli($db_host, $db_user, $db_password, $db_name);

    // Check connection
    if ($conn->connect_error) {
        $info['errors'][] = "Connection failed: " . $conn->connect_error;
    } else {
        $info['database']['connection'] = true;
        
        // Get list of tables
        $tables_result = $conn->query("SHOW TABLES");
        if ($tables_result) {
            while ($table = $tables_result->fetch_row()) {
                $info['database']['tables'][] = $table[0];
            }
        }
        
        // Check if cloth_details exists
        if (in_array('cloth_details', $info['database']['tables'])) {
            $info['database']['cloth_details_exists'] = true;
            
            // Get columns of cloth_details
            $columns_result = $conn->query("SHOW COLUMNS FROM cloth_details");
            if ($columns_result) {
                while ($column = $columns_result->fetch_assoc()) {
                    $info['database']['cloth_details_columns'][] = $column['Field'];
                }
            }
            
            // Count products
            $count_result = $conn->query("SELECT COUNT(*) as count FROM cloth_details");
            if ($count_result && $row = $count_result->fetch_assoc()) {
                $info['database']['product_count'] = (int)$row['count'];
            }
            
            // If the table exists but has no products, show sample data
            if ($info['database']['product_count'] === 0) {
                // Let's create a sample product for testing
                $sample_query = "INSERT INTO cloth_details 
                    (seller_id, cloth_title, description, size, category, rental_price, contact_number, whatsapp_number, terms_and_conditions, created_at) 
                    VALUES 
                    (1, 'Sample Formal Suit', 'A stylish formal suit for special occasions', 'M', 'Formal', 299.99, '9876543210', '9876543210', 'Return in good condition', NOW())";
                
                if ($conn->query($sample_query)) {
                    $info['database']['product_count'] = 1;
                    $info['message'] = "Created a sample product for testing";
                }
            }
        }
        
        // Check if users exists
        if (in_array('users', $info['database']['tables'])) {
            $info['database']['users_exists'] = true;
            
            // Get columns of users
            $columns_result = $conn->query("SHOW COLUMNS FROM users");
            if ($columns_result) {
                while ($column = $columns_result->fetch_assoc()) {
                    $info['database']['users_columns'][] = $column['Field'];
                }
            }
        }
        
        // Check if cloth_images exists
        if (in_array('cloth_images', $info['database']['tables'])) {
            $info['database']['cloth_images_exists'] = true;
            
            // Get columns of cloth_images
            $columns_result = $conn->query("SHOW COLUMNS FROM cloth_images");
            if ($columns_result) {
                while ($column = $columns_result->fetch_assoc()) {
                    $info['database']['cloth_images_columns'][] = $column['Field'];
                }
            }
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    $info['errors'][] = "Exception: " . $e->getMessage();
}

// Output the gathered information
echo json_encode($info, JSON_PRETTY_PRINT);
?> 