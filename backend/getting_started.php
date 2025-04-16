<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to check if a table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Database connection parameters
$db_host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "clothloop";

// Connect to database
try {
    $conn = new mysqli($db_host, $db_user, $db_password, $db_name);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Check database status
    $clothDetailsExists = tableExists($conn, 'cloth_details');
    $usersExists = tableExists($conn, 'users');
    $clothImagesExists = tableExists($conn, 'cloth_images');
    
    // Count products
    $productCount = 0;
    if ($clothDetailsExists) {
        $result = $conn->query("SELECT COUNT(*) as count FROM cloth_details");
        if ($result) {
            $row = $result->fetch_assoc();
            $productCount = $row['count'];
        }
    }
    
    $conn->close();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClothLoop - Getting Started</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            color: #2980b9;
            margin-top: 30px;
        }
        .status-box {
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin: 20px 0;
        }
        .error {
            border-left-color: #e74c3c;
        }
        .success {
            border-left-color: #2ecc71;
        }
        .warning {
            border-left-color: #f39c12;
        }
        .step {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background-color: #3498db;
            color: white;
            text-align: center;
            line-height: 30px;
            border-radius: 50%;
            margin-right: 10px;
        }
        code {
            background-color: #f1f1f1;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: Consolas, monospace;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <h1>ClothLoop - Getting Started</h1>
    
    <div class="status-box <?php echo isset($error) ? 'error' : 'success'; ?>">
        <h3>System Status</h3>
        <?php if (isset($error)): ?>
            <p>Error connecting to database: <?php echo $error; ?></p>
            <p>Please make sure your database server is running and the 'clothloop' database exists.</p>
        <?php else: ?>
            <p>Database connection: <strong>Working</strong></p>
            <p>Required tables:</p>
            <ul>
                <li>cloth_details: <?php echo $clothDetailsExists ? '✅ Exists' : '❌ Missing'; ?></li>
                <li>users: <?php echo $usersExists ? '✅ Exists' : '❌ Missing'; ?></li>
                <li>cloth_images: <?php echo $clothImagesExists ? '✅ Exists' : '❌ Missing'; ?></li>
            </ul>
            <p>Total products in database: <?php echo $productCount; ?></p>
        <?php endif; ?>
    </div>
    
    <h2>Getting Started</h2>
    
    <div class="step">
        <span class="step-number">1</span>
        <h3>Initialize Database</h3>
        <p>Set up the database with required tables:</p>
        <p><a href="setup_database.php" class="btn">Initialize Database</a></p>
    </div>
    
    <div class="step">
        <span class="step-number">2</span>
        <h3>Check Database Connection</h3>
        <p>Verify that database connection and structure are correct:</p>
        <p><a href="check_database.php" class="btn">Check Database</a></p>
    </div>
    
    <div class="step">
        <span class="step-number">3</span>
        <h3>Access the Buyer Dashboard</h3>
        <p>View all cloth listings as a buyer:</p>
        <p><a href="../frontend/pages/buyer/buyer_dashboard.html" class="btn">Buyer Dashboard</a></p>
    </div>
    
    <div class="step">
        <span class="step-number">4</span>
        <h3>Upload a New Cloth Item</h3>
        <p>Add a new cloth item as a seller:</p>
        <p><a href="../frontend/pages/seller/cloth_upload.html" class="btn">Add Cloth Item</a></p>
    </div>
    
    <h2>Troubleshooting</h2>
    
    <div class="status-box warning">
        <h3>Common Issues</h3>
        <ul>
            <li><strong>No products showing:</strong> Make sure you've initialized the database in step 1.</li>
            <li><strong>Can't upload images:</strong> Check file permissions on the uploads directory.</li>
            <li><strong>SQL errors:</strong> The database schema might need updating. Try step 1 again.</li>
        </ul>
    </div>
    
    <h2>Additional Resources</h2>
    
    <ul>
        <li><strong>Database Check:</strong> <a href="check_database.php">Check Database Status</a></li>
        <li><strong>Source Code:</strong> <a href="https://github.com/your-repo/clothloop" target="_blank">GitHub Repository</a></li>
    </ul>
</body>
</html> 