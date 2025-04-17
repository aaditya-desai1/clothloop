<?php
// API Endpoint: Get Seller Profile
// This endpoint returns the profile information of the current authenticated seller

// Set the response header to JSON
header('Content-Type: application/json');

// Include the database connection
require_once '../../config/db_connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is authenticated and is a seller
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access. Please log in as a seller.'
    ]);
    exit;
}

// Get seller ID from session
$seller_id = $_SESSION['user_id'];

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
        echo json_encode([
            'status' => 'error',
            'message' => 'Seller profile not found.'
        ]);
        exit;
    }
    
    $profile = $result->fetch_assoc();
    
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
        $profile['shop_logo'] = '../../../backend/' . $profile['shop_logo'];
    }
    
    // Return the profile information
    echo json_encode([
        'status' => 'success',
        'profile' => $profile
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log("Database error in get_seller_profile.php: " . $e->getMessage());
    
    // Return error message
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?> 