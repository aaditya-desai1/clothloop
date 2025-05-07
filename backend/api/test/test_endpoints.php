<?php
/**
 * API Endpoint Test Script
 * 
 * This script tests key API endpoints to ensure they are functioning correctly.
 * Run this after deployment to verify the backend is working properly.
 */

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Start output buffer to capture results
ob_start();

// Define the endpoints to test
$endpoints = [
    // Basic endpoints
    [
        'name' => 'CORS Check',
        'url' => '../cors.php',
        'method' => 'GET',
        'expected_status' => 200
    ],
    
    // User endpoints
    [
        'name' => 'Login Endpoint',
        'url' => '../users/login.php',
        'method' => 'POST',
        'data' => [
            'email' => 'seller@test.com',
            'password' => 'testpassword'
        ],
        'expected_status' => 200
    ],
    
    // Seller endpoints
    [
        'name' => 'Seller Dashboard Stats',
        'url' => '../sellers/dashboard_stats.php?user_id=1&seller_id=1',
        'method' => 'GET',
        'expected_status' => 200
    ],
    
    // Product endpoints
    [
        'name' => 'Get Products',
        'url' => '../products/get_products.php',
        'method' => 'GET',
        'expected_status' => 200
    ]
];

// Results array
$results = [
    'status' => 'running',
    'timestamp' => date('Y-m-d H:i:s'),
    'endpoint_results' => [],
    'summary' => [
        'total' => count($endpoints),
        'successful' => 0,
        'failed' => 0
    ]
];

// Check if curl is available
$hasCurl = function_exists('curl_init');

// Loop through endpoints and test them
foreach ($endpoints as $endpoint) {
    $result = [
        'name' => $endpoint['name'],
        'url' => $endpoint['url'],
        'method' => $endpoint['method'],
        'status' => 'pending'
    ];
    
    try {
        if ($hasCurl) {
            // Test with curl if available
            testWithCurl($endpoint, $result);
        } else {
            // Test with file_get_contents as fallback
            testWithFileGetContents($endpoint, $result);
        }
        
        // Update success/failure count
        if ($result['status'] === 'success') {
            $results['summary']['successful']++;
        } else {
            $results['summary']['failed']++;
        }
    } catch (Exception $e) {
        $result['status'] = 'error';
        $result['error'] = $e->getMessage();
        $results['summary']['failed']++;
    }
    
    // Add result to results array
    $results['endpoint_results'][] = $result;
}

// Update overall status
if ($results['summary']['failed'] === 0) {
    $results['status'] = 'success';
    $results['message'] = 'All endpoints are working correctly.';
} else {
    $results['status'] = 'failed';
    $results['message'] = $results['summary']['failed'] . ' out of ' . $results['summary']['total'] . ' endpoints failed.';
}

// Send response
echo json_encode($results, JSON_PRETTY_PRINT);

// Database connectivity test
try {
    require_once __DIR__ . '/../../config/database.php';
    
    $database = new Database();
    $db = $database->connect();
    
    // Test query
    $stmt = $db->query("SELECT 1 as test");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get database type
    $dbType = $database->dbType;
    
    // Add database info to response
    $dbInfo = [
        'status' => 'connected',
        'type' => $dbType,
        'test_query' => $result,
        'tables' => []
    ];
    
    // Get list of tables
    if ($dbType === 'pgsql') {
        $tablesQuery = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'";
    } else {
        $tablesQuery = "SHOW TABLES";
    }
    
    $stmt = $db->query($tablesQuery);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tableName = $dbType === 'pgsql' ? $row['table_name'] : array_values($row)[0];
        
        // Get row count for each table
        try {
            $countStmt = $db->query("SELECT COUNT(*) AS count FROM $tableName");
            $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $dbInfo['tables'][] = [
                'name' => $tableName,
                'rows' => $count
            ];
        } catch (Exception $e) {
            $dbInfo['tables'][] = [
                'name' => $tableName,
                'rows' => 'Error counting: ' . $e->getMessage()
            ];
        }
    }
    
    echo json_encode(['database_info' => $dbInfo], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'database_info' => [
            'status' => 'error',
            'message' => $e->getMessage()
        ]
    ], JSON_PRETTY_PRINT);
}

/**
 * Test an endpoint using curl
 * 
 * @param array $endpoint Endpoint configuration
 * @param array &$result Result array to update
 */
function testWithCurl($endpoint, &$result) {
    // Create cURL handle
    $ch = curl_init();
    
    // Set basic options
    curl_setopt($ch, CURLOPT_URL, $endpoint['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    // Set method
    if ($endpoint['method'] === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if (isset($endpoint['data'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($endpoint['data']));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
    } else if ($endpoint['method'] === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if (isset($endpoint['data'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($endpoint['data']));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
    } else if ($endpoint['method'] === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    // Execute the request
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $body = substr($response, $headerSize);
    
    // Close cURL handle
    curl_close($ch);
    
    // Process the response
    $responseData = json_decode($body, true);
    
    // Check status code
    if ($httpCode === $endpoint['expected_status']) {
        $result['status'] = 'success';
    } else {
        $result['status'] = 'failed';
        $result['error'] = 'Expected status ' . $endpoint['expected_status'] . ', got ' . $httpCode;
    }
    
    // Add response data
    $result['http_code'] = $httpCode;
    $result['response'] = $responseData;
}

/**
 * Test an endpoint using file_get_contents
 * 
 * @param array $endpoint Endpoint configuration
 * @param array &$result Result array to update
 */
function testWithFileGetContents($endpoint, &$result) {
    // Set up stream context for different HTTP methods
    $context = stream_context_create([
        'http' => [
            'method' => $endpoint['method'],
            'ignore_errors' => true  // Continue even on HTTP error
        ]
    ]);
    
    // Add request body for POST/PUT
    if (in_array($endpoint['method'], ['POST', 'PUT']) && isset($endpoint['data'])) {
        $data = json_encode($endpoint['data']);
        $context['http']['header'] = "Content-Type: application/json\r\n";
        $context['http']['content'] = $data;
    }
    
    // Make the request
    $response = @file_get_contents($endpoint['url'], false, $context);
    
    // Get response status
    if (isset($http_response_header)) {
        preg_match('/^HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $matches);
        $httpCode = intval($matches[1]);
    } else {
        $httpCode = 0;
    }
    
    // Process the response
    $responseData = json_decode($response, true);
    
    // Check status code
    if ($httpCode === $endpoint['expected_status']) {
        $result['status'] = 'success';
    } else {
        $result['status'] = 'failed';
        $result['error'] = 'Expected status ' . $endpoint['expected_status'] . ', got ' . $httpCode;
    }
    
    // Add response data
    $result['http_code'] = $httpCode;
    $result['response'] = $responseData;
} 