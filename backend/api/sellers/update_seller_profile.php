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

// Write debug info to a log file
$logFile = __DIR__ . '/../../logs/profile_update.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// Check if using form data (for file uploads) or JSON
$data = [];
if (!empty($_POST)) {
    $data = $_POST;
    file_put_contents($logFile, "Received POST data: " . json_encode($_POST, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
    
    // Log file upload information if present
    if (!empty($_FILES)) {
        file_put_contents($logFile, "Received FILES data: " . json_encode($_FILES, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
    }
} else {
    // Get posted data from JSON input
    $data = json_decode(file_get_contents("php://input"), true);
    file_put_contents($logFile, "Received JSON data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
}

// Basic validation
if (empty($data)) {
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
    
    // Handle profile photo upload
    $profile_photo_path = null;
    if (!empty($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        // Create uploads directory if it doesn't exist
        $upload_dir = __DIR__ . '/../../uploads/profile_photos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename with profile_ prefix
        $file_extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $file_name = 'profile_' . uniqid() . '.' . $file_extension;
        $target_file = $upload_dir . $file_name;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file)) {
            // Use just the filename, the path will be constructed in frontend
            $profile_photo_path = $file_name;
            file_put_contents($logFile, "File uploaded successfully to: $target_file as $profile_photo_path\n", FILE_APPEND);
        } else {
            file_put_contents($logFile, "ERROR: Failed to upload file\n", FILE_APPEND);
        }
    }
    
    // Update users table if needed
    $userUpdated = false;
    if (isset($data['name']) || isset($data['email']) || isset($data['phone_no']) || $profile_photo_path) {
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
        
        if ($profile_photo_path) {
            $userFields[] = "profile_photo = ?";
            $userParams[] = $profile_photo_path;
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
        'data' => ['profile_photo' => $profile_photo_path]
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