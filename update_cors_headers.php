<?php
/**
 * Update CORS Headers
 * 
 * This script updates CORS headers in all PHP files in the API directory to 
 * ensure proper cross-origin access from Vercel to Render.
 */

// Directory containing API files
$apiDir = __DIR__ . '/backend/api';

// Count of updated files
$updatedCount = 0;

// CORS headers to add/replace
$corsHeaders = <<<'EOD'
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

EOD;

/**
 * Process a PHP file to update its CORS headers
 * 
 * @param string $file Path to PHP file
 * @return bool True if file was updated, false otherwise
 */
function updateCorsHeaders($file) {
    global $corsHeaders;
    
    // Read file content
    $content = file_get_contents($file);
    
    // Check if the file has PHP opening tag
    if (strpos($content, '<?php') === false) {
        return false;
    }
    
    // Check if the file already contains our CORS headers
    if (strpos($content, 'Access-Control-Allow-Origin: *') !== false &&
        strpos($content, 'Access-Control-Allow-Methods:') !== false &&
        strpos($content, 'OPTIONS') !== false) {
        // File already has CORS headers, check if they're correct
        if (strpos($content, "header(\"Access-Control-Allow-Origin: *\");") !== false) {
            return false; // Already has correct headers
        }
    }
    
    // Remove existing CORS headers
    $pattern = '/\/\/ (Allow CORS|Set CORS headers).*?exit;/s';
    $content = preg_replace($pattern, '', $content);
    
    // Add our CORS headers after PHP opening tag
    $content = preg_replace('/(<\?php.*?)(\n\s*\/\*|\n\s*\/\/|\n\s*require|\n\s*include)/s', '$1' . "\n" . $corsHeaders . '$2', $content, 1);
    
    // Write updated content back to file
    file_put_contents($file, $content);
    
    return true;
}

/**
 * Recursively scan directory for PHP files
 * 
 * @param string $dir Directory to scan
 */
function scanDirectory($dir) {
    global $updatedCount;
    
    // Get all files in current directory
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            // Recursively scan subdirectories
            scanDirectory($path);
        } else if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            // Update PHP files
            if (updateCorsHeaders($path)) {
                echo "Updated: $path\n";
                $updatedCount++;
            }
        }
    }
}

// Main execution
echo "Updating CORS headers in API files...\n";
scanDirectory($apiDir);
echo "Completed! Updated $updatedCount files.\n"; 