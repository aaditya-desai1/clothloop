<?php
/**
 * CORS Controller
 * Handles CORS preflight requests and provides CORS headers for all API requests
 */

// Set CORS headers to allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Max-Age: 3600");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * Apply CORS headers to any endpoint
 * This function can be included in any API file
 */
function apply_cors() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
    header("Access-Control-Max-Age: 3600");
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

// For regular requests, return basic API info
if ($_SERVER['REQUEST_METHOD'] === 'GET' && basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'CORS enabled',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// For other methods when directly accessing this file, return method not allowed
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
} 