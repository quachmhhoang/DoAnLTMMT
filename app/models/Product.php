<?php
// Import file cấu hình database để kết nối CSDL
require_once __DIR__ . '/../config/database.php';

/**
 * Class Product - Model xử lý dữ liệu sản phẩm
 * Chứa các phương thức CRUD và các thao tác liên quan đến sản phẩm
 */
class Product {
    // Kết nối database
    private $conn;
    // Tên bảng trong database
    private $table_name = "products";

    // Các thuộc tính của sản phẩm (tương ứng với các cột trong bảng)
    public $product_id;      // ID sản phẩm (khóa chính)
    public $name;            // Tên sản phẩm
    public $price;           // Giá sản phẩm
    public $description;     // Mô tả sản phẩm
    public $category_id;     // ID danh mục (khóa ngoại)
    public $brand_id;        // ID thương hiệu (khóa ngoại)

    /**
     * Constructor - Khởi tạo kết nối database
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Tạo sản phẩm mới trong database
     * @return int|false ID của sản phẩm vừa tạo hoặc false nếu thất bại
     */
    public function create() {
        // Câu SQL INSERT để thêm sản phẩm mới
        $query = "INSERT INTO " . $this->table_name . "
                  (name, price, description, category_id, brand_id)
                  VALUES (:name, :price, :description, :category_id, :brand_id)";

        // Chuẩn bị câu SQL
        $stmt = $this->conn->prepare($query);

        // Bind các tham số với giá trị từ thuộc tính object
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':brand_id', $this->brand_id);

        // Thực thi câu SQL
        if($stmt->execute()) {
            // Lấy ID của record vừa insert và gán vào thuộc tính
            $this->product_id = $this->conn->lastInsertId();
            return $this->product_id;
        }
        return false;
    }

    /**
     * Lấy tất cả sản phẩm với thông tin category và brand
     * @param int|null $limit Giới hạn số lượng record trả về
     * @param int|null $offset Vị trí bắt đầu lấy record (cho phân trang)
     * @return array Mảng chứa thông tin tất cả sản phẩm
     */
    public function getAllProducts($limit = null, $offset = null) {
        // Câu SQL JOIN để lấy sản phẩm kèm thông tin danh mục, thương hiệu và hình ảnh
        $query = "SELECT p.*, c.name as category_name, b.brand_name,
                         GROUP_CONCAT(i.image_url) as images
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN brands b ON p.brand_id = b.brand_id
                  LEFT JOIN images i ON p.product_id = i.product_id
                  GROUP BY p.product_id, p.name, p.price, p.description, p.category_id, p.brand_id,
                           c.name, b.brand_name
                  ORDER BY p.product_id DESC";

        // Thêm LIMIT và OFFSET nếu được cung cấp (cho phân trang)
        if($limit) {
            $query .= " LIMIT :limit";
            if($offset) {
                $query .= " OFFSET :offset";
            }
        }

        // Chuẩn bị câu SQL
        $stmt = $this->conn->prepare($query);

        // Bind tham số LIMIT và OFFSET nếu có
        if($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            if($offset) {
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
        }

        // Thực thi và trả về tất cả kết quả
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Lấy thông tin sản phẩm theo ID
     * @param int $id ID của sản phẩm cần lấy
     * @return object|false Thông tin sản phẩm hoặc false nếu không tìm thấy
     */
    public function getProductById($id) {
        // Câu SQL JOIN để lấy sản phẩm kèm thông tin danh mục, thương hiệu và hình ảnh
        $query = "SELECT p.*, c.name as category_name, b.brand_name,
                         GROUP_CONCAT(i.image_url) as images
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN brands b ON p.brand_id = b.brand_id
                  LEFT JOIN images i ON p.product_id = i.product_id
                  WHERE p.product_id = :product_id
                  GROUP BY p.product_id, p.name, p.price, p.description, p.category_id, p.brand_id,
                           c.name, b.brand_name";

        // Chuẩn bị và thực thi câu SQL
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $id);
        $stmt->execute();
        return $stmt->fetch();  // Trả về 1 record duy nhất
    }

    /**
     * Lấy danh sách sản phẩm theo danh mục
     * @param int $category_id ID của danh mục
     * @param int|null $limit Giới hạn số lượng sản phẩm trả về
     * @return array Mảng chứa các sản phẩm thuộc danh mục
     */
    public function getProductsByCategory($category_id, $limit = null) {
        // Câu SQL JOIN để lấy sản phẩm theo danh mục
        $query = "SELECT p.*, c.name as category_name, b.brand_name,
                         GROUP_CONCAT(i.image_url) as images
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN brands b ON p.brand_id = b.brand_id
                  LEFT JOIN images i ON p.product_id = i.product_id
                  WHERE p.category_id = :category_id
                  GROUP BY p.product_id, p.name, p.price, p.description, p.category_id, p.brand_id,
                           c.name, b.brand_name
                  ORDER BY p.product_id DESC";

        // Thêm LIMIT nếu được cung cấp
        if($limit) {
            $query .= " LIMIT :limit";
        }

        // Chuẩn bị câu SQL và bind tham số
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id);

        if($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }

        // Thực thi và trả về kết quả
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Tìm kiếm sản phẩm theo từ khóa
     * @param string $keyword Từ khóa tìm kiếm
     * @return array Mảng chứa các sản phẩm phù hợp
     */
    public function searchProducts($keyword) {
        // Câu SQL tìm kiếm trong tên và mô tả sản phẩm
        $query = "SELECT p.*, c.name as category_name, b.brand_name,
                         GROUP_CONCAT(i.image_url) as images
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN brands b ON p.brand_id = b.brand_id
                  LEFT JOIN images i ON p.product_id = i.product_id
                  WHERE p.name LIKE :keyword OR p.description LIKE :keyword
                  GROUP BY p.product_id, p.name, p.price, p.description, p.category_id, p.brand_id,
                           c.name, b.brand_name
                  ORDER BY p.product_id DESC";

        // Chuẩn bị câu SQL
        $stmt = $this->conn->prepare($query);
        // Thêm ký tự % để tìm kiếm LIKE (tìm kiếm gần đúng)
        $keyword = "%{$keyword}%";
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Cập nhật thông tin sản phẩm
     * @return bool true nếu cập nhật thành công, false nếu thất bại
     */
    public function update() {
        // Câu SQL UPDATE để cập nhật thông tin sản phẩm
        $query = "UPDATE " . $this->table_name . "
                  SET name = :name, price = :price, description = :description,
                      category_id = :category_id, brand_id = :brand_id
                  WHERE product_id = :product_id";

        // Chuẩn bị câu SQL
        $stmt = $this->conn->prepare($query);

        // Bind các tham số với giá trị từ thuộc tính object
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':brand_id', $this->brand_id);
        $stmt->bindParam(':product_id', $this->product_id);

        // Thực thi và trả về kết quả
        return $stmt->execute();
    }

    /**
     * Xóa sản phẩm và tất cả dữ liệu liên quan
     * Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
     * @param int $product_id ID của sản phẩm cần xóa
     * @return bool true nếu xóa thành công, false nếu thất bại
     */
    public function delete($product_id) {
        try {
            // Bắt đầu transaction để đảm bảo tất cả thao tác thành công hoặc rollback
            $this->conn->beginTransaction();

            // Xóa từ bảng carts_detail trước (do ràng buộc khóa ngoại)
            $query = "DELETE FROM carts_detail WHERE product_id = :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();

            // Xóa từ bảng order_details nếu tồn tại
            $query = "DELETE FROM order_details WHERE product_id = :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();

            // Xóa hình ảnh sản phẩm
            $query = "DELETE FROM images WHERE product_id = :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();

            // Cuối cùng xóa sản phẩm chính
            $query = "DELETE FROM " . $this->table_name . " WHERE product_id = :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $product_id);
            $result = $stmt->execute();

            // Commit transaction nếu tất cả thao tác thành công
            $this->conn->commit();
            return $result;

        } catch (Exception $e) {
            // Rollback transaction nếu có lỗi xảy ra
            $this->conn->rollBack();
            error_log("Error deleting product: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Đếm tổng số sản phẩm trong hệ thống
     * @return int Tổng số sản phẩm
     */
    public function countProducts() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row->total;
    }

    /**
     * Kiểm tra xem sản phẩm có thể xóa được không
     * Sản phẩm không thể xóa nếu đang có trong đơn hàng đang xử lý
     * @param int $product_id ID của sản phẩm cần kiểm tra
     * @return bool true nếu có thể xóa, false nếu không thể xóa
     */
    public function canDelete($product_id) {
        // Đếm số đơn hàng đang xử lý có chứa sản phẩm này
        $query = "SELECT COUNT(*) as count FROM order_details od
                  INNER JOIN orders o ON od.order_id = o.order_id
                  WHERE od.product_id = :product_id
                  AND o.status IN ('pending', 'processing', 'confirmed')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $row = $stmt->fetch();
        // Trả về true nếu không có đơn hàng nào đang xử lý
        return $row->count == 0;
    }

    /**
     * Lấy thông tin chi tiết về các ràng buộc của sản phẩm trước khi xóa
     * Kiểm tra số lượng trong giỏ hàng, đơn hàng, hình ảnh liên quan
     * @param int $product_id ID của sản phẩm cần kiểm tra
     * @return array Mảng chứa thông tin chi tiết về ràng buộc
     */
    public function getDeletionInfo($product_id) {
        // Khởi tạo mảng thông tin với giá trị mặc định
        $info = [
            'can_delete' => true,        // Có thể xóa hay không
            'cart_items' => 0,           // Số lượng trong giỏ hàng
            'pending_orders' => 0,       // Số đơn hàng đang xử lý
            'completed_orders' => 0,     // Số đơn hàng đã hoàn thành
            'images' => 0                // Số hình ảnh
        ];

        // Đếm số lượng sản phẩm trong giỏ hàng của các user
        $query = "SELECT COUNT(*) as count FROM carts_detail WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $row = $stmt->fetch();
        $info['cart_items'] = $row->count;

        // Đếm số đơn hàng đang xử lý có chứa sản phẩm này
        $query = "SELECT COUNT(*) as count FROM order_details od
                  INNER JOIN orders o ON od.order_id = o.order_id
                  WHERE od.product_id = :product_id
                  AND o.status IN ('pending', 'processing', 'confirmed')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $row = $stmt->fetch();
        $info['pending_orders'] = $row->count;

        // Đếm số đơn hàng đã hoàn thành có chứa sản phẩm này
        $query = "SELECT COUNT(*) as count FROM order_details od
                  INNER JOIN orders o ON od.order_id = o.order_id
                  WHERE od.product_id = :product_id
                  AND o.status IN ('completed', 'delivered')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $row = $stmt->fetch();
        $info['completed_orders'] = $row->count;

        // Đếm số hình ảnh của sản phẩm
        $query = "SELECT COUNT(*) as count FROM images WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $row = $stmt->fetch();
        $info['images'] = $row->count;

        // Quy tắc: Không thể xóa nếu có đơn hàng đang xử lý
        if ($info['pending_orders'] > 0) {
            $info['can_delete'] = false;
        }

        return $info;
    }
}
?>
