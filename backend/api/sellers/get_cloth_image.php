<?php
// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once '../../config/db_connect.php';

// Prevent image caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Path to fallback image
$fallback_image = '../../assets/placeholder.jpg';

// Create simple text log function
function log_error($message) {
    $log_file = '../../logs/image_errors.txt';
    $time = date('Y-m-d H:i:s');
    $log_message = "[$time] $message\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Function to serve fallback image
function serve_fallback_image() {
    global $fallback_image;
    
    // If fallback image exists, serve it
    if (file_exists($fallback_image)) {
        header('Content-Type: image/jpeg');
        readfile($fallback_image);
    } else {
        // If fallback doesn't exist, serve a text response
        header('Content-Type: text/plain');
        echo "Image not available";
    }
    exit;
}

// Check if ID parameter exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    log_error("No ID parameter provided");
    serve_fallback_image();
}

$id = intval($_GET['id']);

try {
    // Super simple direct query approach
    $query = "SELECT cloth_photo, photo_type FROM cloth_details WHERE id = $id";
    $result = $conn->query($query);
    
    if (!$result) {
        log_error("Query failed: " . $conn->error);
        serve_fallback_image();
    }
    
    if ($result->num_rows === 0) {
        log_error("No image found for ID: $id");
        serve_fallback_image();
    }
    
    $row = $result->fetch_assoc();
    
    // Check if we have image data
    if (empty($row['cloth_photo'])) {
        log_error("Empty image data for ID: $id");
        serve_fallback_image();
    }
    
    // Set content type and output image
    header("Content-Type: " . $row['photo_type']);
    echo $row['cloth_photo'];
    
} catch (Exception $e) {
    log_error("Error: " . $e->getMessage());
    serve_fallback_image();
}
?> 