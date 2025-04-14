<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html');
echo "<h1>Database Connection Test</h1>";

try {
    // Include database configuration
    require_once __DIR__ . '/config/db_connect.php';
    
    if ($conn && !$conn->connect_error) {
        echo "<p style='color:green'>✓ Database connection successful!</p>";
        
        // Check if users table exists
        $result = $conn->query("SHOW TABLES LIKE 'users'");
        if ($result && $result->num_rows > 0) {
            echo "<p style='color:green'>✓ 'users' table exists</p>";
            
            // Check users table structure
            $result = $conn->query("DESCRIBE users");
            if ($result) {
                echo "<p style='color:green'>✓ 'users' table structure:</p>";
                echo "<ul>";
                while ($row = $result->fetch_assoc()) {
                    echo "<li>{$row['Field']} - {$row['Type']}</li>";
                }
                echo "</ul>";
                
                // Check user count
                $result = $conn->query("SELECT COUNT(*) as count FROM users");
                if ($result) {
                    $row = $result->fetch_assoc();
                    echo "<p>Total users in database: {$row['count']}</p>";
                }
            } else {
                echo "<p style='color:red'>✗ Error checking table structure: " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color:red'>✗ 'users' table does not exist!</p>";
        }
    } else {
        echo "<p style='color:red'>✗ Database connection failed: " . ($conn ? $conn->connect_error : "Connection object not created") . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error: " . $e->getMessage() . "</p>";
} finally {
    if (isset($conn)) {
        $conn->close();
        echo "<p>Database connection closed.</p>";
    }
}
?> 