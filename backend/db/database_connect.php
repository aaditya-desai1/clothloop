<?php
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