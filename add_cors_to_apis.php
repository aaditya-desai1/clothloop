<?php
/**
 * Add CORS Headers to API Files
 * 
 * This script adds CORS headers to all PHP files in the API directory.
 * Run this script once to update all files.
 */

// Directory to process
$apiDir = __DIR__ . '/backend/api';

// CORS headers to add
$corsHeaders = <<<'EOD'

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Max-Age: 3600");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

EOD;

// Function to scan directory recursively
function scanDirectory($dir, $corsHeaders) {
    $files = glob($dir . '/*');
    $count = 0;
    
    foreach ($files as $file) {
        if (is_dir($file)) {
            $count += scanDirectory($file, $corsHeaders);
        } elseif (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $count += updateFile($file, $corsHeaders);
        }
    }
    
    return $count;
}

// Function to update a file with CORS headers
function updateFile($file, $corsHeaders) {
    $content = file_get_contents($file);
    
    // Skip if the file already has CORS headers
    if (strpos($content, 'Access-Control-Allow-Origin') !== false) {
        return 0;
    }
    
    // Add CORS headers after the opening PHP tag
    $content = preg_replace('/<\?php\s+/', "<?php\n$corsHeaders", $content, 1);
    
    file_put_contents($file, $content);
    echo "Updated: $file\n";
    return 1;
}

// Run the script
echo "Adding CORS headers to PHP files in $apiDir...\n";
$updatedCount = scanDirectory($apiDir, $corsHeaders);
echo "Done! Updated $updatedCount files.\n"; 