<?php
// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Start session
session_start();

// Simplified login handler - no database connection
try {
    // Get POST data
    $raw_data = file_get_contents('php://input');
    $data = json_decode($raw_data, true);
    
    // Hardcoded test users for emergency login
    $test_users = [
        'test@example.com' => [
            'password' => 'password123',
            'user_type' => 'buyer'
        ],
        'nishidh@gmail.com' => [
            'password' => 'password',
            'user_type' => 'buyer'
        ],
        'seller@example.com' => [
            'password' => 'seller123',
            'user_type' => 'seller'
        ]
    ];
    
    // Check if email exists and password matches
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    if (isset($test_users[$email]) && $test_users[$email]['password'] === $password) {
        // Set session variables
        $_SESSION['user_id'] = 1;
        $_SESSION['email'] = $email;
        $_SESSION['username'] = explode('@', $email)[0]; // Use part before @ as username
        $_SESSION['user_type'] = $test_users[$email]['user_type'];
        
        // Return success
        echo json_encode([
            'success' => true,
            'user_type' => $test_users[$email]['user_type'],
            'message' => 'Login successful (emergency mode)'
        ]);
    } else {
        // For any other user, try to connect to the main users table
        include_once __DIR__ . '/config/db_connect.php';
        
        // Simple query without prepared statement for emergency cases
        $query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Return success
                echo json_encode([
                    'success' => true,
                    'user_type' => $user['user_type'],
                    'message' => 'Login successful'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid password'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'User not found'
            ]);
        }
    }
} catch (Exception $e) {
    // Return error
    echo json_encode([
        'success' => false,
        'error' => 'Login error: ' . $e->getMessage()
    ]);
}
?> 