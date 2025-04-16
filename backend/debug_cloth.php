<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once './config/db_connect.php';

// Helper function to print data in readable format
function dump($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}

// Get cloth ID from URL parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 1;

echo "<h1>Cloth Item Diagnostic for ID: $id</h1>";

// Get cloth data from database - Fix the table name issue
$sql = "SELECT c.* FROM cloth_details c WHERE c.id = $id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    echo "<div style='display: flex; flex-wrap: wrap;'>";
    
    // Left column - Image
    echo "<div style='flex: 1; min-width: 300px; margin-right: 20px;'>";
    echo "<h2>Cloth Image</h2>";
    
    // Display image if we have photo data
    if (!empty($row['cloth_photo']) && $row['photo_type']) {
        $timestamp = time();
        echo "<img src='./api/sellers/get_cloth_image.php?id=$id&t=$timestamp' style='max-width: 300px; border: 1px solid #ddd;' alt='Cloth Image'>";
        echo "<p><a href='debug_image.php?id=$id'>Debug this image</a></p>";
    } else {
        echo "<p>No image available</p>";
    }
    echo "</div>";
    
    // Right column - Data
    echo "<div style='flex: 2; min-width: 300px;'>";
    echo "<h2>Cloth Data</h2>";
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    
    // Display all cloth fields except the binary data
    foreach ($row as $key => $value) {
        if ($key != 'cloth_photo' && $key != 'photo_type') {
            echo "<tr>";
            echo "<th style='text-align: left; background: #f2f2f2;'>" . htmlspecialchars($key) . "</th>";
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            echo "</tr>";
        }
    }
    
    // Add photo information
    echo "<tr>";
    echo "<th style='text-align: left; background: #f2f2f2;'>photo_type</th>";
    echo "<td>" . htmlspecialchars($row['photo_type'] ?? 'NULL') . "</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<th style='text-align: left; background: #f2f2f2;'>photo_size</th>";
    echo "<td>" . (empty($row['cloth_photo']) ? '0' : strlen($row['cloth_photo'])) . " bytes</td>";
    echo "</tr>";
    
    echo "</table>";
    echo "</div>";
    echo "</div>";
    
    // Database relations
    echo "<h2>Related Data</h2>";
    
    // Check if the cloth_reviews table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'cloth_reviews'");
    if ($table_check && $table_check->num_rows > 0) {
        // Table exists, get ratings
        $ratings_sql = "SELECT * FROM cloth_reviews WHERE cloth_id = $id";
        $ratings_result = $conn->query($ratings_sql);
        echo "<h3>Reviews (" . ($ratings_result ? $ratings_result->num_rows : 0) . ")</h3>";
        
        if ($ratings_result && $ratings_result->num_rows > 0) {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr style='background: #f2f2f2;'><th>ID</th><th>User ID</th><th>Rating</th><th>Comment</th><th>Date</th></tr>";
            
            while ($review = $ratings_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($review['id']) . "</td>";
                echo "<td>" . htmlspecialchars($review['user_id']) . "</td>";
                echo "<td>" . htmlspecialchars($review['rating']) . "</td>";
                echo "<td>" . htmlspecialchars($review['comment']) . "</td>";
                echo "<td>" . htmlspecialchars($review['date']) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>No reviews found</p>";
        }
    } else {
        echo "<h3>Reviews</h3>";
        echo "<p>The reviews table does not exist in the database.</p>";
        echo "<p>To add reviews functionality, you need to create the cloth_reviews table with the following structure:</p>";
        echo "<pre>
CREATE TABLE `cloth_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cloth_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cloth_id` (`cloth_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
</pre>";
    }
    
} else {
    echo "<p style='color: red;'>No cloth item found with ID $id</p>";
}

echo "<h2>Quick Links</h2>";
echo "<ul>";
echo "<li><a href='debug_image.php?id=$id'>Debug image for this cloth item</a></li>";

// Get next and previous IDs
$prev_sql = "SELECT id FROM cloth_details WHERE id < $id ORDER BY id DESC LIMIT 1";
$prev_result = $conn->query($prev_sql);
$prev_id = ($prev_result && $prev_result->num_rows > 0) ? $prev_result->fetch_assoc()['id'] : null;

$next_sql = "SELECT id FROM cloth_details WHERE id > $id ORDER BY id ASC LIMIT 1";
$next_result = $conn->query($next_sql);
$next_id = ($next_result && $next_result->num_rows > 0) ? $next_result->fetch_assoc()['id'] : null;

if ($prev_id) {
    echo "<li><a href='debug_cloth.php?id=$prev_id'>Previous cloth (ID: $prev_id)</a></li>";
}
if ($next_id) {
    echo "<li><a href='debug_cloth.php?id=$next_id'>Next cloth (ID: $next_id)</a></li>";
}

echo "<li><a href='debug_cloth.php?id=" . rand(1, 20) . "'>Random cloth item</a></li>";
echo "</ul>";

$conn->close();
?> 