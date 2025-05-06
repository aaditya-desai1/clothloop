<?php
/**
 * Get Reviews API
 * Returns reviews for a specific seller with pagination
 */

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Required files
require_once '../config/database.php';
require_once '../models/Review.php';
require_once '../utils/response.php';

// Get query parameters
$seller_id = isset($_GET['seller_id']) ? $_GET['seller_id'] : null;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$rating = isset($_GET['rating']) ? intval($_GET['rating']) : null;

// Validate seller_id is provided
if (!$seller_id) {
    Response::error('Seller ID is required');
    exit;
}

try {
    // Instantiate DB & connect
    $database = new Database();
    $db = $database->getConnection();

    // Instantiate review object
    $review = new Review($db);

    // Get reviews with pagination and optional rating filter
    $reviews = $review->getSellerReviews($seller_id, $limit, $offset, $rating);
    
    // Get rating summary
    $summary = $review->getSellerRatingSummary($seller_id);
    
    Response::success('Reviews retrieved successfully', [
        'reviews' => $reviews,
        'summary' => $summary,
        'pagination' => [
            'total' => $review->countSellerReviews($seller_id, $rating),
            'limit' => $limit,
            'offset' => $offset
        ]
    ]);
} catch (Exception $e) {
    // Log the error
    error_log('Error in getreviews.php: ' . $e->getMessage());
    Response::error('Server error: ' . $e->getMessage(), 500);
} 
?> 