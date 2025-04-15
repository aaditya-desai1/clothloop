<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to HTML
header('Content-Type: text/html');

echo "<h1>File Upload Process</h1>";

// Function to display upload information
function displayUploadInfo($file) {
    echo "<h2>Upload Information</h2>";
    echo "<pre>";
    print_r($file);
    echo "</pre>";
}

// Define upload directory
$uploads_dir = __DIR__ . '/../uploads/clothes';

// Create directory if not exists
if (!file_exists($uploads_dir)) {
    if (!mkdir($uploads_dir, 0777, true)) {
        echo "<p>Error: Failed to create upload directory</p>";
        exit;
    }
}

// Check if a file was uploaded
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $file = $_FILES['image'];
    
    // Display upload info
    displayUploadInfo($file);
    
    // Generate unique filename
    $filename = uniqid() . '_' . basename($file['name']);
    $target_file = $uploads_dir . '/' . $filename;
    
    // Attempt to move the uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        echo "<p>File was successfully uploaded to: $target_file</p>";
        
        // Display the image
        $web_path = '../uploads/clothes/' . $filename;
        echo "<p>Image preview:</p>";
        echo "<img src='$web_path' style='max-width: 300px;'>";
        
        // Insert into database test
        echo "<h2>Database Test</h2>";
        echo "<p>Simulating database insertion...</p>";
        
        // Connect to database
        $db_host = "localhost";
        $db_user = "root";
        $db_password = "";
        $db_name = "clothloop";
        
        try {
            $conn = new mysqli($db_host, $db_user, $db_password, $db_name);
            
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }
            
            // Check if cloth_details table exists
            $table_check = $conn->query("SHOW TABLES LIKE 'cloth_details'");
            if ($table_check->num_rows == 0) {
                echo "<p>The cloth_details table does not exist. Creating it now...</p>";
                
                // Create the table
                $create_sql = "CREATE TABLE IF NOT EXISTS cloth_details (
                    id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    seller_id INT(11) NOT NULL,
                    cloth_title VARCHAR(255) NOT NULL,
                    description TEXT,
                    size VARCHAR(50),
                    category VARCHAR(100) DEFAULT 'General',
                    rental_price DECIMAL(10,2) NOT NULL,
                    contact_number VARCHAR(20),
                    whatsapp_number VARCHAR(20),
                    terms_and_conditions TEXT,
                    status VARCHAR(20) DEFAULT 'active',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                
                if ($conn->query($create_sql)) {
                    echo "<p>cloth_details table created successfully</p>";
                } else {
                    throw new Exception("Error creating cloth_details table: " . $conn->error);
                }
            }
            
            // Check if cloth_images table exists
            $table_check = $conn->query("SHOW TABLES LIKE 'cloth_images'");
            if ($table_check->num_rows == 0) {
                echo "<p>The cloth_images table does not exist. Creating it now...</p>";
                
                // Create the table
                $create_sql = "CREATE TABLE IF NOT EXISTS cloth_images (
                    id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    cloth_id INT(11) NOT NULL,
                    image_data MEDIUMBLOB,
                    image_type VARCHAR(20),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                
                if ($conn->query($create_sql)) {
                    echo "<p>cloth_images table created successfully</p>";
                } else {
                    throw new Exception("Error creating cloth_images table: " . $conn->error);
                }
            }
            
            // Add a test product
            $sql = "INSERT INTO cloth_details (
                seller_id, 
                cloth_title, 
                description, 
                size, 
                category, 
                rental_price, 
                contact_number, 
                whatsapp_number, 
                terms_and_conditions, 
                status
            ) VALUES (1, 'Test Product', 'Test Description', 'M', 'Casual', 299.99, '1234567890', '1234567890', 'Test Terms', 'active')";
            
            if ($conn->query($sql)) {
                $cloth_id = $conn->insert_id;
                echo "<p>Test product added with ID: $cloth_id</p>";
                
                // Read the image file
                $image_data = file_get_contents($target_file);
                $image_type = pathinfo($file['name'], PATHINFO_EXTENSION);
                
                // Insert image
                $stmt = $conn->prepare("INSERT INTO cloth_images (cloth_id, image_data, image_type) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $cloth_id, $image_data, $image_type);
                
                if ($stmt->execute()) {
                    echo "<p>Image added successfully to database</p>";
                } else {
                    echo "<p>Error adding image to database: " . $stmt->error . "</p>";
                }
                
                // Check if image was inserted correctly
                $result = $conn->query("SELECT * FROM cloth_images WHERE cloth_id = $cloth_id");
                if ($result->num_rows > 0) {
                    echo "<p>Verified: Image record exists in database</p>";
                } else {
                    echo "<p>Warning: Image record not found in database</p>";
                }
            } else {
                echo "<p>Error adding test product: " . $conn->error . "</p>";
            }
            
            $conn->close();
            
        } catch (Exception $e) {
            echo "<p>Database error: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p>Error: There was a problem uploading the file</p>";
        echo "<p>Error details: " . error_get_last()['message'] . "</p>";
    }
} else {
    echo "<p>No file was uploaded or an error occurred</p>";
    
    if (isset($_FILES['image'])) {
        $error_code = $_FILES['image']['error'];
        $error_messages = [
            1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            3 => 'The uploaded file was only partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk',
            8 => 'A PHP extension stopped the file upload'
        ];
        
        $error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Unknown error';
        echo "<p>Error code: $error_code - $error_message</p>";
    }
}

// Back link
echo "<p><a href='test_upload.php'>Go back to test form</a></p>";
?> 