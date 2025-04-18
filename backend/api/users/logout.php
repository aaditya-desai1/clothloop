<?php
/**
 * User Logout API
 * Ends user session and logs them out
 */

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Required files
require_once __DIR__ . '/../../utils/auth.php';
require_once __DIR__ . '/../../utils/response.php';

// Process only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Allow GET for browsers redirecting to logout page
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        Response::error('Method not allowed', null, 405);
    }
}

// End the session
Auth::endSession();

// Send success response
Response::success('Logout successful'); 