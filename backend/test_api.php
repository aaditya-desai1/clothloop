<?php
// Enable errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include session configuration
require_once 'config/session.php';

// Include database connection
require_once 'config/db_connect.php';

// Set headers for easier viewing
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
        }
        .endpoint {
            background: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .response {
            background: #eef;
            padding: 10px;
            border: 1px solid #ddf;
            max-height: 300px;
            overflow: auto;
            white-space: pre-wrap;
            font-family: monospace;
            margin-top: 10px;
        }
        button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            cursor: pointer;
            border-radius: 4px;
            margin-right: 10px;
        }
        input, select {
            padding: 8px;
            margin: 5px 0;
            display: block;
            width: 300px;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>API Test Console</h1>
    
    <div class="session-info">
        <h2>Current Session</h2>
        <div class="response">
            Session ID: <?php echo session_id(); ?>
            
            Session Data:
            <?php print_r($_SESSION); ?>
            
            Cookie Data:
            <?php print_r($_COOKIE); ?>
        </div>
    </div>
    
    <h2>API Endpoints</h2>
    
    <div class="endpoint">
        <h3>Login</h3>
        <form id="loginForm">
            <input type="email" id="loginEmail" placeholder="Email" value="test@example.com">
            <input type="password" id="loginPassword" placeholder="Password" value="test123">
            <button type="submit">Login</button>
        </form>
        <div id="loginResponse" class="response"></div>
    </div>
    
    <div class="endpoint">
        <h3>Check Session</h3>
        <button id="checkSessionBtn">Check Session</button>
        <div id="checkSessionResponse" class="response"></div>
    </div>
    
    <div class="endpoint">
        <h3>Get Buyer Profile</h3>
        <button id="getBuyerProfileBtn">Get Profile</button>
        <div id="getBuyerProfileResponse" class="response"></div>
    </div>
    
    <div class="endpoint">
        <h3>Logout</h3>
        <button id="logoutBtn">Logout</button>
        <div id="logoutResponse" class="response"></div>
    </div>
    
    <script>
        // Login form
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            fetch('../backend/api/users/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    email: email,
                    password: password
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loginResponse').innerHTML = JSON.stringify(data, null, 2);
                if (data.success) {
                    document.getElementById('loginResponse').classList.add('success');
                    document.getElementById('loginResponse').classList.remove('error');
                    // Reload page after successful login
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    document.getElementById('loginResponse').classList.add('error');
                    document.getElementById('loginResponse').classList.remove('success');
                }
            })
            .catch(error => {
                document.getElementById('loginResponse').innerHTML = 'Error: ' + error.message;
                document.getElementById('loginResponse').classList.add('error');
            });
        });
        
        // Check session
        document.getElementById('checkSessionBtn').addEventListener('click', function() {
            fetch('../backend/api/users/check_session.php', {
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('checkSessionResponse').innerHTML = JSON.stringify(data, null, 2);
            })
            .catch(error => {
                document.getElementById('checkSessionResponse').innerHTML = 'Error: ' + error.message;
            });
        });
        
        // Get buyer profile
        document.getElementById('getBuyerProfileBtn').addEventListener('click', function() {
            fetch('../backend/api/buyers/get_buyer_profile.php', {
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('getBuyerProfileResponse').innerHTML = JSON.stringify(data, null, 2);
            })
            .catch(error => {
                document.getElementById('getBuyerProfileResponse').innerHTML = 'Error: ' + error.message;
            });
        });
        
        // Logout
        document.getElementById('logoutBtn').addEventListener('click', function() {
            fetch('../backend/api/users/logout.php', {
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('logoutResponse').innerHTML = JSON.stringify(data, null, 2);
                if (data.status === 'success') {
                    // Reload page after successful logout
                    setTimeout(() => window.location.reload(), 1000);
                }
            })
            .catch(error => {
                document.getElementById('logoutResponse').innerHTML = 'Error: ' + error.message;
            });
        });
    </script>
</body>
</html> 