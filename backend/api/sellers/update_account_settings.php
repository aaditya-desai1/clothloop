<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session for user authentication
session_start();

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Use the database connection
require_once '../../config/db_connect.php';

// Default response
$response = [
    'status' => 'error',
    'message' => 'Failed to update account settings.'
];

// Debug session info
error_log("Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set'));
error_log("Session user_type: " . (isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'Not set'));

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    $response['message'] = 'Unauthorized access. Please login as a seller.';
    echo json_encode($response);
    exit;
}

// Get seller ID from session
$sellerId = $_SESSION['user_id'];

// Debug POST data
error_log("POST data: " . json_encode($_POST));

try {
    // Get form data
    $username = isset($_POST['username']) ? trim($_POST['username']) : null;
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;
    $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : null;
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : null;
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : null;

    // Validate required fields
    if (empty($username) || empty($email) || empty($phone)) {
        throw new Exception("Required fields are missing: Username, Email, and Phone are required.");
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format.");
    }

    // Begin transaction
    $conn->begin_transaction();

    // Check email uniqueness (except for current user)
    $checkEmail = $conn->prepare("SELECT id FROM sellers WHERE email = ? AND id != ?");
    $checkEmail->bind_param("si", $email, $sellerId);
    $checkEmail->execute();
    $result = $checkEmail->get_result();
    
    if ($result->num_rows > 0) {
        throw new Exception("Email already in use by another seller.");
    }

    // If changing password, validate current password
    if ($newPassword) {
        if (empty($currentPassword)) {
            throw new Exception("Current password is required to change password.");
        }
        
        if ($newPassword !== $confirmPassword) {
            throw new Exception("New passwords don't match.");
        }
        
        // Check current password
        $stmt = $conn->prepare("SELECT password FROM sellers WHERE id = ?");
        $stmt->bind_param("i", $sellerId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Seller not found.");
        }
        
        $row = $result->fetch_assoc();
        $storedPassword = $row['password'];
        
        if (!password_verify($currentPassword, $storedPassword)) {
            throw new Exception("Current password is incorrect.");
        }
        
        // Update with new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE sellers SET name = ?, email = ?, phone_no = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $username, $email, $phone, $hashedPassword, $sellerId);
    } else {
        // Update without changing password
        $stmt = $conn->prepare("UPDATE sellers SET name = ?, email = ?, phone_no = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $email, $phone, $sellerId);
    }

    // Execute query
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }

    // Check if update was successful
    if ($stmt->affected_rows >= 0) {
        // Update session variables
        $_SESSION['user_name'] = $username;
        $_SESSION['user_email'] = $email;
        
        // Commit transaction
        $conn->commit();

        // Set success response
        $response['status'] = 'success';
        $response['message'] = 'Account settings updated successfully.';
        $response['user'] = [
            'username' => $username,
            'email' => $email,
            'phone' => $phone
        ];
    } else {
        throw new Exception('No changes were made to the account settings.');
    }
} catch (Exception $e) {
    // Rollback transaction if started
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }

    $response['message'] = $e->getMessage();
    error_log("Error updating account settings: " . $e->getMessage());
}

// Return response as JSON
echo json_encode($response);

// Close connection
$conn->close();
?> 