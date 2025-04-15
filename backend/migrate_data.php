<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log messages to both console and file
function log_message($message) {
    echo $message;
    file_put_contents(__DIR__ . '/migrate_data.log', $message, FILE_APPEND);
}

// Database connection settings
$db_host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "clothloop";

// Clear previous log file
file_put_contents(__DIR__ . '/migrate_data.log', "");

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
    // Check if there's data to migrate
    $check_sql = "SELECT COUNT(*) as count FROM cloth_images WHERE image_path_old IS NOT NULL AND image_path_old != ''";
    $check_result = $conn->query($check_sql);
    $row = $check_result->fetch_assoc();
    $count = $row['count'];
    
    log_message("Found $count records to migrate.\n");
    
    if ($count > 0) {
        // Get all records that need migration
        $select_sql = "SELECT id, cloth_id, image_path_old FROM cloth_images WHERE image_path_old IS NOT NULL AND image_path_old != ''";
        $select_result = $conn->query($select_sql);
        
        $migrated = 0;
        $errors = 0;
        
        while ($row = $select_result->fetch_assoc()) {
            $id = $row['id'];
            $cloth_id = $row['cloth_id'];
            $image_path = $row['image_path_old'];
            
            log_message("Migrating record $id with path: $image_path\n");
            
            // Check if the file exists
            if (file_exists($image_path)) {
                // Get file contents
                $image_data = file_get_contents($image_path);
                
                // Determine image type from file extension
                $path_parts = pathinfo($image_path);
                $extension = strtolower($path_parts['extension']);
                
                switch ($extension) {
                    case 'jpg':
                    case 'jpeg':
                        $image_type = 'jpeg';
                        break;
                    case 'png':
                        $image_type = 'png';
                        break;
                    case 'gif':
                        $image_type = 'gif';
                        break;
                    case 'webp':
                        $image_type = 'webp';
                        break;
                    default:
                        $image_type = 'jpeg'; // Default to JPEG
                }
                
                // Update the record with blob data
                $update_sql = "UPDATE cloth_images SET image_data = ?, image_type = ? WHERE id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("ssi", $image_data, $image_type, $id);
                
                if ($stmt->execute()) {
                    log_message("  Success: Updated record with image data.\n");
                    $migrated++;
                } else {
                    log_message("  Error: Failed to update record: " . $stmt->error . "\n");
                    $errors++;
                }
                
                $stmt->close();
            } else {
                log_message("  Error: File not found at path: $image_path\n");
                $errors++;
            }
        }
        
        log_message("Migration complete. Successfully migrated: $migrated, Errors: $errors\n");
    } else {
        log_message("No records need migration.\n");
    }
    
    // Commit the transaction
    $conn->commit();
    log_message("Changes committed to database.\n");
    
} catch (Exception $e) {
    // Rollback the transaction in case of error
    $conn->rollback();
    log_message("Error: " . $e->getMessage() . "\n");
}

$conn->close();
log_message("Done.\n");
log_message("Check " . __DIR__ . "/migrate_data.log for complete output.\n");
?> 