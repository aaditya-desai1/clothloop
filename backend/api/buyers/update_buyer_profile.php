<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// For preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include session configuration
require_once "../../config/session.php";

// Include database connection
include_once "../../config/db_connect.php";

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'buyer') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access',
        'debug' => [
            'session_id' => session_id(),
            'session_data' => $_SESSION
        ]
    ]);
    exit;
}

// Get the buyer ID from the session
$buyer_id = $_SESSION['user_id'];

// Get POST data
$data = json_decode(file_get_contents("php://input"));

// Validate input data
if (
    !isset($data->name) || empty(trim($data->name)) ||
    !isset($data->email) || empty(trim($data->email)) ||
    !isset($data->phone_no) || empty(trim($data->phone_no))
) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please provide all required information'
    ]);
    exit;
}

// Sanitize input data
$name = htmlspecialchars(strip_tags(trim($data->name)));
$email = htmlspecialchars(strip_tags(trim($data->email)));
$phone_no = htmlspecialchars(strip_tags(trim($data->phone_no)));
$password = isset($data->password) && !empty(trim($data->password)) ? 
            htmlspecialchars(strip_tags(trim($data->password))) : null;

// Check if email is already taken by another user
$checkEmail = $conn->prepare("SELECT id FROM buyers WHERE email = ? AND id != ?");
$checkEmail->bind_param("si", $email, $buyer_id);
$checkEmail->execute();
$emailResult = $checkEmail->get_result();

if ($emailResult->num_rows > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email is already used by another user'
    ]);
    exit;
}

// Update buyer profile
if ($password) {
    // If password is provided, update it as well
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE buyers SET name = ?, email = ?, phone_no = ?, password = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $email, $phone_no, $hashedPassword, $buyer_id);
} else {
    // Otherwise, update only the basic info
    $stmt = $conn->prepare("UPDATE buyers SET name = ?, email = ?, phone_no = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $email, $phone_no, $buyer_id);
}

if ($stmt->execute()) {
    // Update session data with new values
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Profile updated successfully',
        'buyer' => [
            'id' => $buyer_id,
            'name' => $name,
            'email' => $email,
            'phone_no' => $phone_no
        ]
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update profile: ' . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?> 