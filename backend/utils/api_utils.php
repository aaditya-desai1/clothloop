<?php
/**
 * API Utilities
 * Helper functions for API responses and error handling
 */

// Include necessary files if not already included
if (!defined('DEBUG_MODE')) {
    require_once __DIR__ . '/../config/env.php';
}

/**
 * Send a JSON response
 * 
 * @param mixed $data The data to send
 * @param int $status_code HTTP status code
 * @return void
 */
function sendResponse($data, $status_code = 200) {
    // Set HTTP status code
    http_response_code($status_code);
    
    // Set content type header
    header('Content-Type: application/json');
    
    // Send response
    echo json_encode($data);
    exit;
}

/**
 * Send a success response
 * 
 * @param string $message Success message
 * @param mixed $data Additional data to include
 * @param int $status_code HTTP status code
 * @return void
 */
function sendSuccess($message, $data = null, $status_code = 200) {
    $response = [
        'status' => 'success',
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    sendResponse($response, $status_code);
}

/**
 * Send an error response
 * 
 * @param string $message Error message
 * @param mixed $errors Additional error details
 * @param int $status_code HTTP status code
 * @return void
 */
function sendError($message, $errors = null, $status_code = 400) {
    $response = [
        'status' => 'error',
        'message' => $message
    ];
    
    if ($errors !== null) {
        $response['errors'] = $errors;
    }
    
    // Log error in production
    if (IS_PRODUCTION && $status_code >= 500) {
        error_log('[API Error] ' . $message . (is_string($errors) ? ': ' . $errors : ''));
    }
    
    sendResponse($response, $status_code);
}

/**
 * Validate required fields in a request
 * 
 * @param array $required Required field names
 * @param array $data Data to validate
 * @return array|null Array of missing fields or null if all required fields are present
 */
function validateRequiredFields($required, $data) {
    $missing = [];
    
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $missing[] = $field;
        }
    }
    
    return empty($missing) ? null : $missing;
}

/**
 * Get request body data
 * 
 * @return array Request data
 */
function getRequestData() {
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Handle different HTTP methods
    switch ($method) {
        case 'GET':
            return $_GET;
        case 'POST':
            // Check if Content-Type is application/json
            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
            
            if (strpos($contentType, 'application/json') !== false) {
                // Get JSON data
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);
                
                // Check for JSON errors
                if (json_last_error() !== JSON_ERROR_NONE) {
                    sendError('Invalid JSON data', json_last_error_msg(), 400);
                }
                
                return $data;
            }
            
            // Handle regular POST data
            return $_POST;
        default:
            // For PUT, DELETE, etc.
            $data = [];
            parse_str(file_get_contents('php://input'), $data);
            return $data;
    }
} 