<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html');
echo "<h1>Create New User</h1>";

try {
    // Include database configuration
    require_once __DIR__ . '/config/db_connect.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get form data
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $phone = $_POST['phone'];
        $user_type = $_POST['user_type'];
        
        // Validate data
        if (empty($username) || empty($email) || empty($password) || empty($phone) || empty($user_type)) {
            throw new Exception("All fields are required");
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Check if username or email exists
        $check_stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("Username or email already exists");
        }
        
        // Insert user
        $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, phone, user_type) VALUES (?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("sssss", $username, $email, $hashed_password, $phone, $user_type);
        
        if ($insert_stmt->execute()) {
            echo "<div style='background-color: #dff0d8; color: #3c763d; border: 1px solid #d6e9c6; padding: 15px; margin-bottom: 20px; border-radius: 4px;'>";
            echo "<h3>User Created Successfully!</h3>";
            echo "<p><strong>Username:</strong> " . htmlspecialchars($username) . "</p>";
            echo "<p><strong>Password:</strong> " . htmlspecialchars($password) . " (store this securely)</p>";
            echo "<p><strong>User Type:</strong> " . htmlspecialchars($user_type) . "</p>";
            echo "<p><a href='../Registration/login.html'>Go to login page</a></p>";
            echo "</div>";
        } else {
            throw new Exception("Error creating user: " . $insert_stmt->error);
        }
    }
} catch (Exception $e) {
    echo "<div style='background-color: #f2dede; color: #a94442; border: 1px solid #ebccd1; padding: 15px; margin-bottom: 20px; border-radius: 4px;'>";
    echo "<h3>Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
} 
?>

<form method="post" style="max-width: 500px; margin: 0 auto;">
    <div style="margin-bottom: 15px;">
        <label for="username" style="display: block; margin-bottom: 5px;">Username:</label>
        <input type="text" id="username" name="username" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="email" style="display: block; margin-bottom: 5px;">Email:</label>
        <input type="email" id="email" name="email" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="password" style="display: block; margin-bottom: 5px;">Password:</label>
        <input type="password" id="password" name="password" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="phone" style="display: block; margin-bottom: 5px;">Phone:</label>
        <input type="text" id="phone" name="phone" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="user_type" style="display: block; margin-bottom: 5px;">User Type:</label>
        <select id="user_type" name="user_type" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="buyer">Buyer</option>
            <option value="seller">Seller</option>
        </select>
    </div>
    
    <button type="submit" style="background-color: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;">Create User</button>
</form>

<hr>
<h2>Quick Links</h2>
<ul>
    <li><a href="debug_login.php">Login Debugger</a></li>
    <li><a href="add_test_user.php">Add Test User</a></li>
    <li><a href="../Registration/login.html">Go to Login Page</a></li>
</ul> 