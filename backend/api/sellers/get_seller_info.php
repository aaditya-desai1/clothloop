<?php
// Start session to get seller ID
session_start();

// Include database connection
require_once '../../config/db_connect.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Create connection
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => "Connection failed: " . $conn->connect_error]));
}

// Get seller information
try {
    // In a real-world application, you would get the seller ID from the session
    // For now, we'll use a placeholder or fetch the first seller from the database
    
    // Option 1: Get seller ID from session (uncomment in production)
    // if (isset($_SESSION['user_id'])) {
    //     $seller_id = $_SESSION['user_id'];
    // } else {
    //     throw new Exception("User not logged in");
    // }
    
    // Option 2: For demonstration, get the first seller from the database
    $sql = "SELECT u.id, u.username 
            FROM users u 
            JOIN seller_details s ON u.id = s.user_id 
            LIMIT 1";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'status' => 'success', 
            'seller_name' => $row['username']
        ]);
    } else {
        // Fallback in case no seller is found
        echo json_encode([
            'status' => 'success', 
            'seller_name' => 'Seller'
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

// Close connection
$conn->close();
?> 