<?php
/**
 * API Index
 * Serves as a health check and documentation endpoint
 */

// Include necessary files
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/cors.php';

// Set content type to JSON
header('Content-Type: application/json');

// Handle request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Health check and API info
    echo json_encode([
        'status' => 'success',
        'message' => 'ClothLoop API is running',
        'version' => '1.0.0',
        'environment' => IS_PRODUCTION ? 'production' : 'development',
        'api_documentation' => [
            'auth' => [
                'login' => API_URL . '/users/login.php',
                'register' => API_URL . '/users/register.php',
                'logout' => API_URL . '/users/logout.php'
            ],
            'products' => [
                'all' => API_URL . '/products/get_products.php',
                'single' => API_URL . '/products/get_product.php?id={product_id}',
                'images' => API_URL . '/products/get_images.php?product_id={product_id}'
            ],
            'buyers' => [
                'profile' => API_URL . '/buyers/get_profile.php',
                'update_profile' => API_URL . '/buyers/update_profile.php',
                'wishlist' => API_URL . '/buyers/get_wishlist.php'
            ],
            'sellers' => [
                'profile' => API_URL . '/sellers/get_profile.php',
                'update_profile' => API_URL . '/sellers/update_profile.php',
                'products' => API_URL . '/sellers/get_products.php'
            ]
        ],
        'timestamp' => date(DB_DATE_FORMAT)
    ]);
} else {
    // Method not allowed
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
} 