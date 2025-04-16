<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once '../../config/db_connect.php';

session_start();

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

// Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone']);
    $userType = $_POST['user_type']; // 'buyer' or 'seller'
    
    // Basic validation
    if (empty($username)) {
        $response['errors'][] = 'Username is required';
    }
    
    if (empty($email)) {
        $response['errors'][] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['errors'][] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $response['errors'][] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $response['errors'][] = 'Password must be at least 8 characters';
    }
    
    if (empty($phone)) {
        $response['errors'][] = 'Phone number is required';
    } elseif (!preg_match('/^\d{10}$/', $phone)) {
        $response['errors'][] = 'Invalid phone number format (must be 10 digits)';
    }
    
    // Check if email already exists in either buyers or sellers table
    $checkBuyerEmail = $conn->prepare("SELECT id FROM buyers WHERE email = ?");
    $checkBuyerEmail->bind_param("s", $email);
    $checkBuyerEmail->execute();
    $buyerResult = $checkBuyerEmail->get_result();
    
    $checkSellerEmail = $conn->prepare("SELECT id FROM sellers WHERE email = ?");
    $checkSellerEmail->bind_param("s", $email);
    $checkSellerEmail->execute();
    $sellerResult = $checkSellerEmail->get_result();
    
    if ($buyerResult->num_rows > 0 || $sellerResult->num_rows > 0) {
        $response['errors'][] = 'Email already exists. Please use a different email or login.';
    }
    
    // Additional validation for seller account
    if ($userType === 'seller') {
        $shopName = trim($_POST['shop_name']);
        $shopAddress = trim($_POST['shop_address']);
        $shopLatitude = $_POST['shop_latitude'];
        $shopLongitude = $_POST['shop_longitude'];
        $shopBio = trim($_POST['shop_bio']);
        
        // Validate required seller fields
        if (empty($shopName)) {
            $response['errors'][] = 'Shop name is required';
        }
        
        if (empty($shopAddress)) {
            $response['errors'][] = 'Shop address is required';
        }
        
        if (empty($shopLatitude) || empty($shopLongitude)) {
            $response['errors'][] = 'Shop location is required';
        }
        
        if (empty($shopBio)) {
            $response['errors'][] = 'Shop bio is required';
        }
        
        // Handle shop logo upload
        $shopLogoPath = null;
        if (isset($_FILES['shop_logo']) && $_FILES['shop_logo']['error'] === UPLOAD_ERR_OK) {
            $fileInfo = pathinfo($_FILES['shop_logo']['name']);
            $fileExtension = strtolower($fileInfo['extension']);
            
            // Validate file extension
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($fileExtension, $allowedExtensions)) {
                $response['errors'][] = 'Invalid file format. Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.';
            }
            
            // Validate file size (max 2MB)
            if ($_FILES['shop_logo']['size'] > 2 * 1024 * 1024) {
                $response['errors'][] = 'File size exceeds the maximum limit of 2MB.';
            }
            
            // Process file upload if no errors
            if (empty($response['errors'])) {
                // Create upload directory if it doesn't exist
                $uploadDir = '../../../frontend/assets/images/shop_logos/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Generate unique filename
                $newFileName = uniqid('shop_logo_') . '.' . $fileExtension;
                $shopLogoPath = 'frontend/assets/images/shop_logos/' . $newFileName;
                $uploadPath = $uploadDir . $newFileName;
                
                // Move uploaded file
                if (!move_uploaded_file($_FILES['shop_logo']['tmp_name'], $uploadPath)) {
                    $response['errors'][] = 'Failed to upload logo. Please try again.';
                }
            }
        } else {
            $response['errors'][] = 'Shop logo is required';
        }
    }
    
    // Process registration if no errors
    if (empty($response['errors'])) {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            if ($userType === 'buyer') {
                // Insert into buyers table
                $insertBuyer = $conn->prepare("INSERT INTO buyers (name, email, password, phone_no) VALUES (?, ?, ?, ?)");
                $insertBuyer->bind_param("ssss", $username, $email, $hashedPassword, $phone);
                $insertBuyer->execute();
                
                if ($insertBuyer->affected_rows <= 0) {
                    throw new Exception("Failed to create buyer account");
                }
                
                $response['success'] = true;
                $response['message'] = 'Registration successful! You can now login as a buyer.';
            } else {
                // Insert into sellers table
                $insertSeller = $conn->prepare("INSERT INTO sellers (name, email, password, phone_no, shop_name, shop_address, shop_location, shop_logo, shop_bio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $shopLocation = $shopLatitude . ',' . $shopLongitude; // Combine lat/long for storage
                $insertSeller->bind_param("sssssssss", $username, $email, $hashedPassword, $phone, $shopName, $shopAddress, $shopLocation, $shopLogoPath, $shopBio);
                $insertSeller->execute();
                
                if ($insertSeller->affected_rows <= 0) {
                    throw new Exception("Failed to create seller account");
                }
                
                $response['success'] = true;
                $response['message'] = 'Registration successful! You can now login as a seller.';
            }
            
            // Commit transaction
            $conn->commit();
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            
            // Delete uploaded file if exists
            if ($userType === 'seller' && $shopLogoPath && file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            
            $response['errors'][] = 'Registration failed: ' . $e->getMessage();
        }
    }
} else {
    // If not a POST request, redirect to registration page
    header("Location: ../Account/register.html");
    exit();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 