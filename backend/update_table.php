<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log messages to both console and file
function log_message($message) {
    echo $message;
    file_put_contents(__DIR__ . '/update_table.log', $message, FILE_APPEND);
}

// Database connection settings
$db_host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "clothloop";

// Clear previous log file
file_put_contents(__DIR__ . '/update_table.log', "");

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
    // First check if the table exists
    $check_table = $conn->query("SHOW TABLES LIKE 'cloth_images'");
    if ($check_table->num_rows === 0) {
        log_message("Table 'cloth_images' does not exist in the database. Creating new table...\n");
        
        // Create the table from scratch
        $create_sql = "
        CREATE TABLE `cloth_images` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `cloth_id` int(11) NOT NULL,
          `image_data` MEDIUMBLOB NOT NULL,
          `image_type` varchar(30) NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `cloth_id` (`cloth_id`),
          CONSTRAINT `fk_cloth_images_cloth_details` FOREIGN KEY (`cloth_id`) REFERENCES `cloth_details` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($conn->query($create_sql)) {
            log_message("Table created successfully.\n");
        } else {
            throw new Exception("Error creating table: " . $conn->error);
        }
    } else {
        log_message("Table 'cloth_images' exists. Altering structure...\n");
        
        // Check if we already have the desired columns
        $result = $conn->query("SHOW COLUMNS FROM cloth_images LIKE 'image_data'");
        if ($result->num_rows > 0) {
            log_message("Column 'image_data' already exists.\n");
        } else {
            // First, rename the existing column to a backup
            $alter_sql = "ALTER TABLE cloth_images CHANGE image_path image_path_old varchar(255) NOT NULL";
            if ($conn->query($alter_sql)) {
                log_message("Renamed image_path to image_path_old.\n");
            } else {
                throw new Exception("Error renaming column: " . $conn->error);
            }
            
            // Add the new columns
            $alter_sql = "ALTER TABLE cloth_images 
                         ADD COLUMN image_data MEDIUMBLOB NOT NULL AFTER cloth_id,
                         ADD COLUMN image_type varchar(30) NOT NULL AFTER image_data";
            if ($conn->query($alter_sql)) {
                log_message("Added new columns image_data and image_type.\n");
            } else {
                throw new Exception("Error adding columns: " . $conn->error);
            }
        }
    }
    
    // Commit the transaction
    $conn->commit();
    log_message("Table update completed successfully.\n");
    
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
log_message("Check " . __DIR__ . "/update_table.log for complete output.\n");
?> 