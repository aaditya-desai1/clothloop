<?php
// Set headers and start session
header('Content-Type: application/json');
// Set permissive CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// For preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include session configuration
require_once "../../config/session.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Enable for debugging

// Set error handler to catch any PHP errors and return them as JSON
function errorHandler($errno, $errstr, $errfile, $errline) {
    $error = "PHP Error [$errno]: $errstr in $errfile on line $errline";
    error_log($error);
    echo json_encode([
        'success' => false,
        'error' => 'Server error occurred. Please try again later.',
        'debug' => $error
    ]);
    exit(1);
}
set_error_handler('errorHandler');

try {
    // Include database connection
    require_once '../../config/db_connect.php';
    
    // Initialize response
    $response = [
        'success' => false,
        'message' => '',
        'user_id' => null,
        'user_type' => null,
        'debug' => []
    ];

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $response['debug']['input'] = $input;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get login credentials
        $email = isset($input['email']) ? trim($input['email']) : '';
        $password = isset($input['password']) ? $input['password'] : '';
        $destination = isset($input['destination']) ? $input['destination'] : null;
        
        // Validate inputs
        if (empty($email) || empty($password)) {
            $response['message'] = 'Email and password are required';
            echo json_encode($response);
            exit;
        }
        
        // First check buyers table
        $buyerStmt = $conn->prepare("SELECT id, name, email, password, phone_no FROM buyers WHERE email = ?");
        $buyerStmt->bind_param("s", $email);
        $buyerStmt->execute();
        $buyerResult = $buyerStmt->get_result();
        
        if ($buyerResult->num_rows > 0) {
            // User found in buyers table
            $buyer = $buyerResult->fetch_assoc();
            $response['debug']['buyer_found'] = true;
            
            // Verify password
            if (password_verify($password, $buyer['password'])) {
                // Password is correct
                $response['success'] = true;
                $response['message'] = 'Login successful as buyer';
                $response['user_id'] = $buyer['id'];
                $response['user_type'] = 'buyer';
                
                // Store user data in session
                $_SESSION['user_id'] = $buyer['id'];
                $_SESSION['user_type'] = 'buyer';
                $_SESSION['user_name'] = $buyer['name'];
                $_SESSION['user_email'] = $buyer['email'];
                
                // Add session debug info
                $response['debug']['session_id'] = session_id();
                $response['debug']['session_status'] = session_status();
                $response['debug']['session_data'] = $_SESSION;
            } else {
                // Invalid password
                $response['message'] = 'Invalid email or password';
                $response['debug']['password_match'] = false;
            }
        } else {
            // Check sellers table
            $sellerStmt = $conn->prepare("SELECT id, name, email, password, phone_no, shop_name FROM sellers WHERE email = ?");
            $sellerStmt->bind_param("s", $email);
            $sellerStmt->execute();
            $sellerResult = $sellerStmt->get_result();
            
            if ($sellerResult->num_rows > 0) {
                // User found in sellers table
                $seller = $sellerResult->fetch_assoc();
                $response['debug']['seller_found'] = true;
                
                // Verify password
                if (password_verify($password, $seller['password'])) {
                    // Password is correct
                    $response['success'] = true;
                    $response['message'] = 'Login successful as seller';
                    $response['user_id'] = $seller['id'];
                    $response['user_type'] = 'seller';
                    
                    // Store user data in session
                    $_SESSION['user_id'] = $seller['id'];
                    $_SESSION['user_type'] = 'seller';
                    $_SESSION['user_name'] = $seller['name'];
                    $_SESSION['user_email'] = $seller['email'];
                    $_SESSION['shop_name'] = $seller['shop_name'];
                    
                    // Add session debug info
                    $response['debug']['session_id'] = session_id();
                    $response['debug']['session_status'] = session_status();
                    $response['debug']['session_data'] = $_SESSION;
                } else {
                    // Invalid password
                    $response['message'] = 'Invalid email or password';
                    $response['debug']['password_match'] = false;
                }
            } else {
                // User not found in either table
                $response['message'] = 'Invalid email or password';
                $response['debug']['user_found'] = false;
            }
        }
    } else {
        $response['message'] = 'Invalid request method';
    }

    // Return JSON response
    echo json_encode($response);
    
} catch (Exception $e) {
    // Log the error
    error_log("Login error: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    // Close connection if it exists
    if (isset($conn)) {
        $conn->close();
    }
}
?> 