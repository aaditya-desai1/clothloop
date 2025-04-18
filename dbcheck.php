<?php
// Include the database configuration
require_once __DIR__ . '/backend/config/database.php';

// Connect to the database
$database = new Database();
$db = $database->getConnection();

// Check if the database connection is successful
if (!$db) {
    die("Database connection failed!");
}

// Describe the products table
$query = "DESCRIBE products";
try {
    $stmt = $db->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Products Table Structure:\n";
    echo "------------------------\n";
    foreach ($columns as $column) {
        echo "Column: {$column['Field']}, Type: {$column['Type']}, Null: {$column['Null']}, Default: {$column['Default']}\n";
    }
    
    // Now check if there are any records with a non-empty status
    $query = "SELECT id, title, status FROM products WHERE status IS NOT NULL AND status != ''";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nProducts with a Status Value:\n";
    echo "---------------------------\n";
    if (count($rows) > 0) {
        foreach ($rows as $row) {
            echo "ID: {$row['id']}, Title: {$row['title']}, Status: {$row['status']}\n";
        }
    } else {
        echo "No products found with a status value\n";
    }
    
    // Now test if we can update a status
    echo "\nTesting Status Update:\n";
    echo "--------------------\n";
    
    // Get the first product
    $query = "SELECT id, seller_id, title FROM products LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        echo "Selected product: ID: {$product['id']}, Title: {$product['title']}\n";
        
        // Try to update the status
        $query = "UPDATE products SET status = 'active', updated_at = NOW() WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $product['id']);
        $result = $stmt->execute();
        
        if ($result) {
            echo "Status updated successfully!\n";
            
            // Verify the update
            $query = "SELECT id, title, status FROM products WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $product['id']);
            $stmt->execute();
            $updatedProduct = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "Updated product status: {$updatedProduct['status']}\n";
        } else {
            echo "Failed to update status. Error: " . print_r($stmt->errorInfo(), true) . "\n";
        }
    } else {
        echo "No products found in the database\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 