<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Set content type
header('Content-Type: text/html; charset=UTF-8');

echo "<h1>Session Information</h1>";

// Check session status
echo "Session Status: " . (session_status() == PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "<br>";
echo "Session ID: " . session_id() . "<br>";

// Check if user is logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    echo "<h2>User is logged in</h2>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "User Type: " . $_SESSION['user_type'] . "<br>";
    
    if (isset($_SESSION['user_name'])) {
        echo "User Name: " . $_SESSION['user_name'] . "<br>";
    }
    
    if (isset($_SESSION['user_email'])) {
        echo "User Email: " . $_SESSION['user_email'] . "<br>";
    }
    
    if (isset($_SESSION['shop_name'])) {
        echo "Shop Name: " . $_SESSION['shop_name'] . "<br>";
    }
    
    // Display all session variables
    echo "<h3>All Session Variables:</h3>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    
    // Add links to seller pages
    echo "<p><a href='../frontend/pages/seller/seller_profile.html'>Go to Shop Profile</a></p>";
    echo "<p><a href='../frontend/pages/seller/settings.html'>Go to Account Settings</a></p>";
    
    // Add logout link
    echo "<p><a href='?logout=1'>Logout</a></p>";
    
    // Handle logout
    if (isset($_GET['logout'])) {
        session_unset();
        session_destroy();
        echo "<p>Logged out! <a href='check_session.php'>Refresh</a> to see updated status.</p>";
    }
} else {
    echo "<h2>User is not logged in</h2>";
    
    // Display all session variables
    echo "<h3>All Session Variables:</h3>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    
    // Add login form
    echo "<h3>Quick Login</h3>";
    echo "<form method='post' action='api/users/login.php'>";
    echo "<div><label>Email: <input type='email' name='email' required></label></div>";
    echo "<div><label>Password: <input type='password' name='password' required></label></div>";
    echo "<div><button type='submit'>Login</button></div>";
    echo "</form>";
    
    echo "<p>After login, you will be redirected to dashboard. To see session info, visit this page again.</p>";
}

// Show cookies
echo "<h3>Cookies:</h3>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

// Show PHP info
echo "<h3>PHP Session Configuration:</h3>";
echo "session.cookie_lifetime: " . ini_get('session.cookie_lifetime') . "<br>";
echo "session.cookie_path: " . ini_get('session.cookie_path') . "<br>";
echo "session.cookie_domain: " . ini_get('session.cookie_domain') . "<br>";
echo "session.cookie_secure: " . ini_get('session.cookie_secure') . "<br>";
echo "session.cookie_httponly: " . ini_get('session.cookie_httponly') . "<br>";
echo "session.use_strict_mode: " . ini_get('session.use_strict_mode') . "<br>";
echo "session.use_cookies: " . ini_get('session.use_cookies') . "<br>";
echo "session.use_only_cookies: " . ini_get('session.use_only_cookies') . "<br>";
?>

<!-- Add some basic styling -->
<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
}
h1, h2, h3 {
    color: #333;
}
pre {
    background-color: #f5f5f5;
    padding: 10px;
    border-radius: 5px;
    overflow-x: auto;
}
a {
    color: #0066cc;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
form {
    margin: 20px 0;
    padding: 15px;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 5px;
}
form div {
    margin-bottom: 10px;
}
input {
    padding: 8px;
    width: 300px;
}
button {
    padding: 8px 15px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
button:hover {
    background-color: #45a049;
}
</style> 