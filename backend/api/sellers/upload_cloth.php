<?php
session_start();
require_once '../../config/db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log function for debugging
function debug_log($message, $data = null) {
    // Create a logs directory if it doesn't exist
    $logs_dir = '../../logs';
    if (!file_exists($logs_dir)) {
        mkdir($logs_dir, 0777, true);
    }
    
    $log_file = '../../logs/debug_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[{$timestamp}] {$message}";
    
    if ($data !== null) {
        $log_message .= ": " . print_r($data, true);
    }
    
    file_put_contents($log_file, $log_message . PHP_EOL, FILE_APPEND);
}

// Check database connection to ensure it's working
if ($conn->connect_error) {
    debug_log("Database connection failed", $conn->connect_error);
    echo json_encode(['status' => 'error', 'message' => 'Database connection error: ' . $conn->connect_error]);
    exit;
}

debug_log("Database connection successful");
debug_log("Starting cloth upload process");

// Debug POST data
debug_log("POST data", $_POST);
debug_log("FILES data", isset($_FILES) ? $_FILES : 'No files uploaded');

$response = ['status' => 'error', 'message' => 'Unknown error occurred'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    debug_log("POST request received");
    
    // Get seller ID from various sources
    $seller_id = null;
    
    // First try from POST data
    if (isset($_POST['seller_id'])) {
        $seller_id = $_POST['seller_id'];
        debug_log("Seller ID from POST", $seller_id);
    } 
    // Then from session
    else if (isset($_SESSION['user_id'])) {
        $seller_id = $_SESSION['user_id'];
        debug_log("Seller ID from session", $seller_id);
    }
    
    // If still no seller ID, use a fallback for testing
    if (!$seller_id) {
        $seller_id = 1; // Fallback for testing
        debug_log("Using fallback seller ID for testing", $seller_id);
    }
    
    // Get form data
    $cloth_title = $_POST['clothTitle'] ?? '';
    $description = $_POST['description'] ?? '';
    $size = $_POST['size'] ?? '';
    $category = $_POST['category'] ?? '';
    $rental_price = $_POST['rentalPrice'] ?? 0;
    $contact_no = $_POST['contactNo'] ?? '';
    $whatsapp_no = $_POST['whatsappNo'] ?? '';
    $shop_address = $_POST['shopAddress'] ?? '';
    $terms_conditions = $_POST['terms'] ?? '';
    
    debug_log("Form data parsed", [
        'cloth_title' => $cloth_title,
        'size' => $size,
        'category' => $category,
        'rental_price' => $rental_price
    ]);
    
    // Validate required fields
    if (empty($cloth_title) || empty($description) || empty($size) || empty($category) || 
        empty($rental_price) || empty($contact_no) || empty($whatsapp_no) || empty($terms_conditions)) {
        
        $missing_fields = [];
        if (empty($cloth_title)) $missing_fields[] = 'clothTitle';
        if (empty($description)) $missing_fields[] = 'description';
        if (empty($size)) $missing_fields[] = 'size';
        if (empty($category)) $missing_fields[] = 'category';
        if (empty($rental_price)) $missing_fields[] = 'rentalPrice';
        if (empty($contact_no)) $missing_fields[] = 'contactNo';
        if (empty($whatsapp_no)) $missing_fields[] = 'whatsappNo';
        if (empty($terms_conditions)) $missing_fields[] = 'terms';
        
        debug_log("Missing required fields", $missing_fields);
        echo json_encode(['status' => 'error', 'message' => 'All fields are required. Missing: ' . implode(', ', $missing_fields)]);
        exit;
    }
    
    try {
        // Handle image upload
        $image_data = null;
        $image_type = null;
        
        if (isset($_FILES['clothImage']) && $_FILES['clothImage']['error'] == 0) {
            debug_log("Processing image upload", $_FILES['clothImage']);
            
            // Get image data
            $image_tmp_name = $_FILES['clothImage']['tmp_name'];
            $image_type = $_FILES['clothImage']['type'];
            
            // Validate image type
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!in_array($image_type, $allowed_types)) {
                debug_log("Invalid image type", $image_type);
                throw new Exception('Invalid image type. Only JPEG, PNG and GIF are allowed.');
            }
            
            // Read image data directly
            $image_data = file_get_contents($image_tmp_name);
            
            if (!$image_data) {
                debug_log("Failed to read image data");
                throw new Exception('Failed to process image');
            }
            
            // Ensure we're storing binary data properly
            debug_log("Image loaded successfully", [
                'size' => strlen($image_data),
                'type' => $image_type
            ]);
        } else {
            debug_log("No new image provided or error in upload", isset($_FILES['clothImage']) ? $_FILES['clothImage']['error'] : 'No image uploaded');
            
            // If this is a new item (not an update), image is required
            if (!isset($_POST['id'])) {
                throw new Exception('Image upload is required for new items');
            }
        }
        
        // Check if this is an update or a new insertion
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $cloth_id = $_POST['id'];
            debug_log("Updating existing cloth", $cloth_id);
            
            // If image data is provided, update it too
            if ($image_data && $image_type) {
                $sql = "UPDATE cloth_details SET 
                        cloth_title = ?, description = ?, size = ?, category = ?, 
                        rental_price = ?, contact_number = ?, whatsapp_number = ?, 
                        shop_address = ?, terms_and_conditions = ?, cloth_photo = ?, photo_type = ? 
                        WHERE id = ? AND seller_id = ?";
                        
                $stmt = $conn->prepare($sql);
                
                if ($stmt === false) {
                    debug_log("SQL prepare failed", $conn->error);
                    throw new Exception("SQL prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("ssssdsssssis", 
                    $cloth_title, 
                    $description, 
                    $size, 
                    $category, 
                    $rental_price, 
                    $contact_no, 
                    $whatsapp_no,
                    $shop_address,
                    $terms_conditions, 
                    $image_data,
                    $image_type,
                    $cloth_id,
                    $seller_id
                );
            } else {
                // Update without changing the image
                $sql = "UPDATE cloth_details SET 
                        cloth_title = ?, description = ?, size = ?, category = ?, 
                        rental_price = ?, contact_number = ?, whatsapp_number = ?, 
                        shop_address = ?, terms_and_conditions = ? 
                        WHERE id = ? AND seller_id = ?";
                        
                $stmt = $conn->prepare($sql);
                
                if ($stmt === false) {
                    debug_log("SQL prepare failed", $conn->error);
                    throw new Exception("SQL prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("ssssdsssis", 
                    $cloth_title, 
                    $description, 
                    $size, 
                    $category, 
                    $rental_price, 
                    $contact_no, 
                    $whatsapp_no,
                    $shop_address,
                    $terms_conditions, 
                    $cloth_id,
                    $seller_id
                );
            }
            
            debug_log("Executing update statement");
            if ($stmt->execute()) {
                debug_log("Cloth updated successfully", $cloth_id);
                $response = [
                    'status' => 'success', 
                    'message' => 'Cloth updated successfully',
                    'cloth_id' => $cloth_id
                ];
            } else {
                debug_log("Database error on update", $stmt->error);
                $response = ['status' => 'error', 'message' => 'Database error: ' . $stmt->error];
            }
        } else {
            // Insert new cloth item
            debug_log("Inserting new cloth item");
            
            // Verify image data is present for new insertions
            if (!$image_data || !$image_type) {
                debug_log("Missing image data for new insertion");
                throw new Exception('Image is required for new cloth items');
            }
            
            $sql = "INSERT INTO cloth_details (seller_id, cloth_title, description, size, category, 
                    rental_price, contact_number, whatsapp_number, shop_address, terms_and_conditions, cloth_photo, photo_type) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            
            if ($stmt === false) {
                debug_log("SQL prepare failed", $conn->error);
                throw new Exception("SQL prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("issssdssssss", 
                $seller_id, 
                $cloth_title, 
                $description, 
                $size, 
                $category, 
                $rental_price, 
                $contact_no, 
                $whatsapp_no,
                $shop_address,
                $terms_conditions, 
                $image_data,
                $image_type
            );
            
            debug_log("Executing insert statement");
            if ($stmt->execute()) {
                $cloth_id = $stmt->insert_id;
                debug_log("Cloth added successfully", $cloth_id);
                $response = [
                    'status' => 'success', 
                    'message' => 'Cloth added successfully',
                    'cloth_id' => $cloth_id
                ];
            } else {
                debug_log("Database error on insert", $stmt->error);
                $response = ['status' => 'error', 'message' => 'Database error: ' . $stmt->error];
            }
        }
        
        $stmt->close();
    } catch (Exception $e) {
        debug_log("Exception caught", $e->getMessage());
        $response = ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()];
    }
} else {
    debug_log("Invalid request method", $_SERVER['REQUEST_METHOD']);
    $response = ['status' => 'error', 'message' => 'Invalid request method'];
}

// Set content type to JSON
header('Content-Type: application/json');
debug_log("Response", $response);
echo json_encode($response);
?> 