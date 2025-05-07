<?php
/**
 * User Login API
 * 
 * Handles user authentication and returns user details with JWT token
 */

// Allow CORS - allow all origins for now to fix the CORS error
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
    
    // Prepare query
    $query = "SELECT u.id, u.name, u.email, u.password, u.role, u.status 
              FROM users u 
              WHERE u.email = :email";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $data['email']);
    $stmt->execute();
    
    // Check if user exists
    if ($stmt->rowCount() === 0) {
        sendError('User not found', null, 404);
    }
    
    // Get user data
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if user is active
    if ($user['status'] !== 'active') {
        sendError('Account is inactive or suspended', null, 403);
    }
    
    // Verify password
    if (!password_verify($data['password'], $user['password'])) {
        sendError('Invalid credentials', null, 401);
    }
    
    // Remove password from response
    unset($user['password']);
    
    // Generate JWT token (placeholder - implement actual JWT in production)
    $token = "jwt_token_placeholder";
    
    // Return success response
    sendSuccess('Login successful', [
        'token' => $token,
        'user' => $user
    ]);
    
} catch (Exception $e) {
    sendError('An error occurred: ' . $e->getMessage(), null, 500);
} 