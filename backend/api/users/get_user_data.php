<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include database connection
require_once '../../config/db_connect.php';

try {
    // Get and validate JSON data
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    if (!$data || !isset($data['user_id'])) {
        throw new Exception("Invalid request data");
    }
    
    $user_id = $data['user_id'];
    
    // Prepare and execute query to get user data
    $stmt = $conn->prepare("SELECT id, username, email, phone, user_type, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("User not found");
    }
    
    // Get user data and return as JSON
    $user = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
    
} catch (Exception $e) {
    // Return error message
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    // Close database connection if it exists
    if (isset($conn)) {
        $conn->close();
    }
}
?> 