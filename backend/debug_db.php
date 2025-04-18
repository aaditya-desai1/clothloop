<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database and model files
require_once __DIR__ . '/config/database.php';

// Create database connection
$database = new Database();
$conn = $database->connect();

// Check if connection was successful
if (!$conn) {
    die("Database connection failed");
}

// Show buyers table structure
echo "<h2>Buyers Table Structure</h2>";
try {
    $stmt = $conn->query("DESCRIBE buyers");
    echo "<pre>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Show users table structure
echo "<h2>Users Table Structure</h2>";
try {
    $stmt = $conn->query("DESCRIBE users");
    echo "<pre>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Show sample data from buyers
echo "<h2>Sample Buyers Data</h2>";
try {
    $stmt = $conn->query("SELECT * FROM buyers LIMIT 5");
    echo "<pre>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Show users and their buyer profiles using JOIN
echo "<h2>Users with Buyer Profiles</h2>";
try {
    $stmt = $conn->query("
        SELECT u.id, u.name, u.email, u.user_type, b.* 
        FROM users u
        LEFT JOIN buyers b ON u.id = b.id
        WHERE u.user_type = 'buyer'
        LIMIT 5
    ");
    echo "<pre>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 