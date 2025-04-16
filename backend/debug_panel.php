<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once './config/db_connect.php';

// Get latest cloth and image IDs
$latest_cloth_sql = "SELECT MAX(id) as max_id FROM cloth_details";
$latest_cloth_result = $conn->query($latest_cloth_sql);
$latest_cloth_id = ($latest_cloth_result && $latest_cloth_result->num_rows > 0) 
    ? $latest_cloth_result->fetch_assoc()['max_id'] 
    : 0;

// Get total counts
$cloth_count_sql = "SELECT COUNT(*) as total FROM cloth_details";
$cloth_count_result = $conn->query($cloth_count_sql);
$cloth_count = ($cloth_count_result && $cloth_count_result->num_rows > 0) 
    ? $cloth_count_result->fetch_assoc()['total'] 
    : 0;

// Get counts of cloth items with and without images
$image_stats_sql = "SELECT 
    SUM(CASE WHEN cloth_photo IS NOT NULL AND LENGTH(cloth_photo) > 100 THEN 1 ELSE 0 END) as with_images,
    SUM(CASE WHEN cloth_photo IS NULL OR LENGTH(cloth_photo) <= 100 THEN 1 ELSE 0 END) as without_images
    FROM cloth_details";
$image_stats_result = $conn->query($image_stats_sql);
$image_stats = ($image_stats_result && $image_stats_result->num_rows > 0) 
    ? $image_stats_result->fetch_assoc() 
    : ['with_images' => 0, 'without_images' => 0];

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClothLoop Debug Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        .stats-panel {
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 15px;
            margin-bottom: 20px;
        }
        .debug-tools {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .tool-card {
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            flex: 1;
            min-width: 300px;
        }
        .tool-card h2 {
            margin-top: 0;
            color: #2c3e50;
        }
        .button {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 8px;
            margin-bottom: 8px;
        }
        .button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ClothLoop Debug Panel</h1>
        
        <div class="stats-panel">
            <h2>System Statistics</h2>
            <p><strong>Total Cloth Items:</strong> <?php echo $cloth_count; ?></p>
            <p><strong>Items with Images:</strong> <?php echo $image_stats['with_images']; ?></p>
            <p><strong>Items without Images:</strong> <?php echo $image_stats['without_images']; ?></p>
            <p><strong>Latest Cloth ID:</strong> <?php echo $latest_cloth_id; ?></p>
        </div>
        
        <div class="debug-tools">
            <div class="tool-card">
                <h2>Cloth Item Debugging</h2>
                <p>Inspect cloth items and their associated data</p>
                
                <a class="button" href="debug_cloth.php?id=1">Cloth ID #1</a>
                <?php if ($latest_cloth_id > 0): ?>
                <a class="button" href="debug_cloth.php?id=<?php echo $latest_cloth_id; ?>">Latest Cloth Item</a>
                <?php endif; ?>
                <a class="button" href="debug_cloth.php?id=<?php echo rand(1, max(1, $latest_cloth_id)); ?>">Random Cloth Item</a>
            </div>
            
            <div class="tool-card">
                <h2>Image Debugging</h2>
                <p>Troubleshoot image display and storage issues</p>
                
                <a class="button" href="debug_image.php?id=1">Image ID #1</a>
                <?php if ($latest_cloth_id > 0): ?>
                <a class="button" href="debug_image.php?id=<?php echo $latest_cloth_id; ?>">Latest Image</a>
                <?php endif; ?>
                <a class="button" href="debug_image.php?id=<?php echo rand(1, max(1, $latest_cloth_id)); ?>">Random Image</a>
            </div>
        </div>
        
        <div class="tool-card" style="margin-top: 20px;">
            <h2>Quick Navigation</h2>
            <p>Enter a specific cloth item ID to debug:</p>
            
            <form action="debug_cloth.php" method="get" style="margin-bottom: 15px;">
                <input type="number" name="id" min="1" placeholder="Enter cloth ID" required>
                <button type="submit" class="button">Debug Cloth Item</button>
            </form>
            
            <form action="debug_image.php" method="get">
                <input type="number" name="id" min="1" placeholder="Enter image ID" required>
                <button type="submit" class="button">Debug Image</button>
            </form>
        </div>
    </div>
</body>
</html> 