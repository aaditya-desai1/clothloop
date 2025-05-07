<?php
/**
 * Database Diagnostics
 * 
 * This script attempts to diagnose database connection issues by trying
 * multiple connection methods.
 */

// Allow CORS from any origin for Vercel frontend
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Max-Age: 3600");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Set content type
header('Content-Type: application/json');

// Include environment variables
require_once __DIR__ . '/../../config/env.php';

// Results array
$results = [
    'environment' => IS_PRODUCTION ? 'production' : 'development',
    'db_host' => DB_HOST,
    'db_name' => DB_NAME,
    'db_user' => DB_USER,
    'db_type' => $dbConfig['type'] ?? 'unknown',
    'tests' => []
];

// Test 1: Standard PDO connection
$results['tests']['standard_pdo'] = ['status' => 'untested'];
try {
    $dsn = "";
    if ($dbConfig['type'] === 'pgsql') {
        $dsn = "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME;
    } else {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
    }
    
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test query
    $stmt = $conn->query("SELECT 1 as test");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row && isset($row['test']) && $row['test'] == 1) {
        $results['tests']['standard_pdo'] = [
            'status' => 'success',
            'message' => 'Connection successful'
        ];
    } else {
        $results['tests']['standard_pdo'] = [
            'status' => 'error',
            'message' => 'Connection successful but query failed'
        ];
    }
} catch (PDOException $e) {
    $results['tests']['standard_pdo'] = [
        'status' => 'error',
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ];
}

// Test 2: PostgreSQL connection with explicit sslmode
if ($dbConfig['type'] === 'pgsql') {
    $results['tests']['pgsql_with_ssl'] = ['status' => 'untested'];
    try {
        $dsn = "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";sslmode=require";
        $conn = new PDO($dsn, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Test query
        $stmt = $conn->query("SELECT 1 as test");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row && isset($row['test']) && $row['test'] == 1) {
            $results['tests']['pgsql_with_ssl'] = [
                'status' => 'success',
                'message' => 'Connection successful with SSL'
            ];
        } else {
            $results['tests']['pgsql_with_ssl'] = [
                'status' => 'error',
                'message' => 'Connection successful but query failed'
            ];
        }
    } catch (PDOException $e) {
        $results['tests']['pgsql_with_ssl'] = [
            'status' => 'error',
            'message' => $e->getMessage(),
            'code' => $e->getCode()
        ];
    }
}

// Test 3: Connection with port
$results['tests']['connection_with_port'] = ['status' => 'untested'];
try {
    $port = $dbConfig['type'] === 'pgsql' ? '5432' : '3306';
    
    if ($dbConfig['type'] === 'pgsql') {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME;
    } else {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME;
    }
    
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test query
    $stmt = $conn->query("SELECT 1 as test");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row && isset($row['test']) && $row['test'] == 1) {
        $results['tests']['connection_with_port'] = [
            'status' => 'success',
            'message' => 'Connection successful with port ' . $port
        ];
    } else {
        $results['tests']['connection_with_port'] = [
            'status' => 'error',
            'message' => 'Connection successful but query failed'
        ];
    }
} catch (PDOException $e) {
    $results['tests']['connection_with_port'] = [
        'status' => 'error',
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ];
}

// Test 4: Using native library if available
if ($dbConfig['type'] === 'pgsql' && function_exists('pg_connect')) {
    $results['tests']['native_pgsql'] = ['status' => 'untested'];
    try {
        $connString = "host=" . DB_HOST . " dbname=" . DB_NAME . " user=" . DB_USER . " password=" . DB_PASS;
        $conn = pg_connect($connString);
        
        if ($conn) {
            $result = pg_query($conn, "SELECT 1 as test");
            $row = pg_fetch_assoc($result);
            
            if ($row && isset($row['test']) && $row['test'] == 1) {
                $results['tests']['native_pgsql'] = [
                    'status' => 'success',
                    'message' => 'Native PostgreSQL connection successful'
                ];
            } else {
                $results['tests']['native_pgsql'] = [
                    'status' => 'error',
                    'message' => 'Native PostgreSQL connection successful but query failed'
                ];
            }
            
            pg_close($conn);
        } else {
            $results['tests']['native_pgsql'] = [
                'status' => 'error',
                'message' => 'Native PostgreSQL connection failed'
            ];
        }
    } catch (Exception $e) {
        $results['tests']['native_pgsql'] = [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
} else if ($dbConfig['type'] === 'mysql' && function_exists('mysqli_connect')) {
    $results['tests']['native_mysql'] = ['status' => 'untested'];
    try {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn) {
            $result = mysqli_query($conn, "SELECT 1 as test");
            $row = mysqli_fetch_assoc($result);
            
            if ($row && isset($row['test']) && $row['test'] == 1) {
                $results['tests']['native_mysql'] = [
                    'status' => 'success',
                    'message' => 'Native MySQL connection successful'
                ];
            } else {
                $results['tests']['native_mysql'] = [
                    'status' => 'error',
                    'message' => 'Native MySQL connection successful but query failed'
                ];
            }
            
            mysqli_close($conn);
        } else {
            $results['tests']['native_mysql'] = [
                'status' => 'error',
                'message' => 'Native MySQL connection failed: ' . mysqli_connect_error()
            ];
        }
    } catch (Exception $e) {
        $results['tests']['native_mysql'] = [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

// Check if any connection method worked
$anySuccess = false;
foreach ($results['tests'] as $test) {
    if ($test['status'] === 'success') {
        $anySuccess = true;
        break;
    }
}

$results['overall_status'] = $anySuccess ? 'success' : 'error';
$results['message'] = $anySuccess 
    ? 'At least one connection method successful' 
    : 'All connection methods failed';
$results['timestamp'] = date('Y-m-d H:i:s');

// Output results
echo json_encode($results, JSON_PRETTY_PRINT); 