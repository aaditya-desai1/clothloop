<?php
// Include database config
require_once __DIR__ . '/backend/config/database.php';

// Connect to database
$database = new Database();
$conn = $database->getConnection();

// First check if seller_reviews table exists
$stmt = $conn->prepare("SHOW TABLES LIKE 'seller_reviews'");
$stmt->execute();
$tableExists = $stmt->rowCount() > 0;

// If table doesn't exist, create it
if (!$tableExists) {
    $createTable = "CREATE TABLE seller_reviews (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        seller_id INT(11) UNSIGNED NOT NULL,
        buyer_id INT(11) UNSIGNED NOT NULL,
        rating DECIMAL(3,1) NOT NULL,
        review_text TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX(seller_id),
        INDEX(buyer_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->exec($createTable);
    echo "Table seller_reviews created successfully\n";
}

// Add sample reviews
$reviews = [
    [
        'seller_id' => 1, // Adjust to match your seller ID
        'buyer_id' => 2,
        'rating' => 4.5,
        'review_text' => 'Great seller! Fast shipping and item was as described.'
    ],
    [
        'seller_id' => 1, // Adjust to match your seller ID
        'buyer_id' => 3,
        'rating' => 5.0,
        'review_text' => 'Excellent service and product quality. Highly recommended!'
    ],
    [
        'seller_id' => 1, // Adjust to match your seller ID
        'buyer_id' => 4,
        'rating' => 4.0,
        'review_text' => 'Good experience. Item was slightly different than pictured but still satisfied.'
    ]
];

// Insert reviews
$inserted = 0;
foreach ($reviews as $review) {
    $sql = "INSERT INTO seller_reviews (seller_id, buyer_id, rating, review_text) 
            VALUES (:seller_id, :buyer_id, :rating, :review_text)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':seller_id', $review['seller_id']);
    $stmt->bindParam(':buyer_id', $review['buyer_id']);
    $stmt->bindParam(':rating', $review['rating']);
    $stmt->bindParam(':review_text', $review['review_text']);
    
    if ($stmt->execute()) {
        $inserted++;
    }
}

echo "Inserted $inserted test reviews\n";

// Verify by getting the average rating
$query = "SELECT AVG(rating) as avg_rating FROM seller_reviews WHERE seller_id = 1";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Average rating for seller ID 1: " . ($result['avg_rating'] ? round($result['avg_rating'], 1) : '0.0') . "\n"; 