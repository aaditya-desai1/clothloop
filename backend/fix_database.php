<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html');
echo "<h1>Database Structure Check & Fix</h1>";

try {
    $servername = "localhost";
    $username = "root";  // default XAMPP username
    $password = "";      // default XAMPP password
    
    // Create connection without database selection first
    $conn = new mysqli($servername, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p style='color:green'>✓ Connected to MySQL Server</p>";
    
    // Step 1: Check if database exists, create if it doesn't
    $dbname = "clothloop";
    $result = $conn->query("SHOW DATABASES LIKE '$dbname'");
    
    if ($result->num_rows === 0) {
        echo "<p>Database '$dbname' does not exist. Creating it...</p>";
        
        if ($conn->query("CREATE DATABASE $dbname")) {
            echo "<p style='color:green'>✓ Database created successfully</p>";
        } else {
            throw new Exception("Error creating database: " . $conn->error);
        }
    } else {
        echo "<p style='color:green'>✓ Database '$dbname' exists</p>";
    }
    
    // Select the database
    $conn->select_db($dbname);
    
    // Step 2: Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    
    if ($result->num_rows === 0) {
        echo "<p>Table 'users' does not exist. Creating it...</p>";
        
        $sql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(15) NOT NULL,
            user_type ENUM('buyer', 'seller') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql)) {
            echo "<p style='color:green'>✓ Table 'users' created successfully</p>";
        } else {
            throw new Exception("Error creating table: " . $conn->error);
        }
    } else {
        echo "<p style='color:green'>✓ Table 'users' exists</p>";
        
        // Step 3: Check users table structure
        $result = $conn->query("DESCRIBE users");
        
        if ($result) {
            $columns = [];
            while ($row = $result->fetch_assoc()) {
                $columns[$row['Field']] = $row;
            }
            
            echo "<p>Current table structure:</p>";
            echo "<ul>";
            foreach ($columns as $name => $details) {
                echo "<li>{$name} - {$details['Type']}</li>";
            }
            echo "</ul>";
            
            // Check for required columns
            $required_columns = [
                'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
                'username' => 'VARCHAR(50) NOT NULL',
                'email' => 'VARCHAR(100) NOT NULL UNIQUE',
                'password' => 'VARCHAR(255) NOT NULL',
                'phone' => 'VARCHAR(15) NOT NULL',
                'user_type' => "ENUM('buyer', 'seller') NOT NULL",
                'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
            ];
            
            $alterations = [];
            
            foreach ($required_columns as $column => $spec) {
                if (!isset($columns[$column])) {
                    $alterations[] = "ADD COLUMN $column $spec";
                }
            }
            
            // Check password column length (should be 255 for bcrypt)
            if (isset($columns['password']) && strpos($columns['password']['Type'], 'varchar') !== false) {
                // Extract the size from varchar(X)
                preg_match('/varchar\((\d+)\)/i', $columns['password']['Type'], $matches);
                $size = $matches[1] ?? 0;
                
                if ($size < 255) {
                    $alterations[] = "MODIFY COLUMN password VARCHAR(255) NOT NULL";
                }
            }
            
            // Apply alterations if needed
            if (!empty($alterations)) {
                echo "<p>Table structure needs updates. Applying changes...</p>";
                
                foreach ($alterations as $alteration) {
                    $sql = "ALTER TABLE users $alteration";
                    if ($conn->query($sql)) {
                        echo "<p style='color:green'>✓ Applied: $alteration</p>";
                    } else {
                        echo "<p style='color:red'>✗ Failed: $alteration - " . $conn->error . "</p>";
                    }
                }
            } else {
                echo "<p style='color:green'>✓ Table structure is correct</p>";
            }
        }
    }
    
    // Step 4: Check for test user
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $test_username = 'testuser';
    $stmt->bind_param("s", $test_username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "<p>No test user found. Creating one...</p>";
        
        $username = 'testuser';
        $email = 'test@example.com';
        $password = 'password123';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $phone = '1234567890';
        $user_type = 'buyer';
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, phone, user_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $hashed_password, $phone, $user_type);
        
        if ($stmt->execute()) {
            echo "<p style='color:green'>✓ Test user created with username: $username, password: $password</p>";
        } else {
            echo "<p style='color:red'>✗ Failed to create test user: " . $stmt->error . "</p>";
        }
    } else {
        $user = $result->fetch_assoc();
        
        // Check if the password is hashed
        $is_hashed = (strpos($user['password'], '$2y$') === 0);
        
        if (!$is_hashed) {
            echo "<p>Test user exists but password is not hashed correctly. Updating...</p>";
            
            $password = 'password123';
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $update_stmt->bind_param("ss", $hashed_password, $test_username);
            
            if ($update_stmt->execute()) {
                echo "<p style='color:green'>✓ Updated test user password hash</p>";
            } else {
                echo "<p style='color:red'>✗ Failed to update test user: " . $update_stmt->error . "</p>";
            }
        } else {
            echo "<p style='color:green'>✓ Test user exists with proper password hash</p>";
        }
    }
    
    // Final summary
    echo "<div style='background-color: #d9edf7; color: #31708f; padding: 15px; margin-top: 20px; border-radius: 4px;'>";
    echo "<h3>Database Check Complete</h3>";
    echo "<p>The database structure has been verified and fixed if needed.</p>";
    echo "<p>You can now try to login with the test user:</p>";
    echo "<p><strong>Username:</strong> testuser<br><strong>Password:</strong> password123</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
} finally {
    if (isset($conn)) {
        $conn->close();
        echo "<p>Database connection closed.</p>";
    }
}
?>

<hr>
<h2>Next Steps</h2>
<ul>
    <li><a href="debug_login.php">Use the login debugger</a> to test login with detailed diagnostics</li>
    <li><a href="create_user.php">Create a new user</a> with a properly hashed password</li>
    <li><a href="../Registration/login.html">Go to the normal login page</a> to try logging in</li>
</ul> 