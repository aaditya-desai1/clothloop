<?php
// Session configuration
ini_set('session.cookie_httponly', 0);
ini_set('session.cookie_secure', 0);
ini_set('session.use_only_cookies', 0);
ini_set('session.cookie_samesite', 'None');
ini_set('session.gc_maxlifetime', 86400); // 24 hours
ini_set('session.cookie_lifetime', 86400); // 24 hours

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?> 