<?php
/**
 * Backend Index
 * Main entry point for the ClothLoop backend API
 */

// Set content type to HTML
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClothLoop API Backend</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1, h2 {
            color: #2c3e50;
        }
        .card {
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .info {
            background-color: #d1ecf1;
            border-left: 4px solid #0c5460;
            padding: 12px;
            margin-bottom: 20px;
        }
        code {
            background-color: #eaeaea;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <h1>ClothLoop API Backend</h1>
    
    <div class="info">
        <p>This is the backend API for the ClothLoop clothing rental platform. The frontend is hosted on Vercel, and this backend is hosted on Render with a PostgreSQL database.</p>
    </div>
    
    <div class="card">
        <h2>Testing Tools</h2>
        <p>Use these tools to test and debug the backend API:</p>
        
        <a href="api/test/test_endpoints.php" class="btn">Test All Endpoints</a>
        <a href="api/cors.php" class="btn">CORS Check</a>
        <a href="api/setup/create_tables.php" class="btn">Setup Database Tables</a>
        
        <p>API Documentation:</p>
        <a href="https://github.com/yourusername/ClothLoop" class="btn">GitHub Repository</a>
    </div>
    
    <div class="card">
        <h2>Environment Information</h2>
        <?php
        // Display PHP version
        echo '<p><strong>PHP Version:</strong> ' . phpversion() . '</p>';
        
        // Check if PostgreSQL extension is loaded
        echo '<p><strong>PostgreSQL Support:</strong> ';
        echo extension_loaded('pgsql') ? 'Enabled' : 'Disabled';
        echo '</p>';
        
        // Check if MySQL extension is loaded
        echo '<p><strong>MySQL Support:</strong> ';
        echo extension_loaded('mysqli') ? 'Enabled' : 'Disabled';
        echo '</p>';
        
        // Check if running on Render
        echo '<p><strong>Running on Render:</strong> ';
        echo getenv('RENDER') === 'true' ? 'Yes' : 'No';
        echo '</p>';
        
        // Show database host (mask credentials)
        if (getenv('DB_HOST')) {
            echo '<p><strong>Database Host:</strong> ';
            $host = getenv('DB_HOST');
            // Only show first few characters for security
            echo substr($host, 0, 5) . '...' . substr($host, -5);
            echo '</p>';
        }
        ?>
    </div>
    
    <div class="card">
        <h2>Common Issues</h2>
        <ul>
            <li><strong>CORS Issues:</strong> If experiencing CORS problems, check that all API endpoints include the CORS headers and are using the <code>cors.php</code> utility.</li>
            <li><strong>Database Connection:</strong> If database connection fails, check the environment variables on Render and run the setup tables endpoint.</li>
            <li><strong>Missing Tables:</strong> Use the "Setup Database Tables" link above to create all necessary tables.</li>
        </ul>
    </div>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> ClothLoop. All rights reserved.</p>
    </footer>
</body>
</html> 