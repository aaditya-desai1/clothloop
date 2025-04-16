<?php
// Include the database connection file
require_once __DIR__ . '/../config/database.php';

// Define response headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// Initialize the database connection
$db = new Database();
$conn = $db->getConnection();

// Get the operation type from the request
$operation = isset($_GET['operation']) ? $_GET['operation'] : '';

// Handle different operations
switch ($operation) {
    case 'get_location':
        getSellerLocation($conn);
        break;
    default:
        // Invalid operation
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid operation specified'
        ]);
        break;
}

/**
 * Get the location coordinates for a seller
 * @param PDO $conn Database connection
 */
function getSellerLocation($conn) {
    // Get seller ID from request
    $seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : 0;
    
    if (!$seller_id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid seller ID'
        ]);
        return;
    }
    
    try {
        // Prepare and execute the query to get location data from shop_location column
        $stmt = $conn->prepare("SELECT shop_location FROM sellers WHERE id = :seller_id");
        $stmt->bindParam(':seller_id', $seller_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Fetch the result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Debug: Log the raw result to see what's coming from the database
        error_log("Debug - Seller ID: $seller_id, Shop Location Raw: " . print_r($result, true));
        
        if ($result && !empty($result['shop_location'])) {
            // Debug: Log the shop_location value
            error_log("Debug - Shop Location Value: " . $result['shop_location']);
            
            // Parse the coordinates from shop_location column (format: "latitude,longitude")
            $coordinates = explode(',', $result['shop_location']);
            
            // Debug: Log the parsed coordinates
            error_log("Debug - Parsed Coordinates: " . print_r($coordinates, true));
            
            if (count($coordinates) == 2) {
                $latitude = trim($coordinates[0]);
                $longitude = trim($coordinates[1]);
                
                // Return the location data
                echo json_encode([
                    'status' => 'success',
                    'location' => [
                        'latitude' => $latitude,
                        'longitude' => $longitude
                    ]
                ]);
            } else {
                // Invalid format
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid location format'
                ]);
            }
        } else {
            // No location data found
            echo json_encode([
                'status' => 'error',
                'message' => 'No location data found for this seller'
            ]);
        }
    } catch (PDOException $e) {
        // Handle database errors
        error_log("Database error in getSellerLocation: " . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?> 