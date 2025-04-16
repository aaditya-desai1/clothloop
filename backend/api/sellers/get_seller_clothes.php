<?php
session_start();
require_once '../../config/db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get seller ID from various sources
$seller_id = null;

// First try from session
if (isset($_SESSION['user_id'])) {
    $seller_id = $_SESSION['user_id'];
}

// If not in session, try from GET/POST parameters
if (!$seller_id && isset($_GET['seller_id'])) {
    $seller_id = $_GET['seller_id'];
} else if (!$seller_id && isset($_POST['seller_id'])) {
    $seller_id = $_POST['seller_id'];
}

// For testing - if no seller ID is found, use a fallback
if (!$seller_id) {
    // This is a development/testing fallback only
    $seller_id = 1;
    error_log("Warning: Using fallback seller ID for testing");
}

$response = ['status' => 'error', 'message' => 'Failed to retrieve cloth items'];

try {
    // Query to get all cloth items for the seller
    $sql = "SELECT id, seller_id, cloth_title, description, size, category, rental_price, 
                   contact_number as contact_no, whatsapp_number as whatsapp_no, 
                   terms_and_conditions as terms_conditions, is_active
            FROM cloth_details 
            WHERE seller_id = ? 
            ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $seller_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    $clothes = [];
    while ($row = $result->fetch_assoc()) {
        // Don't include the actual image data in the list to keep response small
        $row['has_image'] = true;
        $clothes[] = $row;
    }
    
    $response = [
        'status' => 'success',
        'clothes' => $clothes
    ];
    
    $stmt->close();
} catch (Exception $e) {
    error_log("Error in get_seller_clothes.php: " . $e->getMessage());
    $response = ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($response);
?> 