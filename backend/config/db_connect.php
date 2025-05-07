<?php
/**
 * Database Connection Script
 * Provides a simple way to get a database connection
 */

// Include the database class
require_once 'database.php';

// Get database connection
function getConnection() {
    try {
        $database = new Database();
        $conn = $database->connect();
        return $conn;
    } catch (Exception $e) {
        // Handle connection errors
        if (DEBUG_MODE) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Database connection failed. Please try again later.'
            ]);
        }
        exit;
    }
}

// Database connection configuration
$host = "localhost";  // Usually localhost for local development
$username = "root";   // Default MySQL username
$password = "";      // Default password is empty for XAMPP
$database = "clothloop";  // Your database name

// Create database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    // Log error and return a friendly message
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Database connection failed. Please try again later.");
}

// Set charset to ensure proper handling of special characters
mysqli_set_charset($conn, "utf8mb4");
?> 