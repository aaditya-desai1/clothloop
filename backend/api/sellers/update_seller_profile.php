<?php
/**
 * Update Seller Profile API
 * Updates the profile information of the authenticated seller
 */

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/response.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"), true);

// Write debug info to a log file
$logFile = __DIR__ . '/../../logs/profile_update.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'received_data' => $data
];
file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

// Basic validation
if (!$data) {
    file_put_contents($logFile, "ERROR: No data provided\n\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'No data provided']);
    exit;
}

if (!isset($data['seller_id']) || empty($data['seller_id'])) {
    file_put_contents($logFile, "ERROR: No seller_id provided\n\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Seller ID is required']);
    exit;
}

$seller_id = intval($data['seller_id']);

try {
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Begin transaction
    $db->beginTransaction();
    file_put_contents($logFile, "Transaction started\n", FILE_APPEND);
    
    // Update users table if needed
    $userUpdated = false;
    if (isset($data['name']) || isset($data['email']) || isset($data['phone_no'])) {
        $userFields = [];
        $userParams = [];
        
        if (isset($data['name'])) {
            $userFields[] = "name = ?";
            $userParams[] = $data['name'];
        }
        
        if (isset($data['email'])) {
            $userFields[] = "email = ?";
            $userParams[] = $data['email'];
        }
        
        if (isset($data['phone_no'])) {
            $userFields[] = "phone_no = ?";
            $userParams[] = $data['phone_no'];
        }
        
        if (!empty($userFields)) {
            $userParams[] = $seller_id; // Add seller_id for WHERE clause
            $userQuery = "UPDATE users SET " . implode(', ', $userFields) . " WHERE id = ?";
            
            file_put_contents($logFile, "User Update Query: $userQuery\n", FILE_APPEND);
            file_put_contents($logFile, "User Params: " . json_encode($userParams) . "\n", FILE_APPEND);
            
            $userStmt = $db->prepare($userQuery);
            $userResult = $userStmt->execute($userParams);
            $userRows = $userStmt->rowCount();
            
            file_put_contents($logFile, "User Update Result: " . ($userResult ? "Success" : "Failed") . "\n", FILE_APPEND);
            file_put_contents($logFile, "User Rows Affected: $userRows\n\n", FILE_APPEND);
            
            $userUpdated = $userResult && $userRows > 0;
        }
    }
    
    // Update sellers table if needed
    $sellerUpdated = false;
    if (isset($data['shop_name']) || isset($data['description']) || isset($data['latitude']) || isset($data['longitude'])) {
        $sellerFields = [];
        $sellerParams = [];
        
        if (isset($data['shop_name'])) {
            $sellerFields[] = "shop_name = ?";
            $sellerParams[] = $data['shop_name'];
        }
        
        if (isset($data['description'])) {
            $sellerFields[] = "description = ?";
            $sellerParams[] = $data['description'];
        }
        
        if (isset($data['latitude'])) {
            $sellerFields[] = "latitude = ?";
            $sellerParams[] = $data['latitude'];
        }
        
        if (isset($data['longitude'])) {
            $sellerFields[] = "longitude = ?";
            $sellerParams[] = $data['longitude'];
        }
        
        if (!empty($sellerFields)) {
            $sellerParams[] = $seller_id; // Add seller_id for WHERE clause
            $sellerQuery = "UPDATE sellers SET " . implode(', ', $sellerFields) . " WHERE id = ?";
            
            file_put_contents($logFile, "Seller Update Query: $sellerQuery\n", FILE_APPEND);
            file_put_contents($logFile, "Seller Params: " . json_encode($sellerParams) . "\n", FILE_APPEND);
            
            $sellerStmt = $db->prepare($sellerQuery);
            $sellerResult = $sellerStmt->execute($sellerParams);
            $sellerRows = $sellerStmt->rowCount();
            
            file_put_contents($logFile, "Seller Update Result: " . ($sellerResult ? "Success" : "Failed") . "\n", FILE_APPEND);
            file_put_contents($logFile, "Seller Rows Affected: $sellerRows\n\n", FILE_APPEND);
            
            $sellerUpdated = $sellerResult && $sellerRows > 0;
        }
    }
    
    // Commit transaction
    $db->commit();
    file_put_contents($logFile, "Transaction committed successfully\n\n", FILE_APPEND);
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Profile updated successfully',
        'data' => null
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($db)) {
        $db->rollBack();
        file_put_contents($logFile, "Transaction rolled back\n", FILE_APPEND);
    }
    
    // Log error
    file_put_contents($logFile, "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    file_put_contents($logFile, "Stack Trace: " . $e->getTraceAsString() . "\n\n", FILE_APPEND);
    
    // Return error response
    echo json_encode([
        'status' => 'error',
        'message' => 'Error updating profile: ' . $e->getMessage(),
        'errors' => null
    ]);
} 