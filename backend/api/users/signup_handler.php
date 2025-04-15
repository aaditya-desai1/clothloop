<?php
// Include database connection
require_once '../../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Get form data
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $user_type = mysqli_real_escape_string($conn, $_POST['user_type']);

    // Check if email already exists
    $check_email = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($check_email);

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email already exists"]);
        exit();
    }

    // Insert new user
    $sql = "INSERT INTO users (username, email, password, phone, user_type) 
            VALUES ('$username', '$email', '$password', '$phone', '$user_type')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Registration successful"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
    }
}
?> 