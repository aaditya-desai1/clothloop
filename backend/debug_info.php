<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Show basic PHP info
echo "<h1>PHP Information</h1>";

// Check session functionality
session_start();
echo "<h2>Session Test</h2>";
$_SESSION['test'] = 'Session is working';
echo "Session ID: " . session_id() . "<br>";
echo "Session variable: " . $_SESSION['test'] . "<br>";

// Check file upload settings
echo "<h2>File Upload Settings</h2>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_execution_time: " . ini_get('max_execution_time') . "<br>";
echo "max_input_time: " . ini_get('max_input_time') . "<br>";
echo "memory_limit: " . ini_get('memory_limit') . "<br>";

// Check directory permissions
echo "<h2>Directory Permissions</h2>";
$uploadDir = "../uploads/sellers/";
if (!file_exists($uploadDir)) {
    if (mkdir($uploadDir, 0777, true)) {
        echo "Created directory: $uploadDir successfully<br>";
    } else {
        echo "Failed to create directory: $uploadDir<br>";
        echo "Error: " . error_get_last()['message'] . "<br>";
    }
} else {
    echo "Directory exists: $uploadDir<br>";
    echo "Is writable: " . (is_writable($uploadDir) ? 'Yes' : 'No') . "<br>";
}

// Check database connection
echo "<h2>Database Connection</h2>";
require_once 'config/db_connect.php';

if (isset($conn)) {
    echo "Database connection successful<br>";
    
    // Check if sellers table exists
    $result = $conn->query("SHOW TABLES LIKE 'sellers'");
    if ($result->num_rows > 0) {
        echo "Sellers table exists<br>";
        
        // Check sellers table structure
        $result = $conn->query("DESCRIBE sellers");
        echo "<h3>Sellers Table Structure:</h3>";
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Sellers table does not exist<br>";
    }
} else {
    echo "Database connection failed<br>";
}

// Get server information
echo "<h2>Server Information</h2>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "<br>";
echo "PHP SAPI: " . php_sapi_name() . "<br>";

// Debug extension information
echo "<h2>Loaded Extensions</h2>";
$extensions = get_loaded_extensions();
sort($extensions);
echo implode(', ', $extensions);
?> 