<?php
// Set error reporting to show all errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
include_once 'config/Database.php';

// Connect to the database
$database = new Database();
$db = $database->getConnection();

// Get seller ID from query string, default to 1
$seller_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Function to output data in a readable format
function outputData($title, $data) {
    echo "<h3>$title</h3>";
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    echo "<hr>";
}

// Get data from the users table
$userQuery = "SELECT * FROM users WHERE id = :id";
$userStmt = $db->prepare($userQuery);
$userStmt->bindParam(':id', $seller_id);
$userStmt->execute();
$userData = $userStmt->fetch(PDO::FETCH_ASSOC);

// Get data from the sellers table
$sellerQuery = "SELECT * FROM sellers WHERE id = :id";
$sellerStmt = $db->prepare($sellerQuery);
$sellerStmt->bindParam(':id', $seller_id);
$sellerStmt->execute();
$sellerData = $sellerStmt->fetch(PDO::FETCH_ASSOC);

// Output the data
?>
<!DOCTYPE html>
<html>
<head>
    <title>Seller Data Check</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        h1, h3 {
            color: #333;
        }
        pre {
            background-color: #eee;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow-x: auto;
        }
        hr {
            margin: 20px 0;
            border: 0;
            border-top: 1px solid #ddd;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Seller Data Check (ID: <?php echo $seller_id; ?>)</h1>
        
        <?php if (!$userData): ?>
            <p>No user data found for ID <?php echo $seller_id; ?></p>
        <?php else: ?>
            <?php outputData("User Data (from users table)", $userData); ?>
        <?php endif; ?>
        
        <?php if (!$sellerData): ?>
            <p>No seller data found for ID <?php echo $seller_id; ?></p>
        <?php else: ?>
            <?php outputData("Seller Data (from sellers table)", $sellerData); ?>
        <?php endif; ?>
        
        <h3>Test URLs</h3>
        <ul>
            <li><a href="api/sellers/get_seller_profile.php?id=<?php echo $seller_id; ?>" target="_blank">View API Response</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?id=1">Check Seller ID 1</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?id=2">Check Seller ID 2</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?id=3">Check Seller ID 3</a></li>
        </ul>
    </div>
</body>
</html> 