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
    // Check if the request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }
    
    // Get POST data
    $user_id = $_POST['user_id'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    
    // Validate required fields
    if (empty($user_id) || empty($username) || empty($email) || empty($phone)) {
        throw new Exception("Required fields are missing");
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Check if email already exists (excluding current user)
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        throw new Exception("Email address already in use by another account");
    }
    
    // Prepare update statement
    if (!empty($new_password)) {
        // Update with new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, phone = ?, password = ? WHERE id = ?");
        $update_stmt->bind_param("ssssi", $username, $email, $phone, $hashed_password, $user_id);
    } else {
        // Update without changing password
        $update_stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, phone = ? WHERE id = ?");
        $update_stmt->bind_param("sssi", $username, $email, $phone, $user_id);
    }
    
    // Execute update
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update user data: " . $update_stmt->error);
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => "User profile updated successfully"
    ]);
    
} catch (Exception $e) {
    // Rollback transaction if it was started
    if (isset($conn) && $conn->connect_errno === 0) {
        $conn->rollback();
    }
    
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