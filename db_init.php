<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = "";

// Connect to MySQL without selecting a database
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS clothloop";
if ($conn->query($sql) === TRUE) {
    echo "Database created or already exists successfully<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db("clothloop");

// Create buyers table
$sql = "CREATE TABLE IF NOT EXISTS buyers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone_no VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Buyers table created or already exists successfully<br>";
} else {
    die("Error creating buyers table: " . $conn->error);
}

// Create sellers table
$sql = "CREATE TABLE IF NOT EXISTS sellers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone_no VARCHAR(20) NOT NULL,
    shop_name VARCHAR(100) NOT NULL,
    shop_address TEXT NOT NULL,
    shop_location VARCHAR(100) NOT NULL,
    shop_logo VARCHAR(255) DEFAULT NULL,
    shop_bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Sellers table created or already exists successfully<br>";
} else {
    die("Error creating sellers table: " . $conn->error);
}

// Create directory for shop logos if it doesn't exist
$uploadDir = 'frontend/assets/images/shop_logos/';
if (!file_exists($uploadDir)) {
    if (mkdir($uploadDir, 0777, true)) {
        echo "Created directory for shop logos<br>";
    } else {
        echo "Failed to create directory for shop logos<br>";
    }
}

echo "<p>Database initialization completed successfully! You can now register users and use the ClothLoop platform.</p>";
echo "<p><a href='home.html'>Go to homepage</a></p>";

// Close connection
$conn->close();
?> 