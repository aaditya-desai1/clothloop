<?php
/**
 * Direct Image API
 * Simpler API that just returns the image path as plain text
 */

// Set headers for plain text response
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/plain');

// Include database connection
require_once '../../config/Database.php';

// Get product ID from request
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';

if (empty($product_id)) {
    echo "uploads/products/default/placeholder.jpg";
    exit;
}

try {
    // Create database connection
    $database = new Database();
    $db = $database->connect();

    // First check if the products table exists
    $checkTable = $db->query("SHOW TABLES LIKE 'products'");
    if ($checkTable->rowCount() == 0) {
        echo "frontend/assets/images/Clothify.png";
        exit;
    }

    // Query to get the image path
    $query = "SELECT image_path FROM products WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$product_id]);
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $image_path = $row['image_path'];
        
        // Return the image path
        if (!empty($image_path)) {
            echo $image_path;
        } else {
            echo "frontend/assets/images/Clothify.png";
        }
    } else {
        // Product not found
        echo "frontend/assets/images/Clothify.png";
    }
} catch (Exception $e) {
    // Log error and return default image
    error_log("Error in direct_image.php: " . $e->getMessage());
    echo "frontend/assets/images/Clothify.png";
} 