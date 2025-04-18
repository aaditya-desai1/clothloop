<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    // Redirect to login page
    header('Location: test_login.php');
    exit;
}

// Get user data
$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ClothLoop</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .header {
            background-color: #333;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            margin: 0;
            font-size: 1.5rem;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .user-info span {
            margin-right: 1rem;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .btn-logout {
            background-color: #f44336;
        }
        .btn-logout:hover {
            background-color: #d32f2f;
        }
        .dashboard-tiles {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .dashboard-tile {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 1.5rem;
            text-align: center;
        }
        .dashboard-tile h2 {
            margin-top: 0;
        }
        .dashboard-tile i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #4CAF50;
        }
    </style>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="header">
        <h1>ClothLoop Dashboard</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($user['name']); ?> (<?php echo ucfirst($user['role']); ?>)</span>
            <button id="logout-btn" class="btn btn-logout">Logout</button>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>Your Account Details</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone_no'] ?? 'Not provided'); ?></p>
            <p><strong>Account Type:</strong> <?php echo ucfirst($user['role']); ?></p>
            <?php if ($user['role'] === 'seller'): ?>
            <p><strong>Shop Name:</strong> <?php echo htmlspecialchars($user['shop_name'] ?? 'Not set'); ?></p>
            <?php endif; ?>
            
            <a href="test_profile.php" class="btn">Edit Profile</a>
        </div>
        
        <h2>Quick Access</h2>
        <div class="dashboard-tiles">
            <?php if ($user['role'] === 'buyer'): ?>
            <div class="dashboard-tile">
                <i class="fas fa-search"></i>
                <h2>Browse Products</h2>
                <p>Find clothing items to rent</p>
                <a href="test_browse_products.php" class="btn">Browse Now</a>
            </div>
            <div class="dashboard-tile">
                <i class="fas fa-heart"></i>
                <h2>Wishlist</h2>
                <p>View your saved items</p>
                <a href="test_wishlist.php" class="btn">View Wishlist</a>
            </div>
            <div class="dashboard-tile">
                <i class="fas fa-shopping-bag"></i>
                <h2>My Orders</h2>
                <p>Track your rental orders</p>
                <a href="test_orders.php" class="btn">View Orders</a>
            </div>
            <?php elseif ($user['role'] === 'seller'): ?>
            <div class="dashboard-tile">
                <i class="fas fa-tshirt"></i>
                <h2>My Products</h2>
                <p>Manage your rental items</p>
                <a href="test_seller_products.php" class="btn">Manage Products</a>
            </div>
            <div class="dashboard-tile">
                <i class="fas fa-plus-circle"></i>
                <h2>Add Product</h2>
                <p>List a new item for rent</p>
                <a href="test_add_product.php" class="btn">Add New</a>
            </div>
            <div class="dashboard-tile">
                <i class="fas fa-clipboard-list"></i>
                <h2>Orders</h2>
                <p>Manage rental orders</p>
                <a href="test_seller_orders.php" class="btn">View Orders</a>
            </div>
            <?php endif; ?>
            <div class="dashboard-tile">
                <i class="fas fa-comments"></i>
                <h2>Messages</h2>
                <p>View your conversations</p>
                <a href="test_messages.php" class="btn">View Messages</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logoutBtn = document.getElementById('logout-btn');
            
            // Handle logout
            logoutBtn.addEventListener('click', function() {
                fetch('backend/api/users/logout.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.href = 'test_login.php';
                    } else {
                        alert('Error logging out. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            });
        });
    </script>
</body>
</html> 