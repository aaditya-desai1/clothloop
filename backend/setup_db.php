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

// Read the SQL file
$sql = file_get_contents('cloth_tables.sql');

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

// Create image upload directory if it doesn't exist
$upload_dir = '../Image/cloth_images/';
if (!file_exists($upload_dir)) {
    if (mkdir($upload_dir, 0777, true)) {
        echo "Image upload directory created successfully.<br>";
    } else {
        echo "Failed to create image upload directory.<br>";
    }
} else {
    echo "Image upload directory already exists.<br>";
}

$conn->close();
echo "Setup complete.";
?> 