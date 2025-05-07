<?php
/**
 * ClothLoop Deployment Helper Script
 * 
 * This script helps prepare the codebase for deployment to Vercel (frontend) and Render (backend).
 * Run this script before pushing to your repository for deployment.
 */

echo "ClothLoop Deployment Helper\n";
echo "==========================\n\n";

// Check environment
if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

// Define the Render backend URL
echo "Enter your Render backend URL (e.g., https://clothloop-backend.onrender.com):\n";
$renderUrl = trim(fgets(STDIN));

if (empty($renderUrl) || !filter_var($renderUrl, FILTER_VALIDATE_URL)) {
    echo "Error: Please enter a valid URL.\n";
    exit(1);
}

// Define the Vercel frontend URL
echo "Enter your Vercel frontend URL (e.g., https://clothloop.vercel.app):\n";
$vercelUrl = trim(fgets(STDIN));

if (empty($vercelUrl) || !filter_var($vercelUrl, FILTER_VALIDATE_URL)) {
    echo "Error: Please enter a valid URL.\n";
    exit(1);
}

// Update env.php with the URLs
echo "\nUpdating environment configuration...\n";

$envFile = __DIR__ . '/backend/config/env.php';
if (file_exists($envFile)) {
    $content = file_get_contents($envFile);
    
    // Update the frontend URL
    $content = preg_replace(
        '/\$baseUrl = \$isProduction \? getenv\(\'FRONTEND_URL\'\) \/\/ This will be your Vercel deployment URL/',
        "\$baseUrl = \$isProduction ? '{$vercelUrl}' // Vercel frontend URL",
        $content
    );
    
    // Update the backend URL
    $content = preg_replace(
        '/\$apiUrl = \$isProduction \? getenv\(\'RENDER_EXTERNAL_URL\'\) \/\/ This will be your Render deployment URL/',
        "\$apiUrl = \$isProduction ? '{$renderUrl}' // Render backend URL",
        $content
    );
    
    // Update the uploads URL
    $content = preg_replace(
        '/\$uploadsPath = \$isProduction \? getenv\(\'RENDER_EXTERNAL_URL\'\) \. \'\/uploads\'/',
        "\$uploadsPath = \$isProduction ? '{$renderUrl}/uploads'",
        $content
    );
    
    file_put_contents($envFile, $content);
    echo "Environment configuration updated successfully.\n";
} else {
    echo "Error: Environment configuration file not found.\n";
    exit(1);
}

// Update API URLs in frontend files
echo "\nUpdating API URLs in frontend files...\n";
if (file_exists(__DIR__ . '/backend/api/update_api_urls.php')) {
    include __DIR__ . '/backend/api/update_api_urls.php';
    echo "Frontend files updated with new API URLs.\n";
} else {
    echo "Error: API URL update script not found.\n";
    exit(1);
}

// Create necessary directories
echo "\nCreating necessary directories...\n";
$directories = [
    'backend/uploads',
    'backend/logs'
];

foreach ($directories as $dir) {
    if (!is_dir(__DIR__ . '/' . $dir)) {
        mkdir(__DIR__ . '/' . $dir, 0777, true);
        echo "Created directory: {$dir}\n";
    } else {
        echo "Directory already exists: {$dir}\n";
    }
}

// Add a .gitkeep file to empty directories
foreach ($directories as $dir) {
    $gitkeepFile = __DIR__ . '/' . $dir . '/.gitkeep';
    if (!file_exists($gitkeepFile)) {
        file_put_contents($gitkeepFile, '');
        echo "Added .gitkeep to: {$dir}\n";
    }
}

// Check for required files
echo "\nChecking for required deployment files...\n";
$requiredFiles = [
    'vercel.json',
    'backend/render.yaml',
    'composer.json',
    '.gitignore'
];

$missingFiles = [];
foreach ($requiredFiles as $file) {
    if (!file_exists(__DIR__ . '/' . $file)) {
        $missingFiles[] = $file;
    }
}

if (!empty($missingFiles)) {
    echo "Warning: The following required files are missing:\n";
    foreach ($missingFiles as $file) {
        echo "- {$file}\n";
    }
    echo "Please make sure these files exist before deploying.\n";
} else {
    echo "All required deployment files are present.\n";
}

echo "\nDeployment preparation completed successfully!\n";
echo "==========================\n";
echo "Next steps:\n";
echo "1. Commit and push these changes to your GitHub repository.\n";
echo "2. Deploy the backend to Render using the dashboard.\n";
echo "3. Deploy the frontend to Vercel using the dashboard.\n";
echo "==========================\n"; 