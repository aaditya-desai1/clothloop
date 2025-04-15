<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'config/db_connect.php';

// Get the operation
$operation = isset($_GET['operation']) ? $_GET['operation'] : 'test';

if ($operation === 'test') {
    // Display form for testing operations
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Product Operations Test</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
            }
            h1, h2 {
                color: #333;
            }
            .card {
                background-color: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 20px;
                margin-bottom: 20px;
            }
            form {
                margin-bottom: 20px;
            }
            label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            input, textarea, select {
                width: 100%;
                padding: 8px;
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-sizing: border-box;
            }
            button {
                background-color: #4CAF50;
                color: white;
                padding: 10px 15px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
            button:hover {
                background-color: #45a049;
            }
            pre {
                background-color: #f5f5f5;
                padding: 10px;
                border-radius: 4px;
                overflow-x: auto;
            }
            .error {
                color: #ff0000;
            }
            .success {
                color: #4CAF50;
            }
        </style>
    </head>
    <body>
        <h1>ClothLoop Product Operations Test</h1>
        
        <div class="card">
            <h2>Database Status</h2>
            <?php
            try {
                // Check connection
                if ($conn->connect_error) {
                    echo "<p class='error'>Connection failed: " . $conn->connect_error . "</p>";
                } else {
                    echo "<p class='success'>Database connection: OK</p>";
                    
                    // Check tables
                    $tables = ["cloth_details", "cloth_images", "users"];
                    foreach ($tables as $table) {
                        $result = $conn->query("SHOW TABLES LIKE '$table'");
                        if ($result->num_rows > 0) {
                            echo "<p class='success'>Table '$table': EXISTS</p>";
                            
                            // Count records
                            $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
                            $count = $count_result->fetch_assoc()['count'];
                            echo "<p>Records in '$table': $count</p>";
                        } else {
                            echo "<p class='error'>Table '$table': MISSING</p>";
                        }
                    }
                }
            } catch (Exception $e) {
                echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
        
        <div class="card">
            <h2>Add Product Test</h2>
            <form id="add-form" method="post" action="utils/product_operations.php?operation=add" enctype="multipart/form-data">
                <input type="hidden" name="seller_id" value="1">
                
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="Test Product" required>
                
                <label for="description">Description:</label>
                <textarea id="description" name="description" required>This is a test product description.</textarea>
                
                <label for="size">Size:</label>
                <input type="text" id="size" name="size" value="M">
                
                <label for="category">Category:</label>
                <input type="text" id="category" name="category" value="Casual">
                
                <label for="rental_price">Rental Price:</label>
                <input type="number" id="rental_price" name="rental_price" value="199.99" step="0.01" required>
                
                <label for="contact_number">Contact Number:</label>
                <input type="text" id="contact_number" name="contact_number" value="9876543210">
                
                <label for="whatsapp_number">WhatsApp Number:</label>
                <input type="text" id="whatsapp_number" name="whatsapp_number" value="9876543210">
                
                <label for="terms_and_conditions">Terms:</label>
                <textarea id="terms_and_conditions" name="terms_and_conditions">Return in good condition.</textarea>
                
                <label for="image">Image:</label>
                <input type="file" id="image" name="image" accept="image/*">
                
                <button type="submit">Test Add Product</button>
            </form>
            
            <div id="add-result"></div>
        </div>
        
        <div class="card">
            <h2>Update Product Test</h2>
            <form id="update-form" method="post" action="utils/product_operations.php?operation=update" enctype="multipart/form-data">
                <label for="product_id">Product ID to Update:</label>
                <input type="number" id="product_id" name="id" required>
                
                <label for="update_title">Title:</label>
                <input type="text" id="update_title" name="title" value="Updated Test Product" required>
                
                <label for="update_description">Description:</label>
                <textarea id="update_description" name="description" required>This is an updated product description.</textarea>
                
                <label for="update_size">Size:</label>
                <input type="text" id="update_size" name="size" value="L">
                
                <label for="update_category">Category:</label>
                <input type="text" id="update_category" name="category" value="Formal">
                
                <label for="update_rental_price">Rental Price:</label>
                <input type="number" id="update_rental_price" name="rental_price" value="299.99" step="0.01" required>
                
                <label for="update_contact_number">Contact Number:</label>
                <input type="text" id="update_contact_number" name="contact_number" value="9876543210">
                
                <label for="update_whatsapp_number">WhatsApp Number:</label>
                <input type="text" id="update_whatsapp_number" name="whatsapp_number" value="9876543210">
                
                <label for="update_terms_and_conditions">Terms:</label>
                <textarea id="update_terms_and_conditions" name="terms_and_conditions">Return in good condition after dry cleaning.</textarea>
                
                <label for="update_image">Image:</label>
                <input type="file" id="update_image" name="image" accept="image/*">
                
                <button type="submit">Test Update Product</button>
            </form>
            
            <div id="update-result"></div>
        </div>
        
        <div class="card">
            <h2>Delete Product Test</h2>
            <form id="delete-form" method="post" action="utils/product_operations.php?operation=delete">
                <label for="delete_id">Product ID to Delete:</label>
                <input type="number" id="delete_id" name="id" required>
                
                <button type="submit">Test Delete Product</button>
            </form>
            
            <div id="delete-result"></div>
        </div>
        
        <div class="card">
            <h2>View All Products</h2>
            <button id="view-all-btn">View All Products</button>
            <div id="products-list"></div>
        </div>
        
        <script>
            // Add product form submission
            document.getElementById('add-form').addEventListener('submit', function(e) {
                e.preventDefault();
                submitForm('add-form', 'add-result');
            });
            
            // Update product form submission
            document.getElementById('update-form').addEventListener('submit', function(e) {
                e.preventDefault();
                submitForm('update-form', 'update-result');
            });
            
            // Delete product form submission
            document.getElementById('delete-form').addEventListener('submit', function(e) {
                e.preventDefault();
                submitForm('delete-form', 'delete-result');
            });
            
            // View all products
            document.getElementById('view-all-btn').addEventListener('click', function() {
                fetch('utils/product_operations.php?operation=fetch_all')
                    .then(response => response.json())
                    .then(data => {
                        const resultDiv = document.getElementById('products-list');
                        resultDiv.innerHTML = '<h3>Result:</h3><pre>' + JSON.stringify(data, null, 2) + '</pre>';
                        
                        if (data.status === 'success' && data.products && data.products.length > 0) {
                            let productsHtml = '<h3>Products:</h3><ul>';
                            data.products.forEach(product => {
                                productsHtml += `<li>
                                    <strong>ID:</strong> ${product.id} | 
                                    <strong>Title:</strong> ${product.title} | 
                                    <strong>Price:</strong> ₹${product.rental_price}
                                </li>`;
                            });
                            productsHtml += '</ul>';
                            resultDiv.innerHTML += productsHtml;
                        }
                    })
                    .catch(error => {
                        document.getElementById('products-list').innerHTML = 
                            '<h3>Error:</h3><pre class="error">' + error.message + '</pre>';
                    });
            });
            
            // Function to submit forms
            function submitForm(formId, resultDivId) {
                const form = document.getElementById(formId);
                const formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        const resultDiv = document.getElementById(resultDivId);
                        resultDiv.innerHTML = '<h3>Result:</h3><pre>' + JSON.stringify(data, null, 2) + '</pre>';
                        
                        if (data.status === 'success') {
                            resultDiv.innerHTML += '<p class="success">' + data.message + '</p>';
                        } else {
                            resultDiv.innerHTML += '<p class="error">' + data.message + '</p>';
                        }
                    })
                    .catch(error => {
                        document.getElementById(resultDivId).innerHTML = 
                            '<h3>Error:</h3><pre class="error">' + error.message + '</pre>';
                    });
            }
        </script>
    </body>
    </html>
    <?php
} else {
    echo "Invalid operation. Use ?operation=test to show the test form.";
}
?> 