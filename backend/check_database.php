<?php
// Database check script for ClothLoop

// Include database connection
require_once 'config/db_connect.php';

// Set header for JSON response
header('Content-Type: application/json');

// Check if db_connect.php exists and the connection works
if (!isset($conn)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection not established. Make sure db_connect.php exists and has the right credentials.'
    ]);
    exit;
}

// Check if cloth_details table exists
$tables = [];
$result = $conn->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
}

$response = [
    'status' => 'success',
    'message' => 'Database check completed',
    'database' => [
        'connection' => true,
        'tables' => $tables
    ]
];

// Check cloth_details table structure
if (in_array('cloth_details', $tables)) {
    $response['database']['cloth_details'] = [
        'exists' => true
    ];
    
    // Check columns
    $columns = [];
    $result = $conn->query("SHOW COLUMNS FROM cloth_details");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
    }
    
    $response['database']['cloth_details']['columns'] = $columns;
    
    // Check if cloth_photo and photo_type fields exist
    $has_photo_fields = in_array('cloth_photo', $columns) && in_array('photo_type', $columns);
    $response['database']['cloth_details']['has_photo_fields'] = $has_photo_fields;
    
    // Count records
    $result = $conn->query("SELECT COUNT(*) as count FROM cloth_details");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        $response['database']['cloth_details']['record_count'] = $count;
    }
    
    // Get sample data
    $result = $conn->query("SELECT id, cloth_title, category, rental_price FROM cloth_details LIMIT 5");
    if ($result) {
        $sample_data = [];
        while ($row = $result->fetch_assoc()) {
            $sample_data[] = $row;
        }
        $response['database']['cloth_details']['sample_data'] = $sample_data;
    }
} else {
    $response['database']['cloth_details'] = [
        'exists' => false
    ];
}

// Check if cloth_images table exists (it shouldn't if migration was successful)
if (in_array('cloth_images', $tables)) {
    $response['database']['cloth_images'] = [
        'exists' => true
    ];
    
    // Count records
    $result = $conn->query("SELECT COUNT(*) as count FROM cloth_images");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        $response['database']['cloth_images']['record_count'] = $count;
    }
} else {
    $response['database']['cloth_images'] = [
        'exists' => false
    ];
}

// Add SQL for creating sample products if none exist
if (isset($response['database']['cloth_details']['record_count']) && $response['database']['cloth_details']['record_count'] == 0) {
    $response['help'] = [
        'message' => 'No products found in cloth_details table. Use the SQL below to create sample products:',
        'sql' => "
INSERT INTO cloth_details (seller_id, cloth_title, description, size, category, rental_price, contact_number, whatsapp_number, terms_and_conditions, status)
VALUES 
(1, 'Elegant Wedding Dress', 'Beautiful white wedding dress with lace details.', 'M', 'Women', 1499.99, '1234567890', '1234567890', 'Return within 3 days', 'active'),
(1, 'Men\'s Formal Suit', 'Classic black suit for formal occasions.', 'L', 'Men', 799.99, '1234567890', '1234567890', 'Dry clean only', 'active'),
(1, 'Kids Party Dress', 'Colorful party dress for young girls.', 'S', 'Kids', 299.99, '1234567890', '1234567890', 'For ages 4-8 years', 'active');
        "
    ];
}

// Output the response
echo json_encode($response, JSON_PRETTY_PRINT); 