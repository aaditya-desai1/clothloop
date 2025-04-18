<?php
/**
 * Seller Profile Page
 * Handles displaying and updating seller profile information
 */

session_start();

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    header('Location: ../auth/login.php');
    exit;
}

// Store user data from session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Profile | ClothLoop</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Theme Variables */
        :root {
            /* Dark Theme (Default) */
            --primary-color: #000000;
            --secondary-color: #333333;
            --accent-color: #ffffff;
            --text-color: #ffffff;
            --light-bg: #111111;
            --card-bg: #1a1a1a;
            --footer-bg: #000000;
            --footer-text: #cccccc;
            --border-radius: 0;
            --box-shadow: 0 5px 10px rgba(0, 0, 0, 0.8);
            --success-color: #4CD964;
            --error-color: #FF3B30;
        }

        /* Light Theme Variables */
        .light-theme {
            --primary-color: #ffffff;
            --secondary-color: #000000;
            --accent-color: #000000;
            --text-color: #000000;
            --light-bg: #f5f5f5;
            --card-bg: #ffffff;
            --footer-bg: #f5f5f5;
            --footer-text: #000000;
            --border-radius: 0;
            --box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.7;
            color: var(--text-color);
            background-color: var(--light-bg);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Theme Switcher */
        .theme-switcher {
            background: var(--primary-color);
            border: 2px solid var(--accent-color);
            color: var(--accent-color);
            width: 40px;
            height: 40px;
            border-radius: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin-left: 20px;
            transition: all 0.2s linear;
        }
        
        .theme-switcher:hover {
            transform: skewX(-5deg);
            box-shadow: 3px 3px 0 rgba(128, 128, 128, 0.3);
        }

        /* Navigation */
        nav {
            background-color: var(--primary-color);
            padding: 1.2rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(255, 255, 255, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        nav .logo {
            color: var(--accent-color);
            font-size: 1.8rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            letter-spacing: 3px;
            text-transform: uppercase;
            position: relative;
        }

        nav .logo i {
            margin-right: 10px;
        }

        nav ul {
            display: flex;
            list-style: none;
        }

        nav ul li {
            margin-left: 1.5rem;
        }

        nav ul li a {
            color: var(--accent-color);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 0;
            transition: all 0.2s linear;
            font-weight: 500;
            border-bottom: 2px solid transparent;
        }

        nav ul li a:hover, nav ul li a.active {
            background-color: transparent;
            color: var(--accent-color);
            border-bottom: 2px solid var(--accent-color);
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 120px auto 50px;
            padding: 20px;
        }

        .page-title {
            text-align: center;
            font-size: 3rem;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: 700;
        }

        .page-subtitle {
            text-align: center;
            font-size: 1.2rem;
            margin-bottom: 50px;
            font-weight: 400;
            color: #aaa;
        }

        /* Profile Form */
        .profile-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 40px;
        }

        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
        }

        .form-section {
            background-color: var(--card-bg);
            padding: 30px;
            box-shadow: var(--box-shadow);
            border: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
            overflow: hidden;
        }

        .form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background-color: var(--accent-color);
        }

        .section-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--accent-color);
        }

        .form-input {
            width: 100%;
            height: 50px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background-color: rgba(255, 255, 255, 0.05);
            padding: 0 15px;
            font-size: 16px;
            color: var(--text-color);
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-textarea {
            width: 100%;
            height: 120px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background-color: rgba(255, 255, 255, 0.05);
            padding: 15px;
            font-size: 16px;
            color: var(--text-color);
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            resize: vertical;
        }

        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 3px 3px 0 rgba(128, 128, 128, 0.1);
            transform: translateY(-2px) skewX(-2deg);
        }

        .btn {
            height: 50px;
            border: none;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            margin-top: 20px;
            font-family: 'Poppins', sans-serif;
            padding: 0 30px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--accent-color);
            border: 2px solid var(--accent-color);
        }

        .btn-primary:hover {
            transform: translateY(-3px) skewX(-5deg);
            box-shadow: 5px 5px 0 rgba(128, 128, 128, 0.2);
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: fadeIn 0.5s ease-out;
        }

        .success-message {
            background-color: var(--success-color);
            color: white;
            border-left: 5px solid #36b349;
        }

        .error-message {
            background-color: var(--error-color);
            color: white;
            border-left: 5px solid #d62d22;
        }

        /* File Upload */
        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload-label {
            display: block;
            padding: 12px 15px;
            border: 2px dashed rgba(255, 255, 255, 0.2);
            background-color: rgba(255, 255, 255, 0.05);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 5px;
        }

        .file-upload-label:hover {
            border-color: var(--accent-color);
        }

        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        /* Profile Image Preview */
        .profile-image-container {
            margin-bottom: 20px;
            text-align: center;
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--accent-color);
            box-shadow: var(--box-shadow);
        }

        /* Footer */
        .footer {
            background-color: var(--footer-bg);
            color: var(--footer-text);
            padding: 1rem 0;
            margin-top: auto;
        }

        .footer-bottom {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            text-align: center;
            font-size: 0.9rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <a href="../../home.html" class="logo">
            <span class="seller-badge" style="font-size: 0.5em; position: absolute; top: -10px; right: -40px; background-color: #fff; color: #000; padding: 2px 5px; border-radius: 3px;">SELLER</span>
            <img src="../../assets/images/logo_f.png" alt="ClothLoop Logo" style="height: 40px; margin-right: 10px;"> ClothLoop
        </a>
        <div style="display: flex; align-items: center;">
            <ul>
                <li><a href="seller_dashboard.html">Dashboard</a></li>
                <li><a href="product_listings.html">Listings</a></li>
                <li><a href="seller_reviews.html">Reviews</a></li>
                <li><a href="seller_profile.php" class="active">Profile</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
            <button class="theme-switcher" id="theme-toggle" aria-label="Toggle theme">
                <i class="fas fa-sun"></i>
            </button>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <h1 class="page-title">Seller Profile</h1>
        <p class="page-subtitle">Manage your shop and personal information</p>

        <div id="message-container"></div>

        <div class="profile-container">
            <!-- Personal Information Section -->
            <div class="form-section">
                <h2 class="section-title">Personal Information</h2>
                
                <div class="profile-image-container">
                    <img src="../../assets/images/default-profile.png" alt="Profile Picture" class="profile-image" id="profile-image-preview">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Profile Image</label>
                    <div class="file-upload">
                        <label class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt"></i> Choose File
                        </label>
                        <input type="file" class="file-upload-input" id="profile-image" accept="image/*">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" id="name" class="form-input" value="<?php echo htmlspecialchars($user_name); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" class="form-input" value="<?php echo htmlspecialchars($user_email); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" id="phone" class="form-input">
                </div>
            </div>
            
            <!-- Shop Information Section -->
            <div class="form-section">
                <h2 class="section-title">Shop Information</h2>
                
                <div class="form-group">
                    <label for="shop-name" class="form-label">Shop Name</label>
                    <input type="text" id="shop-name" class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="shop-description" class="form-label">Shop Description</label>
                    <textarea id="shop-description" class="form-textarea"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="shop-address" class="form-label">Shop Address</label>
                    <textarea id="shop-address" class="form-textarea"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="shop-location" class="form-label">Shop Location (Map Coordinates)</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="latitude" class="form-input" placeholder="Latitude">
                        <input type="text" id="longitude" class="form-input" placeholder="Longitude">
                    </div>
                </div>
                
                <button id="update-profile" class="btn btn-primary">Update Profile</button>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-bottom">
            <p>&copy; 2024 ClothLoop. All rights reserved.</p>
        </div>
    </footer>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme switcher functionality
            const themeToggle = document.getElementById('theme-toggle');
            const themeIcon = themeToggle.querySelector('i');
            
            // Check for saved theme preference
            const savedTheme = localStorage.getItem('clothloop-theme');
            if (savedTheme === 'light') {
                document.body.classList.add('light-theme');
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
            
            // Toggle theme
            themeToggle.addEventListener('click', () => {
                document.body.classList.toggle('light-theme');
                
                // Update icon
                if (document.body.classList.contains('light-theme')) {
                    themeIcon.classList.remove('fa-sun');
                    themeIcon.classList.add('fa-moon');
                    localStorage.setItem('clothloop-theme', 'light');
                } else {
                    themeIcon.classList.remove('fa-moon');
                    themeIcon.classList.add('fa-sun');
                    localStorage.setItem('clothloop-theme', 'dark');
                }
            });

            // Profile image preview
            const profileImageInput = document.getElementById('profile-image');
            const profileImagePreview = document.getElementById('profile-image-preview');
            
            profileImageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        profileImagePreview.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Fetch seller profile data
            fetchSellerProfile();

            // Update profile button event
            document.getElementById('update-profile').addEventListener('click', updateProfile);
        });

        // Fetch seller profile data from API
        function fetchSellerProfile() {
            fetch('/ClothLoop/backend/api/sellers/get_seller_profile.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Populate form fields with seller data
                        document.getElementById('name').value = data.data.name || '';
                        document.getElementById('email').value = data.data.email || '';
                        document.getElementById('phone').value = data.data.phone_no || '';
                        document.getElementById('shop-name').value = data.data.shop_name || '';
                        document.getElementById('shop-description').value = data.data.description || '';
                        document.getElementById('shop-address').value = data.data.address || '';
                        document.getElementById('latitude').value = data.data.latitude || '';
                        document.getElementById('longitude').value = data.data.longitude || '';
                        
                        // Set profile image if available
                        if (data.data.profile_photo) {
                            document.getElementById('profile-image-preview').src = data.data.profile_photo;
                        }
                    } else {
                        showMessage('error', 'Failed to load profile data: ' + data.message);
                    }
                })
                .catch(error => {
                    showMessage('error', 'Error loading profile data: ' + error.message);
                });
        }

        // Update profile function
        function updateProfile() {
            // Collect form data
            const formData = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                phone_no: document.getElementById('phone').value,
                shop_name: document.getElementById('shop-name').value,
                description: document.getElementById('shop-description').value,
                address: document.getElementById('shop-address').value,
                latitude: document.getElementById('latitude').value,
                longitude: document.getElementById('longitude').value
            };

            // Send data to API
            fetch('/ClothLoop/backend/api/sellers/update_seller_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showMessage('success', 'Profile updated successfully');
                } else {
                    showMessage('error', 'Failed to update profile: ' + data.message);
                }
            })
            .catch(error => {
                showMessage('error', 'Error updating profile: ' + error.message);
            });

            // Handle profile image upload separately if a file was selected
            const profileImageInput = document.getElementById('profile-image');
            if (profileImageInput.files.length > 0) {
                const imageFormData = new FormData();
                imageFormData.append('profile_image', profileImageInput.files[0]);

                fetch('/ClothLoop/backend/api/users/upload_profile_image.php', {
                    method: 'POST',
                    body: imageFormData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showMessage('success', 'Profile image updated successfully');
                    } else {
                        showMessage('error', 'Failed to update profile image: ' + data.message);
                    }
                })
                .catch(error => {
                    showMessage('error', 'Error uploading profile image: ' + error.message);
                });
            }
        }

        // Show message function
        function showMessage(type, text) {
            const messageContainer = document.getElementById('message-container');
            const messageElement = document.createElement('div');
            messageElement.className = `message ${type === 'success' ? 'success-message' : 'error-message'}`;
            messageElement.textContent = text;
            
            // Clear any existing messages
            messageContainer.innerHTML = '';
            messageContainer.appendChild(messageElement);
            
            // Auto-remove message after 5 seconds
            setTimeout(() => {
                messageElement.remove();
            }, 5000);
        }
    </script>
</body>
</html> 