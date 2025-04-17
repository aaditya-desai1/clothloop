<?php
// Migration script to change shop_logo from VARCHAR path to MEDIUMBLOB data
// Execute this script after updating the database.sql schema

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once '../config/db_connect.php';

echo "Starting shop logo migration...\n";

try {
    // Get all sellers with shop logos
    $query = "SELECT id, shop_logo FROM sellers WHERE shop_logo IS NOT NULL AND shop_logo != ''";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Error querying sellers: " . $conn->error);
    }
    
    $totalSellers = $result->num_rows;
    echo "Found $totalSellers sellers with shop logos to migrate.\n";
    
    // Keep track of success and failures
    $successCount = 0;
    $failureCount = 0;
    
    // Begin transaction
    $conn->begin_transaction();
    
    // Prepare update statement
    $updateStmt = $conn->prepare("UPDATE sellers SET shop_logo = ? WHERE id = ?");
    
    if (!$updateStmt) {
        throw new Exception("Error preparing update statement: " . $conn->error);
    }
    
    while ($seller = $result->fetch_assoc()) {
        $sellerId = $seller['id'];
        $logoPath = $seller['shop_logo'];
        
        // Try to get the full server path
        $fullPath = __DIR__ . "/../../" . $logoPath;
        
        echo "Processing seller ID $sellerId with logo path: $logoPath\n";
        echo "Full path: $fullPath\n";
        
        // Check if file exists
        if (!file_exists($fullPath)) {
            echo "Warning: File not found at $fullPath\n";
            $failureCount++;
            continue;
        }
        
        // Read file contents
        $logoData = file_get_contents($fullPath);
        
        if ($logoData === false) {
            echo "Error: Could not read file $fullPath\n";
            $failureCount++;
            continue;
        }
        
        // Update record with image data
        $updateStmt->bind_param("bi", $logoData, $sellerId);
        
        if (!$updateStmt->execute()) {
            echo "Error updating seller ID $sellerId: " . $updateStmt->error . "\n";
            $failureCount++;
            continue;
        }
        
        $successCount++;
        echo "Successfully migrated shop logo for seller ID $sellerId\n";
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "\nMigration completed.\n";
    echo "Successfully migrated: $successCount\n";
    echo "Failed migrations: $failureCount\n";
    
} catch (Exception $e) {
    // If an exception occurs, roll back any changes
    if (isset($conn)) {
        $conn->rollback();
    }
    
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Close connection
$conn->close();

echo "Migration script finished.\n";
?> 