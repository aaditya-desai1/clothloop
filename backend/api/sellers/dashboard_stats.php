<?php
/**
 * Seller Dashboard Stats API
 * Returns statistics and data for the seller dashboard
 */

// Include and apply CORS headers
require_once __DIR__ . '/../../api/cors.php';
apply_cors();

// Set content type
header('Content-Type: application/json');

// Include necessary files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/api_utils.php';

// Get user ID and seller ID from parameters
$userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$sellerId = isset($_GET['seller_id']) ? $_GET['seller_id'] : $userId;
$userName = isset($_GET['user_name']) ? $_GET['user_name'] : 'Seller';

// Initialize response data
$response = [
    'status' => 'success',
    'message' => 'Dashboard data retrieved successfully',
    'data' => [
        'seller_name' => $userName,
        'total_products' => 0,
        'interested_customers' => 0,
        'average_rating' => 0
    ]
];

try {
    // Connect to database
    $database = new Database();
    $db = $database->connect();
    $dbType = $database->dbType;
    
    if (IS_PRODUCTION) {
        error_log("[Dashboard Stats] Using DB type: $dbType for seller ID: $sellerId");
    }
    
    // Get total products count
    try {
        $query = "SELECT COUNT(*) FROM products WHERE seller_id = :seller_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':seller_id', $sellerId);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        $response['data']['total_products'] = (int)$count;
        
        if (IS_PRODUCTION) {
            error_log("[Dashboard Stats] Product count: $count");
        }
    } catch (Exception $e) {
        // Log error but continue
        error_log("[Dashboard Stats] Error getting product count: " . $e->getMessage());
    }
    
    // Get seller rating - try different approaches based on database setup
    try {
        // First try with seller_reviews
        $avgRating = 0;
        $hasRating = false;
        
        // Try seller_reviews first if table exists
        try {
            $query = "SELECT AVG(rating) FROM seller_reviews WHERE seller_id = :seller_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':seller_id', $sellerId);
            $stmt->execute();
            $avgRating = $stmt->fetchColumn();
            $hasRating = ($avgRating !== false && $avgRating !== null);
        } catch (Exception $e) {
            // Table might not exist, try product_reviews instead
            error_log("[Dashboard Stats] seller_reviews error: " . $e->getMessage());
        }
        
        // If seller_reviews didn't work, try product_reviews
        if (!$hasRating) {
            try {
                $query = "SELECT AVG(r.rating) FROM product_reviews r 
                         JOIN products p ON r.product_id = p.id 
                         WHERE p.seller_id = :seller_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':seller_id', $sellerId);
                $stmt->execute();
                $avgRating = $stmt->fetchColumn();
                $hasRating = ($avgRating !== false && $avgRating !== null);
            } catch (Exception $e) {
                // Log error but continue
                error_log("[Dashboard Stats] product_reviews error: " . $e->getMessage());
            }
        }
        
        // Set the rating in response
        $response['data']['average_rating'] = $hasRating ? round(floatval($avgRating), 1) : 0;
        
        if (IS_PRODUCTION) {
            error_log("[Dashboard Stats] Average rating: " . $response['data']['average_rating']);
        }
    } catch (Exception $e) {
        // Log error but continue
        error_log("[Dashboard Stats] Error getting average rating: " . $e->getMessage());
    }
    
    // Get interested customers 
    try {
        // First try with customer_interests table
        try {
            $query = "SELECT COUNT(DISTINCT buyer_id) FROM customer_interests 
                     JOIN products ON customer_interests.product_id = products.id 
                     WHERE products.seller_id = :seller_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':seller_id', $sellerId);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            $response['data']['interested_customers'] = (int)$count;
        } catch (Exception $e) {
            // Try wishlist if it exists
            error_log("[Dashboard Stats] customer_interests error: " . $e->getMessage());
            
            try {
                $query = "SELECT COUNT(DISTINCT buyer_id) FROM wishlist 
                         JOIN products ON wishlist.product_id = products.id 
                         WHERE products.seller_id = :seller_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':seller_id', $sellerId);
                $stmt->execute();
                $count = $stmt->fetchColumn();
                $response['data']['interested_customers'] = (int)$count;
            } catch (Exception $e) {
                // Log error but continue
                error_log("[Dashboard Stats] wishlist error: " . $e->getMessage());
            }
        }
        
        if (IS_PRODUCTION) {
            error_log("[Dashboard Stats] Interested customers: " . $response['data']['interested_customers']);
        }
    } catch (Exception $e) {
        // Log error but continue
        error_log("[Dashboard Stats] Error getting interested customers count: " . $e->getMessage());
    }
    
    // Return success response
    echo json_encode($response);
    
} catch (Exception $e) {
    // Handle database errors
    $errorResponse = [
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ];
    http_response_code(500);
    echo json_encode($errorResponse);
} 