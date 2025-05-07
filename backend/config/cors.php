<?php
/**
 * CORS Handler
 * Manages Cross-Origin Resource Sharing for the API
 */

// Include environment configuration if not already included
if (!defined('ALLOWED_ORIGINS')) {
    require_once __DIR__ . '/env.php';
}

// CORS settings - in production, allow the frontend URL
// Add the Vercel domain explicitly
$frontendUrl = $isProduction ? (getenv('FRONTEND_URL') ?: 'https://clothloop-frontend.vercel.app') : null;
define('ALLOWED_ORIGINS', $isProduction 
    ? [$frontendUrl, 'https://cloth-loop.vercel.app', 'https://clothloop-nyjms3mwb-aaditya-desais-projects.vercel.app', '*'] 
    : ['http://localhost:3000', 'http://localhost', 'http://localhost/ClothLoop']
);

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
    
    // In production, be more permissive to allow Vercel domains
    if (IS_PRODUCTION) {
        // Allow all origins for now to fix CORS issues
        header("Access-Control-Allow-Origin: *");
    } else {
        // In development, check against allowed origins
        $allowedOrigins = ALLOWED_ORIGINS;
        if ($origin && in_array($origin, $allowedOrigins)) {
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