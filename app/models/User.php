<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table_name = "users";

    public $user_id;
    public $username;
    public $password;
    public $full_name;
    public $address;
    public $phone;
    public $email;
    public $role;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Đăng ký người dùng mới
    public function register() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, password, full_name, address, phone, email, role) 
                  VALUES (:username, :password, :full_name, :address, :phone, :email, :role)";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash password
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':role', $this->role);
        
        return $stmt->execute();
    }

    // Đăng nhập
    public function login($username, $password) {
        $query = "SELECT user_id, username, password, full_name, role FROM " . $this->table_name . " 
                  WHERE username = :username";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            if(password_verify($password, $row->password)) {
                $this->user_id = $row->user_id;
                $this->username = $row->username;
                $this->full_name = $row->full_name;
                $this->role = $row->role;
                return true;
            }
        }
        return false;
    }

    // Lấy thông tin user theo ID
    public function getUserById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Cập nhật thông tin user
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET full_name = :full_name, address = :address, 
                      phone = :phone, email = :email 
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':user_id', $this->user_id);
        
        return $stmt->execute();
    }

    // Lấy tất cả users (cho admin)
    public function getAllUsers() {
        $query = "SELECT user_id, username, full_name, email, phone, role FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Xóa user
    public function delete($user_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }
}
?>
