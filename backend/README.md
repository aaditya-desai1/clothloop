# ClothLoop Backend Deployment Guide

This guide provides instructions for deploying and testing the ClothLoop backend API on Render.

## Important URLs

- Frontend (Vercel): https://clothloop.vercel.app
- Backend (Render): https://clothloop-backend.onrender.com

## Testing the API

After deployment, you can test the API endpoints using the test script:

1. Visit `https://clothloop-backend.onrender.com/api/test/test_endpoints.php`
2. The script will test key endpoints and show their status
3. It will also check database connectivity and show table information

## Setting Up the Database

To set up the database with all necessary tables:

1. Visit: `https://clothloop-backend.onrender.com/api/setup/create_tables.php`
2. This script creates all tables defined in clothloop_updates.sql
3. It handles both PostgreSQL (on Render) and MySQL (for local development)
4. It also seeds test data (categories, test users)

Alternative setup URLs:
- `/api/system/setup_tables.php` - System endpoint that redirects to the main setup script

## Common Issues and Fixes

### CORS Issues

If you're experiencing CORS issues between the frontend and backend:

1. Check that all API endpoints include the CORS headers
2. Make sure endpoints are using the `cors.php` utility:
   ```php
   require_once __DIR__ . '/../../api/cors.php';
   apply_cors();
   ```
3. Verify that `Access-Control-Allow-Origin: *` is being sent in the response headers

### Database Connection Issues

If the PostgreSQL database on Render isn't connecting properly:

1. Check Render environment variables (DB_HOST, DB_NAME, DB_USER, DB_PASS)
2. Run the setup tables endpoint: `/api/setup/create_tables.php`
3. Check database logs in the Render dashboard
4. Test connection with: `/api/test/test_endpoints.php`

### Database Schema Issues

If tables don't exist or are missing columns:

1. Run the setup tables endpoint: `/api/setup/create_tables.php`
2. Check the output for any errors
3. The setup script creates all tables defined in clothloop_updates.sql:
   - Users, Buyers, Sellers tables
   - Products and Product Images tables
   - Categories table
   - Customer Interests table
   - Wishlist table
   - Seller Notifications table
   - Additional indexes for performance

### Data Seeding

To create test data:

1. The setup tables script will automatically create:
   - Test categories (Men, Women, Kids, Ethnic, Formal)
   - A test seller (email: seller@test.com, password: testpassword)
   - A test buyer (email: buyer@test.com, password: testpassword)

2. To manually create a user, use these credentials in the login endpoint.

## Debugging Tools

1. **Test Endpoints Script**: `/api/test/test_endpoints.php`
   - Tests key endpoints
   - Checks database connectivity
   - Shows table information

2. **Database Setup**: `/api/setup/create_tables.php`
   - Creates all necessary tables
   - Seeds demo data

3. **CORS Check**: `/api/cors.php`
   - Verifies CORS is working

## Updating API Endpoints

When updating endpoints, follow these guidelines:

1. Always include CORS headers by using the CORS utility:
   ```php
   require_once __DIR__ . '/../../api/cors.php';
   apply_cors();
   ```

2. Use PostgreSQL-compatible SQL:
   - Use `ILIKE` instead of `LIKE` for case-insensitive search
   - Use `true`/`false` instead of `1`/`0` for boolean values
   - Check database type: `$dbType = $database->dbType;`
   - Use different SQL based on the database type:
     ```php
     if ($dbType === 'pgsql') {
         // PostgreSQL specific SQL
     } else {
         // MySQL specific SQL
     }
     ```

3. Use standard response functions from `api_utils.php`:
   - `sendSuccess($message, $data)` for successful responses
   - `sendError($message, $errors, $statusCode)` for error responses 