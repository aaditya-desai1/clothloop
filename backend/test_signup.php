<?php
/**
 * Test script for signup process
 * This will help debug the registration process
 */

// Include database setup to ensure it exists
include_once __DIR__ . '/utils/create_database.php';

// Display any errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Testing Signup API</h2>";

// Directories check
echo "<h3>Directory Check</h3>";
$directories = [
    __DIR__ . '/uploads',
    __DIR__ . '/uploads/profile_photos',
    __DIR__ . '/config',
    __DIR__ . '/utils',
    __DIR__ . '/api/users'
];

foreach ($directories as $dir) {
    echo "Directory $dir: " . (is_dir($dir) ? "Exists" : "Missing") . "<br>";
    if (is_dir($dir)) {
        echo "Writable: " . (is_writable($dir) ? "Yes" : "No") . "<br>";
    }
}

// Files check
echo "<h3>Required Files Check</h3>";
$files = [
    __DIR__ . '/config/database.php',
    __DIR__ . '/config/constants.php',
    __DIR__ . '/utils/auth.php',
    __DIR__ . '/utils/response.php',
    __DIR__ . '/utils/validate.php',
    __DIR__ . '/api/users/signup_process.php'
];

foreach ($files as $file) {
    echo "File $file: " . (file_exists($file) ? "Exists" : "Missing") . "<br>";
}

// Display phpinfo for database extensions
echo "<h3>PHP Information</h3>";
echo "PDO Extension: " . (extension_loaded('pdo') ? "Loaded" : "Not loaded") . "<br>";
echo "PDO MySQL Extension: " . (extension_loaded('pdo_mysql') ? "Loaded" : "Not loaded") . "<br>";

// Test database connection
echo "<h3>Database Connection Test</h3>";
try {
    $host = "localhost";
    $username = "root";
    $password = "";
    $dbname = "clothloop";
    
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection successful!<br>";
    
    // Test listing tables
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables in database:<br>";
    foreach ($tables as $table) {
        echo "- $table<br>";
    }
} catch(PDOException $e) {
    echo "Database connection error: " . $e->getMessage() . "<br>";
} 