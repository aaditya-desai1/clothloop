<?php
/**
 * Environment Configuration
 * This file manages environment variables for both local and production environments
 */

// Check if we're in a production environment (Render)
$isProduction = (getenv('RENDER') === 'true');

// Database credentials
$dbConfig = [
    'host' => $isProduction ? getenv('DB_HOST') : 'localhost',
    'dbname' => $isProduction ? getenv('DB_NAME') : 'clothloop',
    'username' => $isProduction ? getenv('DB_USER') : 'root',
    'password' => $isProduction ? getenv('DB_PASS') : '',
];

// Base URLs
$baseUrl = $isProduction 
    ? getenv('FRONTEND_URL') // This will be your Vercel deployment URL
    : 'http://localhost/ClothLoop';

$apiUrl = $isProduction 
    ? getenv('RENDER_EXTERNAL_URL') // This will be your Render deployment URL
    : 'http://localhost/ClothLoop/backend/api';

// Upload paths
$uploadsPath = $isProduction 
    ? getenv('RENDER_EXTERNAL_URL') . '/uploads'
    : 'http://localhost/ClothLoop/backend/uploads';

// Define constants
define('IS_PRODUCTION', $isProduction);
define('DB_HOST', $dbConfig['host']);
define('DB_NAME', $dbConfig['dbname']);
define('DB_USER', $dbConfig['username']);
define('DB_PASS', $dbConfig['password']);
define('BASE_URL', $baseUrl);
define('API_URL', $apiUrl);
define('UPLOADS_URL', $uploadsPath);
define('UPLOADS_PATH', __DIR__ . '/../uploads');

// Auth settings
define('JWT_SECRET', $isProduction ? getenv('JWT_SECRET') : 'clothloop_secret_key_change_in_production');
define('SESSION_DURATION', 86400); // 24 hours in seconds

// CORS settings
define('ALLOWED_ORIGINS', $isProduction 
    ? [getenv('FRONTEND_URL')] 
    : ['http://localhost:3000', 'http://localhost', 'http://localhost/ClothLoop']
);

// Debug mode (disable in production)
define('DEBUG_MODE', !$isProduction);

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_SELLER', 'seller');
define('ROLE_BUYER', 'buyer');

// Date format for database
define('DB_DATE_FORMAT', 'Y-m-d H:i:s');

// Upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Pagination defaults
define('DEFAULT_PAGE_SIZE', 10); 