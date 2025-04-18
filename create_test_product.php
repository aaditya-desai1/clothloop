<?php
// Include database connection
require_once 'backend/config/db_connect.php';

echo "<h1>Creating Test Product</h1>";

// Check if any products exist
$anyProducts = mysqli_query($conn, "SELECT id FROM products ORDER BY id ASC LIMIT 1");
$existingId = null;

if (mysqli_num_rows($anyProducts) > 0) {
    $row = mysqli_fetch_assoc($anyProducts);
    $existingId = $row['id'];
    echo "<p>Found existing product with ID: {$existingId}</p>";
}

// First check if product with ID 1 already exists
$checkProduct = mysqli_query($conn, "SELECT * FROM products WHERE id = 1");

if (mysqli_num_rows($checkProduct) > 0) {
    echo "<p>Test product with ID 1 already exists.</p>";
    
    // Display the product details
    $product = mysqli_fetch_assoc($checkProduct);
    echo "<h2>Existing Product Details</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Category</th></tr>";
    echo "<tr>";
    echo "<td>{$product['id']}</td>";
    echo "<td>{$product['name']}</td>";
    echo "<td>{$product['price']}</td>";
    echo "<td>{$product['category']}</td>";
    echo "</tr>";
    echo "</table>";
    
    echo "<script>
        // Update product_id in the review form (in the parent window if opened in a new tab)
        if (window.opener && !window.opener.closed) {
            const input = window.opener.document.querySelector('input[name=\"product_id\"]');
            if (input) input.value = '1';
        }
    </script>";
} else {
    // Create a test product with ID 1
    $createProduct = "INSERT INTO products (id, name, description, price, seller_id, category, status, created_at) 
                      VALUES (1, 'Test Product', 'This is a test product for reviews', 99.99, 1, 'Test', 'active', NOW())";
    
    if (mysqli_query($conn, $createProduct)) {
        echo "<p>Test product created successfully with ID 1.</p>";
        echo "<script>
            // Update product_id in the review form (in the parent window if opened in a new tab)
            if (window.opener && !window.opener.closed) {
                const input = window.opener.document.querySelector('input[name=\"product_id\"]');
                if (input) input.value = '1';
            }
        </script>";
    } else {
        echo "<p>Error creating test product: " . mysqli_error($conn) . "</p>";
        
        if ($existingId !== null) {
            echo "<p>However, you can use the existing product ID: {$existingId} for your review.</p>";
            echo "<button onclick=\"updateProductId({$existingId})\">Use This Product ID</button>";
            echo "<script>
                function updateProductId(id) {
                    if (window.opener && !window.opener.closed) {
                        const input = window.opener.document.querySelector('input[name=\"product_id\"]');
                        if (input) {
                            input.value = id;
                            alert('Product ID updated to ' + id + ' in the review form');
                        }
                    }
                }
            </script>";
        }
        
        // Let's check the structure of the products table to see what columns are required
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
    }
}

// Display all products for reference
$allProducts = mysqli_query($conn, "SELECT * FROM products ORDER BY id ASC LIMIT 10");
if (mysqli_num_rows($allProducts) > 0) {
    echo "<h2>All Available Products</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Category</th><th>Action</th></tr>";
    
    while ($row = mysqli_fetch_assoc($allProducts)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$row['price']}</td>";
        echo "<td>{$row['category']}</td>";
        echo "<td><button onclick=\"updateProductId({$row['id']})\">Use This ID</button></td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

// Close the connection
mysqli_close($conn);
?> 