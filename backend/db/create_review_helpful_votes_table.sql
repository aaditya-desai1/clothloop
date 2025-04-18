CREATE TABLE IF NOT EXISTS review_helpful_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY unique_review_user (review_id, user_id),
    FOREIGN KEY (review_id) REFERENCES product_reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add helpful_count column to product_reviews table if it doesn't exist
ALTER TABLE product_reviews ADD COLUMN IF NOT EXISTS helpful_count INT DEFAULT 0; 