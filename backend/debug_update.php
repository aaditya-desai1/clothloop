<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');

echo "<h1>Debug Form Submission</h1>";

// Start session for user authentication
session_start();

echo "<h2>Session Information</h2>";
echo "Session started: " . (session_status() == PHP_SESSION_ACTIVE ? 'Yes' : 'No') . "<br>";
echo "Session ID: " . session_id() . "<br>";

// Check if user is logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "User Type: " . $_SESSION['user_type'] . "<br>";
} else {
    echo "No user logged in<br>";
}

// Check POST data
echo "<h2>POST Data</h2>";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "POST data received:<br>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
} else {
    echo "No POST data (GET request)<br>";
}

// Check FILES data
echo "<h2>FILES Data</h2>";
if (isset($_FILES) && !empty($_FILES)) {
    echo "Files received:<br>";
    echo "<pre>";
    print_r($_FILES);
    echo "</pre>";
} else {
    echo "No files uploaded<br>";
}

// Check upload directories
echo "<h2>Upload Directories</h2>";

// Check sellers directory
$sellersDir = "../uploads/sellers/";
if (!file_exists($sellersDir)) {
    if (mkdir($sellersDir, 0777, true)) {
        echo "Created directory: $sellersDir successfully<br>";
    } else {
        echo "Failed to create directory: $sellersDir<br>";
        echo "Error: " . error_get_last()['message'] . "<br>";
    }
} else {
    echo "Directory exists: $sellersDir<br>";
    echo "Is writable: " . (is_writable($sellersDir) ? 'Yes' : 'No') . "<br>";
}

// Check shop_logos directory 
$logosDir = "../uploads/shop_logos/";
if (!file_exists($logosDir)) {
    if (mkdir($logosDir, 0777, true)) {
        echo "Created directory: $logosDir successfully<br>";
    } else {
        echo "Failed to create directory: $logosDir<br>";
        echo "Error: " . error_get_last()['message'] . "<br>";
    }
} else {
    echo "Directory exists: $logosDir<br>";
    echo "Is writable: " . (is_writable($logosDir) ? 'Yes' : 'No') . "<br>";
}

// Test database connection
echo "<h2>Database Connection</h2>";

// Try connecting with db_connect.php
echo "Using db_connect.php:<br>";
require_once 'config/db_connect.php';
if (isset($conn)) {
    echo "Connection successful (db_connect.php)<br>";
    
    // Check if sellers table exists
    $result = $conn->query("SHOW TABLES LIKE 'sellers'");
    if ($result->num_rows > 0) {
        echo "Sellers table exists<br>";
    } else {
        echo "Sellers table does not exist<br>";
    }
} else {
    echo "Connection failed (db_connect.php)<br>";
}

// Try connecting with database.php if it exists
if (file_exists('config/database.php')) {
    echo "<br>Using database.php:<br>";
    require_once 'config/database.php';
    try {
        $db = new Database();
        $conn2 = $db->getConnection();
        echo "Connection successful (database.php)<br>";
    } catch (Exception $e) {
        echo "Connection failed (database.php): " . $e->getMessage() . "<br>";
    }
} else {
    echo "<br>database.php does not exist<br>";
}

// Create a form for testing
echo "<h2>Test Form</h2>";
?>
<form action="api/sellers/update_seller_profile.php" method="post" enctype="multipart/form-data">
    <div>
        <label for="shop_name">Shop Name:</label>
        <input type="text" id="shop_name" name="shop_name" value="Test Shop" required>
    </div>
    <div>
        <label for="shop_address">Shop Address:</label>
        <textarea id="shop_address" name="shop_address" required>123 Test Street</textarea>
    </div>
    <div>
        <label for="shop_bio">Shop Bio:</label>
        <textarea id="shop_bio" name="shop_bio">Test shop bio text</textarea>
    </div>
    <div>
        <label for="shop_location">Shop Location:</label>
        <input type="text" id="shop_location" name="shop_location" value="40.7128,-74.0060">
    </div>
    <div>
        <label for="shop_logo">Shop Logo:</label>
        <input type="file" id="shop_logo" name="shop_logo">
    </div>
    <div>
        <button type="submit">Test Update</button>
    </div>
</form> 