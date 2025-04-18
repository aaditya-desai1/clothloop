<?php
/**
 * Get Reviews API
 * Fetches reviews for a product by product ID
 */

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');

// Response array initialization
$response = [
    'status' => 'error',
    'message' => '',
    'data' => null
];

// Include database connection
require_once '../config/db_connect.php';

try {
    // Get parameters from request
    $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
    
    if (empty($productId)) {
        $response['message'] = 'Product ID is required';
        echo json_encode($response);
        exit;
    }
    
    // Query to get reviews from product_reviews table
    $query = "SELECT pr.*, COALESCE(u.name, 'Anonymous User') as reviewer_name 
              FROM product_reviews pr
              LEFT JOIN users u ON pr.buyer_id = u.id
              WHERE pr.product_id = ? 
              ORDER BY pr.created_at DESC 
              LIMIT ?, ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'iii', $productId, $offset, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Format each review
        $reviews = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            // Format date to a nicer format
            $date = new DateTime($row['created_at']);
            $formattedDate = $date->format('Y-m-d\TH:i:s');
            
            $reviews[] = [
                'id' => $row['id'],
                'reviewer_name' => $row['reviewer_name'],
                'rating' => (int)$row['rating'],
                'review_text' => $row['review'],
                'date' => $formattedDate
            ];
        }
        
        $response['status'] = 'success';
        $response['message'] = 'Reviews retrieved successfully';
        $response['data'] = $reviews;
    } else {
        $response['status'] = 'success';
        $response['message'] = 'No reviews found for this product';
        $response['data'] = [];
    }
    
    // Output response
    echo json_encode($response);
    
} catch (Exception $e) {
    // Log error
    error_log("Error fetching reviews: " . $e->getMessage());
    
    $response['message'] = 'Error fetching reviews: ' . $e->getMessage();
    echo json_encode($response);
} 
?> 