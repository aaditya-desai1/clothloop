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
require_once __DIR__ . '/../../utils/auth.php';
require_once __DIR__ . '/../../utils/response.php';

// Check if user is authenticated
Auth::requireAuth();

// Get the current user
$user = Auth::getCurrentUser();

// Make sure the user is a seller
if ($user['role'] !== 'seller') {
    Response::error('Access denied. This endpoint is for sellers only.', null, 403);
}

// Get posted data
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    Response::error('No data provided', null, 400);
}

try {
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    $db->beginTransaction();
    
    // Update user personal information
    $userFieldsToUpdate = [];
    $userParams = [];
    
    // Check which fields were provided and prepare update query
    if (isset($data['name'])) {
        $userFieldsToUpdate[] = "name = :name";
        $userParams[':name'] = $data['name'];
    }
    
    if (isset($data['email'])) {
        $userFieldsToUpdate[] = "email = :email";
        $userParams[':email'] = $data['email'];
    }
    
    if (isset($data['phone_no'])) {
        $userFieldsToUpdate[] = "phone_no = :phone_no";
        $userParams[':phone_no'] = $data['phone_no'];
    }
    
    // If profile photo was provided, handle it
    if (isset($data['profile_photo'])) {
        $userFieldsToUpdate[] = "profile_photo = :profile_photo";
        $userParams[':profile_photo'] = $data['profile_photo'];
    }
    
    // Update user table if any personal fields need updating
    if (!empty($userFieldsToUpdate)) {
        $userQuery = "UPDATE users SET " . implode(", ", $userFieldsToUpdate) . " WHERE id = :id";
        $userParams[':id'] = $user['id'];
        
        $stmt = $db->prepare($userQuery);
        $stmt->execute($userParams);
    }
    
    // Update seller shop information
    $sellerFieldsToUpdate = [];
    $sellerParams = [];
    
    // Check which fields were provided and prepare update query
    if (isset($data['shop_name'])) {
        $sellerFieldsToUpdate[] = "shop_name = :shop_name";
        $sellerParams[':shop_name'] = $data['shop_name'];
    }
    
    if (isset($data['shop_description'])) {
        $sellerFieldsToUpdate[] = "description = :description";
        $sellerParams[':description'] = $data['shop_description'];
    }
    
    if (isset($data['address'])) {
        $sellerFieldsToUpdate[] = "address = :address";
        $sellerParams[':address'] = $data['address'];
    }
    
    if (isset($data['latitude']) && isset($data['longitude'])) {
        $sellerFieldsToUpdate[] = "latitude = :latitude";
        $sellerFieldsToUpdate[] = "longitude = :longitude";
        $sellerParams[':latitude'] = $data['latitude'];
        $sellerParams[':longitude'] = $data['longitude'];
    }
    
    // Update sellers table if any shop fields need updating
    if (!empty($sellerFieldsToUpdate)) {
        $sellerQuery = "UPDATE sellers SET " . implode(", ", $sellerFieldsToUpdate) . " WHERE id = :id";
        $sellerParams[':id'] = $user['id'];
        
        $stmt = $db->prepare($sellerQuery);
        $stmt->execute($sellerParams);
    }
    
    // Commit transaction
    $db->commit();
    
    Response::success('Profile updated successfully');
    
} catch (Exception $e) {
    // Rollback transaction if error occurs
    if (isset($db)) {
        $db->rollBack();
    }
    Response::error('Error updating profile: ' . $e->getMessage());
} 