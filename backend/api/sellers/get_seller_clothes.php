<?php
session_start();
require_once '../../config/db_connect.php';

// Check if the user is logged in and is a seller
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'seller') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$response = ['status' => 'error', 'message' => 'Unknown error occurred'];

// Get seller email from session
$seller_email = $_SESSION['user_email'];

// Get clothes data from database
$stmt = $conn->prepare("SELECT * FROM clothes WHERE seller_email = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $seller_email);
$stmt->execute();
$result = $stmt->get_result();

$clothes = [];
while ($row = $result->fetch_assoc()) {
    // Convert JSON string of images back to array
    $row['images'] = json_decode($row['images']);
    $clothes[] = $row;
}

$stmt->close();

$response = [
    'status' => 'success',
    'clothes' => $clothes
];

echo json_encode($response);
?> 