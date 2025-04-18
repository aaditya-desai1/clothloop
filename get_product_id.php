<?php
// Include database connection
require_once 'backend/config/db_connect.php';

// Initialize response array
$response = [
    'valid_id' => null,
    'message' => ''
];

// Try to get a valid product ID
$result = mysqli_query($conn, "SELECT id FROM products ORDER BY id ASC LIMIT 1");

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $response['valid_id'] = $row['id'];
    $response['message'] = 'Found valid product ID';
} else {
    $response['message'] = 'No products found';
}

// Close the connection
mysqli_close($conn);

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 