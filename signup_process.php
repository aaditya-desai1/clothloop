<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once __DIR__ . '/config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get form data
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $user_type = mysqli_real_escape_string($conn, $_POST['user_type']);

        // Check if email exists
        $check_email = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($check_email);

        if ($result->num_rows > 0) {
            throw new Exception("Email already exists!");
        }

        // Insert user
        $sql = "INSERT INTO users (username, email, password, phone, user_type) 
                VALUES ('$username', '$email', '$password', '$phone', '$user_type')";

        if ($conn->query($sql) === TRUE) {
            $redirect = $user_type === 'buyer' ? 'home.html' : 'seller_home.html';
            echo "<script>alert('Registration successful!'); window.location.href='" . $redirect . "';</script>";
        } else {
            throw new Exception("Database Error: " . $conn->error);
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "'); window.location.href='signup.html';</script>";
    }
}
?> 