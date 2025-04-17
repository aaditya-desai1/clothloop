<?php
// Start session
session_start();

// Set headers for easier viewing in browser
header('Content-Type: text/plain');

// Output session information
echo "Session Test Script\n";
echo "=================\n\n";

echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n\n";

// If there's existing session data, display it
if (!empty($_SESSION)) {
    echo "Current Session Data:\n";
    print_r($_SESSION);
    echo "\n";
} else {
    echo "No existing session data found.\n\n";
    
    // Create some test session data
    $_SESSION['test_value'] = 'This is a test value';
    $_SESSION['test_time'] = date('Y-m-d H:i:s');
    
    echo "Created new session data. Refresh this page to see if it persists.\n";
    echo "New session data:\n";
    print_r($_SESSION);
}

// Display PHP session configuration
echo "\nPHP Session Configuration:\n";
echo "session.save_path: " . ini_get('session.save_path') . "\n";
echo "session.name: " . ini_get('session.name') . "\n";
echo "session.cookie_path: " . ini_get('session.cookie_path') . "\n";
echo "session.cookie_domain: " . ini_get('session.cookie_domain') . "\n";
echo "session.cookie_secure: " . ini_get('session.cookie_secure') . "\n";
echo "session.cookie_httponly: " . ini_get('session.cookie_httponly') . "\n";
echo "session.use_cookies: " . ini_get('session.use_cookies') . "\n";
echo "session.use_only_cookies: " . ini_get('session.use_only_cookies') . "\n";

// Print all cookies
echo "\nCurrent Cookies:\n";
if (!empty($_COOKIE)) {
    print_r($_COOKIE);
} else {
    echo "No cookies found.\n";
}
?> 