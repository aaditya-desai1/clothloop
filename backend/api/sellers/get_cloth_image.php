<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once '../../config/db_connect.php';

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Function to output a transparent 1x1 PNG as fallback
function sendTransparentPixel() {
    header("Content-Type: image/png");
    // Base64 encoded 1x1 transparent PNG
    echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
    exit;
}

// Check if ID parameter exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    sendTransparentPixel();
    exit;
}

$id = intval($_GET['id']);

try {
    // Simple direct query focusing on just getting the raw image data
    $query = "SELECT cloth_photo, photo_type FROM cloth_details WHERE id = $id";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if (!empty($row['cloth_photo'])) {
            // Set the correct content type
            $contentType = !empty($row['photo_type']) ? $row['photo_type'] : 'image/jpeg';
            header("Content-Type: $contentType");
            
            // Output the binary image data directly
            echo $row['cloth_photo'];
            exit;
        }
    }
    
    // If we get here, no image was found
    sendTransparentPixel();
    
} catch (Exception $e) {
    // Log error silently
    error_log("Error retrieving image: " . $e->getMessage());
    sendTransparentPixel();
}
?> 