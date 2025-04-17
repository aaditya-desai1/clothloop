<?php
// Display any errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'config/db_connect.php';

// Process login form submission
$loginMessage = '';
$loginSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $loginMessage = 'Email and password are required';
    } else {
        // Check in buyers table
        $stmt = $conn->prepare("SELECT id, name, email, password FROM buyers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = 'buyer';
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                
                $loginSuccess = true;
                $loginMessage = 'Login successful as buyer!';
            } else {
                $loginMessage = 'Invalid password';
            }
        } else {
            // Check sellers table
            $stmt = $conn->prepare("SELECT id, name, email, password FROM sellers WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['password'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_type'] = 'seller';
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    
                    $loginSuccess = true;
                    $loginMessage = 'Login successful as seller!';
                } else {
                    $loginMessage = 'Invalid password';
                }
            } else {
                $loginMessage = 'User not found';
            }
        }
    }
}

// Process logout
if (isset($_GET['logout'])) {
    // Destroy the session
    session_unset();
    session_destroy();
    $loginMessage = 'You have been logged out';
    header('Location: test_login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        form {
            background: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background: #333;
            color: #fff;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 4px;
        }
        .session-data {
            background: #f5f5f5;
            padding: 15px;
            border: 1px solid #ddd;
            margin-top: 20px;
        }
        pre {
            white-space: pre-wrap;
            background: #eee;
            padding: 10px;
            overflow: auto;
        }
    </style>
</head>
<body>
    <h1>Test Login Page</h1>
    
    <?php if (!empty($loginMessage)): ?>
        <div class="message <?php echo $loginSuccess ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($loginMessage); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Display logged in information and logout button -->
        <div class="success message">
            <p>You are logged in as: <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong> (<?php echo htmlspecialchars($_SESSION['user_type']); ?>)</p>
            <p>Email: <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
            <p>User ID: <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
        </div>
        
        <p><a href="../frontend/pages/buyer/buyer_profile.html">Go to Profile Page</a> | <a href="?logout=1">Logout</a></p>
        
    <?php else: ?>
        <!-- Login form -->
        <form method="post" action="">
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" name="login">Login</button>
        </form>
        
        <p>Test User Credentials:</p>
        <ul>
            <li>Email: test@example.com</li>
            <li>Password: test123</li>
        </ul>
    <?php endif; ?>
    
    <!-- Session information for debugging -->
    <div class="session-data">
        <h2>Session Information</h2>
        <p>Session ID: <?php echo session_id(); ?></p>
        
        <h3>Session Data:</h3>
        <pre><?php print_r($_SESSION); ?></pre>
        
        <h3>Cookie Information:</h3>
        <pre><?php print_r($_COOKIE); ?></pre>
        
        <h3>Session Settings:</h3>
        <ul>
            <li>session.cookie_httponly: <?php echo ini_get('session.cookie_httponly'); ?></li>
            <li>session.cookie_secure: <?php echo ini_get('session.cookie_secure'); ?></li>
            <li>session.use_only_cookies: <?php echo ini_get('session.use_only_cookies'); ?></li>
            <li>session.save_path: <?php echo ini_get('session.save_path'); ?></li>
        </ul>
    </div>
</body>
</html> 