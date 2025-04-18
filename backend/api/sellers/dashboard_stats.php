<?php
/**
 * Seller Dashboard Stats API
 * Returns statistics and data for the seller dashboard
 */

// Set proper CORS headers
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
$allowed_origins = [
    'http://localhost', 
    'http://127.0.0.1',
    'http://localhost:8080',
    'http://localhost:3000'
];

// Allow from any of the allowed origins
if (in_array($origin, $allowed_origins) || strpos($origin, 'clothloop') !== false) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    header("Access-Control-Allow-Origin: *");
}

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get user ID from session or query parameters
$userId = null;

// Check session first
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
}

// If not found in session, check query parameters
if (!$userId && isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];
}

// If still not found, check for seller_id parameter 
if (!$userId && isset($_GET['seller_id'])) {
    $userId = $_GET['seller_id'];
}

// Get user name from session or localStorage
$sellerName = 'Seller';
if (isset($_SESSION['user_name'])) {
    $sellerName = $_SESSION['user_name'];
} else if (isset($_GET['user_name'])) {
    $sellerName = $_GET['user_name'];
}

// Initialize response
$response = [
    'status' => 'success',
    'message' => 'Dashboard data retrieved successfully',
    'data' => [
        'seller_name' => $sellerName,
        'total_products' => 0,
        'interested_customers' => 0,
        'average_rating' => 0,
        'avg_product_price' => 0,
        'interested_customers_list' => []
    ]
];

try {
    // Include database config
    require_once __DIR__ . '/../../config/database.php';
    
    // Connect to database
    $database = new Database();
    $conn = $database->getConnection();
    
    // Initialize real data
    $data = [
        'seller_name' => $sellerName,
        'total_products' => 0,
        'interested_customers' => 0,
        'average_rating' => 0,
        'avg_product_price' => 0,
        'interested_customers_list' => []
    ];
    
    // Only proceed with database queries if we have a user ID
    if ($userId) {
        // Get total products
        $query = "SELECT COUNT(*) as count FROM products WHERE seller_id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bindParam(1, $userId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $data['total_products'] = (int)$result['count'];
            }
        }
        
        // Get average product price
        $query = "SELECT AVG(price) as avg_price FROM products WHERE seller_id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bindParam(1, $userId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && $result['avg_price'] !== null) {
                $data['avg_product_price'] = round((float)$result['avg_price'], 2);
            } else {
                $data['avg_product_price'] = 0;
            }
        }
        
        // Get average rating
        $query = "SELECT AVG(rating) as avg_rating FROM seller_reviews WHERE seller_id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bindParam(1, $userId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && $result['avg_rating'] !== null) {
                $data['average_rating'] = round((float)$result['avg_rating'], 1);
            }
        }
        
        // Get count of interested customers
        $query = "SELECT COUNT(DISTINCT buyer_id) as count FROM customer_interests 
                 JOIN products ON customer_interests.product_id = products.id 
                 WHERE products.seller_id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bindParam(1, $userId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $data['interested_customers'] = (int)$result['count'];
            }
        }
        
        // Get list of interested customers
        $query = "SELECT 
                    ci.id,
                    u.name as customer_name,
                    u.phone_no,
                    p.name as product_name,
                    ci.created_at as interest_date
                 FROM customer_interests ci 
                 JOIN users u ON ci.buyer_id = u.id
                 JOIN products p ON ci.product_id = p.id
                 WHERE p.seller_id = ?
                 ORDER BY ci.created_at DESC
                 LIMIT 10";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bindParam(1, $userId);
            $stmt->execute();
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($customers) {
                $data['interested_customers_list'] = $customers;
            }
        }
    }
    
    // Use the actual data
    $response['data'] = $data;
    
} catch (PDOException $e) {
    // Log error but don't expose details to client
    error_log("Database error in dashboard_stats.php: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'Database error occurred. Please check database connection.';
} catch (Exception $e) {
    // Log error but don't expose details to client
    error_log("General error in dashboard_stats.php: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Send JSON response
echo json_encode($response); 