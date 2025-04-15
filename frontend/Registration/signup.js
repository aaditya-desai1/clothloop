function showForm(type) {
    const buyerForm = document.getElementById('buyerForm');
    const sellerForm = document.getElementById('sellerForm');
    const roleButtons = document.getElementById('roleButtons');

    // Hide the initial signup buttons
    roleButtons.classList.add('hidden');
    
    if (type === 'buyer') {
        buyerForm.classList.remove('hidden');
        sellerForm.classList.add('hidden');
    } else {
        sellerForm.classList.remove('hidden');
        buyerForm.classList.add('hidden');
    }
}

// Password validation function
function validatePassword(password) {
    // Password validation rules
    const minLength = 8;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumbers = /\d/.test(password);
    const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~]/.test(password);
    
    const errors = [];
    
    if (password.length < minLength) {
        errors.push(`Password must be at least ${minLength} characters`);
    }
    if (!hasUpperCase) errors.push('Include at least one uppercase letter');
    if (!hasLowerCase) errors.push('Include at least one lowercase letter');
    if (!hasNumbers) errors.push('Include at least one number');
    if (!hasSpecialChar) errors.push('Include at least one special character');
    
    return {
        isValid: errors.length === 0,
        errors: errors
    };
}

// Update password strength indicator
function updatePasswordStrength(password) {
    let strength = 0;
    const strengthElement = document.getElementById('password-strength');
    const errorElement = document.getElementById('password-error');
    
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    const strengthText = ['Very Weak', 'Weak', 'Medium', 'Strong', 'Very Strong'];
    const strengthColor = ['#ff4444', '#ffbb33', '#ffeb3b', '#00C851', '#007E33'];
    
    if (strength > 0) {
        strengthElement.style.width = `${(strength / 5) * 100}%`;
        strengthElement.style.backgroundColor = strengthColor[strength - 1];
        strengthElement.textContent = strengthText[strength - 1];
    } else {
        strengthElement.style.width = '0';
        strengthElement.textContent = '';
    }
    
    // Validate password and show errors
    const result = validatePassword(password);
    errorElement.innerHTML = result.errors.map(error => 
        `<div class="error-message">${error}</div>`
    ).join('');
    
    return result.isValid;
}

// Document ready event
document.addEventListener('DOMContentLoaded', function() {
    // Add password input event listener
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            updatePasswordStrength(this.value);
        });
    }
    
    // Form submission handler
    const signupForm = document.getElementById('signup-form');
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const phone = document.getElementById('phone').value;
            const userType = document.getElementById('user_type').value;
            
            // Validate phone number
            if (!/^\d{10}$/.test(phone)) {
                document.getElementById('phone-error').textContent = 'Please enter a valid 10-digit phone number';
                return;
            } else {
                document.getElementById('phone-error').textContent = '';
            }
            
            // Validate password
            if (!updatePasswordStrength(password)) {
                return;
            }
            
            // Validate confirm password
            if (password !== confirmPassword) {
                document.getElementById('confirm-password-error').textContent = 'Passwords do not match';
                return;
            } else {
                document.getElementById('confirm-password-error').textContent = '';
            }
            
            // Disable submit button and show loading state
            const submitBtn = document.getElementById('submit-btn');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Creating Account...';
            submitBtn.disabled = true;
            
            // Create form data
            const formData = new FormData(this);
            
            // Send data to the server
            fetch('../backend/signup_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(data => {
                // Check if response contains success message
                if (data.includes('Registration successful')) {
                    // Show success message
                    const successMessage = document.getElementById('success-message');
                    successMessage.style.display = 'block';
                    successMessage.innerHTML = `
                        <h3>User Created Successfully!</h3>
                        <p><strong>Username:</strong> ${username}</p>
                        <p><strong>User Type:</strong> ${userType}</p>
                        <p><a href="login.html">Go to login page</a></p>
                    `;
                    
                    // Hide the form
                    signupForm.style.display = 'none';
                } else {
                    // Show error if registration failed
                    document.getElementById('username-error').textContent = 'Registration failed. Please try again.';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('username-error').textContent = 'An error occurred. Please try again.';
            })
            .finally(() => {
                // Reset button state
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});

function handleGoogleCredential(response, userType) {
    const credential = response.credential;
    const decodedToken = jwt_decode(credential);
    
    if (userType === 'buyer') {
        // Buyer process remains the same
        document.getElementById('buyerGoogleToken').value = credential;
        document.getElementById('buyerForm').submit();
    } else {
        // For seller, show phone verification form
        const sellerForm = document.getElementById('sellerForm');
        const verificationForm = document.getElementById('sellerGoogleVerification');
        const roleButtons = document.getElementById('roleButtons');
        
        // Hide other forms
        sellerForm.classList.add('hidden');
        roleButtons.classList.add('hidden');
        
        // Show verification form and set token
        verificationForm.classList.remove('hidden');
        document.getElementById('sellerGoogleVerificationToken').value = credential;
    }
}

window.onload = function() {
    // Initialize Google Sign-In button for Buyer
    google.accounts.id.initialize({
        client_id: 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com',
        callback: (response) => handleGoogleCredential(response, 'buyer')
    });

    google.accounts.id.renderButton(
        document.getElementById("buyerGoogleButton"),
        { theme: "outline", size: "large", width: 250 }
    );

    // Initialize Google Sign-In button for Seller
    google.accounts.id.initialize({
        client_id: 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com',
        callback: (response) => handleGoogleCredential(response, 'seller')
    });

    google.accounts.id.renderButton(
        document.getElementById("sellerGoogleButton"),
        { theme: "outline", size: "large", width: 250 }
    );

    // Add form validation for the verification form
    document.getElementById('sellerGoogleVerification').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Add any additional validation if needed
        
        // If validation passes, submit the form
        this.submit();
    });
}