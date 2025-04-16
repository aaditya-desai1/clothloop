<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a debug log file
$debug_log_file = dirname(__FILE__) . '/../../logs/image_debug.log';

// Function to log debug messages
function debug_log($message, $data = null) {
    global $debug_log_file;
    
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[{$timestamp}] " . $message;
    
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            $log_message .= ": " . json_encode($data);
        } else {
            $log_message .= ": " . strval($data);
        }
    }
    
    error_log($log_message . "\n", 3, $debug_log_file);
}

// Include database connection
debug_log('Starting image request');
require_once '../../config/db_connect.php';

if (!$conn) {
    debug_log('Database connection failed');
}

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Function to output a fallback image file
function sendFallbackImage($reason = 'unknown') {
    global $debug_log_file;
    debug_log("Sending fallback image - reason: " . $reason);
    
    // Use an existing image file as fallback
    $fallbackImagePath = dirname(__FILE__) . '/../../../frontend/assets/images/shop_logo.png';
    
    if (file_exists($fallbackImagePath)) {
        header("Content-Type: image/png");
        readfile($fallbackImagePath);
    } else {
        // If fallback image doesn't exist, send a transparent pixel
        header("Content-Type: image/png");
        echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
    }
    exit;
}

// Check if ID parameter exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    debug_log('No ID provided');
    sendFallbackImage('no id provided');
    exit;
}

$id = intval($_GET['id']);
debug_log('Processing image request', ['id' => $id]);

try {
    // First, let's check if there's an image for this ID
    $query = "SELECT id, cloth_photo, photo_type FROM cloth_details WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        debug_log('Failed to prepare statement', $conn->error);
        sendFallbackImage('prepare failed');
        exit;
    }
    
    $stmt->bind_param('i', $id);
    $result = null;
    
    if (!$stmt->execute()) {
        debug_log('Failed to execute statement', $stmt->error);
        sendFallbackImage('execute failed');
        exit;
    }
    
    $result = $stmt->get_result();
    
    debug_log('Query executed', ['rows' => $result ? $result->num_rows : 0]);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Check if cloth_photo is a stored binary content and not 'na' or empty
        if (isset($row['cloth_photo']) && $row['cloth_photo'] !== null && $row['cloth_photo'] !== 'na' && strlen($row['cloth_photo']) > 10) {
            // Set the correct content type based on what's stored in the database
            $contentType = !empty($row['photo_type']) ? $row['photo_type'] : 'image/jpeg';
            debug_log('Image found, serving with content type', $contentType);
            
            // Clean output buffer and set headers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            header("Content-Type: $contentType");
            
            // Output the binary data directly
            echo $row['cloth_photo'];
            exit;
        } else {
            // Invalid image data
            debug_log('Invalid image data', [
                'is_null' => $row['cloth_photo'] === null,
                'is_na' => $row['cloth_photo'] === 'na',
                'length' => isset($row['cloth_photo']) ? strlen($row['cloth_photo']) : 0
            ]);
            sendFallbackImage('invalid image data');
        }
    } else {
        debug_log('No cloth record found for ID', $id);
        sendFallbackImage('no record found');
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    debug_log('Exception while retrieving image', $e->getMessage());
    sendFallbackImage('exception: ' . $e->getMessage());
}

// If execution reaches here, fallback to default image
sendFallbackImage('fallback - end of script');
?> 