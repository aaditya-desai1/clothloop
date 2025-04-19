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

// Load seller's products if seller_id is provided
$sellerId = isset($_GET['seller_id']) ? (int)$_GET['seller_id'] : (isset($_POST['seller_id']) ? (int)$_POST['seller_id'] : null);
$products = [];

if ($sellerId) {
    try {
        $query = "SELECT id, name, description FROM products WHERE seller_id = :seller_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':seller_id', $sellerId);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($products) > 0) {
            outputMessage("Found " . count($products) . " products for seller ID: " . $sellerId);
        } else {
            outputMessage("No products found for seller ID: " . $sellerId, true);
        }
    } catch (PDOException $e) {
        outputMessage("Error fetching products: " . $e->getMessage(), true);
    }
}

// Check if the form was submitted
if (isset($_POST['add_reviews'])) {
    // First check if product_reviews table exists
    $stmt = $conn->prepare("SHOW TABLES LIKE 'product_reviews'");
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;

    // If table doesn't exist, create it
    if (!$tableExists) {
        try {
            $createTable = "CREATE TABLE IF NOT EXISTS product_reviews (
                id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                product_id INT(11) UNSIGNED NOT NULL,
                buyer_id INT(11),
                user_name VARCHAR(100) DEFAULT 'Anonymous',
                rating INT(1) NOT NULL,
                review TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX(product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $conn->exec($createTable);
            outputMessage("Table product_reviews created successfully");
        } catch (PDOException $e) {
            outputMessage("Error creating table: " . $e->getMessage(), true);
        }
    } else {
        outputMessage("Table product_reviews already exists");
    }

    // Get product IDs from form
    $productIds = isset($_POST['product_ids']) ? $_POST['product_ids'] : [];
    
    if (empty($productIds)) {
        outputMessage("No products selected for reviews", true);
    } else {
        $inserted = 0;
        
        foreach ($productIds as $productId) {
            // Generate 2-4 random reviews for each product
            $numReviews = rand(2, 4);
            
            for ($i = 0; $i < $numReviews; $i++) {
                // Generate random rating (3-5 stars)
                $rating = rand(3, 5);
                $buyerId = rand(10, 100); // Random buyer ID
                $userName = "Test Buyer " . $buyerId;
                
                // Generate review text based on rating
                $reviewTexts = [
                    5 => [
                        "Excellent product! Exactly as described and very good quality.",
                        "I'm extremely satisfied with this purchase. Would buy again!",
                        "The quality of this item is outstanding. Fast shipping too!",
                        "Absolutely love it! Perfect fit and looks great."
                    ],
                    4 => [
                        "Good product for the price. Recommended.",
                        "Nice item, shipped quickly and as described.",
                        "Very good quality. Minor issues but overall satisfied.",
                        "Quite happy with my purchase. Would recommend."
                    ],
                    3 => [
                        "Decent product. Not exceptional but does the job.",
                        "Average quality but fair for the price.",
                        "Product is OK. Shipping was a bit slow though.",
                        "Reasonable quality. Some minor flaws but acceptable."
                    ]
                ];
                
                // Select random review text for the rating
                $reviewText = $reviewTexts[$rating][array_rand($reviewTexts[$rating])];
                
                try {
                    $sql = "INSERT INTO product_reviews (product_id, buyer_id, user_name, rating, review) 
                            VALUES (:product_id, :buyer_id, :user_name, :rating, :review)";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':product_id', $productId);
                    $stmt->bindParam(':buyer_id', $buyerId);
                    $stmt->bindParam(':user_name', $userName);
                    $stmt->bindParam(':rating', $rating);
                    $stmt->bindParam(':review', $reviewText);
                    
                    if ($stmt->execute()) {
                        $inserted++;
                    }
                } catch (PDOException $e) {
                    outputMessage("Error inserting review: " . $e->getMessage(), true);
                }
            }
        }

        outputMessage("Inserted $inserted test reviews");

        if (count($productIds) > 0) {
            // Show average rating for the first product
            try {
                $query = "SELECT AVG(rating) as avg_rating FROM product_reviews WHERE product_id = :product_id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':product_id', $productIds[0]);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                outputMessage("Average rating for product ID {$productIds[0]}: " . 
                    ($result['avg_rating'] ? round((float)$result['avg_rating'], 1) : '0.0'));
            } catch (PDOException $e) {
                outputMessage("Error getting product average rating: " . $e->getMessage(), true);
            }
            
            // Also show average seller rating
            if ($sellerId) {
                try {
                    $query = "SELECT AVG(pr.rating) as avg_rating 
                            FROM product_reviews pr 
                            JOIN products p ON pr.product_id = p.id 
                            WHERE p.seller_id = :seller_id";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':seller_id', $sellerId);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    outputMessage("Average seller rating for seller ID $sellerId: " . 
                        ($result['avg_rating'] ? round((float)$result['avg_rating'], 1) : '0.0'));
                } catch (PDOException $e) {
                    outputMessage("Error getting seller average rating: " . $e->getMessage(), true);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Test Product Reviews - ClothLoop</title>
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
        .product-list {
            margin: 15px 0;
        }
        .product-item {
            padding: 10px;
            margin-bottom: 5px;
            background-color: #f9f9f9;
            border-radius: 3px;
        }
        .product-item label {
            display: inline;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Test Product Reviews</h1>
        <p>This page will add sample review data to the product_reviews table for testing purposes.</p>
        
        <form method="<?php echo $sellerId ? 'post' : 'get'; ?>">
            <div>
                <label for="seller_id">Seller ID:</label>
                <input type="number" id="seller_id" name="seller_id" value="<?php echo $sellerId ?: 1; ?>" min="1">
            </div>
            
            <?php if (empty($sellerId)): ?>
                <button type="submit">Load Seller Products</button>
            <?php endif; ?>
        </form>
        
        <?php if ($sellerId && !empty($products)): ?>
            <form method="post">
                <input type="hidden" name="seller_id" value="<?php echo $sellerId; ?>">
                
                <h3>Select Products to Add Reviews:</h3>
                <div class="product-list">
                    <?php foreach ($products as $product): ?>
                        <div class="product-item">
                            <input type="checkbox" id="product_<?php echo $product['id']; ?>" 
                                name="product_ids[]" value="<?php echo $product['id']; ?>" checked>
                            <label for="product_<?php echo $product['id']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="submit" name="add_reviews">Add Test Reviews</button>
            </form>
        <?php elseif ($sellerId && empty($products)): ?>
            <p>No products found for this seller. Please check the seller ID and try again.</p>
        <?php endif; ?>
        
        <a href="seller_dashboard.html" class="back-link">Back to Seller Dashboard</a>
    </div>
</body>
</html> 