<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not logged in'
    ]);
    exit;
}

// Include database connection
require_once '../../config/db_connect.php';

// Get user ID from session
$userId = $_SESSION['user_id'];

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get form data
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$shopName = isset($_POST['shop_name']) ? trim($_POST['shop_name']) : '';
$shopAddress = isset($_POST['shop_address']) ? trim($_POST['shop_address']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$confirmPassword = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

// Validate required fields
if (empty($username) || empty($email) || empty($phone) || empty($shopName) || empty($shopAddress)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required'
    ]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email format'
    ]);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Update user information
    $updateUserQuery = "UPDATE users SET username = ?, email = ?, phone = ? WHERE id = ?";
    $stmt = $conn->prepare($updateUserQuery);
    $stmt->bind_param("sssi", $username, $email, $phone, $userId);
    $stmt->execute();
    
    // Check if seller record exists
    $checkSellerQuery = "SELECT * FROM sellers WHERE user_id = ?";
    $checkStmt = $conn->prepare($checkSellerQuery);
    $checkStmt->bind_param("i", $userId);
    $checkStmt->execute();
    $sellerResult = $checkStmt->get_result();
    
    if ($sellerResult->num_rows > 0) {
        // Update existing seller record
        $updateSellerQuery = "UPDATE sellers SET shop_name = ?, shop_address = ? WHERE user_id = ?";
        $sellerStmt = $conn->prepare($updateSellerQuery);
        $sellerStmt->bind_param("ssi", $shopName, $shopAddress, $userId);
        $sellerStmt->execute();
    } else {
        // Insert new seller record
        $insertSellerQuery = "INSERT INTO sellers (user_id, shop_name, shop_address) VALUES (?, ?, ?)";
        $sellerStmt = $conn->prepare($insertSellerQuery);
        $sellerStmt->bind_param("iss", $userId, $shopName, $shopAddress);
        $sellerStmt->execute();
    }
    
    // Handle password update if provided
    if (!empty($password)) {
        // Validate password match
        if ($password !== $confirmPassword) {
            throw new Exception('Passwords do not match');
        }
        
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password
        $updatePasswordQuery = "UPDATE users SET password = ? WHERE id = ?";
        $passwordStmt = $conn->prepare($updatePasswordQuery);
        $passwordStmt->bind_param("si", $hashedPassword, $userId);
        $passwordStmt->execute();
    }
    
    // Handle profile image upload if provided
    $profileImageUrl = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        // Read the image file
        $imageData = file_get_contents($_FILES['profile_image']['tmp_name']);
        $imageType = $_FILES['profile_image']['type'];
        
        // Base64 encode the image data for storing in database
        $base64Image = 'data:' . $imageType . ';base64,' . base64_encode($imageData);
        
        // Update profile image in database
        $updateImageQuery = "UPDATE sellers SET profile_image = ? WHERE user_id = ?";
        $imageStmt = $conn->prepare($updateImageQuery);
        $imageStmt->bind_param("si", $base64Image, $userId);
        $imageStmt->execute();
        
        $profileImageUrl = $base64Image;
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Profile updated successfully',
        'profileImageUrl' => $profileImageUrl
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Return error response
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?> 