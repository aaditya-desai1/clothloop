<?php
/**
 * Universal Image Display API
 * Displays images for various entity types (cloth, user, etc.)
 * 
 * Usage:
 * /backend/api/image_display.php?type=cloth&id=123
 * /backend/api/image_display.php?type=user&id=456
 */

// Set headers for cross-origin access
header('Access-Control-Allow-Origin: *');

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Required files
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/response.php';

// Get request parameters
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';
$debug = isset($_GET['debug']) && $_GET['debug'] === 'true';

// If debug mode is on, set content type to text/html
if ($debug) {
    header('Content-Type: text/html');
}

// Validate required parameters
if (empty($type) || empty($id)) {
    if ($debug) {
        echo "Error: Type and ID parameters are required";
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Type and ID parameters are required']);
    }
    exit;
}

// Define fallback images for different types
$fallbackImages = [
    'cloth' => 'frontend/assets/images/placeholder.png',
    'product' => 'frontend/assets/images/placeholder.png',
    'user' => 'frontend/assets/images/user-placeholder.png',
    'default' => 'frontend/assets/images/placeholder.png'
];

// Define database tables and fields for different types
$entityConfig = [
    'cloth' => [
        'table' => 'products',
        'id_field' => 'id',
        'image_fields' => ['image_path', 'image_url', 'image', 'thumbnail', 'photo']
    ],
    'product' => [
        'table' => 'products',
        'id_field' => 'id',
        'image_fields' => ['image_path', 'image_url', 'image', 'thumbnail', 'photo']
    ],
    'user' => [
        'table' => 'users',
        'id_field' => 'id',
        'image_fields' => ['profile_image', 'avatar', 'image', 'photo']
    ]
];

// Get fallback image for requested type
$fallbackImage = $fallbackImages[$type] ?? $fallbackImages['default'];

try {
    // If the type is not configured, use fallback
    if (!isset($entityConfig[$type])) {
        if ($debug) {
            echo "Error: Type '$type' is not configured, using fallback image";
        } else {
            displayFallbackImage($fallbackImage, $debug);
        }
        exit;
    }

    // Get config for the requested type
    $config = $entityConfig[$type];
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if the table exists
    $checkTable = $db->query("SHOW TABLES LIKE '{$config['table']}'");
    if ($checkTable->rowCount() == 0) {
        if ($debug) {
            echo "Error: Table '{$config['table']}' does not exist, using fallback image";
        } else {
            displayFallbackImage($fallbackImage, $debug);
        }
        exit;
    }
    
    // Query to get the entity
    $query = "SELECT * FROM {$config['table']} WHERE {$config['id_field']} = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $entity = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($debug) {
            echo "<h2>Entity Data for {$type} #{$id}</h2>";
            echo "<pre>" . print_r($entity, true) . "</pre>";
            echo "<h2>Checking Image Fields</h2>";
        }
        
        // Try each of the possible image fields
        foreach ($config['image_fields'] as $field) {
            if (isset($entity[$field]) && !empty($entity[$field])) {
                $imagePath = $entity[$field];
                
                if ($debug) {
                    echo "<p>Found image in field '$field': $imagePath</p>";
                }
                
                // Check if it's a base64 encoded image
                if (strpos($imagePath, 'data:image/') === 0) {
                    if ($debug) {
                        echo "<p>Image is base64 encoded</p>";
                    } else {
                        // It's already a base64 image, output it directly
                        list($type, $data) = explode(';', $imagePath);
                        list(, $data) = explode(',', $data);
                        $imgData = base64_decode($data);
                        
                        header('Content-Type: ' . str_replace('data:', '', $type));
                        echo $imgData;
                        exit;
                    }
                }
                
                // It's a file path, try to find the file
                $baseDir = dirname(dirname(__DIR__)); // 2 levels up from /backend/api/image_display.php
                $possiblePaths = [
                    $imagePath,
                    "$baseDir/$imagePath",
                    __DIR__ . "/$imagePath",
                    __DIR__ . "/../$imagePath",
                    __DIR__ . "/../../$imagePath",
                    "../$imagePath",
                    "../../$imagePath",
                    "../../../$imagePath",
                    "/xampp/htdocs/ClothLoop/$imagePath"
                ];
                
                if ($debug) {
                    echo "<h3>Possible Paths for $field</h3>";
                    echo "<ul>";
                    foreach ($possiblePaths as $path) {
                        echo "<li>" . $path . " - " . (file_exists($path) ? "EXISTS" : "NOT FOUND") . "</li>";
                    }
                    echo "</ul>";
                }
                
                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        if ($debug) {
                            echo "<p>Found file at: $path</p>";
                        } else {
                            // Determine content type based on file extension
                            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                            $contentType = 'image/jpeg'; // Default
                            
                            if ($ext === 'png') $contentType = 'image/png';
                            elseif ($ext === 'gif') $contentType = 'image/gif';
                            elseif ($ext === 'webp') $contentType = 'image/webp';
                            
                            header("Content-Type: $contentType");
                            readfile($path);
                            exit;
                        }
                    }
                }
            }
        }
        
        // If we reach here, we didn't find a valid image
        if ($debug) {
            echo "<p>No valid image found in any field, using fallback</p>";
        } else {
            displayFallbackImage($fallbackImage, $debug);
        }
    } else {
        // Entity not found
        if ($debug) {
            echo "Error: No $type found with ID $id, using fallback image";
        } else {
            displayFallbackImage($fallbackImage, $debug);
        }
    }
} catch (Exception $e) {
    // Log error
    error_log("Error displaying image: " . $e->getMessage());
    
    if ($debug) {
        echo "Error: " . $e->getMessage();
    } else {
        // Return fallback image
        displayFallbackImage($fallbackImage, $debug);
    }
}

/**
 * Display a fallback image
 * @param string $imagePath Path to the fallback image
 * @param bool $debug Whether to output debug information
 */
function displayFallbackImage($imagePath, $debug = false) {
    $baseDir = dirname(dirname(__DIR__)); // 2 levels up from /backend/api/image_display.php
    $possiblePaths = [
        $imagePath,
        "$baseDir/$imagePath",
        __DIR__ . "/$imagePath", 
        __DIR__ . "/../$imagePath",
        __DIR__ . "/../../$imagePath",
        "../$imagePath",
        "../../$imagePath",
        "../../../$imagePath",
        "/xampp/htdocs/ClothLoop/$imagePath",
        "frontend/assets/images/placeholder.png",
        "../frontend/assets/images/placeholder.png",
        "../../frontend/assets/images/placeholder.png",
        "$baseDir/frontend/assets/images/placeholder.png"
    ];
    
    if ($debug) {
        echo "<h3>Searching for Fallback Image</h3>";
        echo "<ul>";
        foreach ($possiblePaths as $path) {
            echo "<li>" . $path . " - " . (file_exists($path) ? "EXISTS" : "NOT FOUND") . "</li>";
        }
        echo "</ul>";
        return;
    }
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $contentType = 'image/jpeg'; // Default
            
            if ($ext === 'png') $contentType = 'image/png';
            elseif ($ext === 'gif') $contentType = 'image/gif';
            elseif ($ext === 'webp') $contentType = 'image/webp';
            
            header("Content-Type: $contentType");
            readfile($path);
            exit;
        }
    }
    
    // If no fallback image is found, return a 1x1 transparent GIF
    header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
} 