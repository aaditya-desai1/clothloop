<?php
// Test file to check the API response

// Call the API
$id = 3; // Test with product ID 3
$api_url = "http://localhost/ClothLoop/backend/api/get_product_images.php?id={$id}";
$response = file_get_contents($api_url);

// Output response
header('Content-Type: application/json');
echo $response;
?> 