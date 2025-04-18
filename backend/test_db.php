<?php
// Simple database connection test
header('Content-Type: text/plain');

// Include database file
require_once __DIR__ . '/config/database.php';

echo "--- ClothLoop Database Test ---\n\n";

try {
    // Create database connection
    echo "Connecting to database...\n";
    $database = new Database();
    $conn = $database->connect();
    
    echo "Connected successfully!\n\n";
    
    // Test buyers table
    echo "Testing 'buyers' table:\n";
    $stmt = $conn->query("SHOW TABLES LIKE 'buyers'");
    if ($stmt->rowCount() > 0) {
        echo "  ✓ 'buyers' table exists\n";
        
        // Get table structure
        echo "  Columns in 'buyers' table:\n";
        $stmt = $conn->query("DESCRIBE buyers");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "    - {$row['Field']} ({$row['Type']})" . 
                 ($row['Key'] == 'PRI' ? " [PRIMARY KEY]" : "") . "\n";
        }
        
        // Count records
        $stmt = $conn->query("SELECT COUNT(*) as count FROM buyers");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "  Total records: $count\n\n";
    } else {
        echo "  ✗ 'buyers' table does not exist\n\n";
    }
    
    // Test users table
    echo "Testing 'users' table:\n";
    $stmt = $conn->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "  ✓ 'users' table exists\n";
        
        // Get table structure
        echo "  Columns in 'users' table:\n";
        $stmt = $conn->query("DESCRIBE users");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "    - {$row['Field']} ({$row['Type']})" . 
                 ($row['Key'] == 'PRI' ? " [PRIMARY KEY]" : "") . "\n";
        }
        
        // Count records
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "  Total records: $count\n\n";
        
        // Check buyers with user type
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'buyer' OR role = 'buyer'");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "  Users with buyer role: $count\n\n";
    } else {
        echo "  ✗ 'users' table does not exist\n\n";
    }
    
    // Check if there are test accounts we can use
    echo "Looking for test accounts:\n";
    try {
        $stmt = $conn->query("SELECT id, name, email, user_type, role FROM users LIMIT 5");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $userType = isset($row['user_type']) ? $row['user_type'] : (isset($row['role']) ? $row['role'] : 'unknown');
            echo "  User ID: {$row['id']}, Name: {$row['name']}, Email: {$row['email']}, Type: $userType\n";
        }
    } catch (PDOException $e) {
        echo "  Error getting test accounts: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n--- Test Complete ---\n";
?> 