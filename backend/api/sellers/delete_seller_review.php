<?php
/**
 * Delete Seller Review API
 * Allows users to delete their reviews for sellers
 */

// Required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and model files
include_once '../../config/Database.php';
include_once '../../models/Review.php';
include_once '../../utils/Validator.php';
include_once '../../utils/Response.php';
include_once '../../utils/Auth.php';

// Make sure user is authenticated
Auth::requireAuth();
$current_user = Auth::getCurrentUser();
$user_id = $current_user['id'];

// Get DELETE data
$data = json_decode(file_get_contents("php://input"), true);

// Initialize validator
$validator = new Validator();

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate review object
$review = new Review($db);

// Check if review_id is provided in URL parameter or request body
$review_id = null;
if (isset($_GET['id'])) {
    $review_id = $_GET['id'];
} elseif (isset($data['review_id'])) {
    $review_id = $data['review_id'];
} else {
    Response::error('Missing review ID', null, 400);
}

// Validate review_id
if (!$validator->validateId($review_id)) {
    Response::error('Invalid review ID format', null, 400);
}

// Check if review exists and belongs to the current user
$review->id = $review_id;
$existing_review = $review->getById($review->id);

if (!$existing_review) {
    Response::error('Review not found', null, 404);
}

// Check if user is the owner of the review or an admin
if ($existing_review['user_id'] != $user_id && $current_user['role'] !== 'admin') {
    Response::error('You can only delete your own reviews', null, 403);
}

// Delete the review
try {
    if ($review->delete()) {
        Response::success('Review deleted successfully', null);
    } else {
        Response::error('Failed to delete review', null, 500);
    }
} catch (Exception $e) {
    Response::error('Error deleting review: ' . $e->getMessage(), null, 500);
} 