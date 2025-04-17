<?php
// API Endpoint: Get Seller Profile
// This endpoint returns the profile information of the current authenticated seller

// Set the response header to JSON
header('Content-Type: application/json');

// Include the database connection and utilities
require_once '../../config/database.php';
require_once '../../utils/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// For the purpose of debugging/development
$debug_mode = true;
$use_sample_data = $debug_mode;

// Check if user is authenticated and is a seller
if (!isAuthenticated() || !isSeller()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access. Please log in as a seller.'
    ]);
    exit;
}

// Get seller ID from session
$seller_id = $_SESSION['user_id'];

try {
    $db = getDbConnection();
    
    // Get seller profile information
    $stmt = $db->prepare("
        SELECT 
            u.name, 
            u.email, 
            u.phone_no,
            u.created_at,
            s.shop_name, 
            s.shop_address, 
            s.shop_bio, 
            s.shop_logo
        FROM 
            users u
        JOIN 
            sellers s ON u.id = s.user_id
        WHERE 
            u.id = :seller_id
    ");
    $stmt->bindParam(':seller_id', $seller_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        // No profile found, use sample data if in debug mode
        if ($use_sample_data) {
            $sample_profile = [
                'name' => 'Sample Seller',
                'email' => 'seller@example.com',
                'phone_no' => '1234567890',
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
                'shop_name' => 'Sample Shop',
                'shop_address' => '123 Sample Street, Sample City',
                'shop_bio' => 'This is a sample shop for testing the ClothLoop platform.',
                'shop_logo' => '../../assets/images/shop_logo.png'
            ];
            
            echo json_encode([
                'status' => 'success',
                'profile' => $sample_profile
            ]);
            exit;
        }
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Seller profile not found.'
        ]);
        exit;
    }
    
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If shop logo exists, generate the full URL
    if (!empty($profile['shop_logo'])) {
        $profile['shop_logo'] = '../../../backend/' . $profile['shop_logo'];
    }
    
    // Return the profile information
    echo json_encode([
        'status' => 'success',
        'profile' => $profile
    ]);
    
} catch (PDOException $e) {
    // Log the error
    error_log("Database error: " . $e->getMessage());
    
    // If in debug mode, return sample data
    if ($use_sample_data) {
        $sample_profile = [
            'name' => 'Sample Seller',
            'email' => 'seller@example.com',
            'phone_no' => '1234567890',
            'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
            'shop_name' => 'Sample Shop',
            'shop_address' => '123 Sample Street, Sample City',
            'shop_bio' => 'This is a sample shop for testing the ClothLoop platform.',
            'shop_logo' => '../../assets/images/shop_logo.png'
        ];
        
        echo json_encode([
            'status' => 'success',
            'profile' => $sample_profile
        ]);
        exit;
    }
    
    // Return error message
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred. Please try again later.'
    ]);
}
?> 