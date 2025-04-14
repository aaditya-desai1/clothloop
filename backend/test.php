<?php
// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Simple response to test if PHP is working
echo json_encode([
    'success' => true,
    'message' => 'PHP server is working correctly',
    'time' => date('Y-m-d H:i:s'),
    'version' => PHP_VERSION
]);
?> 