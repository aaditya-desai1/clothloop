<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// For preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include session configuration
require_once "../../config/session.php";

// Get current session data for debugging
$session_data = $_SESSION;

// Clear all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Return success response
echo json_encode([
    'status' => 'success',
    'message' => 'Logout successful',
    'debug' => [
        'previous_session' => $session_data,
        'session_destroyed' => true,
        'time' => date('Y-m-d H:i:s')
    ]
]);
?> 