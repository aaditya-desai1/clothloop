<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once '../../config/db_connect.php';

header('Content-Type: application/json');

try {
    // Get filter parameters if provided
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $brand = isset($_GET['brand']) ? $_GET['brand'] : null;
    
    // Base query
    $query = "
        SELECT 
            MIN(price) as min_price,
            MAX(price) as max_price
        FROM 
            products
        WHERE 
            status = 'active'
    ";
    
    // Add filters if provided
    if ($category) {
        $category = $conn->real_escape_string($category);
        $query .= " AND category = '$category'";
    }
    
    if ($brand) {
        $brand = $conn->real_escape_string($brand);
        $query .= " AND brand = '$brand'";
    }

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Database query error: " . $conn->error);
    }

    $priceRange = $result->fetch_assoc();
    
    echo json_encode([
        'status' => 'success',
        'price_range' => [
            'min' => floatval($priceRange['min_price']),
            'max' => floatval($priceRange['max_price'])
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error retrieving price range: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?> 