<?php

require_once __DIR__ . '/../config/database.php';

class Promotion {
    private $conn;
    private $table_name = "promotions";

    public $promotion_id;
    public $promotion_name;
    public $description;
    public $discount_percent;
    public $start_date;
    public $end_date;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Tạo promotion mới
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (promotion_name, description, discount_percent, start_date, end_date) 
                  VALUES (:promotion_name, :description, :discount_percent, :start_date, :end_date)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':promotion_name', $this->promotion_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':discount_percent', $this->discount_percent);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        
        if($stmt->execute()) {
            $this->promotion_id = $this->conn->lastInsertId();
            return $this->promotion_id;
        }
        return false;
    }

    // Lấy tất cả promotions
    public function getAllPromotions() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY start_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy promotion theo ID
    public function getPromotionById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE promotion_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $this->promotion_id = $row->promotion_id;
            $this->promotion_name = $row->promotion_name;
            $this->description = $row->description;
            $this->discount_percent = $row->discount_percent;
            $this->start_date = $row->start_date;
            $this->end_date = $row->end_date;
            return $row;
        }
        return false;
    }

    // Lấy promotions đang hoạt động
    public function getActivePromotions() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE start_date <= CURDATE() AND end_date >= CURDATE() 
                  ORDER BY start_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Cập nhật promotion
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET promotion_name = :promotion_name, description = :description,
                      discount_percent = :discount_percent, start_date = :start_date, end_date = :end_date
                  WHERE promotion_id = :promotion_id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':promotion_name', $this->promotion_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':discount_percent', $this->discount_percent);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':promotion_id', $this->promotion_id);
        
        return $stmt->execute();
    }

    // Xóa promotion
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE promotion_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Đếm số lượng promotions
    public function countPromotions() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row->total;
    }
}
?>
