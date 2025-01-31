const multer = require('multer');
const path = require('path');

// Configure multer for file storage
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        cb(null, 'uploads/'); // Upload folder
    },
    filename: function (req, file, cb) {
        cb(null, Date.now() + path.extname(file.originalname)); // Unique file name
    }
});
const upload = multer({ storage: storage });

// Endpoint to upload images
app.post('/upload-images', upload.array('file', 5), (req, res) => {
    const imagePaths = req.files.map(file => `/uploads/${file.filename}`);
    res.json({ imagePaths });
});
