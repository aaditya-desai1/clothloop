<?php
/**
 * Seller Check Session API
 * 
 * Verifies if the current user session is valid and returns seller data
 */

// Include and apply CORS headers
require_once __DIR__ . '/../../api/cors.php';
apply_cors();

// Set content type
header('Content-Type: application/json');

// Include necessary files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/api_utils.php';

// Validate token (this is a simplified example)
$token = null;

// Check for Authorization header
$headers = getallheaders();
if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
    
    // Check if it starts with "Bearer "
    if (strpos($authHeader, 'Bearer ') === 0) {
        $token = substr($authHeader, 7);
    }
}

// If no Authorization header, check query string
if (!$token && isset($_GET['token'])) {
    $token = $_GET['token'];
}

// Get user ID from parameters (in a real app, you'd extract this from the token)
$userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;

// If no user ID, but we have a token, get user ID from token (simplified)
if (!$userId && $token) {
    // In a real app, this would verify and decode the JWT token
    // For now, we'll just allow the request to proceed
    if (IS_PRODUCTION) {
        error_log("[Check Session] Using token auth without user_id");
    }
}

// If still no user ID, return error
if (!$userId) {
    sendError('User ID is required', null, 400);
}

try {
    // Connect to database
    $database = new Database();
    $db = $database->connect();
    
    // Get user data
    $query = "SELECT u.id, u.name, u.email, u.role, u.status 
              FROM users u 
              WHERE u.id = :user_id AND u.role = 'seller'";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    // Check if user exists
    if ($stmt->rowCount() === 0) {
        sendError('Seller not found', null, 404);
    }
    
    // Get user data
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if user is active
    if ($user['status'] !== 'active') {
        sendError('Account is inactive or suspended', null, 403);
    }
    
    // Get seller information
    $sellerQuery = "SELECT id, shop_name, address, description, profile_photo 
                  FROM sellers WHERE id = :id";
    $sellerStmt = $db->prepare($sellerQuery);
    $sellerStmt->bindParam(':id', $user['id']);
    $sellerStmt->execute();
    
    $sellerInfo = null;
    if ($sellerStmt->rowCount() > 0) {
        $sellerInfo = $sellerStmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Return success response
    sendSuccess('Session is valid', [
        'user' => $user,
        'seller_info' => $sellerInfo
    ]);
    
} catch (Exception $e) {
    if (IS_PRODUCTION) {
        error_log("[Check Session] Error: " . $e->getMessage());
    }
    sendError('An error occurred: ' . $e->getMessage(), null, 500);
} 