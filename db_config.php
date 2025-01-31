<?php
// Database configuration
$host = "localhost";
$dbname = "clothloop";  // Your database name
$username = "root";     // Usually "root" for local development
$password = "";         // Usually empty for local XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Database connection successful");
} catch(PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Connection failed: " . $e->getMessage());
}
?> 