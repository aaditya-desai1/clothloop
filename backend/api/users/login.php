<?php
/**
 * Login API
 * Authenticates users and creates a session
 */

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true'); // Added for cookie support

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/auth.php';
require_once __DIR__ . '/../../utils/response.php';

// Get posted data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required'
    ]);
    exit;
}

$email = $data['email'];
$password = $data['password'];

// Special case for buyer demo login
if ($email === 'demo@clothloop.com' || $email === 'buyer@clothloop.com') {
    // Create a demo buyer user
    $user = [
        'id' => 1,
        'name' => 'Demo Buyer',
        'email' => 'demo@clothloop.com',
        'role' => 'buyer',
        'phone_no' => '1234567890',
        'status' => 'active'
    ];
    
    // Store in session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
    exit;
}

// Special case for seller demo login
if ($email === 'seller@clothloop.com') {
    // Create a demo seller user
    $user = [
        'id' => 2,
        'name' => 'Demo Seller',
        'email' => 'seller@clothloop.com',
        'role' => 'seller',
        'phone_no' => '1234567890',
        'status' => 'active'
    ];
    
    // Store in session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
    exit;
}

// Special case for admin demo login
if ($email === 'admin@clothloop.com') {
    // Create a demo admin user
    $user = [
        'id' => 3,
        'name' => 'Demo Admin',
        'email' => 'admin@clothloop.com',
        'role' => 'admin',
        'phone_no' => '1234567890',
        'status' => 'active'
    ];
    
    // Store in session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
    exit;
}

try {
    // Database connection
    $database = new Database();
    $db = $database->connect();
    
    // Check if users table exists
    $checkTable = $db->prepare("SHOW TABLES LIKE 'users'");
    $checkTable->execute();
    
    if ($checkTable->rowCount() == 0) {
        // Users table doesn't exist yet - create a demo account instead
        // Store demo account in session
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'Demo User';
        $_SESSION['user_email'] = 'demo@clothloop.com';
        $_SESSION['user_role'] = 'buyer';
        
        // Return success with demo account
        echo json_encode([
            'success' => true,
            'message' => 'Using demo account as database is not set up',
            'user' => [
                'id' => 1,
                'name' => 'Demo User',
                'email' => 'demo@clothloop.com',
                'role' => 'buyer'
            ]
        ]);
        exit;
    }
    
    // Get user from database
    $stmt = $db->prepare("
        SELECT id, name, email, password, role, phone_no, status
        FROM users
        WHERE email = :email AND status = 'active'
    ");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        // User not found - return demo account for testing
        if ($email === 'demo@clothloop.com' || $password === 'password') {
            // Return demo account
            $_SESSION['user_id'] = 1;
            $_SESSION['user_name'] = 'Demo User';
            $_SESSION['user_email'] = 'demo@clothloop.com';
            $_SESSION['user_role'] = 'buyer';
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful with demo account',
                'user' => [
                    'id' => 1,
                    'name' => 'Demo User',
                    'email' => 'demo@clothloop.com',
                    'role' => 'buyer'
                ]
            ]);
        } else {
            // Invalid credentials
            echo json_encode([
                'success' => false,
                'message' => 'Invalid credentials. Try demo@clothloop.com/password.'
            ]);
        }
        exit;
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // For demo purposes, accept any password (remove in production!)
    // or verify using password_verify function in production
    
    // Store in session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
    
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Login failed: ' . $e->getMessage()
    ]);
} 