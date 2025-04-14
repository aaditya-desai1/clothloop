<?php
session_start();
require_once '../config/db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log function for debugging
function debug_log($message, $data = null) {
    $log_file = '../error_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[{$timestamp}] {$message}";
    
    if ($data !== null) {
        $log_message .= ": " . print_r($data, true);
    }
    
    file_put_contents($log_file, $log_message . PHP_EOL, FILE_APPEND);
}

debug_log("Starting cloth upload process");

// Check if the user is logged in and is a seller
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'seller') {
    debug_log("Unauthorized access attempt", $_SESSION);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

debug_log("Seller authenticated: " . $_SESSION['user_email']);
$response = ['status' => 'error', 'message' => 'Unknown error occurred'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    debug_log("POST data received", $_POST);
    
    // Get seller email from session
    $seller_email = $_SESSION['user_email'];
    
    // Get form data
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $size = $_POST['size'] ?? '';
    $color = $_POST['color'] ?? '';
    $price = $_POST['price'] ?? 0;
    $contact = $_POST['contact'] ?? '';
    $whatsapp = $_POST['whatsapp'] ?? '';
    $shop_name = $_POST['shopName'] ?? '';
    $terms = $_POST['terms'] ?? '';
    
    debug_log("Form data parsed", [
        'title' => $title,
        'size' => $size,
        'color' => $color,
        'price' => $price
    ]);
    
    // Handle location/address - only one should be provided
    $address = '';
    $location_coordinates = null;
    $use_address_input = isset($_POST['use_address_input']) && $_POST['use_address_input'] === 'true';
    
    debug_log("Location method", [
        'use_address_input' => $use_address_input,
        'address_post' => $_POST['address'] ?? 'not set',
        'location_post' => $_POST['location'] ?? 'not set'
    ]);
    
    if ($use_address_input) {
        // User is providing a manual address
        $address = $_POST['address'] ?? '';
        debug_log("Using manual address", $address);
    } else {
        // User is providing geolocation coordinates
        $location = $_POST['location'] ?? '';
        debug_log("Using coordinates", $location);
        
        if (!empty($location)) {
            // Parse latitude and longitude
            $coordinates = explode(',', $location);
            if (count($coordinates) === 2) {
                $latitude = trim($coordinates[0]);
                $longitude = trim($coordinates[1]);
                $location_coordinates = $location;
                debug_log("Parsed coordinates", [$latitude, $longitude]);
                
                // For debugging, skip API call and use coordinates as address
                $address = "Address from coordinates: {$latitude}, {$longitude}";
                debug_log("Using coordinates as address", $address);
                
                /* Commented out for debugging
                // Call Google Maps Geocoding API to get the address
                $apiKey = 'YOUR_GOOGLE_MAPS_API_KEY'; // Replace with your actual API key
                $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key={$apiKey}";
                
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response_json = curl_exec($ch);
                curl_close($ch);
                
                $geocode_data = json_decode($response_json, true);
                
                if ($geocode_data['status'] === 'OK' && !empty($geocode_data['results'])) {
                    $address = $geocode_data['results'][0]['formatted_address'];
                } else {
                    debug_log("Geocoding failed", $geocode_data);
                    echo json_encode(['status' => 'error', 'message' => 'Failed to geocode coordinates']);
                    exit;
                }
                */
            }
        }
    }
    
    // Validate required fields
    if (empty($title) || empty($description) || empty($size) || empty($color) || 
        empty($price) || empty($contact) || empty($whatsapp) || empty($shop_name) || empty($terms) || empty($address)) {
        
        $missing_fields = [];
        if (empty($title)) $missing_fields[] = 'title';
        if (empty($description)) $missing_fields[] = 'description';
        if (empty($size)) $missing_fields[] = 'size';
        if (empty($color)) $missing_fields[] = 'color';
        if (empty($price)) $missing_fields[] = 'price';
        if (empty($contact)) $missing_fields[] = 'contact';
        if (empty($whatsapp)) $missing_fields[] = 'whatsapp';
        if (empty($shop_name)) $missing_fields[] = 'shop_name';
        if (empty($terms)) $missing_fields[] = 'terms';
        if (empty($address)) $missing_fields[] = 'address';
        
        debug_log("Missing required fields", $missing_fields);
        echo json_encode(['status' => 'error', 'message' => 'All fields are required. Missing: ' . implode(', ', $missing_fields)]);
        exit;
    }
    
    // Handle image uploads
    $images = [];
    debug_log("Files data", isset($_FILES) ? $_FILES : 'no files');
    
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $upload_dir = '../../uploads/clothes/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            debug_log("Creating upload directory", $upload_dir);
            if (!mkdir($upload_dir, 0777, true)) {
                debug_log("Failed to create upload directory");
                echo json_encode(['status' => 'error', 'message' => 'Failed to create upload directory']);
                exit;
            }
        }
        
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['images']['name'][$key];
            $file_tmp = $_FILES['images']['tmp_name'][$key];
            
            // Generate unique filename
            $unique_filename = uniqid() . '_' . $file_name;
            $target_file = $upload_dir . $unique_filename;
            
            debug_log("Uploading file", [
                'original' => $file_name,
                'tmp_name' => $file_tmp,
                'target' => $target_file
            ]);
            
            if (move_uploaded_file($file_tmp, $target_file)) {
                $images[] = 'uploads/clothes/' . $unique_filename;
                debug_log("File uploaded successfully", $unique_filename);
            } else {
                debug_log("File upload failed", [
                    'file' => $file_name,
                    'error' => $_FILES['images']['error'][$key]
                ]);
            }
        }
    }
    
    if (empty($images)) {
        debug_log("No images uploaded");
        echo json_encode(['status' => 'error', 'message' => 'At least one image is required']);
        exit;
    }
    
    // Convert images array to JSON string
    $images_json = json_encode($images);
    debug_log("Images JSON", $images_json);
    
    try {
        // Insert data into database
        debug_log("Preparing SQL statement");
        $sql = "INSERT INTO clothes (seller_email, title, description, size, color, price, contact, whatsapp, shop_name, address, location_coordinates, use_address_input, terms, images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            debug_log("SQL prepare failed", $conn->error);
            throw new Exception("SQL prepare failed: " . $conn->error);
        }
        
        // Convert boolean to integer for MySQL
        $use_address_input_int = $use_address_input ? 1 : 0;
        debug_log("Binding parameters", [
            'types' => "sssssdsssssisd", // Note: changed 'b' to 'i' for boolean as integer
            'use_address_input' => $use_address_input_int
        ]);
        
        // Make sure we handle null values properly
        if ($location_coordinates === null) {
            $location_coordinates = ''; // Convert null to empty string for binding
        }
        
        $stmt->bind_param("sssssdsssssisd", 
            $seller_email, 
            $title, 
            $description, 
            $size, 
            $color, 
            $price, 
            $contact, 
            $whatsapp, 
            $shop_name, 
            $address, 
            $location_coordinates, 
            $use_address_input_int, // Note: using integer (i) instead of boolean (b)
            $terms, 
            $images_json
        );
        
        debug_log("Executing statement");
        if ($stmt->execute()) {
            debug_log("Cloth added successfully", $stmt->insert_id);
            $response = ['status' => 'success', 'message' => 'Cloth added successfully'];
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

debug_log("Response", $response);
echo json_encode($response);
?> 