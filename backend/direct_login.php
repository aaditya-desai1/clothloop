<?php
// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Start session
session_start();

// Get POST data
$raw_data = file_get_contents('php://input');
$data = json_decode($raw_data, true);

// Log the received data for debugging
error_log("Received login attempt: " . print_r($data, true));

// Get credentials from request
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

// Super simple direct login for users
if ($email === 'nishidh@gmail.com' && $password === 'password') {
    $_SESSION['user_id'] = 1;
    $_SESSION['email'] = 'nishidh@gmail.com';
    $_SESSION['username'] = 'Nishidh';
    $_SESSION['user_type'] = 'buyer';
    
    echo json_encode([
        'success' => true,
        'user_type' => 'buyer',
        'message' => 'Login successful'
    ]);
} else if ($email === 'seller@example.com' && $password === 'seller123') {
    $_SESSION['user_id'] = 2;
    $_SESSION['email'] = 'seller@example.com';
    $_SESSION['username'] = 'Seller';
    $_SESSION['user_type'] = 'seller';
    
    echo json_encode([
        'success' => true,
        'user_type' => 'seller',
        'message' => 'Login successful'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid email or password. Try nishidh@gmail.com/password or seller@example.com/seller123'
    ]);
}
?> 