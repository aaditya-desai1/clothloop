<?php
// API Endpoint: Get Shop Logo
// This endpoint returns the shop logo image for a seller

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once '../../config/db_connect.php';

// Start session for user authentication
session_start();

// Get seller ID from request parameter
$seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : 0;

// Validate seller ID
if ($seller_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid seller ID'
    ]);
    exit;
}

try {
    // Query to get shop logo
    $stmt = $conn->prepare("SELECT shop_logo FROM sellers WHERE id = ?");
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $stmt->store_result();
    
    // Check if seller exists
    if ($stmt->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Seller not found'
        ]);
        exit;
    }
    
    // Bind result to a variable
    $stmt->bind_result($shopLogo);
    $stmt->fetch();
    
    // Check if logo exists
    if (empty($shopLogo)) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'No logo found for this seller'
        ]);
        exit;
    }
    
    // Determine the image type - default to JPEG if we can't detect it
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->buffer($shopLogo);
    
    // Set appropriate content type header
    header("Content-Type: {$mimeType}");
    header("Content-Length: " . strlen($shopLogo));
    header("Cache-Control: public, max-age=86400"); // Cache for 24 hours
    
    // Output the image data
    echo $shopLogo;
    
} catch (Exception $e) {
    // Return error as JSON
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Error retrieving shop logo: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?> 