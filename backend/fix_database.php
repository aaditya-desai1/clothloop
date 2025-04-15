<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'config/db_connect.php';

echo "<h2>ClothLoop Database Setup</h2>";

try {
    // Check database connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? "Unable to connect"));
    }
    
    echo "<p>Database connection successful!</p>";
    
    // Get SQL from database.sql file
    $sql_file = file_get_contents('db/database.sql');
    
    if (!$sql_file) {
        throw new Exception("Could not read database.sql file");
    }
    
    echo "<p>SQL file loaded successfully.</p>";
    
    // Split SQL by semicolons to execute multiple statements
    $statements = explode(';', $sql_file);
    
    echo "<p>Starting database setup...</p>";
    echo "<ul>";
    
    // Execute each SQL statement
    foreach ($statements as $statement) {
        $statement = trim($statement);
        
        if (!empty($statement)) {
            if ($conn->query($statement) === TRUE) {
                // Extract table name for better output
                if (preg_match('/CREATE TABLE\s+IF NOT EXISTS\s+`?(\w+)`?/i', $statement, $matches)) {
                    echo "<li>Table '{$matches[1]}' created or already exists.</li>";
                } else if (preg_match('/INSERT INTO\s+`?(\w+)`?/i', $statement, $matches)) {
                    echo "<li>Data inserted into '{$matches[1]}'.</li>";
                } else {
                    echo "<li>Statement executed successfully.</li>";
                }
            } else {
                echo "<li style='color:red'>Error executing SQL: " . $conn->error . "</li>";
            }
        }
    }
    
    echo "</ul>";
    echo "<p style='color:green;font-weight:bold'>Database setup completed!</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
} finally {
    // Close connection if it exists
    if (isset($conn)) {
        $conn->close();
        echo "<p>Database connection closed.</p>";
    }
}

echo "<p><a href='../index.html'>Return to home page</a></p>";
?> 