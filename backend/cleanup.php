<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log messages to both console and file
function log_message($message) {
    echo $message;
    file_put_contents(__DIR__ . '/cleanup.log', $message, FILE_APPEND);
}

// Database connection settings
$db_host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "clothloop";

// Clear previous log file
file_put_contents(__DIR__ . '/cleanup.log', "");

log_message("Connecting to database...\n");

// Create connection
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    log_message("Connection failed: " . $conn->connect_error . "\n");
    exit;
}

log_message("Connected successfully.\n");

// Begin transaction
$conn->begin_transaction();

try {
    // Drop the old column
    $alter_sql = "ALTER TABLE cloth_images DROP COLUMN image_path_old";
    if ($conn->query($alter_sql)) {
        log_message("Successfully dropped image_path_old column.\n");
    } else {
        throw new Exception("Error dropping column: " . $conn->error);
    }
    
    // Commit the transaction
    $conn->commit();
    log_message("Changes committed to database.\n");
    
} catch (Exception $e) {
    // Rollback the transaction in case of error
    $conn->rollback();
    log_message("Error: " . $e->getMessage() . "\n");
}

// Check final table structure
$sql = "DESCRIBE cloth_images";
$result = $conn->query($sql);

if (!$result) {
    log_message("Error executing query: " . $conn->error . "\n");
    exit;
}

log_message("Final table structure for cloth_images:\n");
log_message("-------------------------------------\n");

$header = str_pad("Field", 15) . 
       str_pad("Type", 25) . 
       str_pad("Null", 7) . 
       str_pad("Key", 7) . 
       str_pad("Default", 10) . 
       "Extra\n";
log_message($header);

log_message(str_repeat('-', 80) . "\n");

while ($row = $result->fetch_assoc()) {
    $line = str_pad($row['Field'], 15) . 
         str_pad($row['Type'], 25) . 
         str_pad($row['Null'], 7) . 
         str_pad($row['Key'], 7) . 
         str_pad(($row['Default'] === null ? 'NULL' : $row['Default']), 10) . 
         $row['Extra'] . "\n";
    log_message($line);
}

$conn->close();
log_message("Done.\n");
log_message("Check " . __DIR__ . "/cleanup.log for complete output.\n");
?> 