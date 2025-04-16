<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once './config/db_connect.php';

// Helper function to print data in readable format
function dump($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}

// Get cloth ID from URL parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 1;

echo "<h1>Image Diagnostic for ID: $id</h1>";

// Get image data from database
$sql = "SELECT cloth_title, cloth_photo, photo_type, LENGTH(cloth_photo) as size FROM cloth_details WHERE id = $id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    echo "<h2>Record Found: {$row['cloth_title']}</h2>";
    echo "<p>Photo Type: " . ($row['photo_type'] ? htmlspecialchars($row['photo_type']) : 'Not set') . "</p>";
    echo "<p>Data Size: " . ($row['size'] ? $row['size'] . " bytes" : 'Empty') . "</p>";
    
    // Check if data exists
    if (!empty($row['cloth_photo']) && $row['size'] > 100) {
        echo "<p style='color: green;'>✓ Image data exists and seems valid</p>";
        
        // Display base64 version
        $base64 = base64_encode($row['cloth_photo']);
        $src = "data:" . $row['photo_type'] . ";base64," . $base64;
        echo "<h3>Base64 Display Test:</h3>";
        echo "<img src='$src' style='max-width: 300px; border: 1px solid #ddd;' alt='Base64 encoded image'>";
        echo "<p>If you can see the image above, the data is valid but there might be an issue with the API.</p>";
        
        // Link to direct image script with timestamp
        $timestamp = time();
        echo "<h3>Direct API Test:</h3>";
        echo "<img src='./api/sellers/get_cloth_image.php?id=$id&t=$timestamp' style='max-width: 300px; border: 1px solid #ddd;' alt='Direct API image'>";
        
        echo "<p>Direct API URL: <a href='./api/sellers/get_cloth_image.php?id=$id&t=$timestamp' target='_blank'>./api/sellers/get_cloth_image.php?id=$id&t=$timestamp</a></p>";
        
        // Show first bytes of binary data
        if (!empty($row['cloth_photo'])) {
            echo "<h3>First 30 bytes of binary data (hex):</h3>";
            $bytes = bin2hex(substr($row['cloth_photo'], 0, 30));
            echo "<code>$bytes</code>";
            
            // Check for JPEG signature
            if (substr($bytes, 0, 4) === 'ffd8') {
                echo "<p style='color: green;'>✓ Valid JPEG signature detected</p>";
            } else {
                echo "<p style='color: orange;'>⚠ No JPEG signature detected - may not be a valid JPEG</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>✗ No valid image data found</p>";
    }
} else {
    echo "<p style='color: red;'>✗ No record found with ID $id</p>";
}

echo "<h2>Database Information</h2>";
echo "<p>Connected to: " . $conn->host_info . "</p>";

// Table structure
echo "<h3>Table Structure:</h3>";
$result = $conn->query("DESCRIBE cloth_details");
if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h2>Server Information</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>MySQL Version: " . $conn->server_info . "</p>";
echo "<p>GD Installed: " . (extension_loaded('gd') ? 'Yes' : 'No') . "</p>";

// Additional tips
echo "<h2>Troubleshooting Tips</h2>";
echo "<ul>";
echo "<li>If the Base64 test shows an image but Direct API doesn't, there's an issue with the API script.</li>";
echo "<li>If neither test shows an image, the data might not be a valid image.</li>";
echo "<li>Try clearing your browser cache or using incognito mode.</li>";
echo "<li>Check network tab in browser dev tools for any errors.</li>";
echo "</ul>";
?> 