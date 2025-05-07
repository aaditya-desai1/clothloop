const fs = require('fs');
const path = require('path');

// Your Render backend URL - replace with your actual URL
const RENDER_BACKEND_URL = 'https://clothloop-backend.onrender.com';

// Local backend paths that need to be replaced
const localPaths = [
    '../../../backend/api/',
    '../../backend/api/',
    '../backend/api/',
    '/backend/api/',
    'backend/api/',
    'http://localhost/ClothLoop/backend/api/',
    'http://localhost/ClothLoop/backend/uploads/'
];

// Function to update file content
function updateFileContent(filePath, backendUrl) {
    try {
        const content = fs.readFileSync(filePath, 'utf8');
        let newContent = content;
        
        // Replace all instances of local backend paths with the Render URL
        localPaths.forEach(localPath => {
            // For API endpoints
            if (localPath.includes('/api/')) {
                const apiPath = localPath.split('/api/')[1] || '';
                const regex = new RegExp(`${localPath.replace(/\//g, '\\/')}`, 'g');
                newContent = newContent.replace(regex, `${backendUrl}/api/${apiPath}`);
            }
            // For uploads
            else if (localPath.includes('/uploads/')) {
                const uploadsPath = localPath.split('/uploads/')[1] || '';
                const regex = new RegExp(`${localPath.replace(/\//g, '\\/')}`, 'g');
                newContent = newContent.replace(regex, `${backendUrl}/uploads/${uploadsPath}`);
            }
        });
        
        // Update relative paths that are now incorrect
        newContent = newContent.replace(/\.\.\/\.\.\/\.\.\/backend\/api\//g, `${backendUrl}/api/`);
        newContent = newContent.replace(/\.\.\/\.\.\/backend\/api\//g, `${backendUrl}/api/`);
        newContent = newContent.replace(/\.\.\/backend\/api\//g, `${backendUrl}/api/`);
        
        // Write the updated content back to the file
        if (content !== newContent) {
            fs.writeFileSync(filePath, newContent, 'utf8');
            return true;
        }
        return false;
    } catch (error) {
        console.error(`Error updating ${filePath}:`, error);
        return false;
    }
}

// Function to recursively scan directory and update files
function updateDirectory(dirPath, backendUrl) {
    const files = fs.readdirSync(dirPath);
    let updatedCount = 0;
    
    for (const file of files) {
        const filePath = path.join(dirPath, file);
        const stats = fs.statSync(filePath);
        
        if (stats.isDirectory()) {
            updatedCount += updateDirectory(filePath, backendUrl);
        } else if (file.endsWith('.html') || file.endsWith('.js')) {
            if (updateFileContent(filePath, backendUrl)) {
                console.log(`Updated: ${filePath}`);
                updatedCount++;
            }
        }
    }
    
    return updatedCount;
}

// Update the frontend directory
console.log(`Updating API URLs to point to: ${RENDER_BACKEND_URL}`);
const frontendDir = path.join(__dirname, 'frontend');
const homeHtml = path.join(__dirname, 'home.html');
const indexHtml = path.join(__dirname, 'index.html');

let totalUpdated = 0;

// Update frontend directory
if (fs.existsSync(frontendDir)) {
    totalUpdated += updateDirectory(frontendDir, RENDER_BACKEND_URL);
} else {
    console.error('Frontend directory not found!');
}

// Update home.html
if (fs.existsSync(homeHtml)) {
    if (updateFileContent(homeHtml, RENDER_BACKEND_URL)) {
        console.log(`Updated: ${homeHtml}`);
        totalUpdated++;
    }
} else {
    console.error('home.html not found!');
}

// Update index.html
if (fs.existsSync(indexHtml)) {
    if (updateFileContent(indexHtml, RENDER_BACKEND_URL)) {
        console.log(`Updated: ${indexHtml}`);
        totalUpdated++;
    }
}

console.log(`Total files updated: ${totalUpdated}`); 