<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'config/db_connect.php';

// Set headers to allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Get operation type from GET parameter
$operation = isset($_GET['operation']) ? $_GET['operation'] : '';

// Process based on operation
switch ($operation) {
    case 'add':
        addProduct($conn);
        break;
    case 'update':
        updateProduct($conn);
        break;
    case 'delete':
        deleteProduct($conn);
        break;
    case 'fetch':
        fetchSellerProducts($conn);
        break;
    case 'fetch_all':
        fetchAllProducts($conn);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid operation']);
        break;
}

// Function to add a new product
function addProduct($conn) {
    // Basic validation
    if (!isset($_POST['seller_id']) || !isset($_POST['title']) || !isset($_POST['rental_price'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        return;
    }
    
    // Get form data
    $seller_id = $_POST['seller_id'];
    $title = $_POST['title'];
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $size = isset($_POST['size']) ? $_POST['size'] : '';
    $category = isset($_POST['category']) ? $_POST['category'] : '';
    $rental_price = floatval($_POST['rental_price']);
    $contact_number = isset($_POST['contact_number']) ? $_POST['contact_number'] : '';
    $whatsapp_number = isset($_POST['whatsapp_number']) ? $_POST['whatsapp_number'] : '';
    $terms_and_conditions = isset($_POST['terms_and_conditions']) ? $_POST['terms_and_conditions'] : '';
    
    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // Insert product into cloth_details table
        $product_sql = "INSERT INTO cloth_details (
            seller_id, cloth_title, description, size, category, 
            rental_price, contact_number, whatsapp_number, terms_and_conditions
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $product_stmt = $conn->prepare($product_sql);
        $product_stmt->bind_param(
            "issssdsss", 
            $seller_id, $title, $description, $size, $category,
            $rental_price, $contact_number, $whatsapp_number, $terms_and_conditions
        );
        
        if (!$product_stmt->execute()) {
            throw new Exception("Error adding product: " . $product_stmt->error);
        }
        
        $product_id = $conn->insert_id;
        
        // Handle image upload if provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            // Get file information
            $file_name = $_FILES['image']['name'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Read image data
            $image_data = file_get_contents($file_tmp);
            
            // Insert image into cloth_images table
            $image_sql = "INSERT INTO cloth_images (cloth_id, image_data, image_type) VALUES (?, ?, ?)";
            $image_stmt = $conn->prepare($image_sql);
            $image_stmt->bind_param("iss", $product_id, $image_data, $file_type);
            
            if (!$image_stmt->execute()) {
                throw new Exception("Error uploading image: " . $image_stmt->error);
            }
            
            $image_stmt->close();
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Product added successfully',
            'product_id' => $product_id
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

// Function to update an existing product
function updateProduct($conn) {
    // Get the product ID
    $product_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if (!$product_id) {
        echo json_encode(['status' => 'error', 'message' => 'Product ID is required']);
        return;
    }
    
    // Get form data
    $title = isset($_POST['title']) ? $_POST['title'] : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $size = isset($_POST['size']) ? $_POST['size'] : '';
    $category = isset($_POST['category']) ? $_POST['category'] : '';
    $rental_price = isset($_POST['rental_price']) ? floatval($_POST['rental_price']) : 0;
    $contact_number = isset($_POST['contact_number']) ? $_POST['contact_number'] : '';
    $whatsapp_number = isset($_POST['whatsapp_number']) ? $_POST['whatsapp_number'] : '';
    $terms_and_conditions = isset($_POST['terms_and_conditions']) ? $_POST['terms_and_conditions'] : '';
    
    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // Update product in cloth_details table
        $product_sql = "UPDATE cloth_details SET 
            cloth_title = ?, 
            description = ?, 
            size = ?, 
            category = ?, 
            rental_price = ?, 
            contact_number = ?, 
            whatsapp_number = ?, 
            terms_and_conditions = ? 
            WHERE id = ?";
        
        $product_stmt = $conn->prepare($product_sql);
        $product_stmt->bind_param(
            "ssssdsssi", 
            $title, $description, $size, $category,
            $rental_price, $contact_number, $whatsapp_number, $terms_and_conditions,
            $product_id
        );
        
        if (!$product_stmt->execute()) {
            throw new Exception("Error updating product: " . $product_stmt->error);
        }
        
        // Handle image upload if provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            // Get file information
            $file_name = $_FILES['image']['name'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Read image data
            $image_data = file_get_contents($file_tmp);
            
            // Check if image exists for this product
            $check_sql = "SELECT COUNT(*) as count FROM cloth_images WHERE cloth_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $product_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $image_exists = $result->fetch_assoc()['count'] > 0;
            
            if ($image_exists) {
                // Update existing image
                $image_sql = "UPDATE cloth_images SET image_data = ?, image_type = ? WHERE cloth_id = ?";
            } else {
                // Insert new image
                $image_sql = "INSERT INTO cloth_images (cloth_id, image_data, image_type) VALUES (?, ?, ?)";
            }
            
            $image_stmt = $conn->prepare($image_sql);
            
            if ($image_exists) {
                $image_stmt->bind_param("ssi", $image_data, $file_type, $product_id);
            } else {
                $image_stmt->bind_param("iss", $product_id, $image_data, $file_type);
            }
            
            if (!$image_stmt->execute()) {
                throw new Exception("Error uploading image: " . $image_stmt->error);
            }
            
            $image_stmt->close();
            $check_stmt->close();
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Product updated successfully'
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

// Function to delete a product
function deleteProduct($conn) {
    // Get the product ID
    $product_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if (!$product_id) {
        echo json_encode(['status' => 'error', 'message' => 'Product ID is required']);
        return;
    }
    
    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // Delete images first (due to foreign key constraint)
        $image_sql = "DELETE FROM cloth_images WHERE cloth_id = ?";
        $image_stmt = $conn->prepare($image_sql);
        $image_stmt->bind_param("i", $product_id);
        $image_stmt->execute();
        
        // Delete product
        $product_sql = "DELETE FROM cloth_details WHERE id = ?";
        $product_stmt = $conn->prepare($product_sql);
        $product_stmt->bind_param("i", $product_id);
        
        if (!$product_stmt->execute()) {
            throw new Exception("Error deleting product: " . $product_stmt->error);
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
}

// Function to fetch products for a seller
function fetchSellerProducts($conn) {
    // Get seller ID from URL parameter
    $seller_id = isset($_GET['local_storage_id']) ? $_GET['local_storage_id'] : 
                (isset($_GET['seller_id']) ? $_GET['seller_id'] : 0);
    
    if (!$seller_id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Seller ID is required'
        ]);
        return;
    }
    
    try {
        // Fetch products for the seller
        $sql = "SELECT * FROM cloth_details WHERE seller_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        
        while ($row = $result->fetch_assoc()) {
            // Get image for the product
            $image_data = '';
            
            $image_sql = "SELECT image_data, image_type FROM cloth_images WHERE cloth_id = ? LIMIT 1";
            $image_stmt = $conn->prepare($image_sql);
            $image_stmt->bind_param("i", $row['id']);
            $image_stmt->execute();
            $image_result = $image_stmt->get_result();
            
            if ($image_row = $image_result->fetch_assoc()) {
                $image_data = 'data:image/' . $image_row['image_type'] . ';base64,' . base64_encode($image_row['image_data']);
            } else {
                $image_data = '../../assets/images/placeholder.png';
            }
            
            // Add product to array
            $products[] = [
                'id' => $row['id'],
                'title' => $row['cloth_title'],
                'description' => $row['description'],
                'size' => $row['size'],
                'category' => $row['category'],
                'rentalPrice' => $row['rental_price'],
                'contactNo' => $row['contact_number'],
                'whatsappNo' => $row['whatsapp_number'],
                'terms' => $row['terms_and_conditions'],
                'created_at' => $row['created_at'],
                'image' => $image_data,
                'seller_id' => $row['seller_id']
            ];
        }
        
        echo json_encode([
            'status' => 'success',
            'products' => $products,
            'count' => count($products)
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

// Function to fetch all products (primarily for buyers)
function fetchAllProducts($conn) {
    try {
        // Base query
        $sql = "SELECT cd.*, CONCAT(u.first_name, ' ', u.last_name) AS seller_name 
                FROM cloth_details cd 
                LEFT JOIN users u ON cd.seller_id = u.id 
                WHERE 1=1";
        
        // Apply filters if provided
        $params = [];
        $types = "";
        
        // Category filter
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $sql .= " AND cd.category = ?";
            $category = $_GET['category'];
            $params[] = $category;
            $types .= "s";
        }
        
        // Price range filter
        if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
            $sql .= " AND cd.rental_price >= ?";
            $min_price = floatval($_GET['min_price']);
            $params[] = $min_price;
            $types .= "d";
        }
        
        if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
            $sql .= " AND cd.rental_price <= ?";
            $max_price = floatval($_GET['max_price']);
            $params[] = $max_price;
            $types .= "d";
        }
        
        // Prepare and execute the query
        $stmt = $conn->prepare($sql);
        
        if (!empty($types) && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        
        while ($row = $result->fetch_assoc()) {
            // Get image for the product
            $image_data = '../../assets/images/placeholder.png';
            
            $image_sql = "SELECT image_data, image_type FROM cloth_images WHERE cloth_id = ? LIMIT 1";
            $image_stmt = $conn->prepare($image_sql);
            $image_stmt->bind_param("i", $row['id']);
            $image_stmt->execute();
            $image_result = $image_stmt->get_result();
            
            if ($image_row = $image_result->fetch_assoc()) {
                $image_data = 'data:image/' . $image_row['image_type'] . ';base64,' . base64_encode($image_row['image_data']);
            }
            
            // Add product to array
            $products[] = [
                'id' => $row['id'],
                'title' => $row['cloth_title'],
                'description' => $row['description'] ?? '',
                'size' => $row['size'] ?? '',
                'category' => $row['category'] ?? '',
                'rental_price' => (float)$row['rental_price'],
                'contact_number' => $row['contact_number'] ?? '',
                'whatsapp_number' => $row['whatsapp_number'] ?? '',
                'terms_and_conditions' => $row['terms_and_conditions'] ?? '',
                'created_at' => $row['created_at'],
                'seller_id' => $row['seller_id'],
                'seller_name' => $row['seller_name'] ?? 'ClothLoop Seller',
                'image' => $image_data
            ];
        }
        
        echo json_encode([
            'status' => 'success',
            'products' => $products,
            'count' => count($products)
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

// Close the database connection
$conn->close();
?> 