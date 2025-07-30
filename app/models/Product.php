<?php
require_once __DIR__ . '/../config/database.php';

class Product {
    private $conn;
    private $table_name = "products";

    public $product_id;
    public $name;
    public $price;
    public $description;
    public $category_id;
    public $brand_id;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Tạo sản phẩm mới
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, price, description, category_id, brand_id) 
                  VALUES (:name, :price, :description, :category_id, :brand_id)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':brand_id', $this->brand_id);
        
        if($stmt->execute()) {
            $this->product_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Lấy tất cả sản phẩm với thông tin category và brand
    public function getAllProducts($limit = null, $offset = null) {
        $query = "SELECT p.*, c.name as category_name, b.brand_name,
                         GROUP_CONCAT(i.image_url) as images
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN brands b ON p.brand_id = b.brand_id
                  LEFT JOIN images i ON p.product_id = i.product_id
                  GROUP BY p.product_id
                  ORDER BY p.product_id DESC";
        
        if($limit) {
            $query .= " LIMIT :limit";
            if($offset) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        
        if($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            if($offset) {
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy sản phẩm theo ID
    public function getProductById($id) {
        $query = "SELECT p.*, c.name as category_name, b.brand_name,
                         GROUP_CONCAT(i.image_url) as images
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN brands b ON p.brand_id = b.brand_id
                  LEFT JOIN images i ON p.product_id = i.product_id
                  WHERE p.product_id = :product_id
                  GROUP BY p.product_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Lấy sản phẩm theo category
    public function getProductsByCategory($category_id, $limit = null) {
        $query = "SELECT p.*, c.name as category_name, b.brand_name,
                         GROUP_CONCAT(i.image_url) as images
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN brands b ON p.brand_id = b.brand_id
                  LEFT JOIN images i ON p.product_id = i.product_id
                  WHERE p.category_id = :category_id
                  GROUP BY p.product_id
                  ORDER BY p.product_id DESC";
        
        if($limit) {
            $query .= " LIMIT :limit";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        
        if($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Tìm kiếm sản phẩm
    public function searchProducts($keyword) {
        $query = "SELECT p.*, c.name as category_name, b.brand_name,
                         GROUP_CONCAT(i.image_url) as images
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN brands b ON p.brand_id = b.brand_id
                  LEFT JOIN images i ON p.product_id = i.product_id
                  WHERE p.name LIKE :keyword OR p.description LIKE :keyword
                  GROUP BY p.product_id
                  ORDER BY p.product_id DESC";
        
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Cập nhật sản phẩm
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, price = :price, description = :description,
                      category_id = :category_id, brand_id = :brand_id
                  WHERE product_id = :product_id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':brand_id', $this->brand_id);
        $stmt->bindParam(':product_id', $this->product_id);
        
        return $stmt->execute();
    }

    // Xóa sản phẩm
    public function delete($product_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        return $stmt->execute();
    }

    // Đếm tổng số sản phẩm
    public function countProducts() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row->total;
    }
}
?>
