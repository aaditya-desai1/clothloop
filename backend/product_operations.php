<?php
// Database connection
$db_host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "clothloop";

// Create connection
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => "Connection failed: " . $conn->connect_error]));
}

// Set headers for JSON response
header('Content-Type: application/json');

// Get the request method
$request_method = $_SERVER['REQUEST_METHOD'];

// Get the operation type
$operation = isset($_GET['operation']) ? $_GET['operation'] : '';

// Handle different operations based on request method
switch ($request_method) {
    case 'GET':
        // Fetch products
        if ($operation === 'fetch') {
            fetchProducts();
        }
        break;
    
    case 'POST':
        // Add/update product
        if ($operation === 'add') {
            addProduct();
        } elseif ($operation === 'update') {
            updateProduct();
        } elseif ($operation === 'delete') {
            deleteProduct();
        }
        break;
    
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        break;
}

// Function to fetch all products
function fetchProducts() {
    global $conn;
    
    // Get seller ID from session (would need to be implemented based on your login system)
    $seller_id = 1; // Placeholder - replace with actual seller ID from session
    
    $sql = "SELECT * FROM cloth_details WHERE seller_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Get the image
        $image_path = "../Image/placeholder.jpg"; // Default image path
        
        // Check if we have an image record
        $img_sql = "SELECT image_path FROM cloth_images WHERE cloth_id = ? LIMIT 1";
        $img_stmt = $conn->prepare($img_sql);
        $img_stmt->bind_param("i", $row['id']);
        $img_stmt->execute();
        $img_result = $img_stmt->get_result();
        
        if ($img_row = $img_result->fetch_assoc()) {
            $image_path = $img_row['image_path'];
        }
        
        // Format the product data
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
            'image' => $image_path
        ];
        
        $img_stmt->close();
    }
    
    $stmt->close();
    echo json_encode(['status' => 'success', 'products' => $products]);
}

// Function to add a new product
function addProduct() {
    global $conn;
    
    // Get seller ID from session (would need to be implemented based on your login system)
    $seller_id = 1; // Placeholder - replace with actual seller ID from session
    
    // Get JSON data from request
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data received']);
        return;
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Prepare SQL statement for cloth_details table
        $sql = "INSERT INTO cloth_details (seller_id, cloth_title, description, size, category, rental_price, contact_number, whatsapp_number, terms_and_conditions) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssdsss", 
            $seller_id, 
            $data['title'], 
            $data['description'], 
            $data['size'], 
            $data['category'], 
            $data['rentalPrice'], 
            $data['contactNo'], 
            $data['whatsappNo'], 
            $data['terms']
        );
        
        $stmt->execute();
        $cloth_id = $conn->insert_id;
        
        // Handle image upload if included
        if (!empty($data['image']) && $data['image'] !== '../Image/placeholder.jpg') {
            // Extract base64 data from the provided image string
            $image_parts = explode(";base64,", $data['image']);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            
            // Generate a unique filename
            $filename = 'cloth_' . $cloth_id . '_' . uniqid() . '.' . $image_type;
            $upload_dir = '../Image/cloth_images/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_path = $upload_dir . $filename;
            
            // Save the image file
            file_put_contents($file_path, $image_base64);
            
            // Insert image record in cloth_images table
            $img_sql = "INSERT INTO cloth_images (cloth_id, image_path) VALUES (?, ?)";
            $img_stmt = $conn->prepare($img_sql);
            $img_stmt->bind_param("is", $cloth_id, $file_path);
            $img_stmt->execute();
            $img_stmt->close();
        }
        
        $stmt->close();
        $conn->commit();
        
        echo json_encode(['status' => 'success', 'message' => 'Product added successfully', 'id' => $cloth_id]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Error adding product: ' . $e->getMessage()]);
    }
}

// Function to update an existing product
function updateProduct() {
    global $conn;
    
    // Get JSON data from request
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data received']);
        return;
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Update cloth_details table
        $sql = "UPDATE cloth_details 
                SET cloth_title = ?, description = ?, size = ?, category = ?, 
                    rental_price = ?, contact_number = ?, whatsapp_number = ?, terms_and_conditions = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdsssi", 
            $data['title'], 
            $data['description'], 
            $data['size'], 
            $data['category'], 
            $data['rentalPrice'], 
            $data['contactNo'], 
            $data['whatsappNo'], 
            $data['terms'],
            $data['id']
        );
        
        $stmt->execute();
        
        // Handle image update if included
        if (!empty($data['image']) && strpos($data['image'], 'data:image') === 0) {
            // Extract base64 data from the provided image string
            $image_parts = explode(";base64,", $data['image']);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            
            // Generate a unique filename
            $filename = 'cloth_' . $data['id'] . '_' . uniqid() . '.' . $image_type;
            $upload_dir = '../Image/cloth_images/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_path = $upload_dir . $filename;
            
            // Save the image file
            file_put_contents($file_path, $image_base64);
            
            // Check if there's already an image record for this cloth
            $check_sql = "SELECT id FROM cloth_images WHERE cloth_id = ? LIMIT 1";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $data['id']);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                // Update existing image record
                $img_sql = "UPDATE cloth_images SET image_path = ? WHERE cloth_id = ?";
                $img_stmt = $conn->prepare($img_sql);
                $img_stmt->bind_param("si", $file_path, $data['id']);
            } else {
                // Insert new image record
                $img_sql = "INSERT INTO cloth_images (cloth_id, image_path) VALUES (?, ?)";
                $img_stmt = $conn->prepare($img_sql);
                $img_stmt->bind_param("is", $data['id'], $file_path);
            }
            
            $img_stmt->execute();
            $img_stmt->close();
            $check_stmt->close();
        }
        
        $stmt->close();
        $conn->commit();
        
        echo json_encode(['status' => 'success', 'message' => 'Product updated successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Error updating product: ' . $e->getMessage()]);
    }
}

// Function to delete a product
function deleteProduct() {
    global $conn;
    
    // Get JSON data from request
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data received']);
        return;
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Delete related images first
        $img_sql = "DELETE FROM cloth_images WHERE cloth_id = ?";
        $img_stmt = $conn->prepare($img_sql);
        $img_stmt->bind_param("i", $data['id']);
        $img_stmt->execute();
        $img_stmt->close();
        
        // Delete the product
        $sql = "DELETE FROM cloth_details WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $data['id']);
        $stmt->execute();
        $stmt->close();
        
        $conn->commit();
        
        echo json_encode(['status' => 'success', 'message' => 'Product deleted successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Error deleting product: ' . $e->getMessage()]);
    }
}

// Close connection
$conn->close();
?> 