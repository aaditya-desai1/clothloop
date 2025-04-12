<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$dbname = "your_database_name";
$username = "your_username";
$password = "your_password";

try {
    // Test database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection successful!<br>";

    // Show all users in the database
    $stmt = $pdo->query("SELECT id, username, password FROM users");
    echo "<pre>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?> 