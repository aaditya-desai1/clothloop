<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection from config
require_once __DIR__ . '/../config/db_connect.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow cross-origin for testing

// Function to check database tables
function checkDatabase() {
    global $conn;
    
    try {
        // Get all tables in database
        $tables_query = "SHOW TABLES";
        $tables_result = $conn->query($tables_query);
        
        if (!$tables_result) {
            throw new Exception("Error querying tables: " . $conn->error);
        }
        
        $tables = [];
        while ($row = $tables_result->fetch_row()) {
            $tables[] = $row[0];
        }
        
        // Check if cloth_details exists
        $has_cloth_details = in_array('cloth_details', $tables);
        
        // Get count of records in cloth_details if it exists
        $cloth_details_count = 0;
        if ($has_cloth_details) {
            $count_query = "SELECT COUNT(*) as count FROM cloth_details";
            $count_result = $conn->query($count_query);
            
            if ($count_result && $count_result->num_rows > 0) {
                $cloth_details_count = (int) $count_result->fetch_assoc()['count'];
            }
        }
        
        // Check if cloth_images table exists
        $has_cloth_images = in_array('cloth_images', $tables);
        
        // Get count of records in cloth_images if it exists
        $cloth_images_count = 0;
        if ($has_cloth_images) {
            $count_query = "SELECT COUNT(*) as count FROM cloth_images";
            $count_result = $conn->query($count_query);
            
            if ($count_result && $count_result->num_rows > 0) {
                $cloth_images_count = (int) $count_result->fetch_assoc()['count'];
            }
        }
        
        // Return database info
        echo json_encode([
            'status' => 'success',
            'database_info' => [
                'tables' => $tables,
                'cloth_details_exists' => $has_cloth_details,
                'cloth_details_count' => $cloth_details_count,
                'cloth_images_exists' => $has_cloth_images,
                'cloth_images_count' => $cloth_images_count
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

// Run the database check
checkDatabase();

// Close connection
$conn->close();
?> 