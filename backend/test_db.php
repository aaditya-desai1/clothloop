<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'config/db_connect.php';

// Test database connection
echo "Database Connection Test:\n";
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error . "\n";
    exit();
} else {
    echo "Connection successful!\n\n";
}

// List all tables
echo "Tables in database:\n";
$result = $conn->query("SHOW TABLES");
if ($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_row()) {
            echo "- " . $row[0] . "\n";
        }
    } else {
        echo "No tables found in database.\n";
    }
} else {
    echo "Error listing tables: " . $conn->error . "\n";
}

// Check products table structure
echo "\nProducts Table Structure:\n";
$result = $conn->query("DESCRIBE products");
if ($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ") " . 
                 ($row['Null'] == 'NO' ? 'NOT NULL' : 'NULL') . 
                 ($row['Key'] == 'PRI' ? ' PRIMARY KEY' : '') . "\n";
        }
    } else {
        echo "Products table not found or empty.\n";
    }
} else {
    echo "Error describing products table: " . $conn->error . "\n";
}

// Close the connection
$conn->close();
echo "\nTest completed.";
?> 