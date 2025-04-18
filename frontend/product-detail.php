<!-- Add this near the end of your product detail page, where you want the review form to appear -->

<div class="review-section">
    <h2>RATE THE PRODUCT</h2>
    
    <form id="reviewForm" method="post">
        <!-- Hidden input for product ID -->
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        
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
        <textarea name="review" placeholder="Write your review here"></textarea>
        
        <!-- Submit button -->
        <button type="submit" id="sendReview">SEND</button>
    </form>
</div>

<!-- Include the review form JavaScript at the end of the file, before the closing body tag -->
<script src="/frontend/js/review-form.js"></script>

<!-- Add this CSS for the star rating (you can move this to your main CSS file) -->
<style>
.review-section {
    margin: 30px 0;
    padding: 20px;
    background-color: #1a1a1a;
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
    background-color: #222;
    border: 1px solid #333;
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
</style> 