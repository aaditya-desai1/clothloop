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

// Form validation
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const password = this.querySelector('input[name="password"]').value;
        const confirmPassword = this.querySelector('input[name="confirm_password"]').value;

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