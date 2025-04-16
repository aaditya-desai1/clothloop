<?php
// If file exists, read it but don't change it
// If file doesn't exist, create a basic connection file
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty
$dbname = "clothloop";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set UTF-8 character set (optional but recommended)
$conn->set_charset("utf8mb4");
?> 