<?php
/**
 * Update API URLs Script
 * 
 * This script updates all frontend files to replace local API URLs with production URLs.
 * Run this script after deploying the backend to Render and before deploying the frontend to Vercel.
 */

// Include environment configuration
require_once __DIR__ . '/../config/env.php';

// Define the local and production API URLs
$localApiUrl = 'http://localhost/ClothLoop/backend/api';
$productionApiUrl = API_URL;

// Define the frontend directory
$frontendDir = __DIR__ . '/../../frontend';

// Define the file extensions to update
$fileExtensions = ['html', 'js'];

// Function to update URLs in files
function updateUrls($dir, $localUrl, $productionUrl, $extensions) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    $updated = 0;
    
    foreach ($files as $file) {
        // Check if the file has one of the extensions we want to update
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        if (in_array($extension, $extensions)) {
            $filePath = $file->getRealPath();
            $content = file_get_contents($filePath);
            
            // Replace local API URL with production URL
            $newContent = str_replace($localUrl, $productionUrl, $content);
            
            // Update local file upload URLs
            $newContent = str_replace(
                'http://localhost/ClothLoop/backend/uploads', 
                UPLOADS_URL, 
                $newContent
            );
            
            // If changes were made, write the file back
            if ($content !== $newContent) {
                file_put_contents($filePath, $newContent);
                $updated++;
                echo "Updated: " . str_replace($dir, '', $filePath) . PHP_EOL;
            }
        }
    }
    
    return $updated;
}

// Run the update
echo "Updating API URLs in frontend files..." . PHP_EOL;
echo "Local API URL: " . $localApiUrl . PHP_EOL;
echo "Production API URL: " . $productionApiUrl . PHP_EOL;
echo "Frontend directory: " . $frontendDir . PHP_EOL;

$updatedFiles = updateUrls($frontendDir, $localApiUrl, $productionApiUrl, $fileExtensions);

echo "Completed! Updated " . $updatedFiles . " files." . PHP_EOL;

// Also update the home.html file in the root directory
$homeFile = __DIR__ . '/../../home.html';
if (file_exists($homeFile)) {
    $content = file_get_contents($homeFile);
    $newContent = str_replace($localApiUrl, $productionApiUrl, $content);
    $newContent = str_replace(
        'http://localhost/ClothLoop/backend/uploads', 
        UPLOADS_URL, 
        $newContent
    );
    
    if ($content !== $newContent) {
        file_put_contents($homeFile, $newContent);
        echo "Updated: home.html" . PHP_EOL;
        $updatedFiles++;
    }
}

echo "Total updated files: " . $updatedFiles . PHP_EOL; 