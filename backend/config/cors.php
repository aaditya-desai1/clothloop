<?php
/**
 * CORS Handler
 * Manages Cross-Origin Resource Sharing for the API
 */

// Include environment configuration if not already included
if (!defined('ALLOWED_ORIGINS')) {
    require_once __DIR__ . '/env.php';
}

/**
 * Set CORS headers to allow cross-origin requests
 * 
 * @param string $origin The origin making the request
 * @return void
 */
function setCorsHeaders($origin = null) {
    // If no origin is provided, get it from the request
    if (!$origin && isset($_SERVER['HTTP_ORIGIN'])) {
        $origin = $_SERVER['HTTP_ORIGIN'];
    }
    
    // Check if the origin is allowed
    $allowedOrigins = ALLOWED_ORIGINS;
    
    // In production, strictly check the origin
    if (IS_PRODUCTION) {
        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: {$origin}");
        }
    } else {
        // In development, be more permissive
        if ($origin) {
            header("Access-Control-Allow-Origin: {$origin}");
        } else {
            // Fallback for requests without origin header
            header("Access-Control-Allow-Origin: *");
        }
    }
    
    // Set other CORS headers
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 3600"); // Cache preflight response for 1 hour
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header("HTTP/1.1 200 OK");
        exit;
    }
}

// Automatically set CORS headers for all API requests
setCorsHeaders(); 