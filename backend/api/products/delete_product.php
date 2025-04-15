<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Include database connection
require_once '../../config/db_connect.php';

// Include session check utility
require_once '../../utils/check_session.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not logged in'
    ]);
    exit;
}

// Get user ID from session
$userId = $_SESSION['user_id'];

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Check if user is a seller
$checkSellerQuery = "SELECT * FROM sellers WHERE user_id = ?";
$sellerStmt = $conn->prepare($checkSellerQuery);
$sellerStmt->bind_param("i", $userId);
$sellerStmt->execute();
$sellerResult = $sellerStmt->get_result();

if ($sellerResult->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User is not registered as a seller'
    ]);
    exit;
}

// Get product ID from POST data
$productId = intval($_POST['product_id'] ?? 0);

if ($productId <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid product ID'
    ]);
    exit;
}

// Check if the product belongs to the seller
$checkProductQuery = "SELECT * FROM products WHERE id = ? AND seller_id = ?";
$productStmt = $conn->prepare($checkProductQuery);
$productStmt->bind_param("ii", $productId, $userId);
$productStmt->execute();
$productResult = $productStmt->get_result();

if ($productResult->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Product not found or you do not have permission to delete it'
    ]);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Get all images for the product
    $getImagesQuery = "SELECT image_url FROM product_images WHERE product_id = ?";
    $imagesStmt = $conn->prepare($getImagesQuery);
    $imagesStmt->bind_param("i", $productId);
    $imagesStmt->execute();
    $imagesResult = $imagesStmt->get_result();

    // Delete all image files
    while ($image = $imagesResult->fetch_assoc()) {
        $imagePath = '../' . $image['image_url'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Delete all images from the database
    $deleteImagesQuery = "DELETE FROM product_images WHERE product_id = ?";
    $deleteImagesStmt = $conn->prepare($deleteImagesQuery);
    $deleteImagesStmt->bind_param("i", $productId);
    $deleteImagesStmt->execute();

    // Delete the product
    $deleteProductQuery = "DELETE FROM products WHERE id = ? AND seller_id = ?";
    $deleteProductStmt = $conn->prepare($deleteProductQuery);
    $deleteProductStmt->bind_param("ii", $productId, $userId);
    $deleteProductStmt->execute();

    // Check if product was deleted
    if ($deleteProductStmt->affected_rows === 0) {
        throw new Exception("Failed to delete product");
    }

    // Remove product directory if it exists
    $productDir = '../uploads/products/' . $productId;
    if (is_dir($productDir)) {
        // Try to remove the directory and its contents
        $files = glob($productDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($productDir);
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Product deleted successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?> 