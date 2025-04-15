<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to HTML
header('Content-Type: text/html');

echo "<h1>File Upload Test</h1>";

// Check if uploads directory exists, create if not
$uploads_dir = __DIR__ . '/../uploads';
if (!file_exists($uploads_dir)) {
    if (mkdir($uploads_dir, 0777, true)) {
        echo "<p>Created main uploads directory: $uploads_dir</p>";
    } else {
        echo "<p>Failed to create uploads directory! Check permissions.</p>";
    }
} else {
    echo "<p>Main uploads directory exists: $uploads_dir</p>";
}

// Check if uploads/clothes directory exists, create if not
$clothes_dir = $uploads_dir . '/clothes';
if (!file_exists($clothes_dir)) {
    if (mkdir($clothes_dir, 0777, true)) {
        echo "<p>Created clothes uploads directory: $clothes_dir</p>";
    } else {
        echo "<p>Failed to create clothes directory! Check permissions.</p>";
    }
} else {
    echo "<p>Clothes directory exists: $clothes_dir</p>";
}

// Check permissions
echo "<h2>Directory Permissions</h2>";
echo "<p>Main uploads directory permissions: " . substr(sprintf('%o', fileperms($uploads_dir)), -4) . "</p>";
if (file_exists($clothes_dir)) {
    echo "<p>Clothes directory permissions: " . substr(sprintf('%o', fileperms($clothes_dir)), -4) . "</p>";
}

// Check if we can write to the clothes directory
$test_file = $clothes_dir . '/test_file.txt';
if (file_put_contents($test_file, 'This is a test file')) {
    echo "<p>Successfully wrote test file: $test_file</p>";
    // Cleanup
    unlink($test_file);
} else {
    echo "<p>Failed to write test file! Check permissions.</p>";
}

// Test upload form
echo <<<HTML
<h2>Test Upload Form</h2>
<form action="test_upload_process.php" method="post" enctype="multipart/form-data">
    <div>
        <label for="image">Select image to upload:</label>
        <input type="file" name="image" id="image" accept="image/*">
    </div>
    <div style="margin-top: 10px;">
        <button type="submit">Upload Image</button>
    </div>
</form>
HTML;

// Server information
echo "<h2>Server Information</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Max upload file size: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>Max post size: " . ini_get('post_max_size') . "</p>";
echo "<p>Memory limit: " . ini_get('memory_limit') . "</p>";
echo "<p>Temporary directory: " . sys_get_temp_dir() . "</p>";
echo "<p>Current directory: " . __DIR__ . "</p>";

// File upload configuration
echo "<h2>File Upload Configuration</h2>";
echo "<pre>";
print_r([
    'file_uploads' => ini_get('file_uploads'),
    'upload_tmp_dir' => ini_get('upload_tmp_dir'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'post_max_size' => ini_get('post_max_size')
]);
echo "</pre>";
?> 