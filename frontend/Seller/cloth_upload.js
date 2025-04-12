  // Image upload preview
  function previewImages() {
    const files = document.getElementById('images').files;
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    for (let i = 0; i < files.length && i < 5; i++) { // Limit to 5 images
        const img = document.createElement('img');
        img.src = URL.createObjectURL(files[i]);
        preview.appendChild(img);
    }
}

// Geolocation and reverse geocoding
function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const latitude = position.coords.latitude;
                const longitude = position.coords.longitude;
                document.getElementById('location').value = `${latitude}, ${longitude}`;
                reverseGeocode(latitude, longitude);
            },
            (error) => {
                alert("Unable to retrieve your location. Please try again.");
                console.error("Geolocation error:", error);
            }
        );
    } else {
        alert("Geolocation is not supported by your browser.");
    }
}

async function reverseGeocode(latitude, longitude) {
    const apiKey = 'YOUR_GOOGLE_MAPS_API_KEY'; // Replace with your API key
    const url = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=${apiKey}`;

    try {
        const response = await fetch(url);
        const data = await response.json();

        if (data.status === "OK" && data.results.length > 0) {
            const address = data.results[0].formatted_address;
            if (confirm(`Do you want to update the address to: ${address}?`)) {
                document.getElementById('address').value = address;
            }
        } else {
            alert("Unable to find a human-readable address for the given location.");
        }
    } catch (error) {
        console.error("Error while fetching address:", error);
        alert("There was an error retrieving the address. Please try again.");
    }
}