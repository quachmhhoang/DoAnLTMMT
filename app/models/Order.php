<?php
require_once __DIR__ . '/../config/database.php';

class Order {
    private $conn;
    private $table_name = "orders";
    private $detail_table = "orders_detail";

    public $order_id;
    public $order_date;
    public $total_amount;
    public $user_id;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Tạo đơn hàng mới
    public function create($user_id, $cart_items) {
        try {
            $this->conn->beginTransaction();
            
            // Tính tổng tiền
            $total = 0;
            foreach($cart_items as $item) {
                $total += $item->quantity * $item->price;
            }
            
            // Tạo order
            $query = "INSERT INTO " . $this->table_name . " 
                      (user_id, total_amount) 
                      VALUES (:user_id, :total_amount)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':total_amount', $total);
            
            if($stmt->execute()) {
                $order_id = $this->conn->lastInsertId();
                
                // Thêm chi tiết đơn hàng
                $detail_query = "INSERT INTO " . $this->detail_table . " 
                                 (order_id, product_id, quantity, total_price) 
                                 VALUES (:order_id, :product_id, :quantity, :total_price)";
                $detail_stmt = $this->conn->prepare($detail_query);
                
                foreach($cart_items as $item) {
                    $item_total = $item->quantity * $item->price;
                    $detail_stmt->bindParam(':order_id', $order_id);
                    $detail_stmt->bindParam(':product_id', $item->product_id);
                    $detail_stmt->bindParam(':quantity', $item->quantity);
                    $detail_stmt->bindParam(':total_price', $item_total);
                    $detail_stmt->execute();
                }
                
                $this->conn->commit();
                return $order_id;
            }
            
            $this->conn->rollBack();
            return false;
            
        } catch(Exception $e) {
            $this->conn->rollBack();
            error_log("Order creation error: " . $e->getMessage());
            return false;
        }
    }

    // Lấy đơn hàng của user
    public function getUserOrders($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  ORDER BY order_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy chi tiết đơn hàng
    public function getOrderDetails($order_id) {
        $query = "SELECT od.*, p.name, p.description, i.image_url
                  FROM " . $this->detail_table . " od
                  JOIN products p ON od.product_id = p.product_id
                  LEFT JOIN images i ON p.product_id = i.product_id
                  WHERE od.order_id = :order_id
                  GROUP BY od.order_detail_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy thông tin đơn hàng
    public function getOrderById($order_id) {
        $query = "SELECT o.*, u.full_name, u.email, u.phone, u.address
                  FROM " . $this->table_name . " o
                  JOIN users u ON o.user_id = u.user_id
                  WHERE o.order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Lấy tất cả đơn hàng (cho admin)
    public function getAllOrders() {
        $query = "SELECT o.*, u.full_name, u.email
                  FROM " . $this->table_name . " o
                  JOIN users u ON o.user_id = u.user_id
                  ORDER BY o.order_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Xóa đơn hàng
    public function delete($order_id) {
        try {
            $this->conn->beginTransaction();
            
            // Xóa chi tiết đơn hàng trước
            $query = "DELETE FROM " . $this->detail_table . " WHERE order_id = :order_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->execute();
            
            // Xóa đơn hàng
            $query = "DELETE FROM " . $this->table_name . " WHERE order_id = :order_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $order_id);
            $result = $stmt->execute();
            
            $this->conn->commit();
            return $result;
            
        } catch(Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>
