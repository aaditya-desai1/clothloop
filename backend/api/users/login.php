<?php
/**
 * User Login API
 * 
 * Handles user authentication and returns user details with JWT token
 */

// Include and apply CORS headers
require_once __DIR__ . '/../../api/cors.php';
apply_cors();

// Set content type
header('Content-Type: application/json');

// Include necessary files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/api_utils.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed. Use POST.', null, 405);
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

// Check if data is valid JSON
if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    sendError('Invalid JSON data: ' . json_last_error_msg(), null, 400);
}

// Validate required fields
$requiredFields = ['email', 'password'];
$missing = validateRequiredFields($requiredFields, $data);
if ($missing) {
    sendError('Missing required fields: ' . implode(', ', $missing), null, 400);
}

try {
    // Connect to database
    $database = new Database();
    $db = $database->connect();
    
    // Log the login attempt in production
    if (IS_PRODUCTION) {
        error_log("[Login] Attempt for user: " . $data['email']);
    }
    
    // Prepare query
    $query = "SELECT u.id, u.name, u.email, u.password, u.role, u.status 
              FROM users u 
              WHERE u.email = :email";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $data['email']);
    $stmt->execute();
    
    // Check if user exists
    if ($stmt->rowCount() === 0) {
        if (IS_PRODUCTION) {
            error_log("[Login] User not found: " . $data['email']);
        }
        sendError('User not found', null, 404);
    }
    
    // Get user data
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if user is active
    if ($user['status'] !== 'active') {
        if (IS_PRODUCTION) {
            error_log("[Login] Account inactive: " . $data['email']);
        }
        sendError('Account is inactive or suspended', null, 403);
    }
    
    // Verify password
    if (!password_verify($data['password'], $user['password'])) {
        if (IS_PRODUCTION) {
            error_log("[Login] Invalid password for: " . $data['email']);
        }
        sendError('Invalid credentials', null, 401);
    }
    
    // Remove password from response
    unset($user['password']);
    
    // Get additional user info based on role
    if ($user['role'] === 'seller') {
        try {
            // Get seller information
            $sellerQuery = "SELECT id, shop_name, address, description, profile_photo 
                          FROM sellers WHERE id = :id";
            $sellerStmt = $db->prepare($sellerQuery);
            $sellerStmt->bindParam(':id', $user['id']);
            $sellerStmt->execute();
            
            if ($sellerStmt->rowCount() > 0) {
                $sellerInfo = $sellerStmt->fetch(PDO::FETCH_ASSOC);
                $user['seller_info'] = $sellerInfo;
            }
        } catch (Exception $e) {
            // Log error but continue
            error_log("[Login] Error getting seller info: " . $e->getMessage());
        }
    } else if ($user['role'] === 'buyer') {
        try {
            // Get buyer information
            $buyerQuery = "SELECT id, shipping_address, profile_photo 
                         FROM buyers WHERE id = :id";
            $buyerStmt = $db->prepare($buyerQuery);
            $buyerStmt->bindParam(':id', $user['id']);
            $buyerStmt->execute();
            
            if ($buyerStmt->rowCount() > 0) {
                $buyerInfo = $buyerStmt->fetch(PDO::FETCH_ASSOC);
                $user['buyer_info'] = $buyerInfo;
            }
        } catch (Exception $e) {
            // Log error but continue
            error_log("[Login] Error getting buyer info: " . $e->getMessage());
        }
    }
    
    // Generate JWT token (placeholder - implement actual JWT in production)
    $token = "jwt_token_placeholder";
    
    if (IS_PRODUCTION) {
        error_log("[Login] Successful login for: " . $data['email']);
    }
    
    // Return success response
    sendSuccess('Login successful', [
        'token' => $token,
        'user' => $user
    ]);
    
} catch (Exception $e) {
    error_log("[Login] Error: " . $e->getMessage());
    sendError('An error occurred: ' . $e->getMessage(), null, 500);
} 