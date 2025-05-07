<?php
/**
 * Setup Database Tables (System Endpoint)
 * 
 * This script redirects to the main setup script in api/setup/create_tables.php
 * which creates all necessary tables for the ClothLoop application
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

// Log the setup request
error_log("Database setup requested from system endpoint");

// Redirect to the main setup script
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . $host;
$setupScript = $baseUrl . '/api/setup/create_tables.php';

// Display redirection message
$response = [
    'status' => 'redirecting',
    'message' => 'Redirecting to main setup script...',
    'setup_url' => $setupScript,
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode($response, JSON_PRETTY_PRINT);

// Execute the setup script via curl
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $setupScript);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

// Log the result
error_log("Setup completed from system endpoint with result: " . substr($result, 0, 200) . "...");

// Forward the setup response
echo $result; 