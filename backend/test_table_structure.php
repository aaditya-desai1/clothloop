<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$db_host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "clothloop";

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the 'clothes' table exists
$result = $conn->query("SHOW TABLES LIKE 'clothes'");
if ($result->num_rows == 0) {
    echo "<h2>The 'clothes' table does not exist</h2>";
    
    // Create the clothes table
    $sql = "CREATE TABLE clothes (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        seller_email VARCHAR(100) NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        size VARCHAR(50),
        color VARCHAR(50),
        category VARCHAR(100) DEFAULT 'General',
        price DECIMAL(10,2) NOT NULL,
        contact VARCHAR(20),
        whatsapp VARCHAR(20),
        shop_name VARCHAR(100),
        address TEXT,
        location_coordinates VARCHAR(50),
        use_address_input TINYINT(1) DEFAULT 1,
        terms TEXT,
        images TEXT,
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql)) {
        echo "<p>Created 'clothes' table successfully</p>";
    } else {
        echo "<p>Error creating table: " . $conn->error . "</p>";
    }
} else {
    echo "<h2>The 'clothes' table exists</h2>";
    
    // Show table structure
    $result = $conn->query("DESCRIBE clothes");
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
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
    
    // Show count of items
    $count_result = $conn->query("SELECT COUNT(*) as count FROM clothes");
    $count_row = $count_result->fetch_assoc();
    echo "<p>Number of items in the 'clothes' table: " . $count_row['count'] . "</p>";
    
    // Check if we need to add a category field (might be missing in older versions)
    $result = $conn->query("SHOW COLUMNS FROM clothes LIKE 'category'");
    if ($result->num_rows == 0) {
        $alter_sql = "ALTER TABLE clothes ADD COLUMN category VARCHAR(100) DEFAULT 'General' AFTER color";
        if ($conn->query($alter_sql)) {
            echo "<p>Added missing 'category' column</p>";
        }
    }
}

// Check if the cloth_details table exists (used by the product_operations.php)
$result = $conn->query("SHOW TABLES LIKE 'cloth_details'");
if ($result->num_rows == 0) {
    echo "<h2>The 'cloth_details' table does not exist</h2>";
} else {
    echo "<h2>The 'cloth_details' table exists</h2>";
    
    // Show table structure
    $result = $conn->query("DESCRIBE cloth_details");
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
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
    
    // Show count of items
    $count_result = $conn->query("SELECT COUNT(*) as count FROM cloth_details");
    $count_row = $count_result->fetch_assoc();
    echo "<p>Number of items in the 'cloth_details' table: " . $count_row['count'] . "</p>";
}

// Close the connection
$conn->close();
?> 