<?php
/**
 * Delete Product API
 * Allows a seller to delete their product
 */

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/auth.php';
require_once __DIR__ . '/../../utils/response.php';

// Check if seller is authenticated
Auth::requireRole('seller');

// Get current seller
$seller = Auth::getCurrentUser();
$sellerId = $seller['id'];

// Get posted data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['id'])) {
    Response::error('Product ID is required');
}

$productId = $data['id'];

try {
    // Database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if products table exists
    $checkTable = $db->prepare("SHOW TABLES LIKE 'products'");
    $checkTable->execute();
    
    if ($checkTable->rowCount() == 0) {
        // Products table doesn't exist yet
        Response::error('Cannot delete product: Database table not found');
        exit;
    }
    
    // Check if product exists and belongs to this seller
    $stmt = $db->prepare("
        SELECT id, image_url 
        FROM products 
        WHERE id = :id AND seller_id = :seller_id
    ");
    $stmt->bindParam(':id', $productId);
    $stmt->bindParam(':seller_id', $sellerId);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        Response::error('Product not found or you do not have permission to delete it');
    }
    
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Begin transaction
    $db->beginTransaction();
    
    // Delete related records first (maintain referential integrity)
    // Delete customer interests for this product
    $stmt = $db->prepare("DELETE FROM customer_interests WHERE product_id = :product_id");
    $stmt->bindParam(':product_id', $productId);
    $stmt->execute();
    
    // Delete product reviews
    $stmt = $db->prepare("DELETE FROM product_reviews WHERE product_id = :product_id");
    $stmt->bindParam(':product_id', $productId);
    $stmt->execute();
    
    // Finally delete the product
    $stmt = $db->prepare("DELETE FROM products WHERE id = :id AND seller_id = :seller_id");
    $stmt->bindParam(':id', $productId);
    $stmt->bindParam(':seller_id', $sellerId);
    $stmt->execute();
    
    // Commit transaction
    $db->commit();
    
    // Delete product image if it exists
    if (!empty($product['image_url'])) {
        $imagePath = __DIR__ . '/../../../' . $product['image_url'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    // Return success response
    Response::success('Product deleted successfully');
    
} catch (Exception $e) {
    // Rollback transaction in case of error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    // Log error
    error_log("Error deleting product: " . $e->getMessage());
    
    // Return error response
    Response::error('Failed to delete product: ' . $e->getMessage());
} 