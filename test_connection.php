<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clothloop";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "Connected successfully to database!";
} catch (Exception $e) {
    die("Connection error: " . $e->getMessage());
}
?> 