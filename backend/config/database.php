<?php
/**
 * Database Configuration File
 * Contains connection parameters and methods for database operations
 */

class Database {
    // Database credentials
    private $host = "localhost";
    private $db_name = "clothloop";
    private $username = "root";
    private $password = "";
    private $conn;

    /**
     * Connect to the database
     * @return PDO Database connection object
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            // Log this error - but don't expose database credentials in response
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }

        return $this->conn;
    }
} 