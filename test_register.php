<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .buyer-fields,
        .seller-fields {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
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
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test User Registration</h1>
        
        <div id="response-message" class="message" style="display: none;"></div>
        
        <form id="register-form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="user_type">User Type:</label>
                <select id="user_type" name="user_type" required>
                    <option value="">Select User Type</option>
                    <option value="buyer">Buyer</option>
                    <option value="seller">Seller</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="phone_no">Phone Number:</label>
                <input type="text" id="phone_no" name="phone_no" required>
            </div>
            
            <div class="form-group">
                <label for="profile_photo">Profile Photo (optional):</label>
                <input type="file" id="profile_photo" name="profile_photo">
            </div>
            
            <!-- Buyer Specific Fields -->
            <div id="buyer-fields" class="buyer-fields">
                <h3>Buyer Information</h3>
                <div class="form-group">
                    <label for="buyer_latitude">Latitude:</label>
                    <input type="text" id="buyer_latitude" name="buyer_latitude" placeholder="e.g. 40.7128">
                </div>
                
                <div class="form-group">
                    <label for="buyer_longitude">Longitude:</label>
                    <input type="text" id="buyer_longitude" name="buyer_longitude" placeholder="e.g. -74.0060">
                </div>
                
                <button type="button" id="get-location">Get My Location</button>
            </div>
            
            <!-- Seller Specific Fields -->
            <div id="seller-fields" class="seller-fields">
                <h3>Seller Information</h3>
                <div class="form-group">
                    <label for="shop_name">Shop Name:</label>
                    <input type="text" id="shop_name" name="shop_name">
                </div>
                
                <div class="form-group">
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address">
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 20px;">
                <button type="submit">Register</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userTypeSelect = document.getElementById('user_type');
            const buyerFields = document.getElementById('buyer-fields');
            const sellerFields = document.getElementById('seller-fields');
            const getLocationBtn = document.getElementById('get-location');
            const registerForm = document.getElementById('register-form');
            const responseMessage = document.getElementById('response-message');

            // Toggle fields based on user type
            userTypeSelect.addEventListener('change', function() {
                if (this.value === 'buyer') {
                    buyerFields.style.display = 'block';
                    sellerFields.style.display = 'none';
                } else if (this.value === 'seller') {
                    buyerFields.style.display = 'none';
                    sellerFields.style.display = 'block';
                } else {
                    buyerFields.style.display = 'none';
                    sellerFields.style.display = 'none';
                }
            });

            // Get user's location
            getLocationBtn.addEventListener('click', function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        document.getElementById('buyer_latitude').value = position.coords.latitude;
                        document.getElementById('buyer_longitude').value = position.coords.longitude;
                    }, function(error) {
                        alert('Error getting location: ' + error.message);
                    });
                } else {
                    alert('Geolocation is not supported by this browser.');
                }
            });

            // Handle form submission
            registerForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                // Client-side validation
                const userType = formData.get('user_type');
                if (userType === 'buyer') {
                    if (!formData.get('buyer_latitude') || !formData.get('buyer_longitude')) {
                        showMessage('Please provide location information for buyer account.', 'error');
                        return;
                    }
                } else if (userType === 'seller') {
                    if (!formData.get('shop_name') || !formData.get('address')) {
                        showMessage('Please provide shop name and address for seller account.', 'error');
                        return;
                    }
                }
                
                // Send data to server
                fetch('backend/api/users/signup_process.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showMessage(data.message, 'success');
                        registerForm.reset();
                        // Redirect to login or dashboard
                        setTimeout(() => {
                            window.location.href = 'test_login.php';
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
                responseMessage.textContent = message;
                responseMessage.className = 'message ' + type;
                responseMessage.style.display = 'block';
                
                // Scroll to message
                responseMessage.scrollIntoView({ behavior: 'smooth' });
            }
        });
    </script>
</body>
</html> 