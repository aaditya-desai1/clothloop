<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once '../config/db_connect.php';

// Get parameters
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$debug = isset($_GET['debug']) ? true : false;

// Validate parameters
if (empty($type) || $id <= 0) {
    header("HTTP/1.0 400 Bad Request");
    exit('Missing or invalid parameters');
}

// Debug mode function
function debugOutput($message) {
    global $debug;
    if ($debug) {
        echo $message . "<br>";
    }
}

// Set default response if image not found
function showDefaultImage() {
    global $debug;
    
    if ($debug) {
        echo "Showing default image because no image data was found.<br>";
        exit;
    }
    
    // Read the default image
    $imageData = file_get_contents('../assets/no-image.png');
    if ($imageData === false) {
        error_log("Failed to read default image file");
        header("HTTP/1.0 500 Internal Server Error");
        exit("Failed to read default image");
    }
    
    // Set proper headers for image display
    header('Content-Type: image/png');
    header('Content-Length: ' . strlen($imageData));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Ensure we're sending binary data
    if (ob_get_level()) ob_end_clean();
    
    // Output the image data directly
    echo $imageData;
    exit;
}

// Attempt to retrieve the image
try {
    if ($debug) {
        echo "<h2>Debug Information</h2>";
        echo "Type: " . htmlspecialchars($type) . "<br>";
        echo "ID: " . $id . "<br>";
    }
    
    switch ($type) {
        case 'shop_logo':
            debugOutput("Retrieving shop logo for seller ID: $id");
            
            // Retrieve seller's logo
            $stmt = $conn->prepare("SELECT shop_logo, shop_logo_type FROM sellers WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->store_result();
            
            debugOutput("Query executed, found " . $stmt->num_rows . " rows");
            
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($imageData, $imageType);
                $stmt->fetch();
                
                if ($imageData && $imageType) {
                    debugOutput("Found image data of type: " . $imageType);
                    debugOutput("Image size: " . strlen($imageData) . " bytes");
                    
                    if (!$debug) {
                        // Set content type and disable output buffering to handle binary data
                        header('Content-Type: ' . $imageType);
                        header('Content-Length: ' . strlen($imageData));
                        header('Cache-Control: no-cache, no-store, must-revalidate');
                        header('Pragma: no-cache');
                        header('Expires: 0');
                        
                        // Ensure we're sending binary data
                        if (ob_get_level()) ob_end_clean();
                        
                        // Output the image data
                        echo $imageData;
                    } else {
                        echo "Image would be displayed normally. MIME type: " . htmlspecialchars($imageType);
                    }
                    exit;
                } else {
                    debugOutput("Image data is empty or null");
                    if ($debug) {
                        echo "Shop logo data: " . ($imageData ? "Not empty" : "Empty") . "<br>";
                        echo "Shop logo type: " . ($imageType ? htmlspecialchars($imageType) : "Empty") . "<br>";
                    }
                }
            } else {
                debugOutput("No seller found with ID: $id");
            }
            break;
            
        case 'product':
            // Retrieve product image
            $imageId = isset($_GET['image_id']) ? intval($_GET['image_id']) : 0;
            
            if ($imageId > 0) {
                // Retrieve specific image
                $stmt = $conn->prepare("SELECT image_data, image_type FROM product_images WHERE id = ? AND product_id = ?");
                $stmt->bind_param("ii", $imageId, $id);
            } else {
                // Retrieve first image for the product
                $stmt = $conn->prepare("SELECT image_data, image_type FROM product_images WHERE product_id = ? ORDER BY id ASC LIMIT 1");
                $stmt->bind_param("i", $id);
            }
            
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($imageData, $imageType);
                $stmt->fetch();
                
                if ($imageData && $imageType) {
                    if (!$debug) {
                        // Set content type and disable output buffering to handle binary data
                        header('Content-Type: ' . $imageType);
                        header('Content-Length: ' . strlen($imageData));
                        header('Cache-Control: no-cache, no-store, must-revalidate');
                        header('Pragma: no-cache');
                        header('Expires: 0');
                        
                        // Ensure we're sending binary data
                        if (ob_get_level()) ob_end_clean();
                        
                        // Output the image data
                        echo $imageData;
                    } else {
                        debugOutput("Found product image with type: " . $imageType);
                    }
                    exit;
                }
            }
            break;
            
        case 'cloth':
            // Retrieve cloth image
            $stmt = $conn->prepare("SELECT cloth_photo, photo_type FROM cloth_details WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($imageData, $imageType);
                $stmt->fetch();
                
                if ($imageData && $imageType) {
                    if (!$debug) {
                        // Set content type and disable output buffering to handle binary data
                        header('Content-Type: ' . $imageType);
                        header('Content-Length: ' . strlen($imageData));
                        header('Cache-Control: no-cache, no-store, must-revalidate');
                        header('Pragma: no-cache');
                        header('Expires: 0');
                        
                        // Ensure we're sending binary data
                        if (ob_get_level()) ob_end_clean();
                        
                        // Output the image data
                        echo $imageData;
                    } else {
                        debugOutput("Found cloth image with type: " . $imageType);
                    }
                    exit;
                }
            }
            break;
            
        default:
            header("HTTP/1.0 400 Bad Request");
            exit('Invalid image type');
    }
    
    // If we get here, no image was found
    debugOutput("No image was found, showing default");
    showDefaultImage();
    
} catch (Exception $e) {
    error_log('Error retrieving image: ' . $e->getMessage());
    
    if ($debug) {
        echo "Error: " . htmlspecialchars($e->getMessage());
    } else {
        showDefaultImage();
    }
} finally {
    // Close connection
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?> 