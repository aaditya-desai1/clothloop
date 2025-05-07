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

// Output database configuration for debugging in logs (only in production)
if ($isProduction) {
    error_log("Database Configuration:");
    error_log("Host: " . $dbConfig['host']);
    error_log("DB Name: " . $dbConfig['dbname']);
    error_log("DB User: " . $dbConfig['username']);
    error_log("DB Pass Length: " . (strlen($dbConfig['password']) > 0 ? 'Set' : 'Not Set'));
}

// Base URLs
$baseUrl = $isProduction 
    ? (getenv('FRONTEND_URL') ?: 'https://clothloop.vercel.app')
    : 'http://localhost/ClothLoop';

// For API URL, in Docker on Render, we need to use the RENDER_EXTERNAL_URL env var
$apiUrl = $isProduction 
    ? (getenv('RENDER_EXTERNAL_URL') ?: 'https://clothloop-backend.onrender.com')
    : 'http://localhost/ClothLoop/backend/api';

// Upload paths - in Docker, the uploads directory is relative to the backend
$uploadsUrl = $isProduction 
    ? ((getenv('RENDER_EXTERNAL_URL') ?: 'https://clothloop-backend.onrender.com') . '/uploads')
    : 'http://localhost/ClothLoop/backend/uploads';

$uploadsPath = $isProduction
    ? '/var/www/html/uploads'
    : __DIR__ . '/../uploads';

// Define constants
define('IS_PRODUCTION', $isProduction);
define('DB_HOST', $dbConfig['host']);
define('DB_NAME', $dbConfig['dbname']);
define('DB_USER', $dbConfig['username']);
define('DB_PASS', $dbConfig['password']);
define('BASE_URL', $baseUrl);
define('API_URL', $apiUrl);
define('UPLOADS_URL', $uploadsUrl);
define('UPLOADS_PATH', $uploadsPath);

// Auth settings
define('JWT_SECRET', $isProduction ? (getenv('JWT_SECRET') ?: 'default_jwt_secret_for_render_deployment') : 'clothloop_secret_key_change_in_production');
define('SESSION_DURATION', 86400); // 24 hours in seconds

// CORS settings - in production, allow all origins for now
// We'll properly configure this later when the frontend is stable
define('ALLOWED_ORIGINS', $isProduction 
    ? ['*'] 
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