<?php
// Set error reporting to maximum for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once 'config/database.php';

// Get seller ID from query string
$seller_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Connect to the database
$database = new Database();
$db = $database->getConnection();

// Simple test - try to update the shop description directly
try {
    // Begin transaction
    $db->beginTransaction();
    
    echo "<h2>Running Direct SQL Test</h2>";
    
    // Check if the sellers table exists
    $checkTable = $db->query("SHOW TABLES LIKE 'sellers'");
    if ($checkTable->rowCount() === 0) {
        echo "<p style='color:red'>ERROR: The 'sellers' table does not exist!</p>";
        exit;
    }
    
    // Check the structure of the sellers table
    echo "<h3>Sellers Table Structure:</h3>";
    $describeTable = $db->query("DESCRIBE sellers");
    $columns = $describeTable->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    // Verify if seller with given ID exists
    $checkSeller = $db->prepare("SELECT * FROM sellers WHERE id = ?");
    $checkSeller->execute([$seller_id]);
    if ($checkSeller->rowCount() === 0) {
        echo "<p style='color:red'>ERROR: No seller found with ID: $seller_id</p>";
        exit;
    }
    
    // Get current seller data
    $sellerData = $checkSeller->fetch(PDO::FETCH_ASSOC);
    echo "<h3>Current Seller Data:</h3>";
    echo "<pre>";
    print_r($sellerData);
    echo "</pre>";
    
    // Update description with a test value
    $testDescription = "Test description updated on " . date("Y-m-d H:i:s");
    $update = $db->prepare("UPDATE sellers SET description = ? WHERE id = ?");
    $updateResult = $update->execute([$testDescription, $seller_id]);
    
    // Check the result
    if ($updateResult) {
        echo "<p style='color:green'>UPDATE SUCCESS! Affected rows: " . $update->rowCount() . "</p>";
        
        // Verify the update by selecting again
        $verifyUpdate = $db->prepare("SELECT description FROM sellers WHERE id = ?");
        $verifyUpdate->execute([$seller_id]);
        $newDescription = $verifyUpdate->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>New Description:</h3>";
        echo "<pre>";
        print_r($newDescription);
        echo "</pre>";
        
        // Commit the transaction
        $db->commit();
        echo "<p style='color:green'>Transaction committed successfully!</p>";
    } else {
        echo "<p style='color:red'>UPDATE FAILED! Error info: ";
        print_r($update->errorInfo());
        echo "</p>";
        $db->rollBack();
    }
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($db)) {
        $db->rollBack();
    }
    echo "<p style='color:red'>ERROR: " . $e->getMessage() . "</p>";
    echo "<p>Stack Trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
}
?> 