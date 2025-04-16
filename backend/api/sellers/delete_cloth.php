<?php
session_start();
require_once '../../config/db_connect.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if user is logged in as a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Unauthorized access. Please login as a seller.'
    ]);
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Cloth ID is required'
    ]);
    exit;
}

$cloth_id = $_GET['id'];
$seller_id = $_SESSION['user_id'];

try {
    // First, verify that the cloth belongs to the requesting seller
    $verify_sql = "SELECT id FROM cloth_details WHERE id = ? AND seller_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $cloth_id, $seller_id);
    $verify_stmt->execute();
    $verify_stmt->store_result();
    
    if ($verify_stmt->num_rows === 0) {
        // Cloth doesn't exist or doesn't belong to this seller
        echo json_encode([
            'status' => 'error', 
            'message' => 'Cloth not found or you do not have permission to delete it'
        ]);
        exit;
    }
    
    // Delete the cloth from the database
    $delete_sql = "DELETE FROM cloth_details WHERE id = ? AND seller_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $cloth_id, $seller_id);
    
    if ($delete_stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Cloth deleted successfully',
            'cloth_id' => $cloth_id
        ]);
    } else {
        throw new Exception("Database error: " . $delete_stmt->error);
    }
    
    $delete_stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 