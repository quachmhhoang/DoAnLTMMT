<?php
require_once __DIR__ . '/../config/database.php';

class Cart {
    private $conn;
    private $table_name = "carts";
    private $detail_table = "carts_detail";

    public $cart_id;
    public $user_id;
    public $create_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Lấy hoặc tạo giỏ hàng cho user
    public function getOrCreateCart($user_id) {
        // Kiểm tra xem user đã có giỏ hàng chưa
        $query = "SELECT cart_id FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            return $row->cart_id;
        } else {
            // Tạo giỏ hàng mới
            $query = "INSERT INTO " . $this->table_name . " (user_id) VALUES (:user_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            
            if($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
        }
        return false;
    }

    // Thêm sản phẩm vào giỏ hàng
    public function addToCart($user_id, $product_id, $quantity) {
        $cart_id = $this->getOrCreateCart($user_id);
        
        if(!$cart_id) return false;
        
        // Kiểm tra sản phẩm đã có trong giỏ hàng chưa
        $query = "SELECT cart_detail_id, quantity FROM " . $this->detail_table . " 
                  WHERE cart_id = :cart_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cart_id', $cart_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            // Cập nhật số lượng
            $row = $stmt->fetch();
            $new_quantity = $row->quantity + $quantity;
            
            $query = "UPDATE " . $this->detail_table . " 
                      SET quantity = :quantity 
                      WHERE cart_detail_id = :cart_detail_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':quantity', $new_quantity);
            $stmt->bindParam(':cart_detail_id', $row->cart_detail_id);
            return $stmt->execute();
        } else {
            // Thêm sản phẩm mới
            $query = "INSERT INTO " . $this->detail_table . " 
                      (cart_id, product_id, quantity) 
                      VALUES (:cart_id, :product_id, :quantity)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':cart_id', $cart_id);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':quantity', $quantity);
            return $stmt->execute();
        }
    }

    // Lấy chi tiết giỏ hàng
    public function getCartItems($user_id) {
        $cart_id = $this->getOrCreateCart($user_id);
        
        if(!$cart_id) return [];
        
        $query = "SELECT cd.*, p.name, p.price, p.description,
                         (cd.quantity * p.price) as subtotal,
                         MIN(i.image_url) as image_url
                  FROM " . $this->detail_table . " cd
                  JOIN products p ON cd.product_id = p.product_id
                  LEFT JOIN images i ON p.product_id = i.product_id
                  WHERE cd.cart_id = :cart_id
                  GROUP BY cd.cart_detail_id, cd.quantity, cd.cart_id, cd.product_id,
                           p.name, p.price, p.description";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cart_id', $cart_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Cập nhật số lượng sản phẩm trong giỏ hàng
    public function updateQuantity($cart_detail_id, $quantity) {
        $query = "UPDATE " . $this->detail_table . " 
                  SET quantity = :quantity 
                  WHERE cart_detail_id = :cart_detail_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':cart_detail_id', $cart_detail_id);
        return $stmt->execute();
    }

    // Xóa sản phẩm khỏi giỏ hàng
    public function removeFromCart($cart_detail_id) {
        $query = "DELETE FROM " . $this->detail_table . " WHERE cart_detail_id = :cart_detail_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cart_detail_id', $cart_detail_id);
        return $stmt->execute();
    }

    // Tính tổng tiền giỏ hàng
    public function getCartTotal($user_id) {
        $cart_id = $this->getOrCreateCart($user_id);
        
        if(!$cart_id) return 0;
        
        $query = "SELECT SUM(cd.quantity * p.price) as total
                  FROM " . $this->detail_table . " cd
                  JOIN products p ON cd.product_id = p.product_id
                  WHERE cd.cart_id = :cart_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cart_id', $cart_id);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row->total ?? 0;
    }

    // Xóa tất cả sản phẩm trong giỏ hàng
    public function clearCart($user_id) {
        $cart_id = $this->getOrCreateCart($user_id);
        
        if(!$cart_id) return false;
        
        $query = "DELETE FROM " . $this->detail_table . " WHERE cart_id = :cart_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cart_id', $cart_id);
        return $stmt->execute();
    }
}
?>
