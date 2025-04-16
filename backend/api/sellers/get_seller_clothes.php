<?php
session_start();
require_once '../../config/db_connect.php';

// Check if the user is logged in as a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please login as a seller.']);
    exit;
}

$seller_id = $_SESSION['user_id'];
$response = ['status' => 'error', 'message' => 'Failed to retrieve cloth items'];

try {
    // Query to get all cloth items for the seller
    $sql = "SELECT id, cloth_title, description, size, category, rental_price, 
                   contact_no, whatsapp_no, terms_conditions, photo_type, 
                   created_at, updated_at, is_active
            FROM cloth_details 
            WHERE seller_id = ? 
            ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
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
    $response = ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($response);
?> 