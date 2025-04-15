<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once '../../config/db_connect.php';

// Get product ID from query parameters
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid product ID'
    ]);
    exit;
}

// Get product details
$query = "
    SELECT p.*, 
           s.shop_name,
           u.username as seller_username,
           GROUP_CONCAT(pi.image_url) as images
    FROM products p
    JOIN sellers s ON p.seller_id = s.user_id
    JOIN users u ON p.seller_id = u.id
    LEFT JOIN product_images pi ON p.id = pi.product_id
    WHERE p.id = ?
    GROUP BY p.id
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Product not found'
    ]);
    exit;
}

$product = $result->fetch_assoc();

// Format images as array
$images = [];
if (!empty($product['images'])) {
    $images = explode(',', $product['images']);
}
$product['images'] = $images;

// Check if user is logged in
$isOwner = false;
if (isset($_SESSION['user_id'])) {
    $isOwner = ($_SESSION['user_id'] == $product['seller_id']);
}
$product['is_owner'] = $isOwner;

// Return product details
echo json_encode([
    'status' => 'success',
    'product' => $product
]);

// Close connection
$conn->close();
?> 