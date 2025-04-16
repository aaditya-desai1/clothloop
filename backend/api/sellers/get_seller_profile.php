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

try {
    // Get seller ID from session
    $sellerId = $_SESSION['user_id'];
    
    // Prepare SQL to fetch seller details
    $stmt = $conn->prepare("SELECT id, name, email, phone_no, shop_name, shop_address, shop_location, shop_logo, shop_bio, created_at FROM sellers WHERE id = ?");
    $stmt->bind_param("i", $sellerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Seller profile not found.'
        ]);
        exit;
    }
    
    // Fetch seller data
    $seller = $result->fetch_assoc();
    
    // Format data for response
    $response = [
        'status' => 'success',
        'seller' => [
            'id' => $seller['id'],
            'username' => $seller['name'],
            'email' => $seller['email'],
            'phone' => $seller['phone_no'],
            'shop_name' => $seller['shop_name'],
            'shop_address' => $seller['shop_address'],
            'shop_location' => $seller['shop_location'],
            'shop_bio' => $seller['shop_bio'],
            'shop_logo' => $seller['shop_logo'] ? "uploads/sellers/" . $seller['shop_logo'] : null,
            'created_at' => $seller['created_at']
        ]
    ];
    
    // Handle shop logo
    if (!empty($seller['shop_logo'])) {
        $imagePath = '../../../' . $seller['shop_logo'];
        if (file_exists($imagePath)) {
            // Get file information
            $fileInfo = pathinfo($imagePath);
            $extension = strtolower($fileInfo['extension']);
            
            // Determine MIME type
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp'
            ];
            
            $mimeType = isset($mimeTypes[$extension]) ? $mimeTypes[$extension] : 'application/octet-stream';
            
            // Read file and convert to base64
            $imageData = file_get_contents($imagePath);
            $base64Image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            
            // Add to response
            $response['seller']['shop_logo'] = $base64Image;
        }
    }
    
    // Return the response
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching seller profile: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?> 