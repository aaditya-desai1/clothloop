<?php
// API Endpoint: Get Seller Profile
// This endpoint returns the profile information of the current authenticated seller

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the response header to JSON
header('Content-Type: application/json');

// Include the database connection
require_once '../../config/db_connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug session info
error_log("Session data in get_seller_profile.php: " . json_encode($_SESSION));

// Check if user is authenticated 
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access. Please log in first.',
        'debug' => [
            'session_status' => session_status(),
            'session_id' => session_id(),
            'session_data' => $_SESSION
        ]
    ]);
    exit;
}

// Check if user is a seller
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access. Only sellers can view this profile.',
        'debug' => [
            'user_type' => isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'not set'
        ]
    ]);
    exit;
}

// Get seller ID from session
$seller_id = $_SESSION['user_id'];
error_log("Fetching profile for seller ID: $seller_id");

try {
    // Get seller profile information
    $sql = "SELECT 
                id,
                name, 
                email, 
                phone_no,
                shop_name, 
                shop_address, 
                shop_location,
                shop_bio, 
                shop_logo,
                created_at
            FROM 
                sellers
            WHERE 
                id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // For debugging purposes, let's list sellers in the database
        $debug_data = [];
        $debug_query = "SELECT id, name, email FROM sellers LIMIT 5";
        $debug_result = $conn->query($debug_query);
        if ($debug_result && $debug_result->num_rows > 0) {
            while ($row = $debug_result->fetch_assoc()) {
                $debug_data[] = $row;
            }
        }
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Seller profile not found for ID: ' . $seller_id,
            'debug' => [
                'seller_id' => $seller_id,
                'sellers_in_db' => $debug_data
            ]
        ]);
        exit;
    }
    
    $profile = $result->fetch_assoc();
    error_log("Found profile: " . json_encode($profile));
    
    // Extract latitude and longitude from shop_location if available
    if (!empty($profile['shop_location'])) {
        $coordinates = explode(',', $profile['shop_location']);
        if (count($coordinates) === 2) {
            $profile['shop_latitude'] = trim($coordinates[0]);
            $profile['shop_longitude'] = trim($coordinates[1]);
        }
    } else {
        $profile['shop_latitude'] = '';
        $profile['shop_longitude'] = '';
    }
    
    // If shop logo exists, generate the full URL
    if (!empty($profile['shop_logo'])) {
        // Create a URL for fetching the shop logo
        $profile['has_shop_logo'] = true;
        // Instead of returning the binary data, provide an endpoint URL
        $profile['shop_logo_url'] = "../../../backend/api/sellers/get_shop_logo.php?seller_id=" . $profile['id'];
        
        // Remove the binary data from the response to reduce payload size
        unset($profile['shop_logo']);
    } else {
        $profile['has_shop_logo'] = false;
        $profile['shop_logo_url'] = null;
    }
    
    // Return the profile information
    echo json_encode([
        'status' => 'success',
        'profile' => $profile
    ]);
    
} catch (Exception $e) {
    // Log the error
    $error_message = "Database error in get_seller_profile.php: " . $e->getMessage();
    error_log($error_message);
    
    // Return error message
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred: ' . $e->getMessage(),
        'debug' => [
            'error' => $error_message,
            'trace' => $e->getTraceAsString()
        ]
    ]);
}

// Close connection
$conn->close();
?> 