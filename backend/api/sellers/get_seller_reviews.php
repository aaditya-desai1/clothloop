<?php
/**
 * Get Seller Reviews API
 * Retrieves all reviews for a specific seller with pagination
 */

// Set headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Include database and utilities
require_once '../../config/Database.php';
require_once '../../models/User.php';
require_once '../../models/Seller.php';
require_once '../../models/Review.php';
require_once '../../utils/Validator.php';

// Instantiate validator
$validator = new Validator();

// Parse the query parameters
$seller_id = isset($_GET['seller_id']) ? $_GET['seller_id'] : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$rating = isset($_GET['rating']) ? (int)$_GET['rating'] : null;

// Validate input parameters
if (!$seller_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Seller ID is required'
    ]);
    exit();
}

if (!$validator->validateId($seller_id)) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Invalid seller ID format'
    ]);
    exit();
}

if ($page < 1) {
    $page = 1;
}

if ($limit < 1 || $limit > 50) {
    $limit = 10; // Default limit if invalid
}

if ($rating !== null && ($rating < 1 || $rating > 5)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Rating must be between 1 and 5'
    ]);
    exit();
}

try {
    // Instantiate DB and connect
    $database = new Database();
    $db = $database->connect();

    // Instantiate seller object
    $seller = new Seller($db);
    
    // Check if seller exists
    if (!$seller->findById($seller_id)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Seller not found'
        ]);
        exit();
    }

    // Instantiate review object
    $review = new Review($db);
    
    // Calculate offset for pagination
    $offset = ($page - 1) * $limit;
    
    // Get reviews with pagination and optional rating filter
    $result = $review->getSellerReviews($seller_id, $limit, $offset, $rating);
    
    // Get total reviews count for pagination
    $totalReviews = $review->countSellerReviews($seller_id, $rating);
    
    // Get rating summary
    $ratingSummary = $review->getSellerRatingSummary($seller_id);
    
    // Calculate total pages
    $totalPages = ceil($totalReviews / $limit);
    
    // Construct response
    $response = [
        'status' => 'success',
        'current_page' => $page,
        'total_pages' => $totalPages,
        'limit' => $limit,
        'total_reviews' => $totalReviews,
        'summary' => $ratingSummary,
        'reviews' => $result
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 