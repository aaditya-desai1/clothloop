document.getElementById('login-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent default form submission
    
    // Get form values
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    // Get destination from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const destination = urlParams.get('destination');
    
    // Make API call to login.php
    fetch('../backend/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            username: username,
            password: password,
            destination: destination
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect based on user type and destination
            if (data.user_type === 'seller') {
                window.location.href = '../frontend/Seller/seller_home.html';
            } else if (data.user_type === 'buyer') {
                if (destination === 'wishlist') {
                    window.location.href = '../frontend/Buyer/wish.html';
                } else {
                    window.location.href = '../frontend/Buyer/Buyer_Dashboard.html';
                }
            }
        } else {
            alert(data.error || 'Login failed. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});

// Handle Google Sign In
document.getElementById('googleSignIn').addEventListener('click', function() {
    // Add Google Sign In logic here
    // After successful Google sign in, you'll need to handle the user type check similarly
    // For now, we'll just show a message
    alert('Google Sign In functionality will be implemented soon');
});
