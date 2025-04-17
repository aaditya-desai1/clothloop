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

// Use the proper database connection file
require_once '../../config/db_connect.php';

// Default response
$response = [
    'status' => 'error',
    'message' => 'Failed to update shop profile.'
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
error_log("FILES data: " . json_encode(isset($_FILES) ? $_FILES : 'No files'));

try {
    // Get form data
    $shopName = isset($_POST['shop_name']) ? $_POST['shop_name'] : null;
    $shopAddress = isset($_POST['shop_address']) ? $_POST['shop_address'] : null;
    $shopBio = isset($_POST['shop_bio']) ? $_POST['shop_bio'] : null;
    
    // Get location coordinates
    $shopLatitude = isset($_POST['shop_latitude']) ? $_POST['shop_latitude'] : '';
    $shopLongitude = isset($_POST['shop_longitude']) ? $_POST['shop_longitude'] : '';
    $shopLocation = '';
    
    // If we have valid coordinates, combine them into the shop_location field
    if (!empty($shopLatitude) && !empty($shopLongitude)) {
        $shopLocation = $shopLatitude . ',' . $shopLongitude;
    }

    // Validate required fields
    if (empty($shopName) || empty($shopAddress)) {
        throw new Exception("Required fields are missing: Shop Name and Shop Address are required.");
    }

    // Create upload directory if it doesn't exist
    $uploadDir = "../../uploads/sellers/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Handle file upload for shop logo
    $shopLogoPath = null;
    if (isset($_FILES['shop_logo']) && $_FILES['shop_logo']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['shop_logo']['tmp_name'];
        $fileName = basename($_FILES['shop_logo']['name']);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = 'shop_' . $sellerId . '_' . time() . '.' . $extension;
        $uploadPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($tmpName, $uploadPath)) {
            $shopLogoPath = 'uploads/sellers/' . $newFileName;
        } else {
            error_log("Failed to move uploaded file: " . error_get_last()['message']);
            throw new Exception('Failed to upload shop logo. Please try again.');
        }
    }

    // Debug update info
    error_log("Updating shop profile for seller ID: $sellerId");
    error_log("Shop Name: $shopName");
    error_log("Shop Address: $shopAddress");
    error_log("Shop Bio: $shopBio");
    error_log("Shop Location: $shopLocation");
    error_log("Shop Logo Path: " . ($shopLogoPath ? $shopLogoPath : "No new logo"));

    // Begin transaction
    $conn->begin_transaction();

    // Prepare update query
    if ($shopLogoPath) {
        // Update with new logo
        $stmt = $conn->prepare("UPDATE sellers SET shop_name = ?, shop_address = ?, shop_bio = ?, shop_location = ?, shop_logo = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $shopName, $shopAddress, $shopBio, $shopLocation, $shopLogoPath, $sellerId);
    } else {
        // Update without changing logo
        $stmt = $conn->prepare("UPDATE sellers SET shop_name = ?, shop_address = ?, shop_bio = ?, shop_location = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $shopName, $shopAddress, $shopBio, $shopLocation, $sellerId);
    }

    // Execute query
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }

    // Check if update was successful
    if ($stmt->affected_rows >= 0) {
        // Commit transaction
        $conn->commit();

        // Set success response
        $response['status'] = 'success';
        $response['message'] = 'Shop profile updated successfully.';
        $response['shop_name'] = $shopName;
        
        // Include shop logo URL if updated
        if ($shopLogoPath) {
            $response['shopLogoUrl'] = $shopLogoPath;
        }
    } else {
        throw new Exception('No changes were made to the profile.');
    }
} catch (Exception $e) {
    // Rollback transaction if started
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }

    // Delete uploaded file if exists
    if (isset($shopLogoPath) && isset($uploadPath) && file_exists($uploadPath)) {
        unlink($uploadPath);
    }

    $response['message'] = $e->getMessage();
    error_log("Error updating shop profile: " . $e->getMessage());
}

// Return response as JSON
echo json_encode($response);

// Close connection
$conn->close();
?> 