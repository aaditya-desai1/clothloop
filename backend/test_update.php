<?php
// Set error reporting to show all errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get seller ID from URL parameter
$seller_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prepare data as JSON
    $data = [
        'seller_id' => $seller_id,
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'phone_no' => $_POST['phone_no'],
        'shop_name' => $_POST['shop_name'],
        'description' => $_POST['description']
    ];
    
    // Add location if provided
    if (!empty($_POST['latitude']) && !empty($_POST['longitude'])) {
        $data['latitude'] = $_POST['latitude'];
        $data['longitude'] = $_POST['longitude'];
    }
    
    // Convert to JSON
    $jsonData = json_encode($data);
    
    // Send to API using cURL
    $ch = curl_init('http://localhost/ClothLoop/backend/api/sellers/update_seller_profile.php');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ]);
    
    // Execute request and get response
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Store the result for display
    $apiResponse = [
        'status_code' => $httpCode,
        'response' => json_decode($result, true) ?: $result
    ];
}

// Get seller data to pre-populate form
$getData = file_get_contents("http://localhost/ClothLoop/backend/api/sellers/get_seller_profile.php?id=$seller_id");
$sellerData = json_decode($getData, true);

// Extract data for the form
$data = [];
if ($sellerData && isset($sellerData['data'])) {
    $data = $sellerData['data'];
}

// After getting seller data
// Get database column details
$database = new Database();
$db = $database->getConnection();

// Get users table structure
$usersStructureQuery = "DESCRIBE users";
$usersStmt = $db->prepare($usersStructureQuery);
$usersStmt->execute();
$usersStructure = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

// Get sellers table structure
$sellersStructureQuery = "DESCRIBE sellers";
$sellersStmt = $db->prepare($sellersStructureQuery);
$sellersStmt->execute();
$sellersStructure = $sellersStmt->fetchAll(PDO::FETCH_ASSOC);

// Add the structures to the page display
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Update API</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        h1, h2 {
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        pre {
            background-color: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow-x: auto;
        }
        .response {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test Update Seller Profile API</h1>
        <p>This form sends direct JSON POST requests to the update API.</p>
        
        <?php if (isset($apiResponse)): ?>
            <div class="response <?php echo $apiResponse['status_code'] === 200 ? 'success' : 'error'; ?>">
                <h3>API Response (Status Code: <?php echo $apiResponse['status_code']; ?>)</h3>
                <pre><?php print_r($apiResponse['response']); ?></pre>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <h2>Personal Information</h2>
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($data['name'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="phone_no">Phone Number:</label>
                <input type="tel" id="phone_no" name="phone_no" value="<?php echo htmlspecialchars($data['phone'] ?? ''); ?>">
            </div>
            
            <h2>Shop Information</h2>
            <div class="form-group">
                <label for="shop_name">Shop Name:</label>
                <input type="text" id="shop_name" name="shop_name" value="<?php echo htmlspecialchars($data['shop_name'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="description">Shop Description:</label>
                <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($data['description'] ?? ''); ?></textarea>
            </div>
            
            <h2>Location</h2>
            <div class="form-group">
                <label for="latitude">Latitude:</label>
                <input type="text" id="latitude" name="latitude" value="<?php echo htmlspecialchars($data['latitude'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="longitude">Longitude:</label>
                <input type="text" id="longitude" name="longitude" value="<?php echo htmlspecialchars($data['longitude'] ?? ''); ?>">
            </div>
            
            <button type="submit">Update Profile</button>
        </form>
        
        <h2>Raw Data from API</h2>
        <pre><?php print_r($sellerData); ?></pre>
        
        <h2>Database Structure</h2>
        <h3>Users Table</h3>
        <pre><?php print_r($usersStructure); ?></pre>
        
        <h3>Sellers Table</h3>
        <pre><?php print_r($sellersStructure); ?></pre>
    </div>
</body>
</html> 