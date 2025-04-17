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
error_log("Session user_id in update_seller_profile.php: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set'));
error_log("Session user_type in update_seller_profile.php: " . (isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'Not set'));
error_log("Session data: " . json_encode($_SESSION));

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

    // Handle file upload for shop logo
    $shopLogoData = null;
    $shopLogoType = null;
    if (isset($_FILES['shop_logo']) && $_FILES['shop_logo']['error'] === UPLOAD_ERR_OK) {
        // Get file data
        $tmpName = $_FILES['shop_logo']['tmp_name'];
        $fileType = $_FILES['shop_logo']['type'];
        
        // Read file contents
        $shopLogoData = file_get_contents($tmpName);
        $shopLogoType = $fileType;
        
        if (!$shopLogoData) {
            error_log("Failed to read uploaded file: " . error_get_last()['message']);
            throw new Exception('Failed to process shop logo. Please try again.');
        }
        
        error_log("Shop logo uploaded, size: " . strlen($shopLogoData) . " bytes, type: " . $shopLogoType);
    }

    // Debug update info
    error_log("Updating shop profile for seller ID: $sellerId");
    error_log("Shop Name: $shopName");
    error_log("Shop Address: $shopAddress");
    error_log("Shop Bio: $shopBio");
    error_log("Shop Location: $shopLocation");
    error_log("Shop Logo Data: " . ($shopLogoData ? 'Data exists' : 'No data'));
    error_log("Shop Logo Type: " . ($shopLogoType ? $shopLogoType : 'No type'));

    // Begin transaction
    $conn->begin_transaction();

    // Prepare update query
    if ($shopLogoData) {
        // Update with new logo
        $stmt = $conn->prepare("UPDATE sellers SET shop_name = ?, shop_address = ?, shop_bio = ?, shop_location = ?, shop_logo = ? WHERE id = ?");
        $stmt->bind_param("ssssbi", $shopName, $shopAddress, $shopBio, $shopLocation, $shopLogoData, $sellerId);
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
        
        // Include shop logo type info if updated
        if ($shopLogoData) {
            $response['shopLogoUpdated'] = true;
            $response['shopLogoType'] = $shopLogoType;
        }
    } else {
        throw new Exception('No changes were made to the profile.');
    }
} catch (Exception $e) {
    // Rollback transaction if started
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }

    $response['message'] = $e->getMessage();
    error_log("Error updating shop profile: " . $e->getMessage());
}

// Return response as JSON
echo json_encode($response);

// Close connection
$conn->close();
?> 