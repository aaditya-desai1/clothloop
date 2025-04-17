<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// For preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Session settings for more permissive cookie handling
ini_set('session.cookie_httponly', 0);
ini_set('session.cookie_secure', 0);
ini_set('session.use_only_cookies', 0);
ini_set('session.cookie_samesite', 'None');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get session information for debugging
$session_id = session_id();
$session_status = session_status();
$session_data = $_SESSION;
$cookie_data = $_COOKIE;

// Check if user is logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    echo json_encode([
        'status' => 'success',
        'logged_in' => true,
        'user_type' => $_SESSION['user_type'],
        'user_id' => $_SESSION['user_id'],
        'user_name' => $_SESSION['user_name'] ?? '',
        'user_email' => $_SESSION['user_email'] ?? '',
        'debug' => [
            'session_id' => $session_id,
            'session_status' => $session_status,
            'session_data' => $session_data,
            'cookie_data' => $cookie_data,
            'php_version' => phpversion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown'
        ]
    ]);
} else {
    echo json_encode([
        'status' => 'success',
        'logged_in' => false,
        'debug' => [
            'session_id' => $session_id,
            'session_status' => $session_status,
            'session_data' => $session_data,
            'cookie_data' => $cookie_data,
            'php_version' => phpversion(), 
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown'
        ]
    ]);
}
?> 