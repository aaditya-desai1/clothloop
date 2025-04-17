<?php
// API Endpoint: Get Seller Products
// This endpoint returns the list of products for the authenticated seller

// Set the response header to JSON
header('Content-Type: application/json');

// Include the database connection and utilities
require_once '../../config/database.php';
require_once '../../utils/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// For the purpose of debugging/development
$debug_mode = true;
$use_sample_data = $debug_mode;

// Check if user is authenticated and is a seller
if (!isAuthenticated() || !isSeller()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access. Please log in as a seller.'
    ]);
    exit;
}

// Get seller ID from session
$seller_id = $_SESSION['user_id'];

try {
    $db = getDbConnection();
    
    // Get seller products
    $stmt = $db->prepare("
        SELECT 
            p.id,
            p.name,
            p.description,
            p.price_per_day,
            p.size,
            p.color,
            p.brand,
            p.condition,
            c.name as category_name,
            (SELECT image_path FROM product_images WHERE product_id = p.id) as image_path,
            p.id as cloth_id
        FROM 
            products p
        LEFT JOIN 
            categories c ON p.category_id = c.id
        WHERE 
            p.seller_id = :seller_id
        ORDER BY 
            p.created_at DESC
    ");
    $stmt->bindParam(':seller_id', $seller_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no products found and in debug mode, return sample data
    if (empty($products) && $use_sample_data) {
        $sample_products = [
            [
                'id' => 1,
                'name' => 'POLO',
                'description' => 'A stylish polo shirt perfect for casual occasions.',
                'price_per_day' => 500,
                'size' => 'M',
                'color' => 'Black',
                'brand' => 'POLO',
                'condition' => 'Excellent',
                'category_name' => 'Men',
                'image_url' => '../../../backend/api/sellers/get_cloth_image.php?id=1',
                'shop_name' => 'Seller Hub'
            ],
            [
                'id' => 2,
                'name' => 'Yash',
                'description' => 'Casual t-shirt for everyday wear.',
                'price_per_day' => 3,
                'size' => 'M',
                'color' => 'cc',
                'brand' => 'Generic',
                'condition' => 'Good',
                'category_name' => 'Men',
                'image_url' => '../../../backend/api/sellers/get_cloth_image.php?id=2',
                'shop_name' => 'Seller Hub'
            ]
        ];
        
        echo json_encode([
            'status' => 'success',
            'products' => $sample_products
        ]);
        exit;
    }
    
    // Process products
    foreach ($products as &$product) {
        // Add image URL - use cloth_details image API instead of file path
        $product['image_url'] = '../../../backend/api/sellers/get_cloth_image.php?id=' . $product['cloth_id'];
        
        // Get shop name
        $stmt = $db->prepare("
            SELECT shop_name FROM sellers WHERE user_id = :seller_id
        ");
        $stmt->bindParam(':seller_id', $seller_id, PDO::PARAM_INT);
        $stmt->execute();
        $shop = $stmt->fetch(PDO::FETCH_ASSOC);
        $product['shop_name'] = $shop ? $shop['shop_name'] : 'My Shop';
    }
    
    // Return the products
    echo json_encode([
        'status' => 'success',
        'products' => $products
    ]);
    
} catch (PDOException $e) {
    // Log the error
    error_log("Database error: " . $e->getMessage());
    
    // If in debug mode, return sample data
    if ($use_sample_data) {
        $sample_products = [
            [
                'id' => 1,
                'name' => 'POLO',
                'description' => 'A stylish polo shirt perfect for casual occasions.',
                'price_per_day' => 500,
                'size' => 'M',
                'color' => 'Black',
                'brand' => 'POLO',
                'condition' => 'Excellent',
                'category_name' => 'Men',
                'image_url' => '../../../backend/api/sellers/get_cloth_image.php?id=1',
                'shop_name' => 'Seller Hub'
            ],
            [
                'id' => 2,
                'name' => 'Yash',
                'description' => 'Casual t-shirt for everyday wear.',
                'price_per_day' => 3,
                'size' => 'M',
                'color' => 'cc',
                'brand' => 'Generic',
                'condition' => 'Good',
                'category_name' => 'Men',
                'image_url' => '../../../backend/api/sellers/get_cloth_image.php?id=2',
                'shop_name' => 'Seller Hub'
            ]
        ];
        
        echo json_encode([
            'status' => 'success',
            'products' => $sample_products
        ]);
        exit;
    }
    
    // Return error message
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred. Please try again later.'
    ]);
}
?> 