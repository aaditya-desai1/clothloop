<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    // Redirect to login page
    header('Location: test_login.php');
    exit;
}

// Check if user is a seller
if ($_SESSION['user']['role'] !== 'seller') {
    // Redirect to dashboard
    header('Location: test_dashboard.php');
    exit;
}

// Get user data
$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - ClothLoop</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .header {
            background-color: #333;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            margin: 0;
            font-size: 1.5rem;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .user-info span {
            margin-right: 1rem;
        }
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        .radio-group {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }
        .radio-option {
            display: flex;
            align-items: center;
        }
        .radio-option input {
            margin-right: 0.5rem;
        }
        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .message {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            display: none;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .preview-images {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }
        .preview-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ClothLoop</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
            <a href="test_dashboard.php" class="btn btn-secondary">Dashboard</a>
        </div>
    </div>
    
    <div class="container">
        <h2>Add New Product</h2>
        
        <div id="message" class="message"></div>
        
        <div class="card">
            <form id="product-form">
                <div class="form-group">
                    <label for="title">Product Title *</label>
                    <input type="text" id="title" name="title" required placeholder="Enter a descriptive title">
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" required placeholder="Describe the item, its condition, features, etc."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Formal Wear">Formal Wear</option>
                        <option value="Casual Wear">Casual Wear</option>
                        <option value="Party Wear">Party Wear</option>
                        <option value="Ethnic Wear">Ethnic Wear</option>
                        <option value="Winter Wear">Winter Wear</option>
                        <option value="Summer Wear">Summer Wear</option>
                        <option value="Accessories">Accessories</option>
                        <option value="Footwear">Footwear</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="size">Size *</label>
                    <select id="size" name="size" required>
                        <option value="">Select Size</option>
                        <option value="XS">XS</option>
                        <option value="S">S</option>
                        <option value="M">M</option>
                        <option value="L">L</option>
                        <option value="XL">XL</option>
                        <option value="XXL">XXL</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="occasion">Occasion</label>
                    <input type="text" id="occasion" name="occasion" placeholder="E.g. Wedding, Business, Casual">
                </div>
                
                <div class="form-group">
                    <label for="rental_price">Rental Price per Day (₹) *</label>
                    <input type="number" id="rental_price" name="rental_price" min="0" step="0.01" required placeholder="Daily rental price">
                </div>
                
                <div class="form-group">
                    <label for="terms">Rental Terms</label>
                    <textarea id="terms" name="terms" placeholder="Specify any terms or conditions for renting this item"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="images">Product Images *</label>
                    <input type="file" id="images" name="images[]" multiple accept="image/*" required>
                    <small>You can select multiple images. First image will be the primary image.</small>
                    
                    <div id="preview-container" class="preview-images"></div>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn">Add Product</button>
                    <a href="test_dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productForm = document.getElementById('product-form');
            const imagesInput = document.getElementById('images');
            const previewContainer = document.getElementById('preview-container');
            const messageDiv = document.getElementById('message');
            
            // Show image previews
            imagesInput.addEventListener('change', function() {
                previewContainer.innerHTML = '';
                
                if (this.files) {
                    for (let i = 0; i < this.files.length; i++) {
                        const file = this.files[i];
                        if (!file.type.startsWith('image/')) continue;
                        
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.classList.add('preview-image');
                            previewContainer.appendChild(img);
                        }
                        reader.readAsDataURL(file);
                    }
                }
            });
            
            // Form submission
            productForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Create FormData object
                const formData = new FormData(this);
                
                // Send data to server
                fetch('backend/api/sellers/upload_cloth.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showMessage(data.message, 'success');
                        
                        // Clear form
                        productForm.reset();
                        previewContainer.innerHTML = '';
                        
                        // Redirect to products list after a delay
                        setTimeout(() => {
                            window.location.href = 'test_dashboard.php';
                        }, 2000);
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    showMessage('An error occurred. Please try again.', 'error');
                    console.error('Error:', error);
                });
            });
            
            // Helper function to show messages
            function showMessage(message, type) {
                messageDiv.textContent = message;
                messageDiv.className = 'message ' + type;
                messageDiv.style.display = 'block';
                
                // Scroll to message
                messageDiv.scrollIntoView({ behavior: 'smooth' });
            }
        });
    </script>
</body>
</html> 