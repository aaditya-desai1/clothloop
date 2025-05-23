<?php
/**
 * Database Configuration Class
 * Handles database connection and provides a PDO instance
 */

// Include environment configuration
require_once __DIR__ . '/env.php';

class Database {
    // Database credentials
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    public $dbType; // Make dbType public so it can be accessed in setup_database.php

    /**
     * Constructor - set database credentials
     */
    public function __construct() {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        
        // Log database connection details in production for debugging
        if (IS_PRODUCTION) {
            error_log("[Database] Initializing connection with: Host=$this->host, DB=$this->db_name, User=$this->username");
        }
        
        // Use the database type from env.php
        global $dbConfig;
        $this->dbType = $dbConfig['type'] ?? 'mysql';
        
        if (IS_PRODUCTION) {
            error_log("[Database] Using database type: " . $this->dbType);
        }
    }

    /**
     * Connect to the database
     * 
     * @return PDO Database connection object
     */
    public function connect() {
        $this->conn = null;

        try {
            // Try connecting to the database
            if ($this->dbType === 'pgsql') {
                // Check if host starts with "dpg-" (Render PostgreSQL)
                $isRenderPostgres = (stripos($this->host, 'dpg-') === 0);
                
                if ($isRenderPostgres) {
                    // For Render PostgreSQL, we need to use SSL
                    $dsn = "pgsql:host={$this->host};dbname={$this->db_name};sslmode=require";
                    if (IS_PRODUCTION) {
                        error_log("[Database] Connecting to Render PostgreSQL with DSN: $dsn");
                    }
                } else {
                    // Standard PostgreSQL connection
                    $dsn = "pgsql:host={$this->host};dbname={$this->db_name}";
                    if (IS_PRODUCTION) {
                        error_log("[Database] Connecting with PostgreSQL DSN: $dsn");
                    }
                }
                
                // Create the connection
                $this->conn = new PDO($dsn, $this->username, $this->password);
            } else {
                // MySQL connection
                $dsn = "mysql:host={$this->host};dbname={$this->db_name}";
                if (IS_PRODUCTION) {
                    error_log("[Database] Connecting with MySQL DSN: $dsn");
                }
                $this->conn = new PDO($dsn, $this->username, $this->password);
            }
            
            // Set common PDO attributes
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            if ($this->dbType === 'mysql') {
                $this->conn->exec("set names utf8");
            }
            
            if (IS_PRODUCTION) {
                error_log("[Database] Connection successful!");
            }
        } catch(PDOException $e) {
            // Log more detailed error information in production
            if (IS_PRODUCTION) {
                error_log("[Database] Connection Error: " . $e->getMessage());
                error_log("[Database] Error Code: " . $e->getCode());
                
                // Try to retrieve more system information
                error_log("[Database] PHP Version: " . phpversion());
                error_log("[Database] Database extensions loaded: " . implode(", ", get_loaded_extensions()));
            }
            
            // Try to automatically create the database if it doesn't exist
            if ($e->getCode() == 1049 || $e->getCode() == 7) { // MySQL or PostgreSQL code for "Unknown database"
                try {
                    // Connect without specifying a database
                    if ($this->dbType === 'pgsql') {
                        $tempConn = new PDO("pgsql:host={$this->host}", $this->username, $this->password);
                        // In PostgreSQL, we need to check if the database exists first
                        $stmt = $tempConn->query("SELECT 1 FROM pg_database WHERE datname = '{$this->db_name}'");
                        if ($stmt->fetchColumn() === false) {
                            $tempConn->exec("CREATE DATABASE {$this->db_name}");
                        }
                    } else {
                        $tempConn = new PDO("mysql:host={$this->host}", $this->username, $this->password);
                        $tempConn->exec("CREATE DATABASE IF NOT EXISTS {$this->db_name}");
                    }
                    
                    $tempConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // Connect to the newly created database
                    if ($this->dbType === 'pgsql') {
                        $this->conn = new PDO("pgsql:host={$this->host};dbname={$this->db_name}", $this->username, $this->password);
                    } else {
                        $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name}", $this->username, $this->password);
                    }
                    
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                    
                    if ($this->dbType === 'mysql') {
                        $this->conn->exec("set names utf8");
                    }
                    
                    // Create the necessary tables
                    $this->createTables();
                    
                    return $this->conn;
                } catch(PDOException $e2) {
                    $this->logError("Error creating database: " . $e2->getMessage());
                    throw new Exception("Failed to create database. Please check your database server configuration.");
                }
            } else {
                // Log this error - but don't expose database credentials in response
                $this->logError("Database Connection Error: " . $e->getMessage());
                throw new Exception("Database connection failed. Please check your database configuration.");
            }
        }

        return $this->conn;
    }
    
    /**
     * Create necessary database tables
     */
    private function createTables() {
        // Import the SQL file for table creation
        if (file_exists(__DIR__ . '/../db/clothloop_updates.sql')) {
            // Read the SQL file
            $sql = file_get_contents(__DIR__ . '/../db/clothloop_updates.sql');
            
            // If using PostgreSQL, modify MySQL-specific SQL to work with PostgreSQL
            if ($this->dbType === 'pgsql') {
                // Convert MySQL syntax to PostgreSQL
                $sql = $this->convertMySqlToPostgres($sql);
            }
            
            // Execute queries
            $this->conn->exec($sql);
        } else {
            $this->logError("SQL file not found for table creation");
            // Fallback to basic table creation
            $this->createBasicTables();
        }
    }
    
    /**
     * Convert MySQL SQL to PostgreSQL compatible SQL
     * 
     * @param string $sql MySQL SQL queries
     * @return string PostgreSQL compatible SQL
     */
    private function convertMySqlToPostgres($sql) {
        // This is a simplified conversion - in a real app you'd need more comprehensive conversion
        $search = [
            'AUTO_INCREMENT',
            'ENGINE=InnoDB',
            'DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci',
            'INT(',
            'TINYINT(1)',
            'DECIMAL(',
            'TEXT',
            'AUTO_INCREMENT PRIMARY KEY',
            'ON UPDATE CURRENT_TIMESTAMP',
            'ENUM(',
        ];
        
        $replace = [
            '',
            '',
            '',
            'INTEGER',
            'BOOLEAN',
            'NUMERIC(',
            'TEXT',
            'SERIAL PRIMARY KEY',
            '',
            'VARCHAR(255)', // Simplified - in real app you'd create custom types
        ];
        
        $sql = str_replace($search, $replace, $sql);
        
        // Replace MySQL backticks with double quotes for PostgreSQL
        $sql = preg_replace('/`([^`]*)`/', '"$1"', $sql);
        
        return $sql;
    }
    
    /**
     * Create basic tables as a fallback
     */
    private function createBasicTables() {
        if ($this->dbType === 'pgsql') {
            // PostgreSQL syntax
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    phone_no VARCHAR(20),
                    role VARCHAR(20) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    profile_photo VARCHAR(255),
                    status VARCHAR(20) DEFAULT 'active'
                )
            ");
        } else {
            // MySQL syntax
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    phone_no VARCHAR(20) DEFAULT NULL,
                    role ENUM('admin','seller','buyer') NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    profile_photo VARCHAR(255) DEFAULT NULL,
                    status ENUM('active','inactive','suspended') DEFAULT 'active'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }
        
        // Create other basic tables as needed
        // ...
    }
    
    /**
     * Log error message
     * 
     * @param string $message Error message to log
     */
    private function logError($message) {
        // Log error to file if in production, otherwise display
        if (IS_PRODUCTION) {
            error_log($message);
        } else {
            echo "ERROR: " . $message;
        }
    }
    
    /**
     * Get connection (alias for connect)
     * This method is provided for backward compatibility
     * 
     * @return PDO Database connection object
     */
    public function getConnection() {
        return $this->connect();
    }
} 