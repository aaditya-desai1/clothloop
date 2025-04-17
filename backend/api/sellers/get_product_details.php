<?php
// API Endpoint: Get Product Details
// This endpoint returns details for a specific product by ID

// Set the response header to JSON
header('Content-Type: application/json');

// Include the database connection and utilities
require_once '../../config/database.php';
require_once '../../utils/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Product ID is required'
    ]);
    exit;
}

$product_id = $_GET['id'];

try {
    $db = getDbConnection();
    
    // Get product details
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = :product_id AND p.seller_id = :seller_id
    ");
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->bindParam(':seller_id', $seller_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Product not found or you do not have permission to view it'
        ]);
        exit;
    }
    
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get product images
    $stmt = $db->prepare("
        SELECT image_path FROM product_images 
        WHERE product_id = :product_id
    ");
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // If no images found, provide a default placeholder
    if (empty($images)) {
        $images = ['uploads/products/placeholder.png'];
    }
    
    // Add image URLs to the product data
    $product['images'] = array_map(function($image) {
        return '../../../backend/' . $image; // Adjust path based on your setup
    }, $images);
    
    // Get product ratings and reviews
    $stmt = $db->prepare("
        SELECT AVG(rating) as average_rating, COUNT(*) as total_ratings
        FROM product_ratings
        WHERE product_id = :product_id
    ");
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $ratings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Add ratings to product data
    $product['average_rating'] = $ratings['average_rating'] ? 
                                number_format((float)$ratings['average_rating'], 1) : 
                                '0.0';
    $product['total_ratings'] = (int)$ratings['total_ratings'];
    
    // Return the product details
    echo json_encode([
        'status' => 'success',
        'product' => $product
    ]);
    
} catch (PDOException $e) {
    // Log the error
    error_log("Database error: " . $e->getMessage());
    
    // Return error message
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred. Please try again later.'
    ]);
}
?> 