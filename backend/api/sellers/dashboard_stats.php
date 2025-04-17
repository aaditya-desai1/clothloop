<?php
// API Endpoint: Provides seller dashboard statistics
// This endpoint returns data that will be displayed on the seller dashboard

// Set the response header to JSON
header('Content-Type: application/json');

// Include the database connection
require_once '../../config/db_connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is authenticated and is a seller
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access. Please log in as a seller.'
    ]);
    exit;
}

// Get seller ID from session
$seller_id = $_SESSION['user_id'];

try {
    // Get total number of products for the seller from cloth_details table
    $sql_products = "SELECT COUNT(*) as total_products FROM cloth_details WHERE seller_id = ?";
    $stmt_products = $conn->prepare($sql_products);
    
    if ($stmt_products === false) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt_products->bind_param("i", $seller_id);
    $stmt_products->execute();
    $result_products = $stmt_products->get_result();
    $product_count = $result_products->fetch_assoc();
    $total_products = $product_count['total_products'];
    
    // Get recent products (limit to 5)
    $sql_recent = "SELECT id, cloth_title as name, rental_price as price_per_day, 
                  created_at, category, occasion, size 
                  FROM cloth_details 
                  WHERE seller_id = ? 
                  ORDER BY created_at DESC LIMIT 5";
    
    $stmt_recent = $conn->prepare($sql_recent);
    
    if ($stmt_recent === false) {
        throw new Exception("Failed to prepare recent products statement: " . $conn->error);
    }
    
    $stmt_recent->bind_param("i", $seller_id);
    $stmt_recent->execute();
    $result_recent = $stmt_recent->get_result();
    
    $recent_products = [];
    while ($row = $result_recent->fetch_assoc()) {
        // Add image URL
        $row['image_url'] = "../../../backend/api/sellers/get_cloth_image.php?id=" . $row['id'];
        
        // Format created_at date
        if (isset($row['created_at'])) {
            $date = new DateTime($row['created_at']);
            $row['created_at_formatted'] = $date->format('M d, Y');
        } else {
            $row['created_at_formatted'] = 'N/A';
        }
        
        $recent_products[] = $row;
    }
    
    // Return the stats and recent products in JSON format
    echo json_encode([
        'status' => 'success',
        'stats' => [
            'total_products' => $total_products,
            'average_rating' => '4.7' // This could be replaced with actual ratings in the future
        ],
        'recent_products' => $recent_products
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log("Database error in dashboard_stats.php: " . $e->getMessage());
    
    // Return error message
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred: ' . $e->getMessage()
    ]);
}
?> 