# ClothLoop Backend API

This is the backend API for the ClothLoop platform, a clothing rental marketplace.

## Setup Instructions

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP/MAMP (for local development)

### Installation Steps

1. **Database Setup**
   - Create a new MySQL database named `clothloop`
   - Update database credentials in `config/database.php` if needed
   - Run the database initialization script by visiting:
     ```
     http://localhost/ClothLoop/backend/db_init.php
     ```

2. **File Permissions**
   - Make sure the `uploads` directory and its subdirectories are writable:
     ```
     chmod -R 755 uploads/
     ```

3. **API Endpoints**

   The API has the following main endpoints:

   **Authentication**
   - `POST /backend/api/users/signup_process.php` - Register a new user
   - `POST /backend/api/users/login.php` - Login
   - `POST /backend/api/users/logout.php` - Logout

   **Buyer Endpoints**
   - `GET /backend/api/buyers/get_buyer_profile.php` - Get buyer profile
   - `POST /backend/api/buyers/update_buyer_profile.php` - Update buyer profile

   **Seller Endpoints**
   - `GET /backend/api/sellers/get_seller_profile.php` - Get seller profile
   - `POST /backend/api/sellers/update_seller_profile.php` - Update seller profile
   - `POST /backend/api/sellers/upload_cloth.php` - Upload/Edit product

   **Product Endpoints**
   - `GET /backend/api/products/get_products.php` - List/search products
   - `GET /backend/api/products/get_product_details.php?id={id}` - Get product details

## API Response Format

All API responses follow a standard format:

### Success Response
```json
{
  "status": "success",
  "message": "Operation successful message",
  "data": {
    // Response data
  }
}
```

### Error Response
```json
{
  "status": "error",
  "message": "Error message",
  "errors": {
    // Optional detailed errors
  }
}
```

## Security

The backend implements the following security features:
- Password hashing with bcrypt
- Session-based authentication
- Input validation and sanitization
- Prepared statements to prevent SQL injection
- CORS headers for API access 