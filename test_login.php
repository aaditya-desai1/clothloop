<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 500px;
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
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
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
        .register-link {
            margin-top: 15px;
            text-align: center;
        }
        .register-link a {
            color: #4CAF50;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test Login</h1>
        
        <div id="response-message" class="message" style="display: none;"></div>
        
        <form id="login-form">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <button type="submit">Login</button>
            </div>
        </form>
        
        <div class="register-link">
            <p>Don't have an account? <a href="test_register.php">Register here</a></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('login-form');
            const responseMessage = document.getElementById('response-message');
            
            // Handle form submission
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = {
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value
                };
                
                // Send data to server
                fetch('backend/api/users/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showMessage(data.message, 'success');
                        
                        // Store user role for redirect
                        const userRole = data.data.user.role;
                        
                        // Redirect based on user role
                        setTimeout(() => {
                            if (userRole === 'buyer') {
                                window.location.href = 'test_buyer_dashboard.php';
                            } else if (userRole === 'seller') {
                                window.location.href = 'test_seller_dashboard.php';
                            } else {
                                window.location.href = 'test_dashboard.php';
                            }
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