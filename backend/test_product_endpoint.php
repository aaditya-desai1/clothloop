<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "====== ClothLoop Product Endpoint Test =======\n\n";

// Test URL
$url = "http://localhost/ClothLoop/backend/utils/product_operations.php?operation=fetch_all";

// Use cURL to make a request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

echo "Sending request to: $url\n\n";
$response = curl_exec($ch);

if ($response === false) {
    echo "cURL Error: " . curl_error($ch) . "\n";
} else {
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "HTTP Response Code: $httpCode\n\n";
    
    // Try to decode the JSON response
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Error decoding JSON: " . json_last_error_msg() . "\n";
        echo "Raw Response:\n" . substr($response, 0, 1000) . "...\n";
    } else {
        echo "API Response Status: " . ($data['status'] ?? 'unknown') . "\n";
        
        if (isset($data['status']) && $data['status'] === 'success') {
            $productCount = isset($data['products']) ? count($data['products']) : 0;
            echo "Product Count: $productCount\n\n";
            
            if ($productCount > 0) {
                echo "Sample Product Data (first item):\n";
                $sampleProduct = $data['products'][0];
                
                foreach ($sampleProduct as $key => $value) {
                    // Skip image data as it's too long
                    if ($key === 'image' && strlen($value) > 50) {
                        echo "- $key: [TRUNCATED IMAGE DATA]\n";
                    } else {
                        echo "- $key: " . (strlen($value) > 100 ? substr($value, 0, 100) . "..." : $value) . "\n";
                    }
                }
            }
        } else {
            echo "Error Message: " . ($data['message'] ?? 'No error message') . "\n";
            if (isset($data['debug'])) {
                echo "Debug Info:\n";
                print_r($data['debug']);
            }
        }
    }
}

curl_close($ch);
echo "\n====== Test Complete =======\n";
?> 