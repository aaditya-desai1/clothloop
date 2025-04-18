<?php
// Include database connection
require_once 'backend/config/db_connect.php';

echo "<h1>Fixing Foreign Key Constraints</h1>";

// Step 1: Check if both tables exist
$tablesOk = true;

// Check products table
$checkProducts = mysqli_query($conn, "SHOW TABLES LIKE 'products'");
if (mysqli_num_rows($checkProducts) == 0) {
    echo "<p style='color: red;'>ERROR: Products table does not exist!</p>";
    $tablesOk = false;
}

// Check product_reviews table
$checkReviews = mysqli_query($conn, "SHOW TABLES LIKE 'product_reviews'");
if (mysqli_num_rows($checkReviews) == 0) {
    echo "<p style='color: red;'>ERROR: Product_reviews table does not exist!</p>";
    $tablesOk = false;
}

if (!$tablesOk) {
    echo "<p>Please create the missing tables before proceeding.</p>";
    exit;
}

// Step 2: Display table structure
echo "<h2>Current Table Structures</h2>";

// Products table structure
$productsStructure = mysqli_query($conn, "DESCRIBE products");
if ($productsStructure) {
    echo "<h3>Products Table</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($productsStructure)) {
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

// Product_reviews table structure
$reviewsStructure = mysqli_query($conn, "DESCRIBE product_reviews");
if ($reviewsStructure) {
    echo "<h3>Product_reviews Table</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($reviewsStructure)) {
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

// Step 3: Check for valid product IDs
$validProducts = mysqli_query($conn, "SELECT id FROM products ORDER BY id ASC LIMIT 10");
if (mysqli_num_rows($validProducts) > 0) {
    echo "<h3>Valid Product IDs for Testing</h3>";
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($validProducts)) {
        echo "<li>Product ID: {$row['id']}</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>WARNING: No products found in the database.</p>";
    
    // Create a test product
    $createProduct = "INSERT INTO products (name, description, price, seller_id, category, status, created_at) 
                      VALUES ('Test Product', 'This is a test product for reviews', 99.99, 1, 'Test', 'active', NOW())";
    
    if (mysqli_query($conn, $createProduct)) {
        $newId = mysqli_insert_id($conn);
        echo "<p style='color: green;'>Created a test product with ID: {$newId}</p>";
    } else {
        echo "<p style='color: red;'>Failed to create test product: " . mysqli_error($conn) . "</p>";
    }
}

// Step 4: Provide a solution
echo "<h2>Recommendations</h2>";
echo "<p>To fix the foreign key constraint error, try the following:</p>";
echo "<ol>";
echo "<li>Update the product_id in your test-review.php form to use one of the valid product IDs listed above.</li>";
echo "<li>If no products exist, create a test product by clicking <a href='create_test_product.php'>here</a>.</li>";
echo "<li>Make sure the buyer_id in the review form is either a valid buyer ID or NULL (if allowed).</li>";
echo "</ol>";

echo "<h2>Test Review Form</h2>";
echo "<p>Return to the <a href='test-review.php'>Test Review Form</a> to try again.</p>";

// Close the connection
mysqli_close($conn);
?> 