<?php
/**
 * CORS Test Endpoint
 * 
 * This is a simple endpoint to test if CORS headers are working correctly
 */

// Allow CORS from any origin for Vercel frontend
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Max-Age: 3600");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Set content type header
header('Content-Type: application/json');

// Return basic test response
echo json_encode([
    'status' => 'success',
    'message' => 'CORS headers test successful',
    'details' => [
        'cors_enabled' => true,
        'request_method' => $_SERVER['REQUEST_METHOD'],
        'request_headers' => getallheaders(),
        'origin' => isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'Not specified',
        'timestamp' => date('Y-m-d H:i:s')
    ]
]); 