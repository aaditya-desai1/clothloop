<?php
/**
 * Seller Product Listings API
 * Returns all products for the authenticated seller
 */

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/auth.php';
require_once __DIR__ . '/../../utils/response.php';

// Check if seller is authenticated
Auth::requireRole('seller');

// Get current seller
$seller = Auth::getCurrentUser();
$sellerId = $seller['id'];

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

try {
    // Database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if products table exists
    $checkTable = $db->prepare("SHOW TABLES LIKE 'products'");
    $checkTable->execute();
    
    if ($checkTable->rowCount() == 0) {
        // Products table doesn't exist yet
        Response::success('Product listings retrieved successfully', [
            'products' => [],
            'pagination' => [
                'total_records' => 0,
                'total_pages' => 0,
                'current_page' => 1,
                'limit' => $limit,
                'has_next_page' => false,
                'has_prev_page' => false
            ]
        ]);
        exit;
    }
    
    // Check if customer_interests table exists
    $checkInterestsTable = $db->prepare("SHOW TABLES LIKE 'customer_interests'");
    $checkInterestsTable->execute();
    $customerInterestsExists = ($checkInterestsTable->rowCount() > 0);
    
    // Build query based on parameters and table existence
    if ($customerInterestsExists) {
        $query = "
            SELECT p.*, 
                   COALESCE(p.status, 'inactive') AS status_normalized,
                   c.name as category_name,
                   (SELECT COUNT(*) FROM customer_interests ci WHERE ci.product_id = p.id) as interest_count
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.seller_id = :seller_id
        ";
    } else {
        $query = "
            SELECT p.*, 
                   COALESCE(p.status, 'inactive') AS status_normalized,
                   c.name as category_name, 
                   0 as interest_count
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.seller_id = :seller_id
        ";
    }
    
    $params = [':seller_id' => $sellerId];
    
    // Add search condition if provided
    if (!empty($search)) {
        $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    // Add category filter if provided
    if (!empty($category)) {
        $query .= " AND p.category_id = :category_id";
        $params[':category_id'] = $category;
    }
    
    // Add status filter if provided
    if (!empty($status)) {
        $query .= " AND p.status = :status";
        $params[':status'] = $status;
    }
    
    // Add ordering
    $query .= " ORDER BY p.created_at DESC";
    
    // Count total records for pagination
    $countQuery = "SELECT COUNT(*) as total FROM products p WHERE p.seller_id = :seller_id";
    
    // Add search condition to count query if provided
    if (!empty($search)) {
        $countQuery .= " AND (p.name LIKE :search OR p.description LIKE :search)";
    }
    
    // Add category filter to count query if provided
    if (!empty($category)) {
        $countQuery .= " AND p.category_id = :category_id";
    }
    
    // Add status filter to count query if provided
    if (!empty($status)) {
        $countQuery .= " AND p.status = :status";
    }
    
    $countStmt = $db->prepare($countQuery);
    
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    
    $countStmt->execute();
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    $totalRecords = $countResult && isset($countResult['total']) ? $countResult['total'] : 0;
    
    // Add pagination
    $query .= " LIMIT :offset, :limit";
    
    // Prepare and execute the main query
    $stmt = $db->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    
    // Debug query
    error_log("Product listings query: " . $query);
    error_log("Params: " . json_encode($params));
    error_log("Offset: " . $offset . ", Limit: " . $limit);
    
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug results
    error_log("Number of products found: " . count($products));
    if (count($products) > 0) {
        error_log("First product: " . json_encode($products[0], JSON_PRETTY_PRINT));
    }
    
    // Calculate pagination metadata
    $totalPages = ceil($totalRecords / $limit);
    $hasNextPage = $page < $totalPages;
    $hasPrevPage = $page > 1;
    
    // Format response
    $response = [
        'products' => $products,
        'pagination' => [
            'total_records' => $totalRecords,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'limit' => $limit,
            'has_next_page' => $hasNextPage,
            'has_prev_page' => $hasPrevPage
        ]
    ];
    
    // Return success response with product listings
    Response::success('Product listings retrieved successfully', $response);
    
} catch (Exception $e) {
    // Log error
    error_log("Error fetching product listings: " . $e->getMessage());
    
    // Return error response
    Response::error('Failed to fetch product listings: ' . $e->getMessage());
} 