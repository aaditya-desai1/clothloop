<?php
// Include database config
require_once __DIR__ . '/backend/config/database.php';

// Connect to database
$database = new Database();
$conn = $database->getConnection();

// Check tables
$stmt = $conn->prepare('SHOW TABLES');
$stmt->execute();
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "Tables in database:\n";
print_r($tables);

// Check if seller_reviews table exists
$tableExists = in_array('seller_reviews', $tables);
echo "\nseller_reviews table exists: " . ($tableExists ? 'Yes' : 'No') . "\n";

// If seller_reviews exists, check records
if ($tableExists) {
    $stmt = $conn->prepare('SELECT * FROM seller_reviews LIMIT 5');
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nSample records from seller_reviews:\n";
    print_r($records);
    
    // Count total records
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM seller_reviews');
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\nTotal records in seller_reviews: " . $count['count'] . "\n";
}

// Check the AVG query specifically
$sellerId = 1; // Replace with an actual seller ID if needed
$query = "SELECT AVG(rating) as avg_rating FROM seller_reviews WHERE seller_id = ?";
$stmt = $conn->prepare($query);
$stmt->bindParam(1, $sellerId);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "\nAVG rating query result for seller_id=$sellerId:\n";
print_r($result);
echo "\nFormatted rating: " . (($result && $result['avg_rating'] !== null) ? round((float)$result['avg_rating'], 1) : '0.0') . "\n"; 