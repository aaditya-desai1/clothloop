<?php
/**
 * Get Seller Reviews API
 * Fetches all reviews for a specific seller
 */

// Allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed. Please use GET.'
    ]);
    exit;
}

// Include database connection
require_once '../../config/db_connect.php';

// Initialize response array
$response = [
    'status' => 'error',
    'message' => '',
    'data' => null
];

// Get seller ID from URL parameter
$sellerId = isset($_GET['seller_id']) ? mysqli_real_escape_string($conn, $_GET['seller_id']) : null;

// Validate seller ID
if (!$sellerId || !is_numeric($sellerId)) {
    $response['message'] = 'Invalid or missing seller ID';
    echo json_encode($response);
    exit;
}

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Check if the seller exists
$checkSellerQuery = "SELECT * FROM sellers WHERE id = '$sellerId'";
$sellerResult = mysqli_query($conn, $checkSellerQuery);

if (!$sellerResult || mysqli_num_rows($sellerResult) === 0) {
    $response['message'] = 'Seller not found';
    echo json_encode($response);
    exit;
}

$sellerData = mysqli_fetch_assoc($sellerResult);

// Get total reviews count for pagination
$countQuery = "SELECT COUNT(*) as total FROM seller_reviews WHERE seller_id = '$sellerId'";
$countResult = mysqli_query($conn, $countQuery);
$totalReviews = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalReviews / $limit);

// Query to get reviews with user information
$query = "SELECT sr.*, u.username, u.profile_image 
          FROM seller_reviews sr
          LEFT JOIN users u ON sr.user_id = u.id
          WHERE sr.seller_id = '$sellerId'
          ORDER BY sr.created_at DESC
          LIMIT $offset, $limit";

$result = mysqli_query($conn, $query);

if ($result) {
    $reviews = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        // Format the created_at date
        $row['created_at'] = date('Y-m-d H:i:s', strtotime($row['created_at']));
        
        // Format the response date if exists
        if (!empty($row['response_date'])) {
            $row['response_date'] = date('Y-m-d H:i:s', strtotime($row['response_date']));
        }
        
        // Mask sensitive user information
        $row['user_email'] = null;
        
        // Add to reviews array
        $reviews[] = $row;
    }
    
    // Calculate average rating
    $avgRatingQuery = "SELECT AVG(rating) as avg_rating FROM seller_reviews WHERE seller_id = '$sellerId'";
    $avgRatingResult = mysqli_query($conn, $avgRatingQuery);
    $avgRating = 0;
    
    if ($avgRatingResult && mysqli_num_rows($avgRatingResult) > 0) {
        $avgRating = round(mysqli_fetch_assoc($avgRatingResult)['avg_rating'], 1);
    }
    
    $response['status'] = 'success';
    $response['message'] = count($reviews) > 0 ? 'Reviews retrieved successfully' : 'No reviews found for this seller';
    $response['data'] = [
        'seller' => [
            'id' => $sellerData['id'],
            'name' => $sellerData['name'],
            'avg_rating' => $avgRating
        ],
        'reviews' => $reviews,
        'pagination' => [
            'total_reviews' => (int)$totalReviews,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'limit' => $limit
        ]
    ];
} else {
    $response['message'] = 'Failed to retrieve reviews: ' . mysqli_error($conn);
}

// Close database connection
mysqli_close($conn);

// Return the response
echo json_encode($response);
?> 