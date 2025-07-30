<?php
require_once __DIR__ . '/../config/database.php';

class Category {
    private $conn;
    private $table_name = "categories";

    public $category_id;
    public $name;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Tạo category mới
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (name) VALUES (:name)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        
        if($stmt->execute()) {
            $this->category_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Lấy tất cả categories
    public function getAllCategories() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy category theo ID
    public function getCategoryById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Cập nhật category
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET name = :name WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':category_id', $this->category_id);
        return $stmt->execute();
    }

    // Xóa category
    public function delete($category_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        return $stmt->execute();
    }
}
?>
