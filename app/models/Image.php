<?php
require_once __DIR__ . '/../config/database.php';

class Image {
    private $conn;
    private $table_name = "images";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function addImage($product_id, $image_url) {
        $query = "INSERT INTO " . $this->table_name . " (product_id, image_url) VALUES (:product_id, :image_url)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':image_url', $image_url);
        return $stmt->execute();
    }

    public function deleteImagesByProductId($product_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        return $stmt->execute();
    }

    public function getImagesByProductId($product_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function deleteImage($image_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE image_id = :image_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':image_id', $image_id);
        return $stmt->execute();
    }

    public function getImageById($image_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE image_id = :image_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':image_id', $image_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function addMultipleImages($product_id, $image_urls) {
        try {
            $this->conn->beginTransaction();

            foreach ($image_urls as $image_url) {
                $query = "INSERT INTO " . $this->table_name . " (product_id, image_url) VALUES (:product_id, :image_url)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':product_id', $product_id);
                $stmt->bindParam(':image_url', $image_url);
                $stmt->execute();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error adding multiple images: " . $e->getMessage());
            return false;
        }
    }

    public function updateImageOrder($image_id, $order_index) {
        $query = "UPDATE " . $this->table_name . " SET order_index = :order_index WHERE image_id = :image_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_index', $order_index);
        $stmt->bindParam(':image_id', $image_id);
        return $stmt->execute();
    }

    public function getImagesByProductIdOrdered($product_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE product_id = :product_id ORDER BY order_index ASC, image_id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMainImage($product_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE product_id = :product_id ORDER BY order_index ASC, image_id ASC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function countImagesByProductId($product_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row->total;
    }
}