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

// Add password validation function
function validatePassword(password) {
    // Password validation rules
    const minLength = 8;
    const maxLength = 12;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumbers = /\d/.test(password);
    const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~]/.test(password);
    
    const errors = [];
    
    if (password.length < minLength || password.length > maxLength) {
        errors.push(`Password must be between ${minLength}-${maxLength} characters`);
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
function updatePasswordStrength(password, strengthElement) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    const strengthText = ['Very Weak', 'Weak', 'Medium', 'Strong', 'Very Strong'];
    const strengthColor = ['#ff4444', '#ffbb33', '#ffeb3b', '#00C851', '#007E33'];
    
    strengthElement.style.width = `${(strength / 5) * 100}%`;
    strengthElement.style.backgroundColor = strengthColor[strength - 1];
    strengthElement.textContent = strengthText[strength - 1];
}

// Add event listeners for password fields
document.addEventListener('DOMContentLoaded', function() {
    const passwordFields = document.querySelectorAll('input[type="password"][name="password"]');
    
    passwordFields.forEach(passwordField => {
        // Create password strength indicator
        const strengthDiv = document.createElement('div');
        strengthDiv.className = 'password-strength-bar';
        passwordField.parentElement.appendChild(strengthDiv);
        
        // Create error message container
        const errorDiv = document.createElement('div');
        errorDiv.className = 'password-error';
        passwordField.parentElement.appendChild(errorDiv);
        
        // Add input event listener
        passwordField.addEventListener('input', function() {
            const result = validatePassword(this.value);
            updatePasswordStrength(this.value, strengthDiv);
            
            // Show/hide error messages
            errorDiv.innerHTML = result.errors.map(error => `<div class="error-message">${error}</div>`).join('');
            
            // Update input validity
            this.setCustomValidity(result.isValid ? '' : 'Please fix password errors');
        });
    });
});

// Update form validation
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const password = this.querySelector('input[name="password"]').value;
        const confirmPassword = this.querySelector('input[name="confirm_password"]').value;
        const passwordValidation = validatePassword(password);

        if (!passwordValidation.isValid) {
            alert('Please fix password errors:\n' + passwordValidation.errors.join('\n'));
            return;
        }

        if (password !== confirmPassword) {
            alert('Passwords do not match!');
            return;
        }

        // If validation passes, submit the form
        this.submit();
    });
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