<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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

// Prepare SQL statement to get buyer details
$stmt = $conn->prepare("SELECT id, name, email, phone_no, created_at FROM buyers WHERE id = ?");
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $buyer = $result->fetch_assoc();
    echo json_encode([
        'status' => 'success',
        'buyer' => $buyer
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Buyer not found'
    ]);
}

$stmt->close();
$conn->close();
?> 