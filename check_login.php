<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once 'db_config.php';

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'];
    $password = $data['password'];
    
    // Debug: Log received data
    error_log("Login attempt - Username: " . $username);
    
    // Prepare SQL statement
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        error_log("User found - Attempting password verification");
        
        // First try direct comparison (for plain text passwords)
        if ($password === $user['password']) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            echo json_encode(['success' => true]);
            exit;
        }
        
        // Then try hashed password
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid password'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Username not found'
        ]);
    }
    
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 