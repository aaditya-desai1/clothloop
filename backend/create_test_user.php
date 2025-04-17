<?php
// Include database connection
require_once 'config/db_connect.php';

// Set headers for easier viewing in browser
header('Content-Type: text/plain');

try {
    // Check if test user already exists
    $checkQuery = "SELECT id FROM buyers WHERE email = 'test@example.com'";
    $result = $conn->query($checkQuery);
    
    if ($result->num_rows > 0) {
        echo "Test user already exists.\n";
        
        // Get the user details
        $userQuery = "SELECT id, name, email, phone_no FROM buyers WHERE email = 'test@example.com'";
        $userResult = $conn->query($userQuery);
        $user = $userResult->fetch_assoc();
        
        echo "User details:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Name: " . $user['name'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Phone: " . $user['phone_no'] . "\n";
    } else {
        // Create a test user with hashed password
        $password = password_hash('test123', PASSWORD_DEFAULT);
        $query = "INSERT INTO buyers (name, email, password, phone_no) VALUES ('Test User', 'test@example.com', '$password', '1234567890')";
        
        if ($conn->query($query)) {
            $userId = $conn->insert_id;
            echo "Test user created successfully with ID: $userId\n";
            echo "Email: test@example.com\n";
            echo "Password: test123\n";
        } else {
            echo "Error creating test user: " . $conn->error . "\n";
        }
    }
    
    // Check current session status
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    echo "\nCurrent session info:\n";
    echo "Session ID: " . session_id() . "\n";
    echo "Session status: " . session_status() . "\n";
    echo "Session data: \n";
    print_r($_SESSION);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    $conn->close();
}
?> 