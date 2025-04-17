<?php
// Authentication utility functions

/**
 * Check if user is authenticated
 * @return bool True if user is authenticated, false otherwise
 */
function isAuthenticated() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user_id exists in session
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if authenticated user is a seller
 * @return bool True if user is a seller, false otherwise
 */
function isSeller() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user_type exists in session and is 'seller'
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'seller';
}

/**
 * Check if authenticated user is a buyer
 * @return bool True if user is a buyer, false otherwise
 */
function isBuyer() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user_type exists in session and is 'buyer'
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'buyer';
}

/**
 * Get the user ID from session
 * @return int|null User ID if authenticated, null otherwise
 */
function getUserId() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Return user_id from session if it exists
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Get the user type from session
 * @return string|null User type if authenticated, null otherwise
 */
function getUserType() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Return user_type from session if it exists
    return isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
}

/**
 * Get the user's username from session
 * @return string|null Username if authenticated, null otherwise
 */
function getUsername() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Return username from session if it exists
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}
?> 