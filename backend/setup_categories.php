<?php
/**
 * Setup Categories Script
 * Creates standard categories for ClothLoop platform
 */

// Include database connection
require_once __DIR__ . '/config/database.php';

// Enable error reporting for development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Create database connection
$database = new Database();
$conn = $database->getConnection();

echo "Starting category setup...\n";

// First, clear existing categories
try {
    $conn->exec("DELETE FROM categories");
    echo "Cleared existing categories.\n";
} catch (PDOException $e) {
    echo "Error clearing categories: " . $e->getMessage() . "\n";
}

// Define the categories we want
$categories = [
    ['name' => 'Men', 'description' => 'Men\'s clothing'],
    ['name' => 'Women', 'description' => 'Women\'s clothing'],
    ['name' => 'Kids', 'description' => 'Kids\' clothing']
];

// Insert categories
try {
    $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
    
    foreach ($categories as $category) {
        $stmt->bindParam(':name', $category['name']);
        $stmt->bindParam(':description', $category['description']);
        $stmt->execute();
        echo "Added category: " . $category['name'] . "\n";
    }
    
    echo "Category setup completed successfully!\n";
} catch (PDOException $e) {
    echo "Error inserting categories: " . $e->getMessage() . "\n";
} 