<?php
session_start();
require_once '../../config/db_connect.php';

// Prevent caching of images
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log errors
function log_error($message) {
    $log_file = "../../logs/image_errors.log";
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// Check if cloth ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    log_error("No cloth ID provided");
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Cloth ID is required']);
    exit;
}

$cloth_id = $_GET['id'];
log_error("Attempting to retrieve image for cloth ID: $cloth_id");

try {
    // Query to get cloth image
    $sql = "SELECT cloth_photo, photo_type FROM cloth_details WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        log_error("Prepare failed: " . $conn->error);
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $cloth_id);
    
    if (!$stmt->execute()) {
        log_error("Execute failed: " . $stmt->error);
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    // Using get_result() instead of store_result() for BLOB data
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image_data = $row['cloth_photo'];
        $image_type = $row['photo_type'];
        
        if ($image_data && $image_type) {
            // Log successful retrieval
            log_error("Successfully retrieved image for cloth ID: $cloth_id, size: " . strlen($image_data) . " bytes, type: $image_type");
            
            // Set the appropriate content type
            header("Content-Type: " . $image_type);
            
            // Output the image data
            echo $image_data;
        } else {
            // No image data or type
            log_error("Image data missing for cloth ID: $cloth_id");
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Image data is empty or invalid']);
        }
    } else {
        // No image found
        log_error("No image found for cloth ID: $cloth_id");
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Image not found']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    log_error("Error: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?> 