<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Include database connection
require_once '../../config/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not logged in'
    ]);
    exit;
}

// Get user ID from session
$userId = $_SESSION['user_id'];

try {
    // First, check if user is a seller
    $checkSellerQuery = "SELECT * FROM sellers WHERE user_id = ?";
    $sellerStmt = $conn->prepare($checkSellerQuery);
    $sellerStmt->bind_param("i", $userId);
    $sellerStmt->execute();
    $sellerResult = $sellerStmt->get_result();
    
    if ($sellerResult->num_rows === 0) {
        // User is not a seller
        echo json_encode([
            'status' => 'error',
            'message' => 'User is not registered as a seller'
        ]);
        exit;
    }
    
    // Get seller's products
    $query = "SELECT p.*, c.category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id
              WHERE p.seller_id = ? 
              ORDER BY p.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Get product images
        $imageQuery = "SELECT image_url FROM product_images WHERE product_id = ?";
        $imageStmt = $conn->prepare($imageQuery);
        $imageStmt->bind_param("i", $row['id']);
        $imageStmt->execute();
        $imageResult = $imageStmt->get_result();
        
        $images = [];
        while ($imageRow = $imageResult->fetch_assoc()) {
            $images[] = $imageRow['image_url'];
        }
        
        $row['images'] = $images;
        $products[] = $row;
    }
    
    // Return products
    echo json_encode([
        'status' => 'success',
        'products' => $products
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?> 