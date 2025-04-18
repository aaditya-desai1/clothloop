<?php
/**
 * Seller Dashboard Stats API
 * Returns statistics and data for seller dashboard
 */

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/auth.php';
require_once __DIR__ . '/../../utils/response.php';

try {
    // Check if seller is authenticated
    Auth::requireRole('seller');
    
    // Get current seller
    $seller = Auth::getCurrentUser();
    $sellerId = $seller['id'];

    try {
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        // Initialize response data
        $dashboardData = [
            'seller_name' => $seller['name'],
            'total_products' => 0,
            'interested_customers' => 0,
            'average_rating' => 0,
            'interested_customers_list' => []
        ];
        
        $productsTableExists = false;
        $reviewsTableExists = false;
        $interestsTableExists = false;
        
        // Check which tables exist
        try {
            $checkTable = $db->prepare("SHOW TABLES LIKE 'products'");
            $checkTable->execute();
            $productsTableExists = ($checkTable->rowCount() > 0);
            
            $checkTable = $db->prepare("SHOW TABLES LIKE 'seller_reviews'");
            $checkTable->execute();
            $reviewsTableExists = ($checkTable->rowCount() > 0);
            
            $checkTable = $db->prepare("SHOW TABLES LIKE 'customer_interests'");
            $checkTable->execute();
            $interestsTableExists = ($checkTable->rowCount() > 0);
        } catch (Exception $e) {
            error_log("Error checking table existence: " . $e->getMessage());
            // Continue with default values if we can't check tables
        }
        
        if ($productsTableExists) {
            // Get total products count
            try {
                $stmt = $db->prepare("
                    SELECT COUNT(*) as product_count 
                    FROM products 
                    WHERE seller_id = :seller_id
                ");
                $stmt->bindParam(':seller_id', $sellerId);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    $dashboardData['total_products'] = (int)$result['product_count'];
                }
            } catch (Exception $e) {
                error_log("Error counting products: " . $e->getMessage());
                // Keep the default value set in the dashboardData
            }
        }
        
        if ($reviewsTableExists) {
            // Get average rating
            try {
                $stmt = $db->prepare("
                    SELECT AVG(rating) as average_rating 
                    FROM seller_reviews 
                    WHERE seller_id = :seller_id
                ");
                $stmt->bindParam(':seller_id', $sellerId);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result && $result['average_rating'] !== null) {
                    $dashboardData['average_rating'] = round((float)$result['average_rating'], 1);
                }
            } catch (Exception $e) {
                error_log("Error fetching average rating: " . $e->getMessage());
                // Keep the default value set in the dashboardData
            }
        }
        
        if ($interestsTableExists && $productsTableExists) {
            // Get interested customers data
            try {
                $stmt = $db->prepare("
                    SELECT c.id, u.name as customer_name, u.phone_no, p.name as product_name, 
                        c.created_at as interest_date
                    FROM customer_interests c
                    JOIN users u ON c.buyer_id = u.id
                    JOIN products p ON c.product_id = p.id
                    WHERE p.seller_id = :seller_id
                    ORDER BY c.created_at DESC
                    LIMIT 10
                ");
                $stmt->bindParam(':seller_id', $sellerId);
                $stmt->execute();
                $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Count interested customers
                $dashboardData['interested_customers'] = count($customers);
                
                // Add customers list to response
                $dashboardData['interested_customers_list'] = $customers;
            } catch (Exception $e) {
                error_log("Error fetching customer interests: " . $e->getMessage());
                // Keep the default values set in the dashboardData
            }
        }
        
        // Return success response with dashboard data
        Response::success('Dashboard data retrieved successfully', $dashboardData);
        
    } catch (Exception $e) {
        // Log error
        error_log("Error fetching dashboard data: " . $e->getMessage());
        
        // Return error response
        Response::error('Failed to fetch dashboard data: ' . $e->getMessage());
    }
} catch (Exception $e) {
    // This will catch authentication errors
    Response::error($e->getMessage(), null, 401);
} 