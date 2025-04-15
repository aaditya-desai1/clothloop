<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once '../../config/db_connect.php';

header('Content-Type: application/json');

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validate product ID
if ($product_id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid product ID'
    ]);
    exit;
}

try {
    // Get product details
    $query = "
        SELECT 
            p.id, 
            p.name, 
            p.description, 
            p.price, 
            p.category,
            p.condition_status,
            p.brand,
            p.size,
            p.gender,
            p.color,
            p.material,
            p.created_at,
            p.seller_id,
            s.store_name,
            s.bio as store_description,
            u.name as seller_name,
            u.profile_image,
            (SELECT COUNT(*) FROM products WHERE seller_id = p.seller_id AND status = 'active') as seller_product_count,
            (SELECT AVG(rating) FROM seller_ratings WHERE seller_id = p.seller_id) as seller_rating,
            (SELECT COUNT(*) FROM seller_ratings WHERE seller_id = p.seller_id) as seller_rating_count
        FROM 
            products p
        LEFT JOIN 
            sellers s ON p.seller_id = s.user_id
        LEFT JOIN 
            users u ON s.user_id = u.id
        WHERE 
            p.id = ? AND p.status = 'active'
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Product not found'
        ]);
        exit;
    }

    $product = $result->fetch_assoc();
    
    // Convert numeric fields
    $product['price'] = floatval($product['price']);
    $product['seller_rating'] = $product['seller_rating'] ? floatval($product['seller_rating']) : null;
    $product['seller_rating_count'] = intval($product['seller_rating_count']);
    $product['seller_product_count'] = intval($product['seller_product_count']);

    // Get product images
    $imageQuery = "
        SELECT 
            id, 
            image_url,
            is_main
        FROM 
            product_images
        WHERE 
            product_id = ?
        ORDER BY 
            is_main DESC, id ASC
    ";

    $imgStmt = $conn->prepare($imageQuery);
    $imgStmt->bind_param('i', $product_id);
    $imgStmt->execute();
    $imgResult = $imgStmt->get_result();

    $images = [];
    while ($img = $imgResult->fetch_assoc()) {
        $images[] = [
            'id' => intval($img['id']),
            'url' => $img['image_url'],
            'is_main' => (bool)$img['is_main']
        ];
    }
    
    $product['images'] = $images;

    // Get similar products based on category and brand
    $similarQuery = "
        SELECT 
            p.id, 
            p.name, 
            p.price,
            p.brand,
            p.category,
            pi.image_url as main_image
        FROM 
            products p
        LEFT JOIN 
            (SELECT product_id, image_url 
             FROM product_images 
             WHERE is_main = 1 OR id = (SELECT MIN(id) FROM product_images pi WHERE pi.product_id = product_id)
             GROUP BY product_id) pi 
        ON p.id = pi.product_id
        WHERE 
            p.id != ? AND 
            p.status = 'active' AND
            (p.category = ? OR p.brand = ?)
        ORDER BY 
            RAND()
        LIMIT 6
    ";

    $similarStmt = $conn->prepare($similarQuery);
    $similarStmt->bind_param('iss', $product_id, $product['category'], $product['brand']);
    $similarStmt->execute();
    $similarResult = $similarStmt->get_result();

    $similarProducts = [];
    while ($similar = $similarResult->fetch_assoc()) {
        $similar['price'] = floatval($similar['price']);
        $similarProducts[] = $similar;
    }

    // Prepare response
    $response = [
        'status' => 'success',
        'product' => $product,
        'similar_products' => $similarProducts
    ];
    
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error retrieving product details: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?> 