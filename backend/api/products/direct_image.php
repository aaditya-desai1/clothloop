<?php
/**
 * Direct Image Access API
 * Provides direct access to product images by product ID
 */

// Required files
require_once __DIR__ . '/../../config/database.php';

// Get the product ID
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId <= 0) {
    header('HTTP/1.0 400 Bad Request');
    echo json_encode(['error' => 'Invalid product ID']);
    exit;
}

try {
    // Array of potential image paths to check
    $potentialPaths = [
        // Primary location: Product folder with ID
        __DIR__ . "/../../uploads/products/{$productId}/",
        // Secondary location: Direct in products folder
        __DIR__ . "/../../uploads/products/"
    ];
    
    $found = false;
    $imagePath = '';
    
    // First check database for the path
    $database = new Database();
    $db = $database->getConnection();
    
    // Check product_images table first (more likely to have correct paths)
    $query = "SELECT image_path FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute([$productId]);
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $dbPath = $row['image_path'];
        
        if (!empty($dbPath)) {
            // Convert relative DB path to absolute file system path
            if (strpos($dbPath, 'uploads/') !== false) {
                $relativePath = substr($dbPath, strpos($dbPath, 'uploads/'));
                $absolutePath = __DIR__ . '/../../' . $relativePath;
                
                if (file_exists($absolutePath)) {
                    $imagePath = $absolutePath;
                    $found = true;
                }
            }
        }
    }
    
    // If not found in DB, check direct products table
    if (!$found) {
        $query = "SELECT image_url, image_path, image FROM products WHERE id = ? LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute([$productId]);
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Try different image fields
            foreach (['image_path', 'image_url', 'image'] as $field) {
                if (!empty($row[$field])) {
                    $dbPath = $row[$field];
                    
                    // Convert relative DB path to absolute file system path
                    if (strpos($dbPath, 'uploads/') !== false) {
                        $relativePath = substr($dbPath, strpos($dbPath, 'uploads/'));
                        $absolutePath = __DIR__ . '/../../' . $relativePath;
                        
                        if (file_exists($absolutePath)) {
                            $imagePath = $absolutePath;
                            $found = true;
                            break;
                        }
                    }
                }
            }
        }
    }
    
    // If still not found, search common locations
    if (!$found) {
        foreach ($potentialPaths as $basePath) {
            if (is_dir($basePath)) {
                $files = scandir($basePath);
                
                // Common image extensions
                $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                foreach ($files as $file) {
                    // Skip dot files
                    if ($file == '.' || $file == '..') continue;
                    
                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    
                    // Check if it's an image
                    if (in_array($extension, $validExtensions)) {
                        $imagePath = $basePath . $file;
                        $found = true;
                        break 2; // Break both loops
                    }
                }
            }
        }
    }
    
    if ($found && !empty($imagePath)) {
        // Get the image mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $imagePath);
        finfo_close($finfo);
        
        // Set headers for image display
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($imagePath));
        header('Cache-Control: max-age=86400'); // Cache for 24 hours
        
        // Output the image
        readfile($imagePath);
        exit;
    } else {
        // Image not found
        header('HTTP/1.0 404 Not Found');
        echo json_encode(['error' => 'Image not found for product ID: ' . $productId]);
    }
    
} catch (Exception $e) {
    // Log error
    error_log('Error in direct_image.php: ' . $e->getMessage());
    
    // Return error
    header('HTTP/1.0 500 Internal Server Error');
    echo json_encode(['error' => 'Failed to retrieve image: ' . $e->getMessage()]);
}
?> 