<?php
// Include database connection
require_once 'backend/config/db_connect.php';

echo "<h1>Updating product_reviews Table Structure</h1>";

// Check if the table exists
$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'product_reviews'");
if (mysqli_num_rows($checkTable) > 0) {
    // Modify the buyer_id column to allow NULL values
    $modifyColumn = "ALTER TABLE product_reviews MODIFY COLUMN buyer_id INT NULL";
    
    if (mysqli_query($conn, $modifyColumn)) {
        echo "<p>Successfully modified buyer_id column to allow NULL values.</p>";
    } else {
        echo "<p>Error modifying column: " . mysqli_error($conn) . "</p>";
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
    }
} else {
    echo "<p>product_reviews table does not exist.</p>";
}

// Close the connection
mysqli_close($conn);
?> 