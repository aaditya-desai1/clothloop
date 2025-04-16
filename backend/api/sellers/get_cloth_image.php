<?php
session_start();
require_once '../../config/db_connect.php';

// Check if cloth ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Cloth ID is required']);
    exit;
}

$cloth_id = $_GET['id'];

try {
    // Query to get cloth image
    $sql = "SELECT cloth_photo, photo_type FROM cloth_details WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cloth_id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($image_data, $image_type);
        $stmt->fetch();
        
        // Set the appropriate content type
        header("Content-Type: " . $image_type);
        
        // Output the image data
        echo $image_data;
    } else {
        // No image found
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Image not found']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?> 