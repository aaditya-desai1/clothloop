document.getElementById('login-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent default form submission
    
    // Get form values
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    // Get destination from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const destination = urlParams.get('destination');
    
    // Show loading state
    const submitButton = document.querySelector('input[type="submit"]');
    const originalButtonText = submitButton.value;
    submitButton.value = 'Logging in...';
    submitButton.disabled = true;
    
    // Clear any previous error messages
    const errorElement = document.getElementById('login-error');
    if (errorElement) {
        errorElement.remove();
    }

    // Create error message display function
    function showError(message) {
        const form = document.getElementById('login-form');
        const errorDiv = document.createElement('div');
        errorDiv.id = 'login-error';
        errorDiv.style.color = 'red';
        errorDiv.style.marginBottom = '10px';
        errorDiv.style.padding = '8px';
        errorDiv.style.backgroundColor = '#ffeeee';
        errorDiv.style.border = '1px solid #ffcccc';
        errorDiv.style.borderRadius = '4px';
        errorDiv.innerHTML = message;
        form.prepend(errorDiv);
    }
    
    console.log('Attempting login for:', username);
    
    // Make API call to login.php with absolute path to avoid relative path issues
    fetch('/ClothLoop/backend/login.php', {
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
    .then(response => {
        console.log('Response status:', response.status);
        
        // Always try to parse the response as JSON, even if status is not 200
        return response.json().catch(err => {
            console.error('JSON parsing error:', err);
            // Return a standardized error object if JSON parsing fails
            return {
                success: false,
                error: 'Server returned an invalid response. Please try again later.'
            };
        });
    })
    .then(data => {
        console.log('Login response:', data);
        
        if (data.success) {
            // Redirect based on user type and destination
            if (data.user_type === 'seller') {
                window.location.href = '/ClothLoop/frontend/Seller/seller_home.html';
            } else if (data.user_type === 'buyer') {
                if (destination === 'wishlist') {
                    window.location.href = '/ClothLoop/frontend/Buyer/wish.html';
                } else {
                    window.location.href = '/ClothLoop/frontend/Buyer/Buyer_Dashboard.html';
                }
            }
        } else {
            // Display error message in the form
            const errorMessage = data.error || 'Login failed. Please try again.';
            showError(errorMessage);
            console.error('Login error:', errorMessage);
        }
    })
    .catch(error => {
        console.error('Login request error:', error);
        
        // Display error message in the form
        showError('Error connecting to server. Please try again.<br><small>Details: ' + error.message + '</small>');
    })
    .finally(() => {
        // Reset button state
        submitButton.value = originalButtonText;
        submitButton.disabled = false;
    });
});

// Handle Google Sign In
document.getElementById('googleSignIn').addEventListener('click', function() {
    // Add Google Sign In logic here
    // After successful Google sign in, you'll need to handle the user type check similarly
    // For now, we'll just show a message
    alert('Google Sign In functionality will be implemented soon');
});
