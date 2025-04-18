<?php
class Review {
    // Database connection and table name
    private $conn;
    private $table = 'reviews';

    // Review properties
    public $id;
    public $order_id;
    public $product_id;
    public $buyer_id;
    public $seller_id;
    public $rating;
    public $comment;
    public $created_at;
    public $updated_at;

    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Get reviews for a seller with pagination and optional rating filter
    public function getSellerReviews($seller_id, $limit, $offset, $rating = null) {
        // Base query
        $query = 'SELECT r.id, r.order_id, r.product_id, r.buyer_id, r.seller_id, 
                        r.rating, r.comment, r.created_at, r.updated_at,
                        u.name as buyer_name, u.profile_image as buyer_image,
                        p.name as product_name, p.image as product_image
                  FROM ' . $this->table . ' r
                  LEFT JOIN users u ON r.buyer_id = u.id
                  LEFT JOIN products p ON r.product_id = p.id
                  WHERE r.seller_id = :seller_id';
        
        // Add rating filter if specified
        if ($rating !== null) {
            $query .= ' AND r.rating = :rating';
        }
        
        // Add ordering and pagination
        $query .= ' ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset';
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $seller_id = htmlspecialchars(strip_tags($seller_id));
        $limit = htmlspecialchars(strip_tags($limit));
        $offset = htmlspecialchars(strip_tags($offset));
        
        // Bind parameters
        $stmt->bindParam(':seller_id', $seller_id);
        if ($rating !== null) {
            $stmt->bindParam(':rating', $rating);
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        // Execute query
        $stmt->execute();
        
        $reviews = [];
        
        // Check if any reviews
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Format the review data
                $review_item = [
                    'id' => $row['id'],
                    'order_id' => $row['order_id'],
                    'product_id' => $row['product_id'],
                    'product_name' => $row['product_name'],
                    'product_image' => $row['product_image'],
                    'buyer_id' => $row['buyer_id'],
                    'buyer_name' => $row['buyer_name'],
                    'buyer_image' => $row['buyer_image'],
                    'seller_id' => $row['seller_id'],
                    'rating' => $row['rating'],
                    'comment' => $row['comment'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at']
                ];
                
                array_push($reviews, $review_item);
            }
        }
        
        return $reviews;
    }
    
    // Count total reviews for a seller with optional rating filter
    public function countSellerReviews($seller_id, $rating = null) {
        // Base query
        $query = 'SELECT COUNT(*) as total FROM ' . $this->table . ' WHERE seller_id = :seller_id';
        
        // Add rating filter if specified
        if ($rating !== null) {
            $query .= ' AND rating = :rating';
        }
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $seller_id = htmlspecialchars(strip_tags($seller_id));
        
        // Bind parameters
        $stmt->bindParam(':seller_id', $seller_id);
        if ($rating !== null) {
            $stmt->bindParam(':rating', $rating);
        }
        
        // Execute query
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$row['total'];
    }
    
    // Get rating summary for a seller (average rating and count by rating)
    public function getSellerRatingSummary($seller_id) {
        // Get average rating
        $avgQuery = 'SELECT AVG(rating) as average FROM ' . $this->table . ' WHERE seller_id = :seller_id';
        $avgStmt = $this->conn->prepare($avgQuery);
        $avgStmt->bindParam(':seller_id', $seller_id);
        $avgStmt->execute();
        $avgRow = $avgStmt->fetch(PDO::FETCH_ASSOC);
        $average = $avgRow['average'] ? round($avgRow['average'], 1) : 0;
        
        // Get count by rating
        $countByRatingQuery = 'SELECT rating, COUNT(*) as count FROM ' . $this->table . ' 
                              WHERE seller_id = :seller_id GROUP BY rating ORDER BY rating DESC';
        $countStmt = $this->conn->prepare($countByRatingQuery);
        $countStmt->bindParam(':seller_id', $seller_id);
        $countStmt->execute();
        
        $ratingBreakdown = [
            5 => 0,
            4 => 0,
            3 => 0,
            2 => 0,
            1 => 0
        ];
        
        while ($row = $countStmt->fetch(PDO::FETCH_ASSOC)) {
            $ratingBreakdown[$row['rating']] = (int)$row['count'];
        }
        
        // Get total count
        $totalQuery = 'SELECT COUNT(*) as total FROM ' . $this->table . ' WHERE seller_id = :seller_id';
        $totalStmt = $this->conn->prepare($totalQuery);
        $totalStmt->bindParam(':seller_id', $seller_id);
        $totalStmt->execute();
        $totalRow = $totalStmt->fetch(PDO::FETCH_ASSOC);
        $total = (int)$totalRow['total'];
        
        return [
            'average' => $average,
            'total' => $total,
            'breakdown' => $ratingBreakdown
        ];
    }
    
    // Create a new review
    public function create() {
        // Create query
        $query = 'INSERT INTO ' . $this->table . ' 
                 (order_id, product_id, buyer_id, seller_id, rating, comment)
                 VALUES (:order_id, :product_id, :buyer_id, :seller_id, :rating, :comment)';
                 
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $this->order_id = htmlspecialchars(strip_tags($this->order_id));
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->buyer_id = htmlspecialchars(strip_tags($this->buyer_id));
        $this->seller_id = htmlspecialchars(strip_tags($this->seller_id));
        $this->rating = htmlspecialchars(strip_tags($this->rating));
        $this->comment = htmlspecialchars(strip_tags($this->comment));
        
        // Bind parameters
        $stmt->bindParam(':order_id', $this->order_id);
        $stmt->bindParam(':product_id', $this->product_id);
        $stmt->bindParam(':buyer_id', $this->buyer_id);
        $stmt->bindParam(':seller_id', $this->seller_id);
        $stmt->bindParam(':rating', $this->rating);
        $stmt->bindParam(':comment', $this->comment);
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        
        return false;
    }
}
?> 