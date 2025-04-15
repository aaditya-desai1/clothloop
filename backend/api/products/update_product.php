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

// Check if form was submitted
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

// Get product ID
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
        'message' => 'Product not found or you do not have permission to edit it'
    ]);
    exit;
}

// Get form data
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 0);
$category_id = intval($_POST['category_id'] ?? 0);
$condition = trim($_POST['condition'] ?? '');
$size = trim($_POST['size'] ?? '');
$brand = trim($_POST['brand'] ?? '');
$color = trim($_POST['color'] ?? '');

// Validate required fields
if (empty($title) || empty($description) || $price <= 0 || $quantity <= 0 || $category_id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please fill all required fields'
    ]);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Update product
    $updateQuery = "UPDATE products SET 
                   title = ?, 
                   description = ?, 
                   price = ?, 
                   quantity = ?, 
                   category_id = ?, 
                   product_condition = ?, 
                   size = ?, 
                   brand = ?, 
                   color = ?, 
                   updated_at = NOW() 
                   WHERE id = ? AND seller_id = ?";
    
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssdiiissssii", $title, $description, $price, $quantity, $category_id, 
                          $condition, $size, $brand, $color, $productId, $userId);
    $updateStmt->execute();
    
    // Process new images if any
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        // Create directory if it doesn't exist
        $uploadDir = '../uploads/products/' . $productId . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $totalFiles = count($_FILES['images']['name']);
        
        for ($i = 0; $i < $totalFiles; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['images']['tmp_name'][$i];
                $name = basename($_FILES['images']['name'][$i]);
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                
                // Only allow certain file formats
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($extension, $allowedExtensions)) {
                    continue;
                }
                
                // Generate a unique filename
                $newFilename = uniqid() . '.' . $extension;
                $uploadPath = $uploadDir . $newFilename;
                
                if (move_uploaded_file($tmp_name, $uploadPath)) {
                    // Store the image URL in database
                    $imageUrl = 'uploads/products/' . $productId . '/' . $newFilename;
                    $imageQuery = "INSERT INTO product_images (product_id, image_url) VALUES (?, ?)";
                    $imageStmt = $conn->prepare($imageQuery);
                    $imageStmt->bind_param("is", $productId, $imageUrl);
                    $imageStmt->execute();
                }
            }
        }
    }
    
    // Handle deleted images
    if (isset($_POST['deleted_images']) && !empty($_POST['deleted_images'])) {
        $deletedImages = json_decode($_POST['deleted_images'], true);
        
        if (is_array($deletedImages) && !empty($deletedImages)) {
            foreach ($deletedImages as $imageId) {
                // Get the image URL first to delete the file
                $getImageQuery = "SELECT image_url FROM product_images WHERE id = ? AND product_id = ?";
                $getImageStmt = $conn->prepare($getImageQuery);
                $getImageStmt->bind_param("ii", $imageId, $productId);
                $getImageStmt->execute();
                $imageResult = $getImageStmt->get_result();
                
                if ($imageResult->num_rows > 0) {
                    $imageData = $imageResult->fetch_assoc();
                    $imagePath = '../' . $imageData['image_url'];
                    
                    // Delete the image from the file system
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                    
                    // Delete the image record from the database
                    $deleteImageQuery = "DELETE FROM product_images WHERE id = ? AND product_id = ?";
                    $deleteImageStmt = $conn->prepare($deleteImageQuery);
                    $deleteImageStmt->bind_param("ii", $imageId, $productId);
                    $deleteImageStmt->execute();
                }
            }
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Product updated successfully',
        'product_id' => $productId
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