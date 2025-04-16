<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters (without database name)
$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty

// Create connection without database
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>ClothLoop Database Setup</h1>";

// Create database if it doesn't exist
$dbname = "clothloop";
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";

if ($conn->query($sql) === TRUE) {
    echo "<p>Database '$dbname' created successfully or already exists.</p>";
} else {
    echo "<p>Error creating database: " . $conn->error . "</p>";
    exit;
}

// Select the database
$conn->select_db($dbname);

// Create cloth_details table
$sql = "CREATE TABLE IF NOT EXISTS cloth_details (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    seller_id INT(11) NOT NULL DEFAULT 1,
    cloth_title VARCHAR(255) NOT NULL,
    description TEXT,
    size VARCHAR(50),
    category VARCHAR(100),
    rental_price DECIMAL(10,2) NOT NULL,
    contact_number VARCHAR(20),
    whatsapp_number VARCHAR(20),
    terms_and_conditions TEXT,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "<p>Table 'cloth_details' created successfully or already exists.</p>";
} else {
    echo "<p>Error creating table: " . $conn->error . "</p>";
}

// Create cloth_images table
$sql = "CREATE TABLE IF NOT EXISTS cloth_images (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    cloth_id INT(11) NOT NULL,
    image_data MEDIUMBLOB,
    image_type VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "<p>Table 'cloth_images' created successfully or already exists.</p>";
} else {
    echo "<p>Error creating table: " . $conn->error . "</p>";
}

// Check if the tables have data
$result = $conn->query("SELECT COUNT(*) as count FROM cloth_details");
$row = $result->fetch_assoc();
$count = $row['count'];

echo "<p>Found $count products in the cloth_details table.</p>";

if ($count === 0) {
    echo "<p><a href='setup_sample_data.php'>Click here to add sample data</a></p>";
} else {
    echo "<p>There are already products in the database. You can view them in the buyer dashboard.</p>";
}

echo "<p><a href='../frontend/pages/buyer/buyer_dashboard.html'>Go to Buyer Dashboard</a></p>";
echo "<p><a href='utils/check_database.php'>Check Database Status</a></p>";

// Close connection
$conn->close();
?> 