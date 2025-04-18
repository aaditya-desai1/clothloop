<?php
// Include database connection
require_once 'backend/config/db_connect.php';

echo "<h1>Fixing Database Structure</h1>";

// Check if product_reviews table exists
$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'product_reviews'");
if (mysqli_num_rows($checkTable) == 0) {
    // Create product_reviews table if it doesn't exist
    $createTable = "CREATE TABLE product_reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        buyer_id INT,
        rating INT NOT NULL,
        review TEXT NOT NULL,
        created_at DATETIME NOT NULL
    )";
    
    if (mysqli_query($conn, $createTable)) {
        echo "<p>Product Reviews table created successfully!</p>";
    } else {
        echo "<p>Error creating product_reviews table: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p>Product Reviews table already exists.</p>";
    
    // Check if review column exists
    $checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM product_reviews LIKE 'review'");
    if (mysqli_num_rows($checkColumn) == 0) {
        // Add review column if it doesn't exist
        $addColumn = "ALTER TABLE product_reviews ADD COLUMN review TEXT NOT NULL";
        
        if (mysqli_query($conn, $addColumn)) {
            echo "<p>Review column added successfully!</p>";
        } else {
            echo "<p>Error adding review column: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p>Review column already exists.</p>";
    }
}

// Display current table structure
$result = mysqli_query($conn, "DESCRIBE product_reviews");
if ($result) {
    echo "<h2>Current Product Reviews Table Structure</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>Error checking table structure: " . mysqli_error($conn) . "</p>";
}

// Close the connection
mysqli_close($conn);
?> 