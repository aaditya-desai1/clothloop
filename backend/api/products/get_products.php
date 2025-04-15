<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers to allow cross-origin requests and JSON content type
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// Include database connection
require_once '../../config/db_connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Get request parameters
    $user_type = isset($_GET['user_type']) ? $_GET['user_type'] : 'buyer';
    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 
              (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0);
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    $min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
    $max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : PHP_FLOAT_MAX;
    
    // Base SQL query
    $sql = "SELECT cd.*, CONCAT(u.first_name, ' ', u.last_name) AS seller_name 
            FROM cloth_details cd 
            LEFT JOIN users u ON cd.seller_id = u.id 
            WHERE 1=1";
    $params = [];
    $types = "";
    
    // Add user filter for sellers (only show their own products)
    if ($user_type === 'seller' && $user_id > 0) {
        $sql .= " AND cd.seller_id = ?";
        $params[] = &$user_id;
        $types .= "i";
    }
    
    // Add category filter if provided
    if (!empty($category)) {
        $sql .= " AND cd.category = ?";
        $params[] = &$category;
        $types .= "s";
    }
    
    // Add price range filter
    $sql .= " AND cd.rental_price >= ? AND cd.rental_price <= ?";
    $params[] = &$min_price;
    $params[] = &$max_price;
    $types .= "dd";
    
    // Prepare and execute query
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        if (!empty($types) && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        
        while ($row = $result->fetch_assoc()) {
            // Get product image (if available)
            $image_data = '../../assets/images/placeholder.png'; // Default image
            
            $img_sql = "SELECT image_data, image_type FROM cloth_images WHERE cloth_id = ? LIMIT 1";
            $img_stmt = $conn->prepare($img_sql);
            
            if ($img_stmt) {
                $cloth_id = $row['id'];
                $img_stmt->bind_param("i", $cloth_id);
                $img_stmt->execute();
                $img_result = $img_stmt->get_result();
                
                if ($img_row = $img_result->fetch_assoc()) {
                    // Convert the BLOB data to a base64 string for display in HTML
                    $image_data = 'data:image/' . $img_row['image_type'] . ';base64,' . base64_encode($img_row['image_data']);
                }
                
                $img_stmt->close();
            }
            
            // Format product data
            $products[] = [
                'id' => $row['id'],
                'title' => $row['cloth_title'],
                'description' => $row['description'] ?? '',
                'size' => $row['size'] ?? '',
                'category' => $row['category'] ?? '',
                'rental_price' => (float)$row['rental_price'],
                'contact_number' => $row['contact_number'] ?? '',
                'whatsapp_number' => $row['whatsapp_number'] ?? '',
                'terms_and_conditions' => $row['terms_and_conditions'] ?? '',
                'created_at' => $row['created_at'],
                'seller_id' => $row['seller_id'],
                'seller_name' => $row['seller_name'] ?? 'ClothLoop Seller',
                'image' => $image_data
            ];
        }
        
        $stmt->close();
        
        // Return JSON response
        echo json_encode([
            'status' => 'success',
            'count' => count($products),
            'products' => $products
        ]);
    } else {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching products: ' . $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}

// Close the database connection
$conn->close();
?> 