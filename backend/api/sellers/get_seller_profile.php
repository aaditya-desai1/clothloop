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

try {
    // Prepare and execute query to get seller information
    $query = "SELECT u.username, u.email, u.phone, s.shop_name, s.shop_address, s.profile_image 
              FROM users u 
              LEFT JOIN sellers s ON u.id = s.user_id 
              WHERE u.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Fetch seller data
        $seller = $result->fetch_assoc();
        
        // The profile_image is now directly stored as base64 in the database,
        // so no path modification is needed
        
        // Return success response with seller data
        echo json_encode([
            'status' => 'success',
            'seller' => $seller
        ]);
    } else {
        // No seller found with this ID
        echo json_encode([
            'status' => 'error',
            'message' => 'Seller not found'
        ]);
    }
    
    // Close statement and connection
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 