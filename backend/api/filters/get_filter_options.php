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
    
    // Price range query
    $priceQuery = "
        SELECT 
            MIN(price) as min_price,
            MAX(price) as max_price
        FROM 
            products
        WHERE 
            status = 'active'
    ";
    
    // Categories query
    $categoriesQuery = "
        SELECT 
            category,
            COUNT(*) as product_count
        FROM 
            products
        WHERE 
            status = 'active'
    ";
    
    // Brands query
    $brandsQuery = "
        SELECT 
            brand,
            COUNT(*) as product_count
        FROM 
            products
        WHERE 
            status = 'active'
    ";
    
    // Add filters if provided
    $filterCondition = "";
    if ($category) {
        $category = $conn->real_escape_string($category);
        $filterCondition = " AND category = '$category'";
        
        // Add to price and brands queries
        $priceQuery .= $filterCondition;
        $brandsQuery .= $filterCondition;
    }
    
    if ($brand) {
        $brand = $conn->real_escape_string($brand);
        $brandFilter = " AND brand = '$brand'";
        
        // Add to price and categories queries
        $priceQuery .= $brandFilter;
        $categoriesQuery .= $brandFilter;
    }
    
    // Complete the queries
    $categoriesQuery .= "
        GROUP BY 
            category
        ORDER BY 
            product_count DESC,
            category ASC
    ";
    
    $brandsQuery .= "
        GROUP BY 
            brand
        ORDER BY 
            product_count DESC,
            brand ASC
    ";
    
    // Execute price range query
    $priceResult = $conn->query($priceQuery);
    if (!$priceResult) {
        throw new Exception("Price range query error: " . $conn->error);
    }
    $priceRange = $priceResult->fetch_assoc();
    
    // Execute categories query
    $categoriesResult = $conn->query($categoriesQuery);
    if (!$categoriesResult) {
        throw new Exception("Categories query error: " . $conn->error);
    }
    
    $categories = [];
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = [
            'category' => $row['category'],
            'count' => (int)$row['product_count']
        ];
    }
    
    // Execute brands query
    $brandsResult = $conn->query($brandsQuery);
    if (!$brandsResult) {
        throw new Exception("Brands query error: " . $conn->error);
    }
    
    $brands = [];
    while ($row = $brandsResult->fetch_assoc()) {
        $brands[] = [
            'brand' => $row['brand'],
            'count' => (int)$row['product_count']
        ];
    }
    
    // Get total product count with applied filters
    $totalQuery = "SELECT COUNT(*) as total FROM products WHERE status = 'active'";
    if ($category) {
        $totalQuery .= " AND category = '$category'";
    }
    if ($brand) {
        $totalQuery .= " AND brand = '$brand'";
    }
    
    $totalResult = $conn->query($totalQuery);
    if (!$totalResult) {
        throw new Exception("Total count query error: " . $conn->error);
    }
    $totalCount = (int)$totalResult->fetch_assoc()['total'];
    
    // Return the combined data
    echo json_encode([
        'status' => 'success',
        'filters' => [
            'price_range' => [
                'min' => floatval($priceRange['min_price']),
                'max' => floatval($priceRange['max_price'])
            ],
            'categories' => $categories,
            'brands' => $brands
        ],
        'applied_filters' => [
            'category' => $category,
            'brand' => $brand
        ],
        'total_products' => $totalCount
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error retrieving filter options: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?> 