<?php
/**
 * Respond to Review API
 * Allows sellers to respond to buyer reviews
 */

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../utils/auth.php';

// Check if user is authenticated
if (!Auth::isAuthenticated()) {
    Response::error('Unauthorized - Please log in to respond to a review', null, 401);
}

// Get current user
$user = Auth::getCurrentUser();

// Verify user is a seller
if ($user['role'] !== 'seller') {
    Response::error('Only sellers can respond to reviews', null, 403);
}

// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['review_id']) || !isset($data['response'])) {
    Response::error('Missing required fields: review_id and response are required', null, 400);
}

// Validate response content
if (empty(trim($data['response'])) || strlen($data['response']) > 1000) {
    Response::error('Response cannot be empty and must be less than 1000 characters', null, 400);
}

// Connect to database
$database = new Database();
$db = $database->getConnection();

try {
    // Check if the review exists and belongs to this seller
    $checkQuery = "SELECT r.id 
                  FROM reviews r 
                  WHERE r.id = :review_id 
                  AND r.seller_id = :seller_id";
                  
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':review_id', $data['review_id']);
    $checkStmt->bindParam(':seller_id', $user['id']);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        Response::error('Review not found or you are not authorized to respond to this review', null, 404);
    }
    
    // Update the review with the seller's response
    $query = "UPDATE reviews 
              SET seller_response = :response, response_date = NOW() 
              WHERE id = :review_id 
              AND seller_id = :seller_id";
              
    $stmt = $db->prepare($query);
    $stmt->bindParam(':response', $data['response']);
    $stmt->bindParam(':review_id', $data['review_id']);
    $stmt->bindParam(':seller_id', $user['id']);
    
    if (!$stmt->execute()) {
        Response::error('Failed to submit response', null, 500);
    }
    
    // Get the updated review
    $getReviewQuery = "SELECT 
                        r.id, 
                        r.buyer_id as user_id, 
                        u.name as user_name, 
                        u.profile_photo, 
                        r.order_id, 
                        r.rating, 
                        r.review_text as comment, 
                        r.created_at,
                        r.seller_response,
                        r.response_date
                      FROM reviews r
                      JOIN users u ON r.buyer_id = u.id
                      WHERE r.id = :review_id";
                      
    $getReviewStmt = $db->prepare($getReviewQuery);
    $getReviewStmt->bindParam(':review_id', $data['review_id']);
    $getReviewStmt->execute();
    $review = $getReviewStmt->fetch(PDO::FETCH_ASSOC);
    
    Response::success('Response submitted successfully', [
        'review' => [
            'id' => $review['id'],
            'user_id' => $review['user_id'],
            'user_name' => $review['user_name'],
            'profile_photo' => $review['profile_photo'],
            'order_id' => $review['order_id'],
            'rating' => (int)$review['rating'],
            'comment' => $review['comment'],
            'created_at' => $review['created_at'],
            'seller_response' => $review['seller_response'],
            'response_date' => $review['response_date']
        ]
    ]);
    
} catch (Exception $e) {
    Response::error('Error submitting response: ' . $e->getMessage());
} 