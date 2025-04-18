<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Product Review</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1a1a1a;
            color: white;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .review-section {
            margin: 30px 0;
            padding: 20px;
            background-color: #222;
            border-radius: 5px;
        }
        
        .review-section h2 {
            color: white;
            margin-bottom: 20px;
        }
        
        .rating-stars {
            display: inline-block;
            position: relative;
            margin-bottom: 20px;
        }
        
        .rating-stars input {
            display: none;
        }
        
        .rating-stars label {
            float: right;
            padding: 0 5px;
            color: #ccc;
            font-size: 30px;
            cursor: pointer;
        }
        
        .rating-stars input:checked ~ label,
        .rating-stars label:hover,
        .rating-stars label:hover ~ label {
            color: #ffcc00;
        }
        
        textarea {
            width: 100%;
            min-height: 80px;
            padding: 10px;
            margin-bottom: 15px;
            background-color: #333;
            border: 1px solid #444;
            color: white;
            border-radius: 4px;
        }
        
        button#sendReview {
            background-color: white;
            color: black;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: bold;
            border-radius: 4px;
        }
        
        button#sendReview:hover {
            background-color: #f0f0f0;
        }
        
        .debug-section {
            margin-top: 40px;
            padding: 15px;
            background-color: #333;
            border-radius: 5px;
        }
        
        .debug-section h3 {
            margin-top: 0;
        }
        
        #responseOutput {
            background-color: #222;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <h1>Test Product Review</h1>
    
    <div class="review-section">
        <h2>RATE THE PRODUCT</h2>
        
        <p>
            <a href="check_products.php" target="_blank" style="color: #ffcc00; margin-right: 15px;">Check Available Products</a>
            <a href="create_test_product.php" target="_blank" style="color: #ffcc00; margin-right: 15px;">Create Test Product</a>
            <a href="fix_foreign_keys.php" target="_blank" style="color: #ffcc00;">Fix Foreign Keys</a>
        </p>
        <p style="color: #aaa; font-size: 14px;">If you're getting a foreign key error, click "Fix Foreign Keys" above to diagnose and resolve the issue.</p>
        
        <form id="reviewForm" method="post">
            <!-- Hidden input for product ID - will be set dynamically if possible -->
            <input type="hidden" name="product_id" id="productIdInput" value="1">
            <div id="productIdStatus" style="color: #ff9900; margin-bottom: 10px; font-size: 14px;">
                Using product ID: 1. If you get foreign key errors, click "Check Available Products" above.
            </div>
            
            <script>
                // Try to fetch a valid product ID when the page loads
                document.addEventListener('DOMContentLoaded', function() {
                    fetch('get_product_id.php')
                        .then(response => response.json())
                        .then(data => {
                            if (data.valid_id) {
                                document.getElementById('productIdInput').value = data.valid_id;
                                document.getElementById('productIdStatus').textContent = 'Using product ID: ' + data.valid_id;
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching product ID:', error);
                        });
                });
            </script>
            
            <!-- Star rating -->
            <div class="rating-stars">
                <input type="radio" id="star5" name="rating" value="5" checked>
                <label for="star5">★</label>
                <input type="radio" id="star4" name="rating" value="4">
                <label for="star4">★</label>
                <input type="radio" id="star3" name="rating" value="3">
                <label for="star3">★</label>
                <input type="radio" id="star2" name="rating" value="2">
                <label for="star2">★</label>
                <input type="radio" id="star1" name="rating" value="1">
                <label for="star1">★</label>
                
                <!-- Hidden input to store the rating value -->
                <input type="hidden" id="ratingValue" name="rating_value" value="5">
            </div>
            
            <!-- Review text area -->
            <textarea name="review" placeholder="Write your review here">This is a test review. The product is great!</textarea>
            
            <!-- Submit button -->
            <button type="submit" id="sendReview">SEND</button>
        </form>
    </div>
    
    <div class="debug-section">
        <h3>Debug Output</h3>
        <div id="responseOutput">Response will appear here...</div>
    </div>
    
    <script>
        // Wait for the DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Find the review form and output element
            const reviewForm = document.getElementById('reviewForm');
            const responseOutput = document.getElementById('responseOutput');
            
            // Find the star rating elements
            const stars = document.querySelectorAll('.rating-stars input[type="radio"]');
            
            // Current rating value
            let currentRating = 5; // Default to 5 stars
            
            // Add event listeners to stars
            stars.forEach(star => {
                star.addEventListener('change', function() {
                    currentRating = parseInt(this.value, 10);
                    console.log('Rating selected:', currentRating);
                    
                    // Update the hidden rating input
                    const hiddenRating = document.getElementById('ratingValue');
                    if (hiddenRating) {
                        hiddenRating.value = currentRating;
                    }
                });
            });
            
            // Handle form submission
            reviewForm.addEventListener('submit', function(event) {
                // Prevent default form submission
                event.preventDefault();
                
                // Get the product ID
                const productId = document.querySelector('input[name="product_id"]').value;
                
                // Get the review text
                const reviewText = document.querySelector('textarea[name="review"]').value;
                
                // Validate inputs
                if (!productId) {
                    alert('Product ID is missing');
                    return;
                }
                
                if (!reviewText.trim()) {
                    alert('Please write a review');
                    return;
                }
                
                // Show that we're loading
                responseOutput.textContent = "Sending review...";
                
                // Prepare the data
                const reviewData = {
                    product_id: productId,
                    rating: currentRating,
                    review: reviewText
                };
                
                // Log the data being sent
                console.log('Submitting review:', reviewData);
                
                // Send the data to the server
                fetch('backend/api/submit_review.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(reviewData)
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.text();
                })
                .then(text => {
                    console.log('Raw response:', text);
                    
                    try {
                        // Try to parse as JSON
                        const data = JSON.parse(text);
                        responseOutput.textContent = JSON.stringify(data, null, 2);
                        
                        if (data.status === 'success') {
                            // Show success message
                            alert('Thank you for your review!');
                            
                            // Reset the form
                            reviewForm.reset();
                        } else {
                            // Show error message
                            alert('Error: ' + data.message);
                        }
                    } catch (e) {
                        // If not valid JSON, show the raw response
                        responseOutput.textContent = text;
                        alert('There was an issue submitting your review. See debug output for details.');
                    }
                })
                .catch(error => {
                    console.error('Error submitting review:', error);
                    responseOutput.textContent = 'Error: ' + error.message;
                    alert('Failed to submit review. Please try again later.');
                });
            });
        });
    </script>
</body>
</html> 