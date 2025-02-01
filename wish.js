 // Sample Wishlist Data with exactly 3 items
 const wishlistItems = [
    {
        imgSrc: "p3.jpg",
        title: "Traditional Woman Attire"
    },
    {
        imgSrc: "p1.jpg",
        title: "Traditional Man Attire"
    },
    {
        imgSrc: "p2.jpg",
        title: "Couple in Traditional Cloths"
    }
];

const wishlistContainer = document.getElementById("wishlist");

// Dynamically load exactly 3 wishlist items
wishlistItems.forEach(item => {
    const wishlistItem = document.createElement("div");
    wishlistItem.classList.add("wishlist-item");

    wishlistItem.innerHTML = `
        <img src="${item.imgSrc}" alt="${item.title}" class="wishlist-img">
        <h3>${item.title}</h3>
    `;

    wishlistContainer.appendChild(wishlistItem);
});

// Existing script remains, adding new animation logic
document.addEventListener('DOMContentLoaded', function() {
    // Add staggered animation to wishlist items
    const items = document.querySelectorAll('.wishlist-item');
    items.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.2}s`;
    });

    // Add click animation for remove button
    items.forEach(item => {
        item.addEventListener('click', function(e) {
            if (e.target === this.querySelector('::before')) {
                this.style.animation = 'fadeOut 0.3s ease-out forwards';
                setTimeout(() => {
                    this.remove();
                }, 300);
            }
        });
    });

    // Mark current page in dashboard
    const currentPage = document.querySelector('.dashboard-item.wishlist');
    if (currentPage) {
        currentPage.classList.add('selected-dashboard');
    }

    // Add smooth removal animation
    items.forEach(item => {
        const removeBtn = document.createElement('button');
        removeBtn.className = 'remove-btn';
        removeBtn.innerHTML = '×';
        
        removeBtn.addEventListener('click', function() {
            item.style.animation = 'slideUp 0.3s reverse forwards';
            setTimeout(() => item.remove(), 300);
        });

        item.querySelector('.wishlist-img-container').appendChild(removeBtn);
    });
});