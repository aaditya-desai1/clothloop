<?php
// Include database connection
require_once 'backend/config/db_connect.php';

// Check the structure of the reviews table
$result = mysqli_query($conn, "DESCRIBE reviews");

if (!$result) {
    die("Error checking table structure: " . mysqli_error($conn));
}

echo "<h2>Reviews Table Structure</h2>";
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "<td>{$row['Extra']}</td>";
    echo "</tr>";
}

echo "</table>";

// Close the connection
mysqli_close($conn);
?> 