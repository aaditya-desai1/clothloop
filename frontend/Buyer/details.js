   // Function to parse URL parameters
   function getQueryParams() {
    const params = new URLSearchParams(window.location.search);
    return {
        image: params.get('image'),
        title: params.get('title'),
        description: params.get('description'),
        size: params.get('size'),
        color: params.get('color'),
        price: params.get('price'),
        contact: params.get('contact'),
        whatsapp: params.get('whatsapp'),
        location: params.get('location'),
        shop: params.get('shop'),
        address: params.get('address'),
        terms: params.get('terms')
    };
}

// Populate the details section based on URL parameters
const params = getQueryParams();
document.getElementById('clothImage').src = params.image;
document.getElementById('clothTitle').textContent = params.title;
document.getElementById('clothDescription').textContent = params.description;
document.getElementById('clothSize').textContent = params.size;
document.getElementById('clothColor').textContent = params.color;
document.getElementById('clothPrice').textContent = params.price;
document.getElementById('contactNumber').textContent = params.contact;
document.getElementById('whatsappNumber').textContent = params.whatsapp;
document.getElementById('shopName').textContent = params.shop;
document.getElementById('shopAddress').textContent = params.address;
document.getElementById('termsConditions').textContent = params.terms;

// Initialize counts
let likeCount = 0;
let commentCount = 0;
let shareCount = 0;
let saveCount = 0;

// Like functionality
let liked = false;
function toggleLike() {
    liked = !liked;
    document.getElementById('likeIcon').className = liked ? 'fas fa-thumbs-up' : 'far fa-thumbs-up';
    likeCount += liked ? 1 : -1;
    document.getElementById('likeCount').textContent = likeCount;
}

// Save functionality
let saved = false;
function toggleSave() {
    saved = !saved;
    document.getElementById('saveIcon').className = saved ? 'fas fa-bookmark' : 'far fa-bookmark';
    saveCount += saved ? 1 : -1;
    document.getElementById('saveCount').textContent = saveCount;
}

// Share functionality
function shareItem() {
    shareCount++;
    document.getElementById('shareCount').textContent = shareCount;
    alert("Item shared successfully!");
}

// Comment functionality
function showCommentBox() {
    document.getElementById('commentBox').style.display = 'block';
}

function addComment() {
    const commentInput = document.getElementById('commentInput');
    const commentText = commentInput.value;
    if (commentText) {
        commentCount++;
        document.getElementById('commentCount').textContent = commentCount;
        const commentItem = document.createElement('li');
        commentItem.textContent = commentText;
        document.getElementById('commentsList').appendChild(commentItem);
        commentInput.value = ''; // Clear the input
    }
}

// Open Google Maps for the location
function openMap() {
    const location = params.location; // Get the location from the URL parameters
    const googleMapsUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(location)}`;
    window.open(googleMapsUrl, '_blank');
}