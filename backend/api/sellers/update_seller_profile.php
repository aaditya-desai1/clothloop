<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access. Please login as a seller.'
    ]);
    exit;
}

// Include database connection
require_once '../../config/db_connect.php';

// Initialize response
$response = [
    'status' => 'error',
    'message' => '',
    'profileImageUrl' => null
];

try {
    // Get seller ID from session
    $sellerId = $_SESSION['user_id'];
    
    // Get form data
    $name = isset($_POST['username']) ? trim($_POST['username']) : null;
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;
    $shopName = isset($_POST['shop_name']) ? trim($_POST['shop_name']) : null;
    $shopAddress = isset($_POST['shop_address']) ? trim($_POST['shop_address']) : null;
    $shopBio = isset($_POST['shop_bio']) ? trim($_POST['shop_bio']) : null;
    $password = isset($_POST['password']) ? $_POST['password'] : null;
    
    // Basic validation
    if (empty($name) || empty($email) || empty($phone) || empty($shopName) || empty($shopAddress)) {
        $response['message'] = 'Required fields are missing.';
        echo json_encode($response);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format.';
        echo json_encode($response);
        exit;
    }
    
    // Check if email exists and belongs to another seller
    $checkEmail = $conn->prepare("SELECT id FROM sellers WHERE email = ? AND id != ?");
    $checkEmail->bind_param("si", $email, $sellerId);
    $checkEmail->execute();
    $emailResult = $checkEmail->get_result();
    
    if ($emailResult->num_rows > 0) {
        $response['message'] = 'Email already in use by another seller.';
        echo json_encode($response);
        exit;
    }
    
    // Handle profile image upload (shop logo)
    $shopLogoPath = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $fileInfo = pathinfo($_FILES['profile_image']['name']);
        $extension = strtolower($fileInfo['extension']);
        
        // Validate file extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedExtensions)) {
            $response['message'] = 'Invalid file format. Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.';
            echo json_encode($response);
            exit;
        }
        
        // Validate file size (max 2MB)
        if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
            $response['message'] = 'File size exceeds the maximum limit of 2MB.';
            echo json_encode($response);
            exit;
        }
        
        // Create upload directory if it doesn't exist
        $uploadDir = '../../../frontend/assets/images/shop_logos/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate unique filename
        $newFileName = 'shop_logo_' . $sellerId . '_' . uniqid() . '.' . $extension;
        $shopLogoPath = 'frontend/assets/images/shop_logos/' . $newFileName;
        $uploadPath = $uploadDir . $newFileName;
        
        // Move uploaded file
        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
            $response['message'] = 'Failed to upload shop logo. Please try again.';
            echo json_encode($response);
            exit;
        }
        
        // Set profile image URL for response
        $response['profileImageUrl'] = $shopLogoPath;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Update seller profile
    if ($password) {
        // Update with new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        if ($shopLogoPath) {
            // Update with new logo and password
            $stmt = $conn->prepare("UPDATE sellers SET name = ?, email = ?, phone_no = ?, shop_name = ?, shop_address = ?, shop_logo = ?, shop_bio = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssssssssi", $name, $email, $phone, $shopName, $shopAddress, $shopLogoPath, $shopBio, $hashedPassword, $sellerId);
        } else {
            // Update with password only, keep existing logo
            $stmt = $conn->prepare("UPDATE sellers SET name = ?, email = ?, phone_no = ?, shop_name = ?, shop_address = ?, shop_bio = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssssssi", $name, $email, $phone, $shopName, $shopAddress, $shopBio, $hashedPassword, $sellerId);
        }
    } else {
        if ($shopLogoPath) {
            // Update with new logo, keep existing password
            $stmt = $conn->prepare("UPDATE sellers SET name = ?, email = ?, phone_no = ?, shop_name = ?, shop_address = ?, shop_logo = ?, shop_bio = ? WHERE id = ?");
            $stmt->bind_param("sssssssi", $name, $email, $phone, $shopName, $shopAddress, $shopLogoPath, $shopBio, $sellerId);
        } else {
            // Update without changing logo or password
            $stmt = $conn->prepare("UPDATE sellers SET name = ?, email = ?, phone_no = ?, shop_name = ?, shop_address = ?, shop_bio = ? WHERE id = ?");
            $stmt->bind_param("ssssssi", $name, $email, $phone, $shopName, $shopAddress, $shopBio, $sellerId);
        }
    }
    
    // Execute query
    $stmt->execute();
    
    // Check if update was successful
    if ($stmt->affected_rows >= 0) {
        // Update session variables
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['shop_name'] = $shopName;
        
        // Commit transaction
        $conn->commit();
        
        // Set success response
        $response['status'] = 'success';
        $response['message'] = 'Profile updated successfully.';
    } else {
        // Rollback transaction
        $conn->rollback();
        
        // Delete uploaded file if exists
        if ($shopLogoPath && file_exists($uploadPath)) {
            unlink($uploadPath);
        }
        
        $response['message'] = 'No changes were made to the profile.';
    }
    
} catch (Exception $e) {
    // Rollback transaction if in progress
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }
    
    // Delete uploaded file if exists
    if (isset($shopLogoPath) && isset($uploadPath) && file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    
    $response['message'] = 'Error updating profile: ' . $e->getMessage();
}

// Return response
echo json_encode($response);

// Close connection
$conn->close();
?> 