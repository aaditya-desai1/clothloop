<?php
/**
 * Migration Script for ClothLoop
 * This script migrates images from cloth_images table to cloth_details table
 */

// Adjust the path to include database connection
$config_path = __DIR__ . '/../config/db_connect.php';
if (!file_exists($config_path)) {
    // Try alternative path
    $config_path = __DIR__ . '/../../backend/config/db_connect.php';
    if (!file_exists($config_path)) {
        die("Could not find database connection file. Please ensure db_connect.php exists in the config directory.");
    }
}
require_once $config_path;

// Set unlimited execution time for large migrations
set_time_limit(0);

// Enable verbose output
$verbose = true;

// Function to log messages
function log_message($message) {
    global $verbose;
    if ($verbose) {
        echo $message . "<br>\n";
        // Flush output buffer to show progress in real-time
        flush();
        ob_flush();
    }
}

// Start migration
log_message("Starting image migration from cloth_images to cloth_details...");

// Check if cloth_details has the required columns
$conn->query("SHOW COLUMNS FROM cloth_details LIKE 'cloth_photo'");
if ($conn->affected_rows <= 0) {
    log_message("Error: cloth_photo column doesn't exist in cloth_details table.");
    log_message("Please run setup_database.php first to update the database schema.");
    exit;
}

$conn->query("SHOW COLUMNS FROM cloth_details LIKE 'photo_type'");
if ($conn->affected_rows <= 0) {
    log_message("Error: photo_type column doesn't exist in cloth_details table.");
    log_message("Please run setup_database.php first to update the database schema.");
    exit;
}

// Check if cloth_images table exists
$result = $conn->query("SHOW TABLES LIKE 'cloth_images'");
if ($result->num_rows == 0) {
    log_message("cloth_images table doesn't exist. No migration needed.");
    exit;
}

// Count how many images need to be migrated
$countResult = $conn->query("SELECT COUNT(*) as count FROM cloth_images");
$countRow = $countResult->fetch_assoc();
$totalImages = $countRow['count'];

log_message("Found {$totalImages} images to migrate.");

if ($totalImages == 0) {
    log_message("No images to migrate. Exiting.");
    exit;
}

// Begin transaction
$conn->begin_transaction();

try {
    // Get all images from cloth_images
    $query = "SELECT ci.*, cd.id as cloth_id 
              FROM cloth_images ci 
              JOIN cloth_details cd ON ci.cloth_id = cd.id";
    
    $result = $conn->query($query);
    
    $migratedCount = 0;
    $skippedCount = 0;
    
    while ($row = $result->fetch_assoc()) {
        // Check if the cloth_details record already has an image
        $checkQuery = "SELECT cloth_photo FROM cloth_details WHERE id = ? AND cloth_photo IS NOT NULL";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $row['cloth_id']);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        // If cloth_details already has an image, skip this record
        if ($checkStmt->num_rows > 0) {
            $skippedCount++;
            log_message("Skipping image for cloth ID {$row['cloth_id']} - already has a photo");
            continue;
        }
        
        // Update cloth_details with the image data
        $updateQuery = "UPDATE cloth_details SET cloth_photo = ?, photo_type = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        
        // Get image type in correct format
        $imageType = "image/" . ($row['image_type'] ?? 'jpeg');
        
        $updateStmt->bind_param("ssi", $row['image_data'], $imageType, $row['cloth_id']);
        
        if ($updateStmt->execute()) {
            $migratedCount++;
            log_message("Migrated image for cloth ID {$row['cloth_id']}");
        } else {
            log_message("Error migrating image for cloth ID {$row['cloth_id']}: " . $updateStmt->error);
        }
        
        $updateStmt->close();
        $checkStmt->close();
    }
    
    // Commit the transaction
    $conn->commit();
    
    log_message("Migration complete!");
    log_message("Total images processed: {$totalImages}");
    log_message("Images migrated: {$migratedCount}");
    log_message("Images skipped: {$skippedCount}");
    
    // Offer to drop the cloth_images table if all images were migrated
    if ($migratedCount == $totalImages) {
        log_message("<a href='?drop_table=1'>All images have been migrated. Click here to drop the cloth_images table.</a>");
    }
    
    // If drop_table parameter is set, drop the cloth_images table
    if (isset($_GET['drop_table']) && $_GET['drop_table'] == 1) {
        $conn->query("DROP TABLE cloth_images");
        log_message("cloth_images table has been dropped.");
    }
    
} catch (Exception $e) {
    // Rollback the transaction in case of error
    $conn->rollback();
    log_message("Error during migration: " . $e->getMessage());
}

// Close connection
$conn->close(); 