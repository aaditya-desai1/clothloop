<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once '../../config/db_connect.php';

header('Content-Type: application/json');

try {
    // Get optional category filter
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    
    // Build the query
    $query = "
        SELECT 
            brand,
            COUNT(*) as product_count
        FROM 
            products
        WHERE 
            status = 'active'
    ";
    
    // Add category filter if provided
    if ($category) {
        $category = $conn->real_escape_string($category);
        $query .= " AND category = '$category'";
    }
    
    // Group and order results
    $query .= "
        GROUP BY 
            brand
        ORDER BY 
            product_count DESC,
            brand ASC
    ";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Database query error: " . $conn->error);
    }
    
    $brands = [];
    while ($row = $result->fetch_assoc()) {
        $brands[] = [
            'brand' => $row['brand'],
            'count' => (int)$row['product_count']
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'category_filter' => $category,
        'brands' => $brands
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error retrieving brands: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?> 