<?php
/**
 * Update Reviews Table Schema
 * 
 * This script adds missing columns to the reviews table:
 * - seller_response: For storing seller's response to a review
 * - response_date: Timestamp when the seller responded
 * - updated_at: Timestamp that updates when a review is modified
 */

// Include database configuration
require_once 'config/database.php';

// Create database connection
$database = new Database();
$conn = $database->getConnection();

// Start transaction
$conn->beginTransaction();

try {
    // Check if the columns already exist
    $columnsQuery = "SHOW COLUMNS FROM reviews LIKE 'seller_response'";
    $stmt = $conn->prepare($columnsQuery);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // Add seller_response column
        $alterQuery1 = "ALTER TABLE reviews ADD COLUMN seller_response TEXT NULL AFTER review_text";
        $conn->exec($alterQuery1);
        echo "Added seller_response column to reviews table.<br>";
    } else {
        echo "seller_response column already exists.<br>";
    }
    
    // Check for response_date column
    $columnsQuery = "SHOW COLUMNS FROM reviews LIKE 'response_date'";
    $stmt = $conn->prepare($columnsQuery);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // Add response_date column
        $alterQuery2 = "ALTER TABLE reviews ADD COLUMN response_date TIMESTAMP NULL AFTER seller_response";
        $conn->exec($alterQuery2);
        echo "Added response_date column to reviews table.<br>";
    } else {
        echo "response_date column already exists.<br>";
    }
    
    // Check for updated_at column
    $columnsQuery = "SHOW COLUMNS FROM reviews LIKE 'updated_at'";
    $stmt = $conn->prepare($columnsQuery);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // Add updated_at column
        $alterQuery3 = "ALTER TABLE reviews ADD COLUMN updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at";
        $conn->exec($alterQuery3);
        echo "Added updated_at column to reviews table.<br>";
    } else {
        echo "updated_at column already exists.<br>";
    }
    
    // Commit the transaction
    $conn->commit();
    echo "<br>Reviews table updated successfully!";
    
} catch (PDOException $e) {
    // Rollback the transaction if something fails
    $conn->rollBack();
    echo "Error updating reviews table: " . $e->getMessage();
}
?> 