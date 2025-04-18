<?php
/**
 * Update Product Status API
 * Allows a seller to update the status of their product (active/inactive)
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

if (!isset($data['status']) || !in_array($data['status'], ['active', 'inactive', 'pending'])) {
    Response::error('Valid status is required (active, inactive, or pending)');
}

$productId = $data['id'];
$status = $data['status'];

try {
    // Database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if product exists and belongs to this seller
    $stmt = $db->prepare("
        SELECT id 
        FROM products 
        WHERE id = :id AND seller_id = :seller_id
    ");
    $stmt->bindParam(':id', $productId);
    $stmt->bindParam(':seller_id', $sellerId);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        Response::error('Product not found or you do not have permission to update it');
    }
    
    // Update product status
    $stmt = $db->prepare("
        UPDATE products 
        SET status = :status, updated_at = NOW() 
        WHERE id = :id AND seller_id = :seller_id
    ");
    $stmt->bindParam(':id', $productId);
    $stmt->bindParam(':seller_id', $sellerId);
    $stmt->bindParam(':status', $status);
    $stmt->execute();
    
    // Return success response
    Response::success('Product status updated successfully');
    
} catch (Exception $e) {
    // Log error
    error_log("Error updating product status: " . $e->getMessage());
    
    // Return error response
    Response::error('Failed to update product status: ' . $e->getMessage());
} 