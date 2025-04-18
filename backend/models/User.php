<?php
/**
 * User Model Class
 * Handles all user-related database operations
 */
class User {
    // Database connection and table name
    private $conn;
    private $table = 'users';

    // User properties
    public $id;
    public $name;
    public $email;
    public $password;
    public $phone;
    public $role;
    public $profile_image;
    public $status;
    public $created_at;
    public $updated_at;

    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get a single user by ID
     * 
     * @return array User data or false if not found
     */
    public function getSingle() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameter
        $stmt->bindParam(':id', $this->id);
        
        // Execute query
        $stmt->execute();
        
        // Check if user exists
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return false;
    }

    /**
     * Create a new user
     * 
     * @return bool True if created successfully, false otherwise
     */
    public function create() {
        // Create query
        $query = "INSERT INTO " . $this->table . " 
                  (name, email, password, phone, role, profile_image, status) 
                  VALUES (:name, :email, :password, :phone, :role, :profile_image, :status)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->profile_image = htmlspecialchars(strip_tags($this->profile_image));
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':profile_image', $this->profile_image);
        $stmt->bindParam(':status', $this->status);
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        
        return false;
    }

    /**
     * Update a user
     * 
     * @return bool True if updated successfully, false otherwise
     */
    public function update() {
        // Create query
        $query = "UPDATE " . $this->table . " 
                  SET name = :name, 
                      email = :email, 
                      phone = :phone, 
                      profile_image = :profile_image, 
                      status = :status, 
                      updated_at = NOW()
                  WHERE id = :id";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->profile_image = htmlspecialchars(strip_tags($this->profile_image));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':profile_image', $this->profile_image);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        
        return false;
    }

    /**
     * Update user password
     * 
     * @param string $newPassword Hashed new password
     * @return bool True if updated successfully, false otherwise
     */
    public function updatePassword($newPassword) {
        // Create query
        $query = "UPDATE " . $this->table . " 
                  SET password = :password,
                      updated_at = NOW()
                  WHERE id = :id";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $newPassword = htmlspecialchars(strip_tags($newPassword));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind parameters
        $stmt->bindParam(':password', $newPassword);
        $stmt->bindParam(':id', $this->id);
        
        // Execute query
        return $stmt->execute();
    }

    /**
     * Get user by email
     * 
     * @param string $email User email
     * @return array User data or false if not found
     */
    public function getUserByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $email = htmlspecialchars(strip_tags($email));
        
        // Bind parameter
        $stmt->bindParam(':email', $email);
        
        // Execute query
        $stmt->execute();
        
        // Check if user exists
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return false;
    }

    /**
     * Get user by ID
     * 
     * @param int $id User ID
     * @return bool True if user found and properties set, false otherwise
     */
    public function readById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $id = htmlspecialchars(strip_tags($id));
        
        // Bind parameter
        $stmt->bindParam(':id', $id);
        
        // Execute query
        $stmt->execute();
        
        // Check if user exists
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Set properties
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->phone_no = $row['phone_no'] ?? $row['phone'] ?? null;
            $this->user_type = $row['user_type'] ?? $row['role'] ?? null;
            $this->profile_photo = $row['profile_photo'] ?? $row['profile_image'] ?? null;
            $this->status = $row['status'] ?? null;
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Update profile photo
     * 
     * @param string $photoFilename Filename of the profile photo
     * @return bool True if updated successfully, false otherwise
     */
    public function updateProfilePhoto($photoFilename) {
        // Create query
        $query = "UPDATE " . $this->table . " 
                  SET profile_photo = :profile_photo,
                      updated_at = NOW()
                  WHERE id = :id";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $photoFilename = htmlspecialchars(strip_tags($photoFilename));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind parameters
        $stmt->bindParam(':profile_photo', $photoFilename);
        $stmt->bindParam(':id', $this->id);
        
        // Execute query
        return $stmt->execute();
    }
}
?> 