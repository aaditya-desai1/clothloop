<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once '../../config/db_connect.php';

// Get search parameters
$keyword = isset($_GET['keyword']) ? $conn->real_escape_string($_GET['keyword']) : '';
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$limit = isset($_GET['limit']) ? min(50, max(1, intval($_GET['limit']))) : 24;

header('Content-Type: application/json');

// Validate required parameters
if (empty($keyword)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Search keyword is required'
    ]);
    exit;
}

try {
    // Build search query
    $query = "
        SELECT 
            p.id, 
            p.name, 
            p.price, 
            p.category,
            p.condition_status,
            p.brand,
            p.created_at,
            pi.image_url as main_image,
            s.store_name,
            u.name as seller_name,
            MATCH(p.name, p.description, p.brand) AGAINST(?) as relevance
        FROM 
            products p
        LEFT JOIN 
            (SELECT product_id, image_url 
             FROM product_images 
             WHERE is_main = 1 OR id = (SELECT MIN(id) FROM product_images pi WHERE pi.product_id = product_id)
             GROUP BY product_id) pi 
        ON p.id = pi.product_id
        LEFT JOIN 
            sellers s ON p.seller_id = s.user_id
        LEFT JOIN 
            users u ON s.user_id = u.id
        WHERE 
            p.status = 'active' AND
            MATCH(p.name, p.description, p.brand) AGAINST(?)
    ";

    $params = [$keyword, $keyword];
    $types = 'ss';

    // Add category filter if provided
    if (!empty($category)) {
        $query .= " AND p.category = ?";
        $params[] = $category;
        $types .= 's';
    }

    // Add order by relevance and limit
    $query .= " ORDER BY relevance DESC LIMIT ?";
    $params[] = $limit;
    $types .= 'i';

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Convert price to float
        $row['price'] = floatval($row['price']);
        
        // Remove relevance score from final output
        unset($row['relevance']);
        
        $products[] = $row;
    }

    // Get suggested categories based on search term
    $categoryQuery = "
        SELECT 
            DISTINCT p.category,
            COUNT(*) as product_count
        FROM 
            products p
        WHERE 
            p.status = 'active' AND
            MATCH(p.name, p.description, p.brand) AGAINST(?)
        GROUP BY 
            p.category
        ORDER BY 
            product_count DESC
        LIMIT 5
    ";

    $catStmt = $conn->prepare($categoryQuery);
    $catStmt->bind_param('s', $keyword);
    $catStmt->execute();
    $catResult = $catStmt->get_result();

    $categories = [];
    while ($cat = $catResult->fetch_assoc()) {
        $categories[] = [
            'name' => $cat['category'],
            'product_count' => intval($cat['product_count'])
        ];
    }

    // Get suggested brands based on search term
    $brandsQuery = "
        SELECT 
            DISTINCT p.brand,
            COUNT(*) as product_count
        FROM 
            products p
        WHERE 
            p.status = 'active' AND
            MATCH(p.name, p.description, p.brand) AGAINST(?)
        GROUP BY 
            p.brand
        ORDER BY 
            product_count DESC
        LIMIT 5
    ";

    $brandStmt = $conn->prepare($brandsQuery);
    $brandStmt->bind_param('s', $keyword);
    $brandStmt->execute();
    $brandResult = $brandStmt->get_result();

    $brands = [];
    while ($brand = $brandResult->fetch_assoc()) {
        if (!empty($brand['brand'])) {
            $brands[] = [
                'name' => $brand['brand'],
                'product_count' => intval($brand['product_count'])
            ];
        }
    }

    // Prepare response
    $response = [
        'status' => 'success',
        'keyword' => $keyword,
        'products_count' => count($products),
        'suggested_categories' => $categories,
        'suggested_brands' => $brands,
        'products' => $products
    ];
    
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error searching products: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?> 