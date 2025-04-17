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

// Enable debug mode for development
$debug_mode = true;

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
    
    // Get product details from cloth_details and sellers tables
    $stmt = $db->prepare("
        SELECT 
            cd.id,
            cd.cloth_title as name,
            cd.description,
            cd.size,
            cd.category,
            cd.occasion,
            cd.rental_price as price_per_day,
            cd.terms_and_conditions,
            s.shop_name,
            s.shop_address,
            s.phone_no as contact_number
        FROM 
            cloth_details cd
        JOIN 
            sellers s ON cd.seller_id = s.id
        WHERE 
            cd.id = :product_id AND cd.seller_id = :seller_id
    ");
    
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->bindParam(':seller_id', $seller_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        // Return sample data in debug mode
        if ($debug_mode) {
            $sample_product = [
                'id' => $product_id,
                'name' => 'Sample Product',
                'description' => 'This is a sample product for testing purposes.',
                'price_per_day' => 199.99,
                'size' => 'M',
                'category' => 'Men',
                'occasion' => 'Casual',
                'shop_name' => 'Seller Hub',
                'shop_address' => 'Sasait',
                'contact_number' => '1234567890',
                'terms_and_conditions' => 'See'
            ];
            
            echo json_encode([
                'status' => 'success',
                'product' => $sample_product,
                'debug_mode' => true
            ]);
            exit;
        }
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Product not found or you do not have permission to view it'
        ]);
        exit;
    }
    
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // No need to fetch image paths separately as we'll use the cloth_photo directly via the get_cloth_image.php endpoint
    
    // Return the product details
    echo json_encode([
        'status' => 'success',
        'product' => $product
    ]);
    
} catch (PDOException $e) {
    // Log the error
    error_log("Database error: " . $e->getMessage());
    
    // Return sample data in debug mode
    if ($debug_mode) {
        $sample_product = [
            'id' => $product_id,
            'name' => 'Sample Product',
            'description' => 'This is a sample product for testing purposes.',
            'price_per_day' => 199.99,
            'size' => 'M',
            'category' => 'Men',
            'occasion' => 'Casual',
            'shop_name' => 'Seller Hub',
            'shop_address' => 'Sasait',
            'contact_number' => '1234567890',
            'terms_and_conditions' => 'See'
        ];
        
        echo json_encode([
            'status' => 'success',
            'product' => $sample_product,
            'debug_mode' => true,
            'error_info' => 'Database error: ' . $e->getMessage()
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