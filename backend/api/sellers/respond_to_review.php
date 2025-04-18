<?php
/**
 * Respond to Review API
 * Allows sellers to respond to buyer reviews
 */

// Allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed. Please use POST.'
    ]);
    exit;
}

// Include database connection and authentication
require_once '../../config/db_connect.php';
require_once '../../auth/auth.php';

// Initialize response array
$response = [
    'status' => 'error',
    'message' => '',
    'data' => null
];

// Check if user is authenticated and is a seller
$userData = authenticate($conn);

if (!$userData) {
    $response['message'] = 'Authentication failed';
    http_response_code(401);
    echo json_encode($response);
    exit;
}

// Check if user is a seller
$userId = $userData['id'];
$checkSellerQuery = "SELECT * FROM sellers WHERE user_id = '$userId'";
$sellerResult = mysqli_query($conn, $checkSellerQuery);

if (!$sellerResult || mysqli_num_rows($sellerResult) === 0) {
    $response['message'] = 'Access denied. Only sellers can respond to reviews.';
    http_response_code(403);
    echo json_encode($response);
    exit;
}

$sellerData = mysqli_fetch_assoc($sellerResult);
$sellerId = $sellerData['id'];

// Get JSON data from request body
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Validate required fields
if (!isset($data['review_id']) || !isset($data['response'])) {
    $response['message'] = 'Missing required fields: review_id and response';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

$reviewId = mysqli_real_escape_string($conn, $data['review_id']);
$responseText = mysqli_real_escape_string($conn, $data['response']);

// Validate response text
if (empty($responseText) || strlen($responseText) > 1000) {
    $response['message'] = 'Response must be between 1 and 1000 characters';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

// Check if the review exists and belongs to this seller
$checkReviewQuery = "SELECT * FROM seller_reviews WHERE id = '$reviewId' AND seller_id = '$sellerId'";
$reviewResult = mysqli_query($conn, $checkReviewQuery);

if (!$reviewResult || mysqli_num_rows($reviewResult) === 0) {
    $response['message'] = 'Review not found or does not belong to your shop';
    http_response_code(404);
    echo json_encode($response);
    exit;
}

// Update the review with the seller's response
$updateQuery = "UPDATE seller_reviews 
                SET seller_response = '$responseText', 
                    response_date = NOW() 
                WHERE id = '$reviewId' AND seller_id = '$sellerId'";

if (mysqli_query($conn, $updateQuery)) {
    // Get the updated review
    $getUpdatedReviewQuery = "SELECT * FROM seller_reviews WHERE id = '$reviewId'";
    $updatedReviewResult = mysqli_query($conn, $getUpdatedReviewQuery);
    $updatedReview = mysqli_fetch_assoc($updatedReviewResult);
    
    // Log the response action
    $logQuery = "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details) 
                VALUES ('$userId', 'respond_to_review', 'seller_review', '$reviewId', 
                'Seller responded to review #$reviewId')";
    mysqli_query($conn, $logQuery);
    
    $response['status'] = 'success';
    $response['message'] = 'Response added successfully';
    $response['data'] = [
        'review_id' => $updatedReview['id'],
        'seller_id' => $updatedReview['seller_id'],
        'response' => $updatedReview['seller_response'],
        'response_date' => $updatedReview['response_date']
    ];
} else {
    $response['message'] = 'Failed to add response: ' . mysqli_error($conn);
    http_response_code(500);
}

// Close database connection
mysqli_close($conn);

// Return the response
echo json_encode($response); 