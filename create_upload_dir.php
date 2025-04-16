<?php
// Create upload directories for shop logos
$uploadDir = 'uploads/shop_logos/';
if (!is_dir($uploadDir)) {
    if (mkdir($uploadDir, 0755, true)) {
        echo "Upload directory created successfully: $uploadDir\n";
    } else {
        echo "Failed to create upload directory: $uploadDir\n";
    }
} else {
    echo "Upload directory already exists: $uploadDir\n";
}

// Set proper permissions
if (is_dir($uploadDir)) {
    if (chmod($uploadDir, 0755)) {
        echo "Permissions set to 755 for $uploadDir\n";
    } else {
        echo "Failed to set permissions for $uploadDir\n";
    }
}

echo "Done!\n";
?> 