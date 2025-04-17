<?php
// API Endpoint: Check Session Status
// This endpoint returns information about the current user session

// Set the response header to JSON
header('Content-Type: application/json');

// Debug information
$debug = [];

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug info about session
$debug['session_id'] = session_id();
$debug['session_status'] = session_status();
$debug['session_data'] = $_SESSION;

// Check if user is authenticated
$isAuthenticated = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;

// Prepare response
$response = [
    'status' => 'success',
    'logged_in' => $isAuthenticated,
    'user_type' => $userType,
    'user_id' => $userId,
    'username' => $userName,
    'debug' => $debug
];

// Return JSON response
echo json_encode($response);
?> 