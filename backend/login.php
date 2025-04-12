<?php
// Set headers and start session
header('Content-Type: application/json');
session_start();

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Include database configuration
    require_once __DIR__ . '/config/db_connect.php';
    
    // Get and validate POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Check if required fields are present
    if (!isset($data['username']) || !isset($data['password'])) {
        throw new Exception("Username and password are required");
    }
    
    $username = $data['username'];
    $password = $data['password'];
    
    // Log login attempt (for debugging)
    error_log("Login attempt for username: " . $username);
    
    // Prepare SQL statement with parameterized query
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    if (!$stmt) {
        throw new Exception("Database query preparation failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $username);
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            
            // Return success response with user type
            echo json_encode([
                'success' => true,
                'user_type' => $user['user_type'],
                'message' => 'Login successful'
            ]);
        } else {
            throw new Exception("Invalid password");
        }
    } else {
        throw new Exception("User not found");
    }
    
    // Close resources
    $stmt->close();
    
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