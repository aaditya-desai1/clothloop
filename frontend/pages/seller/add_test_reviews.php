<?php
// Include database config
require_once '../../../backend/config/database.php';

// Function to output messages
function outputMessage($message, $isError = false) {
    echo '<div style="padding: 10px; margin: 5px; border-radius: 5px; ' .
        'background-color: ' . ($isError ? '#ffdddd' : '#ddffdd') . '; ' .
        'color: ' . ($isError ? '#990000' : '#009900') . ';">' .
        htmlspecialchars($message) . '</div>';
}

// Connect to database
try {
    $database = new Database();
    $conn = $database->getConnection();
    outputMessage("Database connection successful");
} catch (Exception $e) {
    outputMessage("Database connection failed: " . $e->getMessage(), true);
    exit;
}

// Check if the form was submitted
if (isset($_POST['add_reviews'])) {
    // First check if seller_reviews table exists
    $stmt = $conn->prepare("SHOW TABLES LIKE 'seller_reviews'");
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;

    // If table doesn't exist, create it
    if (!$tableExists) {
        try {
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
            outputMessage("Table seller_reviews created successfully");
        } catch (PDOException $e) {
            outputMessage("Error creating table: " . $e->getMessage(), true);
        }
    } else {
        outputMessage("Table seller_reviews already exists");
    }

    // Get seller ID from form
    $sellerId = isset($_POST['seller_id']) ? (int)$_POST['seller_id'] : 1;

    // Add sample reviews
    $reviews = [
        [
            'seller_id' => $sellerId,
            'buyer_id' => 2,
            'rating' => 4.5,
            'review_text' => 'Great seller! Fast shipping and item was as described.'
        ],
        [
            'seller_id' => $sellerId,
            'buyer_id' => 3,
            'rating' => 5.0,
            'review_text' => 'Excellent service and product quality. Highly recommended!'
        ],
        [
            'seller_id' => $sellerId,
            'buyer_id' => 4,
            'rating' => 4.0,
            'review_text' => 'Good experience. Item was slightly different than pictured but still satisfied.'
        ]
    ];

    // Insert reviews
    $inserted = 0;
    foreach ($reviews as $review) {
        try {
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
        } catch (PDOException $e) {
            outputMessage("Error inserting review: " . $e->getMessage(), true);
        }
    }

    outputMessage("Inserted $inserted test reviews");

    // Verify by getting the average rating
    try {
        $query = "SELECT AVG(rating) as avg_rating FROM seller_reviews WHERE seller_id = :seller_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':seller_id', $sellerId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        outputMessage("Average rating for seller ID $sellerId: " . 
            ($result['avg_rating'] ? round($result['avg_rating'], 1) : '0.0'));
    } catch (PDOException $e) {
        outputMessage("Error getting average rating: " . $e->getMessage(), true);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Test Reviews - ClothLoop</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="number"] {
            width: 100px;
            padding: 5px;
            margin-bottom: 15px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .back-link {
            margin-top: 20px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Test Reviews</h1>
        <p>This page will add sample review data to the seller_reviews table for testing purposes.</p>
        
        <form method="post">
            <div>
                <label for="seller_id">Seller ID:</label>
                <input type="number" id="seller_id" name="seller_id" value="1" min="1">
            </div>
            
            <button type="submit" name="add_reviews">Add Test Reviews</button>
        </form>
        
        <a href="seller_dashboard.html" class="back-link">Back to Seller Dashboard</a>
    </div>
</body>
</html> 