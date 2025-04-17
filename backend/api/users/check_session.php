<?php
// API Endpoint: Check Session Status
// This endpoint returns information about the current user session

// Set the response header to JSON
header('Content-Type: application/json');

// Include required files
require_once '../../utils/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is authenticated
$isAuthenticated = isAuthenticated();

// Prepare response
$response = [
    'status' => 'success',
    'logged_in' => $isAuthenticated,
    'user_type' => getUserType(),
    'user_id' => getUserId(),
    'username' => getUsername()
];

// Return JSON response
echo json_encode($response);
?> 