<?php
// Include database connection
require_once 'backend/config/db_connect.php';

echo "<h1>Available Products</h1>";

// Check if products table exists
$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'products'");
if (mysqli_num_rows($checkTable) == 0) {
    echo "<p>Products table does not exist!</p>";
} else {
    // Get all products
    $result = mysqli_query($conn, "SELECT * FROM products LIMIT 10");
    
    if (mysqli_num_rows($result) > 0) {
        echo "<h2>Products Available for Review</h2>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Category</th></tr>";
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['name']}</td>";
            echo "<td>{$row['price']}</td>";
            echo "<td>{$row['category']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<p>Use one of these product IDs in your test-review.php form.</p>";
    } else {
        echo "<p>No products found in the database. Please create a test product first.</p>";
        echo "<p><a href='create_test_product.php'>Create Test Product</a></p>";
    }
}

// Display products table structure
$result = mysqli_query($conn, "DESCRIBE products");
if ($result) {
    echo "<h2>Products Table Structure</h2>";
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

// Close the connection
mysqli_close($conn);
?> 