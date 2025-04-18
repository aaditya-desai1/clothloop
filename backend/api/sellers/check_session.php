<?php
/**
 * Session Checker API Endpoint
 * Validates session status and attempts to restore session if needed
 */

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/auth.php';
require_once __DIR__ . '/../../utils/response.php';

// Initialize response
$response = [
    'status' => 'error',
    'message' => 'Session validation failed',
    'is_valid' => false,
    'session_restored' => false
];

try {
    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if session already exists
    if (Auth::checkSession()) {
        $user = Auth::getCurrentUser();
        
        // Verify role is seller
        if ($user['role'] === 'seller') {
            $response = [
                'status' => 'success',
                'message' => 'Session is valid',
                'is_valid' => true,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ];
            echo json_encode($response);
            exit();
        } else {
            $response['message'] = 'User is not a seller';
            echo json_encode($response);
            exit();
        }
    }
    
    // If no valid session, try to restore from submitted data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // If no data was submitted, check for GET parameters
    if (!$data) {
        $data = [
            'user_id' => isset($_GET['user_id']) ? $_GET['user_id'] : null,
            'user_role' => isset($_GET['user_role']) ? $_GET['user_role'] : null
        ];
    }
    
    // Verify required data is present
    if (!isset($data['user_id']) || !isset($data['user_role']) || $data['user_role'] !== 'seller') {
        $response['message'] = 'Invalid data submitted for session restoration';
        echo json_encode($response);
        exit();
    }
    
    // Attempt to retrieve user data from database
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT u.id, u.name, u.email, u.role, u.status
        FROM users u
        WHERE u.id = :user_id AND u.role = :role AND u.status = 'active'
    ");
    
    $stmt->bindParam(':user_id', $data['user_id']);
    $stmt->bindParam(':role', $data['user_role']);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $response['message'] = 'Could not restore session: user not found or inactive';
        echo json_encode($response);
        exit();
    }
    
    // Start a new session with the user data
    Auth::startSession($user);
    
    // Success response
    $response = [
        'status' => 'success',
        'message' => 'Session restored successfully',
        'is_valid' => true,
        'session_restored' => true,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ];
    
} catch (Exception $e) {
    $response['message'] = 'Error validating session: ' . $e->getMessage();
}

// Send response
echo json_encode($response); 