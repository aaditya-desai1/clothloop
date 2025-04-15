<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once '../../config/db_connect.php';

header('Content-Type: application/json');

try {
    // Get optional brand filter
    $brand = isset($_GET['brand']) ? $_GET['brand'] : null;
    
    // Build the query
    $query = "
        SELECT 
            category,
            COUNT(*) as product_count
        FROM 
            products
        WHERE 
            status = 'active'
    ";
    
    // Add brand filter if provided
    if ($brand) {
        $brand = $conn->real_escape_string($brand);
        $query .= " AND brand = '$brand'";
    }
    
    // Group and order results
    $query .= "
        GROUP BY 
            category
        ORDER BY 
            product_count DESC,
            category ASC
    ";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Database query error: " . $conn->error);
    }
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'category' => $row['category'],
            'count' => (int)$row['product_count']
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'brand_filter' => $brand,
        'categories' => $categories
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error retrieving categories: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?> 