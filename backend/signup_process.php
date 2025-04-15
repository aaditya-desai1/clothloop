<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once __DIR__ . '/config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get form data
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $phone = $_POST['phone'];
        $user_type = $_POST['user_type'];

        // Check if email exists using prepared statement
        $check_stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("Email already exists!");
        }
        
        // Insert user using prepared statement
        $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, phone, user_type) VALUES (?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("sssss", $username, $email, $password, $phone, $user_type);

        if ($insert_stmt->execute()) {
            // Redirect to login page after successful registration
            echo "<script>
                alert('Registration successful! Please login.');
                window.location.href='../Account/login.html';
            </script>";
        } else {
            throw new Exception("Database Error: " . $conn->error);
        }
    } catch (Exception $e) {
        echo "<script>
            alert('Error: " . $e->getMessage() . "');
            window.location.href='../Account/register.html';
        </script>";
    }

       // Insert user directly open seller home page or buyer home page

        //     $sql = "INSERT INTO users (username, email, password, phone, user_type) 
        //     VALUES ('$username', '$email', '$password', '$phone', '$user_type')";

        // if ($conn->query($sql) === TRUE) {
        // $redirect = $user_type === 'buyer' ? 'Buyer_Dashboard.html' : 'seller_home.html';
        // echo "<script>alert('Registration successful!'); window.location.href='" . $redirect . "';</script>";
        // } else {
        // throw new Exception("Database Error: " . $conn->error);
        // }
        // } catch (Exception $e) {
        // echo "<script>alert('Error: " . $e->getMessage() . "'); window.location.href='signup.html';</script>";

}
?> 