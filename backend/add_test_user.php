<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html');
echo "<h1>Add Test User</h1>";

try {
    // Include database configuration
    require_once __DIR__ . '/config/db_connect.php';
    
    // Test user details
    $username = 'testuser';
    $email = 'test@example.com';
    $password = 'password123'; // Plain password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hashed password
    $phone = '1234567890';
    $user_type = 'buyer';
    
    // Check if test user already exists
    $check_stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User exists, update the password to ensure it's hashed
        $user = $result->fetch_assoc();
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->bind_param("si", $hashed_password, $user['id']);
        
        if ($update_stmt->execute()) {
            echo "<p style='color:green'>Test user updated with hashed password!</p>";
            echo "<p>Username: {$username}<br>Password: {$password}</p>";
        } else {
            throw new Exception("Error updating user: " . $conn->error);
        }
    } else {
        // Insert new test user
        $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, phone, user_type) VALUES (?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("sssss", $username, $email, $hashed_password, $phone, $user_type);
        
        if ($insert_stmt->execute()) {
            echo "<p style='color:green'>Test user created successfully!</p>";
            echo "<p>Username: {$username}<br>Password: {$password}</p>";
        } else {
            throw new Exception("Error creating user: " . $conn->error);
        }
    }
    
    echo "<p>You can now try to log in with these credentials.</p>";
    echo "<p><a href='../Registration/login.html'>Go to login page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 