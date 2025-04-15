<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once '../../config/db_connect.php';

session_start();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = $_POST['phone'];
    $user_type = $_POST['user_type'];
    
    // Validate data
    $errors = array();
    
    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if email already exists
    $check_email = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Email already exists";
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    // Validate confirm password
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Validate phone
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    } elseif (!preg_match("/^\d{10}$/", $phone)) {
        $errors[] = "Phone number must be 10 digits";
    }
    
    // Check for seller-specific fields if user is a seller
    if ($user_type === "seller") {
        $shop_name = $_POST['shop_name'] ?? '';
        $shop_address = $_POST['shop_address'] ?? '';
        $latitude = $_POST['shop_latitude'] ?? '';
        $longitude = $_POST['shop_longitude'] ?? '';
        
        if (empty($shop_name)) {
            $errors[] = "Shop name is required";
        }
        
        if (empty($shop_address)) {
            $errors[] = "Shop address is required";
        }
        
        if (empty($latitude) || empty($longitude)) {
            $errors[] = "Shop location is required";
        }
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert user into users table
            $insert_user = $conn->prepare("INSERT INTO users (username, email, password, phone, user_type) VALUES (?, ?, ?, ?, ?)");
            $insert_user->bind_param("sssss", $username, $email, $hashed_password, $phone, $user_type);
            $insert_user->execute();
            
            // Get the user ID
            $user_id = $conn->insert_id;
            
            // Insert user type specific details
            if ($user_type === "buyer") {
                // Insert into buyer_details table
                $insert_buyer = $conn->prepare("INSERT INTO buyer_details (user_id) VALUES (?)");
                $insert_buyer->bind_param("i", $user_id);
                $insert_buyer->execute();
            } else if ($user_type === "seller") {
                // Insert into seller_details table
                $insert_seller = $conn->prepare("INSERT INTO seller_details (user_id, shop_name, shop_address, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
                $insert_seller->bind_param("issdd", $user_id, $shop_name, $shop_address, $latitude, $longitude);
                $insert_seller->execute();
            }
            
            // Commit the transaction
            $conn->commit();
            
            // Registration successful
            echo "Registration successful";
        } catch (Exception $e) {
            // Rollback transaction if something failed
            $conn->rollback();
            echo "Registration failed: " . $e->getMessage();
        }
    } else {
        // Display errors
        echo "Registration failed: " . implode(', ', $errors);
    }
} else {
    // If not a POST request, redirect to registration page
    header("Location: ../Account/register.html");
    exit();
}
?> 