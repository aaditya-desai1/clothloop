<?php
// Database connection settings
$db_host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "clothloop";

// Create connection
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully.<br>";

// Read the SQL file with the correct path
$sql = file_get_contents(__DIR__ . '/cloth_tables.sql');

// Execute multi-query SQL commands
if ($conn->multi_query($sql)) {
    echo "Tables created/updated successfully.<br>";
    
    // Clean up result sets
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
} else {
    echo "Error creating tables: " . $conn->error . "<br>";
}

$conn->close();
echo "Setup complete.";
?> 