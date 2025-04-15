<?php
require_once 'config.php';

/**
 * Database connection class
 */
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    
    private $conn;
    private $error;
    
    /**
     * Constructor - establishes database connection
     */
    public function __construct() {
        // Create connection using MySQLi
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        
        // Check connection
        if ($this->conn->connect_error) {
            $this->error = "Connection failed: " . $this->conn->connect_error;
            die($this->error);
        }
        
        // Set charset to UTF-8
        $this->conn->set_charset("utf8");
    }
    
    /**
     * Execute query
     * @param string $sql - SQL query to execute
     * @return mysqli_result object or false on failure
     */
    public function query($sql) {
        $result = $this->conn->query($sql);
        
        if (!$result) {
            $this->error = "Query failed: " . $this->conn->error;
            die($this->error);
        }
        
        return $result;
    }
    
    /**
     * Execute prepared statement
     * @param string $sql - SQL query with placeholders
     * @param string $types - Types of parameters (s:string, i:integer, d:double, b:blob)
     * @param array $params - Parameters to bind
     * @return mysqli_stmt object or false on failure
     */
    public function prepare($sql, $types = "", $params = []) {
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            $this->error = "Prepare failed: " . $this->conn->error;
            die($this->error);
        }
        
        // Bind parameters if provided
        if (!empty($params) && !empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        
        return $stmt;
    }
    
    /**
     * Get the last inserted ID
     * @return int - Last inserted ID
     */
    public function getLastInsertId() {
        return $this->conn->insert_id;
    }
    
    /**
     * Escape string for SQL injection prevention
     * @param string $string - String to escape
     * @return string - Escaped string
     */
    public function escapeString($string) {
        return $this->conn->real_escape_string($string);
    }
    
    /**
     * Get the number of affected rows
     * @return int - Number of affected rows
     */
    public function getAffectedRows() {
        return $this->conn->affected_rows;
    }
    
    /**
     * Close database connection
     */
    public function close() {
        $this->conn->close();
    }
    
    /**
     * Get error message
     * @return string - Error message
     */
    public function getError() {
        return $this->error;
    }
}

// Create a global database object
$db = new Database();
?>
