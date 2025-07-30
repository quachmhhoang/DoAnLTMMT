<?php
require_once __DIR__ . '/../config/database.php';

class Brand {
    private $conn;
    private $table_name = "brands";

    public $brand_id;
    public $brand_name;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Tạo brand mới
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (brand_name) VALUES (:brand_name)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':brand_name', $this->brand_name);
        
        if($stmt->execute()) {
            $this->brand_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Lấy tất cả brands
    public function getAllBrands() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY brand_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy brand theo ID
    public function getBrandById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE brand_id = :brand_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':brand_id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Cập nhật brand
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET brand_name = :brand_name WHERE brand_id = :brand_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':brand_name', $this->brand_name);
        $stmt->bindParam(':brand_id', $this->brand_id);
        return $stmt->execute();
    }

    // Xóa brand
    public function delete($brand_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE brand_id = :brand_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':brand_id', $brand_id);
        return $stmt->execute();
    }
}
?>
