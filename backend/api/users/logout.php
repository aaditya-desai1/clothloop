<?php
// API Endpoint: User Logout
// This endpoint destroys the user session and logs out the user

// Set the response header to JSON
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy the session
session_destroy();

// Return success response
echo json_encode([
    'status' => 'success',
    'message' => 'Logged out successfully'
]);
?> 