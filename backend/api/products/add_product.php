<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once '../../config/db_connect.php';

// Include session check
require_once '../../utils/check_session.php';

// Start session for user authentication
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'You must be logged in to add a product'
    ]);
    exit;
}

// Check if the user has a seller account
$userId = $_SESSION['user_id'];
$checkSellerQuery = "SELECT * FROM sellers WHERE user_id = ?";
$stmt = $conn->prepare($checkSellerQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$sellerResult = $stmt->get_result();

if ($sellerResult->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'You must have a seller account to add products'
    ]);
    exit;
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get and validate product data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$condition = isset($_POST['condition']) ? trim($_POST['condition']) : '';
$size = isset($_POST['size']) ? trim($_POST['size']) : '';
$brand = isset($_POST['brand']) ? trim($_POST['brand']) : '';
$location = isset($_POST['location']) ? trim($_POST['location']) : '';

// Validation
if (empty($name) || empty($description) || $price <= 0 || empty($category) || empty($condition)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please fill in all required fields'
    ]);
    exit;
}

// Begin transaction for product and image uploads
$conn->begin_transaction();

try {
    // Insert product data
    $insertProductQuery = "
        INSERT INTO products (
            seller_id, name, description, price, category, 
            condition_status, size, brand, location, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
    ";
    
    $stmt = $conn->prepare($insertProductQuery);
    $stmt->bind_param(
        "issdssssss",
        $userId, $name, $description, $price, $category,
        $condition, $size, $brand, $location
    );
    
    $stmt->execute();
    $productId = $conn->insert_id;
    
    // Handle image uploads
    $imageUrls = [];
    $uploadDir = '../uploads/products/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $fileCount = count($_FILES['images']['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            $fileName = $_FILES['images']['name'][$i];
            $tmpFilePath = $_FILES['images']['tmp_name'][$i];
            
            // Generate a unique filename
            $uniqueName = uniqid() . '_' . $fileName;
            $uploadPath = $uploadDir . $uniqueName;
            
            // Move the uploaded file
            if (move_uploaded_file($tmpFilePath, $uploadPath)) {
                $imageUrl = 'uploads/products/' . $uniqueName;
                $imageUrls[] = $imageUrl;
                
                // Insert image URL into database
                $insertImageQuery = "INSERT INTO product_images (product_id, image_url) VALUES (?, ?)";
                $stmt = $conn->prepare($insertImageQuery);
                $stmt->bind_param("is", $productId, $imageUrl);
                $stmt->execute();
            }
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Product added successfully',
        'product_id' => $productId,
        'images' => $imageUrls
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Error adding product: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?> 