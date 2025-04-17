<?php
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "clothloop";
    private $conn;

    public function __construct() {
        $this->conn = null;
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            // Set character set
            $this->conn->set_charset("utf8");
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}

/**
 * Get a PDO database connection
 * @return PDO PDO database connection
 */
function getDbConnection() {
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "clothloop";
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $conn;
    } catch(PDOException $e) {
        // Log the error
        error_log("Database connection error: " . $e->getMessage());
        // Return error response
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection failed. Please try again later.'
        ]);
        exit;
    }
}
?> 