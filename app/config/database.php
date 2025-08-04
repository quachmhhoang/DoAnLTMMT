<?php
class Database{
    private $host = "localhost";
    private $db_name = "web_store";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection(){
        $this->conn = null;
        try{
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);

            // Set charset and collation explicitly
            $this->conn->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Verify connection
            if ($this->conn) {
                error_log("Database connection established successfully");
            }
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            die("Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
        }
        return $this->conn;
    }
}