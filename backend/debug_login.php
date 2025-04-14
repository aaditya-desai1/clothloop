<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html');
echo "<h1>Login Debugging Tool</h1>";

// Include database configuration
require_once __DIR__ . '/config/db_connect.php';

// Function to safely print user info (hiding password hash)
function printUserInfo($user) {
    $userCopy = $user;
    $userCopy['password'] = substr($userCopy['password'], 0, 10) . '... (truncated for security)';
    return "<pre>" . print_r($userCopy, true) . "</pre>";
}

try {
    if (isset($_POST['debug_login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        echo "<h2>Login Attempt for: " . htmlspecialchars($username) . "</h2>";
        
        // Step 1: Database connection check
        echo "<h3>Step 1: Database Connection</h3>";
        if (!isset($conn) || $conn->connect_error) {
            echo "<p style='color:red'>✗ Database connection failed: " . $conn->connect_error . "</p>";
        } else {
            echo "<p style='color:green'>✓ Database connection successful</p>";
        }
        
        // Step 2: Find user by username or email
        echo "<h3>Step 2: User Lookup</h3>";
        
        // Try username
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Check if username found
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<p style='color:green'>✓ User found by username</p>";
            echo printUserInfo($user);
        } else {
            echo "<p>User not found by username, trying email lookup...</p>";
            
            // Try email
            $stmt->close();
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                echo "<p style='color:green'>✓ User found by email</p>";
                echo printUserInfo($user);
            } else {
                echo "<p style='color:red'>✗ User not found in database</p>";
                echo "<p>Please check spelling or try the test user account</p>";
                $stmt->close();
                throw new Exception("User not found");
            }
        }
        
        // Step 3: Password verification
        echo "<h3>Step 3: Password Verification</h3>";
        
        // Check if password is hashed
        $stored_password = $user['password'];
        $is_hashed = (strpos($stored_password, '$2y$') === 0);
        
        if ($is_hashed) {
            echo "<p>✓ Password is properly hashed with bcrypt</p>";
        } else {
            echo "<p style='color:red'>✗ Password is NOT properly hashed - this is a security issue!</p>";
        }
        
        // Try normal password verification
        $verify_result = password_verify($password, $stored_password);
        if ($verify_result) {
            echo "<p style='color:green'>✓ Password verify SUCCESS - correct password entered</p>";
            $password_correct = true;
        } else {
            echo "<p style='color:red'>✗ Password verify FAILED</p>";
            
            // Try direct comparison (insecure, but checking for plaintext passwords)
            if ($password === $stored_password) {
                echo "<p style='color:orange'>⚠ Direct password comparison matched - password stored as plaintext!</p>";
                $password_correct = true;
            } else {
                echo "<p>Password incorrect. Try again or reset your password.</p>";
                $password_correct = false;
            }
        }
        
        // Step 4: Session handling
        echo "<h3>Step 4: Session Check</h3>";
        if (session_status() === PHP_SESSION_NONE) {
            echo "<p>Session not started - would start with session_start()</p>";
            session_start();
        } else {
            echo "<p>Session already started</p>";
        }
        
        // Final result
        echo "<h3>Final Result</h3>";
        if (isset($user) && $password_correct) {
            echo "<p style='color:green'>✓ LOGIN SUCCESS! User would be logged in.</p>";
        } else {
            echo "<p style='color:red'>✗ LOGIN FAILED. See above for details.</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>

<h2>Test Login</h2>
<form method="post">
    <label for="username">Username or Email:</label><br>
    <input type="text" id="username" name="username" required><br><br>
    
    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password" required><br><br>
    
    <input type="submit" name="debug_login" value="Debug Login">
</form>

<hr>
<h2>Quick Links</h2>
<ul>
    <li><a href="add_test_user.php">Add Test User</a></li>
    <li><a href="../Registration/login.html">Go to Login Page</a></li>
</ul> 