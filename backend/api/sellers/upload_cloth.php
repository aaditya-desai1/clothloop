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

// Check if the user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    debug_log("Unauthorized access attempt", $_SESSION);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please login as a seller.']);
    exit;
}

debug_log("Seller authenticated: " . $_SESSION['user_id']);
$response = ['status' => 'error', 'message' => 'Unknown error occurred'];

// Debug session and POST data
debug_log("Script started - SESSION data", $_SESSION);
debug_log("POST data", $_POST);
debug_log("FILES data", isset($_FILES) ? $_FILES : 'No files uploaded');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    debug_log("POST request received");
    
    // Get seller ID from session
    $seller_id = $_SESSION['user_id'];
    
    // Get form data
    $cloth_title = $_POST['clothTitle'] ?? '';
    $description = $_POST['description'] ?? '';
    $size = $_POST['size'] ?? '';
    $category = $_POST['category'] ?? '';
    $rental_price = $_POST['rentalPrice'] ?? 0;
    $contact_no = $_POST['contactNo'] ?? '';
    $whatsapp_no = $_POST['whatsappNo'] ?? '';
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
    
    // Handle image upload
    if (!isset($_FILES['clothImage']) || $_FILES['clothImage']['error'] != 0) {
        debug_log("Image upload error", isset($_FILES['clothImage']) ? $_FILES['clothImage']['error'] : 'No image uploaded');
        echo json_encode(['status' => 'error', 'message' => 'Image upload is required']);
        exit;
    }
    
    debug_log("Image file information", $_FILES['clothImage']);
    
    // Get image data
    $image_tmp_name = $_FILES['clothImage']['tmp_name'];
    $image_type = $_FILES['clothImage']['type'];
    $image_data = file_get_contents($image_tmp_name);
    
    if (!$image_data) {
        debug_log("Failed to read image data");
        echo json_encode(['status' => 'error', 'message' => 'Failed to process image']);
        exit;
    }
    
    try {
        // Insert data into database
        debug_log("Preparing SQL statement");
        $sql = "INSERT INTO cloth_details (seller_id, cloth_title, description, size, category, rental_price, 
                                         contact_number, whatsapp_number, terms_and_conditions, cloth_photo, photo_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        debug_log("SQL statement", $sql);
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            debug_log("SQL prepare failed", $conn->error);
            throw new Exception("SQL prepare failed: " . $conn->error);
        }
        
        debug_log("Binding parameters");
        debug_log("Parameter types", "issssdsssss");
        debug_log("Parameter values", [
            'seller_id' => $seller_id,
            'cloth_title' => $cloth_title,
            'description' => mb_substr($description, 0, 50) . "...",
            'size' => $size,
            'category' => $category,
            'rental_price' => $rental_price,
            'contact_number' => $contact_no,
            'whatsapp_number' => $whatsapp_no,
            'terms_and_conditions' => mb_substr($terms_conditions, 0, 50) . "...",
            'photo_type' => $image_type
        ]);
        
        $stmt->bind_param("issssdsssss", 
            $seller_id, 
            $cloth_title, 
            $description, 
            $size, 
            $category, 
            $rental_price, 
            $contact_no, 
            $whatsapp_no, 
            $terms_conditions, 
            $image_data,
            $image_type
        );
        
        debug_log("Executing statement");
        if ($stmt->execute()) {
            $cloth_id = $stmt->insert_id;
            debug_log("Cloth added successfully", $cloth_id);
            $response = [
                'status' => 'success', 
                'message' => 'Cloth added successfully',
                'cloth_id' => $cloth_id
            ];
        } else {
            debug_log("Database error", $stmt->error);
            $response = ['status' => 'error', 'message' => 'Database error: ' . $stmt->error];
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