<?php
/**
 * Configuration Check
 * 
 * This script checks the current configuration and provides diagnostic information
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

// Set content type
header('Content-Type: application/json');

// Include necessary files
require_once __DIR__ . '/../../config/env.php';

// Check database connection
$dbStatus = 'unknown';
$dbError = null;
try {
    require_once __DIR__ . '/../../config/database.php';
    $database = new Database();
    $db = $database->connect();
    
    // Test query
    $stmt = $db->query("SELECT 1 as test");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && isset($result['test']) && $result['test'] == 1) {
        $dbStatus = 'connected';
    } else {
        $dbStatus = 'error';
        $dbError = 'Connection successful but test query failed';
    }
} catch (Exception $e) {
    $dbStatus = 'error';
    $dbError = $e->getMessage();
}

// Check for required PHP extensions
$extensions = [
    'pdo' => extension_loaded('pdo'),
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'mysqli' => extension_loaded('mysqli'),
    'gd' => extension_loaded('gd'),
    'json' => extension_loaded('json')
];

// Check file permissions
$uploadsPerm = is_writable(UPLOADS_PATH);

// Get environment info
$envInfo = [
    'php_version' => phpversion(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
    'environment' => IS_PRODUCTION ? 'production' : 'development',
    'database_type' => stripos(DB_HOST, 'postgres') !== false ? 'PostgreSQL' : 'MySQL',
    'uploads_path' => UPLOADS_PATH,
    'uploads_url' => UPLOADS_URL,
    'api_url' => API_URL,
    'frontend_url' => BASE_URL,
    'cors_origins' => ALLOWED_ORIGINS
];

// Return configuration status
echo json_encode([
    'status' => ($dbStatus === 'connected' ? 'success' : 'error'),
    'message' => ($dbStatus === 'connected' ? 'Configuration is valid' : 'Configuration issues detected'),
    'database' => [
        'status' => $dbStatus,
        'host' => DB_HOST,
        'name' => DB_NAME,
        'user' => DB_USER,
        'error' => $dbError
    ],
    'extensions' => $extensions,
    'permissions' => [
        'uploads_writable' => $uploadsPerm
    ],
    'environment' => $envInfo,
    'timestamp' => date('Y-m-d H:i:s')
]); 