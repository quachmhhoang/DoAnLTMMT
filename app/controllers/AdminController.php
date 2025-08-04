<?php
// Import các model cần thiết cho AdminController
require_once __DIR__ . '/../models/Product.php';      // Model quản lý sản phẩm
require_once __DIR__ . '/../models/Category.php';     // Model quản lý danh mục
require_once __DIR__ . '/../models/Brand.php';        // Model quản lý thương hiệu
require_once __DIR__ . '/../models/Order.php';        // Model quản lý đơn hàng
require_once __DIR__ . '/../models/User.php';         // Model quản lý người dùng
require_once __DIR__ . '/../models/Promotion.php';    // Model quản lý khuyến mãi
require_once __DIR__ . '/../models/Image.php';        // Model quản lý hình ảnh
require_once __DIR__ . '/../helpers/SessionHelper.php';  // Helper xử lý session và flash messages
require_once __DIR__ . '/../services/NotificationService.php';  // Service xử lý thông báo

/**
 * AdminController - Controller chính xử lý tất cả các chức năng quản trị
 * Bao gồm: quản lý sản phẩm, danh mục, thương hiệu, đơn hàng, người dùng, khuyến mãi
 */
class AdminController {

    /**
     * Hiển thị trang dashboard admin với các thống kê tổng quan
     * @return void
     */
    public function dashboard() {
        // Khởi tạo các model cần thiết để lấy dữ liệu thống kê
        $productModel = new Product();              // Model sản phẩm
        $orderModel = new Order();                  // Model đơn hàng
        $userModel = new User();                    // Model người dùng
        $promotionModel = new Promotion();          // Model khuyến mãi
        $notificationService = new NotificationService();  // Service thông báo

        // Thu thập dữ liệu thống kê từ các model
        $totalProducts = $productModel->countProducts();        // Đếm tổng số sản phẩm
        $totalOrders = count($orderModel->getAllOrders());      // Đếm tổng số đơn hàng
        $totalUsers = count($userModel->getAllUsers());         // Đếm tổng số người dùng
        $totalPromotions = $promotionModel->countPromotions();  // Đếm tổng số khuyến mãi

        // Lấy thống kê thông báo từ service
        $notificationStats = $notificationService->getNotificationStats();

        // Lấy 5 đơn hàng gần đây nhất để hiển thị trên dashboard
        $recentOrders = array_slice($orderModel->getAllOrders(), 0, 5);

        // Include view dashboard với dữ liệu đã chuẩn bị
        include __DIR__ . '/../views/admin/dashboard.php';
    }

    /**
     * Hiển thị danh sách tất cả sản phẩm trong hệ thống
     * @return void
     */
    public function products() {
        // Khởi tạo model sản phẩm
        $productModel = new Product();
        // Lấy tất cả sản phẩm từ database
        $products = $productModel->getAllProducts();

        // Include view hiển thị danh sách sản phẩm
        include __DIR__ . '/../views/admin/products/index.php';
    }

    /**
     * Xử lý thêm sản phẩm mới
     * GET: Hiển thị form thêm sản phẩm
     * POST: Xử lý dữ liệu form và tạo sản phẩm mới
     * @return void
     */
    public function addProduct() {
        // Khởi tạo các model cần thiết để lấy dữ liệu cho form
        $categoryModel = new Category();
        $brandModel = new Brand();

        // Lấy danh sách tất cả danh mục và thương hiệu cho dropdown
        $categories = $categoryModel->getAllCategories();
        $brands = $brandModel->getAllBrands();

        // Kiểm tra nếu là POST request (submit form)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lấy và làm sạch dữ liệu từ form
            $name = trim($_POST['name'] ?? '');                    // Tên sản phẩm
            $price = (float)($_POST['price'] ?? 0);                // Giá sản phẩm (chuyển sang float)
            $description = trim($_POST['description'] ?? '');      // Mô tả sản phẩm
            $category_id = (int)($_POST['category_id'] ?? 0);      // ID danh mục (chuyển sang int)
            $brand_id = (int)($_POST['brand_id'] ?? 0);            // ID thương hiệu (chuyển sang int)

            // Validate dữ liệu đầu vào
            if (empty($name) || $price <= 0 || !$category_id || !$brand_id) {
                // Nếu thiếu thông tin, set flash message lỗi và redirect
                SessionHelper::setFlash('error', 'Vui lòng nhập đầy đủ thông tin!');
                header('Location: /admin/products/add');
                exit();
            }

            // Tạo object sản phẩm mới và gán dữ liệu
            $product = new Product();
            $product->name = $name;
            $product->price = $price;
            $product->description = $description;
            $product->category_id = $category_id;
            $product->brand_id = $brand_id;

            // Thực hiện tạo sản phẩm trong database
            $productId = $product->create();
            if ($productId) {
                // Nếu tạo thành công, xử lý upload hình ảnh
                $this->handleImageUploads($productId);

                // Gửi thông báo sản phẩm mới đến người dùng
                try {
                    $notificationService = new NotificationService();
                    $notificationService->notifyProductAdded($productId, $name);
                } catch (Exception $e) {
                    // Log lỗi nếu không gửi được thông báo (không làm gián đoạn flow chính)
                    error_log("Failed to send product notification: " . $e->getMessage());
                }

                // Set flash message thành công và redirect về danh sách sản phẩm
                SessionHelper::setFlash('success', 'Thêm sản phẩm thành công!');
                header('Location: /admin/products');
                exit();
            } else {
                // Nếu tạo thất bại, set flash message lỗi
                SessionHelper::setFlash('error', 'Có lỗi xảy ra khi thêm sản phẩm!');
            }
        }

        // Include view form thêm sản phẩm (cho GET request hoặc khi có lỗi)
        include __DIR__ . '/../views/admin/products/add.php';
    }

    /**
     * Xử lý upload nhiều hình ảnh cho sản phẩm
     * @param int $productId ID của sản phẩm cần upload hình ảnh
     * @return bool true nếu thành công, false nếu thất bại
     */
    private function handleImageUploads($productId) {
        // Kiểm tra xem có file images được upload không
        if (!isset($_FILES['images']) || !is_array($_FILES['images']['name'])) {
            return false;
        }

        // Đường dẫn thư mục lưu trữ hình ảnh
        $uploadDir = __DIR__ . '/../../assets/images/';
        // Tạo thư mục nếu chưa tồn tại
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);  // 0755: quyền đọc/ghi/thực thi cho owner, đọc/thực thi cho group và other
        }

        // Khởi tạo model Image và các biến theo dõi
        $imageModel = new Image();
        $uploadedImages = [];    // Mảng chứa đường dẫn các file đã upload thành công
        $uploadCount = 0;        // Đếm số file upload thành công

        // Duyệt qua từng file được upload
        for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
            // Kiểm tra file upload thành công (không có lỗi)
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                // Lấy thông tin file
                $fileName = $_FILES['images']['name'][$i];      // Tên file gốc
                $tmpName = $_FILES['images']['tmp_name'][$i];   // Đường dẫn file tạm
                $fileSize = $_FILES['images']['size'][$i];      // Kích thước file

                // Validate định dạng file
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
                $fileType = mime_content_type($tmpName);  // Lấy MIME type thực tế của file

                // Kiểm tra định dạng file có được phép không
                if (!in_array($fileType, $allowedTypes)) {
                    SessionHelper::setFlash('warning', "File {$fileName} không đúng định dạng. Chỉ chấp nhận JPG, PNG, WebP.");
                    continue;  // Bỏ qua file này, tiếp tục với file tiếp theo
                }

                // Kiểm tra kích thước file (tối đa 5MB)
                if ($fileSize > 5 * 1024 * 1024) { // 5MB = 5 * 1024 * 1024 bytes
                    SessionHelper::setFlash('warning', "File {$fileName} quá lớn. Tối đa 5MB.");
                    continue;  // Bỏ qua file này, tiếp tục với file tiếp theo
                }

                // Tạo tên file mới để tránh trùng lặp
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);  // Lấy phần mở rộng
                $newFileName = uniqid() . '_' . time() . '_' . $i . '.' . $extension;  // Tạo tên unique
                $uploadPath = $uploadDir . $newFileName;  // Đường dẫn đầy đủ để lưu file

                // Di chuyển file từ thư mục tạm đến thư mục đích
                if (move_uploaded_file($tmpName, $uploadPath)) {
                    // Thêm đường dẫn relative vào mảng (để lưu vào database)
                    $uploadedImages[] = '/assets/images/' . $newFileName;
                    $uploadCount++;  // Tăng counter
                }
            }
        }

        // Lưu tất cả hình ảnh vào database cùng lúc
        if (!empty($uploadedImages)) {
            if ($imageModel->addMultipleImages($productId, $uploadedImages)) {
                SessionHelper::setFlash('success', "Đã tải lên {$uploadCount} hình ảnh thành công!");
                return true;
            } else {
                SessionHelper::setFlash('error', 'Có lỗi xảy ra khi lưu hình ảnh vào database!');
                return false;
            }
        }

        return true;  // Trả về true ngay cả khi không có file nào được upload
    }

    /**
     * Xử lý chỉnh sửa sản phẩm
     * GET: Hiển thị form chỉnh sửa với dữ liệu hiện tại
     * POST: Xử lý cập nhật thông tin sản phẩm
     * @return void
     */
    public function editProduct() {
        // Lấy product_id từ URL parameters
        $product_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;

        // Kiểm tra product_id hợp lệ
        if (!$product_id) {
            header('Location: /admin/products');
            exit();
        }

        // Khởi tạo các model cần thiết
        $productModel = new Product();
        $categoryModel = new Category();
        $brandModel = new Brand();
        $imageModel = new Image();

        // Lấy thông tin sản phẩm và dữ liệu liên quan
        $product = $productModel->getProductById($product_id);        // Thông tin sản phẩm hiện tại
        $categories = $categoryModel->getAllCategories();             // Danh sách danh mục cho dropdown
        $brands = $brandModel->getAllBrands();                       // Danh sách thương hiệu cho dropdown
        $images = $imageModel->getImagesByProductId($product_id);     // Hình ảnh hiện tại của sản phẩm

        // Kiểm tra sản phẩm có tồn tại không
        if (!$product) {
            header('Location: /admin/products');
            exit();
        }

        // Xử lý POST request (submit form chỉnh sửa)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lấy và làm sạch dữ liệu từ form
            $name = trim($_POST['name'] ?? '');                    // Tên sản phẩm mới
            $price = (float)($_POST['price'] ?? 0);                // Giá mới
            $description = trim($_POST['description'] ?? '');      // Mô tả mới
            $category_id = (int)($_POST['category_id'] ?? 0);      // Danh mục mới
            $brand_id = (int)($_POST['brand_id'] ?? 0);            // Thương hiệu mới

            // Validate dữ liệu đầu vào
            if (empty($name) || $price <= 0 || !$category_id || !$brand_id) {
                SessionHelper::setFlash('error', 'Vui lòng nhập đầy đủ thông tin!');
                header('Location: /admin/products/edit/' . $product_id);
                exit();
            }

            // Gán dữ liệu mới cho model
            $productModel->product_id = $product_id;
            $productModel->name = $name;
            $productModel->price = $price;
            $productModel->description = $description;
            $productModel->category_id = $category_id;
            $productModel->brand_id = $brand_id;

            // Thực hiện cập nhật trong database
            if ($productModel->update()) {
                // Xử lý upload hình ảnh mới (nếu có)
                $this->handleImageUploads($product_id);

                // Set flash message thành công và redirect
                SessionHelper::setFlash('success', 'Cập nhật sản phẩm thành công!');
                header('Location: /admin/products');
                exit();
            } else {
                // Set flash message lỗi nếu cập nhật thất bại
                SessionHelper::setFlash('error', 'Có lỗi xảy ra khi cập nhật sản phẩm!');
            }
        }

        // Include view form chỉnh sửa (cho GET request hoặc khi có lỗi)
        include __DIR__ . '/../views/admin/products/edit.php';
    }

    /**
     * Xử lý xóa sản phẩm
     * Kiểm tra điều kiện xóa, xóa file hình ảnh và xóa sản phẩm khỏi database
     * @return void
     */
    public function deleteProduct() {
        // Lấy product_id từ URL parameters
        $product_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;

        // Kiểm tra product_id hợp lệ
        if (!$product_id) {
            header('Location: /admin/products');
            exit();
        }

        // Khởi tạo các model cần thiết
        $productModel = new Product();
        $imageModel = new Image();

        // Kiểm tra xem sản phẩm có thể xóa an toàn không
        $deletionInfo = $productModel->getDeletionInfo($product_id);

        // Nếu không thể xóa (có đơn hàng đang xử lý)
        if (!$deletionInfo['can_delete']) {
            $message = 'Không thể xóa sản phẩm này vì:';
            if ($deletionInfo['pending_orders'] > 0) {
                $message .= " Có {$deletionInfo['pending_orders']} đơn hàng đang xử lý.";
            }
            SessionHelper::setFlash('error', $message);
            header('Location: /admin/products');
            exit();
        }

        // Lấy danh sách hình ảnh trước khi xóa để xóa file vật lý
        $images = $imageModel->getImagesByProductId($product_id);

        // Thực hiện xóa sản phẩm từ database
        if ($productModel->delete($product_id)) {
            // Xóa các file hình ảnh vật lý khỏi server
            foreach ($images as $image) {
                // Tạo đường dẫn đầy đủ đến file hình ảnh
                $imagePath = __DIR__ . '/../../' . ltrim($image->image_url, '/');
                // Kiểm tra file tồn tại và xóa
                if (file_exists($imagePath)) {
                    unlink($imagePath);  // Xóa file vật lý
                }
            }

            // Tạo thông báo thành công với thông tin chi tiết
            $message = 'Xóa sản phẩm thành công!';
            if ($deletionInfo['cart_items'] > 0) {
                $message .= " Đã xóa {$deletionInfo['cart_items']} mục trong giỏ hàng.";
            }
            if ($deletionInfo['images'] > 0) {
                $message .= " Đã xóa {$deletionInfo['images']} hình ảnh.";
            }

            SessionHelper::setFlash('success', $message);
        } else {
            // Thông báo lỗi nếu xóa thất bại
            SessionHelper::setFlash('error', 'Có lỗi xảy ra khi xóa sản phẩm!');
        }

        // Redirect về danh sách sản phẩm
        header('Location: /admin/products');
        exit();
    }

    /**
     * Hiển thị danh sách tất cả danh mục
     * @return void
     */
    public function categories() {
        // Khởi tạo model danh mục
        $categoryModel = new Category();
        // Lấy tất cả danh mục từ database
        $categories = $categoryModel->getAllCategories();

        // Include view hiển thị danh sách danh mục
        include __DIR__ . '/../views/admin/categories/index.php';
    }

    /**
     * Xử lý thêm danh mục mới
     * GET: Hiển thị form thêm danh mục
     * POST: Xử lý dữ liệu form và tạo danh mục mới
     * @return void
     */
    public function addCategory() {
        // Kiểm tra nếu là POST request (submit form)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lấy và làm sạch tên danh mục từ form
            $name = trim($_POST['name'] ?? '');

            // Validate dữ liệu đầu vào
            if (empty($name)) {
                SessionHelper::setFlash('error', 'Tên danh mục không được để trống!');
                header('Location: /admin/categories/add');
                exit();
            }

            // Tạo object danh mục mới và gán dữ liệu
            $category = new Category();
            $category->name = $name;

            // Thực hiện tạo danh mục trong database
            if ($category->create()) {
                SessionHelper::setFlash('success', 'Thêm danh mục thành công!');
                header('Location: /admin/categories');
                exit();
            } else {
                SessionHelper::setFlash('error', 'Có lỗi xảy ra khi thêm danh mục!');
            }
        }

        // Include view form thêm danh mục (cho GET request hoặc khi có lỗi)
        include __DIR__ . '/../views/admin/categories/add.php';
    }

    /**
     * Hiển thị danh sách tất cả thương hiệu
     * @return void
     */
    public function brands() {
        // Khởi tạo model thương hiệu
        $brandModel = new Brand();
        // Lấy tất cả thương hiệu từ database
        $brands = $brandModel->getAllBrands();

        // Include view hiển thị danh sách thương hiệu
        include __DIR__ . '/../views/admin/brands/index.php';
    }

    /**
     * Xử lý thêm thương hiệu mới
     * GET: Hiển thị form thêm thương hiệu
     * POST: Xử lý dữ liệu form và tạo thương hiệu mới
     * @return void
     */
    public function addBrand() {
        // Kiểm tra nếu là POST request (submit form)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lấy và làm sạch tên thương hiệu từ form
            $brand_name = trim($_POST['brand_name'] ?? '');

            // Validate dữ liệu đầu vào
            if (empty($brand_name)) {
                SessionHelper::setFlash('error', 'Tên thương hiệu không được để trống!');
                header('Location: /admin/brands/add');
                exit();
            }

            // Tạo object thương hiệu mới và gán dữ liệu
            $brand = new Brand();
            $brand->brand_name = $brand_name;

            // Thực hiện tạo thương hiệu trong database
            if ($brand->create()) {
                SessionHelper::setFlash('success', 'Thêm thương hiệu thành công!');
                header('Location: /admin/brands');
                exit();
            } else {
                SessionHelper::setFlash('error', 'Có lỗi xảy ra khi thêm thương hiệu!');
            }
        }

        // Include view form thêm thương hiệu (cho GET request hoặc khi có lỗi)
        include __DIR__ . '/../views/admin/brands/add.php';
    }

    /**
     * Hiển thị danh sách tất cả khuyến mãi
     * @return void
     */
    public function promotions() {
        // Khởi tạo model khuyến mãi
        $promotionModel = new Promotion();
        // Lấy tất cả khuyến mãi từ database
        $promotions = $promotionModel->getAllPromotions();

        // Include view hiển thị danh sách khuyến mãi
        include __DIR__ . '/../views/admin/promotions/index.php';
    }

    /**
     * Xử lý thêm khuyến mãi mới
     * GET: Hiển thị form thêm khuyến mãi
     * POST: Xử lý dữ liệu form và tạo khuyến mãi mới
     * @return void
     */
    public function addPromotion() {
        // Kiểm tra nếu là POST request (submit form)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lấy và làm sạch dữ liệu từ form
            $promotion_name = trim($_POST['promotion_name'] ?? '');        // Tên khuyến mãi
            $description = trim($_POST['description'] ?? '');             // Mô tả khuyến mãi
            $discount_percent = (float)($_POST['discount_percent'] ?? 0);  // Phần trăm giảm giá
            $start_date = $_POST['start_date'] ?? '';                     // Ngày bắt đầu
            $end_date = $_POST['end_date'] ?? '';                         // Ngày kết thúc

            // Validate dữ liệu đầu vào
            if (empty($promotion_name) || $discount_percent <= 0 || empty($start_date) || empty($end_date)) {
                SessionHelper::setFlash('error', 'Vui lòng nhập đầy đủ thông tin!');
                header('Location: /admin/promotions/add');
                exit();
            }

            // Kiểm tra logic ngày: ngày kết thúc phải sau ngày bắt đầu
            if (strtotime($end_date) <= strtotime($start_date)) {
                SessionHelper::setFlash('error', 'Ngày kết thúc phải sau ngày bắt đầu!');
                header('Location: /admin/promotions/add');
                exit();
            }

            // Tạo object khuyến mãi mới và gán dữ liệu
            $promotion = new Promotion();
            $promotion->promotion_name = $promotion_name;
            $promotion->description = $description;
            $promotion->discount_percent = $discount_percent;
            $promotion->start_date = $start_date;
            $promotion->end_date = $end_date;

            // Gán người tạo khuyến mãi (admin hiện tại)
            $currentUser = SessionHelper::getCurrentUser();
            $promotion->created_by = $currentUser ? $currentUser->user_id : null;

            // Thực hiện tạo khuyến mãi trong database
            $promotionId = $promotion->create();
            if ($promotionId) {
                // Model Promotion tự động tạo notification record
                // Bây giờ chỉ cần gửi WebSocket notification cho real-time updates
                try {
                    $notificationService = new NotificationService();

                    // Gửi WebSocket notification cho khuyến mãi mới
                    // Sẽ lấy notification từ database và gửi qua WebSocket
                    $notificationService->sendPromotionWebSocketNotification($promotionId);
                } catch (Exception $e) {
                    // Log lỗi nếu không gửi được real-time notification (không làm gián đoạn flow chính)
                    error_log("Failed to send real-time promotion notification: " . $e->getMessage());
                }

                SessionHelper::setFlash('success', 'Thêm khuyến mãi thành công!');
                header('Location: /admin/promotions');
                exit();
            } else {
                SessionHelper::setFlash('error', 'Có lỗi xảy ra khi thêm khuyến mãi!');
            }
        }

        // Include view form thêm khuyến mãi (cho GET request hoặc khi có lỗi)
        include __DIR__ . '/../views/admin/promotions/add.php';
    }

    /**
     * Xử lý xóa khuyến mãi
     * @return void
     */
    public function deletePromotion() {
        // Lấy promotion_id từ URL parameters
        $promotion_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;

        // Kiểm tra promotion_id hợp lệ
        if (!$promotion_id) {
            header('Location: /admin/promotions');
            exit();
        }

        // Khởi tạo model khuyến mãi
        $promotionModel = new Promotion();

        // Thực hiện xóa khuyến mãi từ database
        if ($promotionModel->delete($promotion_id)) {
            SessionHelper::setFlash('success', 'Xóa khuyến mãi thành công!');
        } else {
            SessionHelper::setFlash('error', 'Có lỗi xảy ra khi xóa khuyến mãi!');
        }

        // Redirect về danh sách khuyến mãi
        header('Location: /admin/promotions');
        exit();
    }

    /**
     * Hiển thị danh sách tất cả đơn hàng
     * @return void
     */
    public function orders() {
        // Khởi tạo model đơn hàng
        $orderModel = new Order();
        // Lấy tất cả đơn hàng từ database
        $orders = $orderModel->getAllOrders();

        // Include view hiển thị danh sách đơn hàng
        include __DIR__ . '/../views/admin/orders/index.php';
    }

    /**
     * Xử lý xóa đơn hàng
     * @return void
     */
    public function deleteOrder() {
        // Lấy order_id từ URL parameters
        $order_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;

        // Kiểm tra order_id hợp lệ
        if (!$order_id) {
            header('Location: /admin/orders');
            exit();
        }

        // Khởi tạo model đơn hàng
        $orderModel = new Order();

        // Thực hiện xóa đơn hàng từ database
        if ($orderModel->delete($order_id)) {
            SessionHelper::setFlash('success', 'Xóa đơn hàng thành công!');
        } else {
            SessionHelper::setFlash('error', 'Có lỗi xảy ra khi xóa đơn hàng!');
        }

        // Redirect về danh sách đơn hàng
        header('Location: /admin/orders');
        exit();
    }

    /**
     * Hiển thị danh sách tất cả người dùng
     * @return void
     */
    public function users() {
        // Khởi tạo model người dùng
        $userModel = new User();
        // Lấy tất cả người dùng từ database
        $users = $userModel->getAllUsers();

        // Include view hiển thị danh sách người dùng
        include __DIR__ . '/../views/admin/users/index.php';
    }

    /**
     * Xử lý chỉnh sửa danh mục
     * GET: Hiển thị form chỉnh sửa với dữ liệu hiện tại
     * POST: Xử lý cập nhật thông tin danh mục
     * @return void
     */
    public function editCategory() {
        // Lấy category_id từ URL parameters
        $category_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;

        // Kiểm tra category_id hợp lệ
        if (!$category_id) {
            header('Location: /admin/categories');
            exit();
        }

        // Khởi tạo model danh mục
        $categoryModel = new Category();
        // Lấy thông tin danh mục hiện tại
        $category = $categoryModel->getCategoryById($category_id);

        // Kiểm tra danh mục có tồn tại không
        if (!$category) {
            header('Location: /admin/categories');
            exit();
        }

        // Xử lý POST request (submit form chỉnh sửa)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lấy và làm sạch tên danh mục mới từ form
            $name = trim($_POST['name'] ?? '');

            // Validate dữ liệu đầu vào
            if (empty($name)) {
                SessionHelper::setFlash('error', 'Tên danh mục không được để trống!');
                header('Location: /admin/categories/edit/' . $category_id);
                exit();
            }

            // Gán dữ liệu mới cho model
            $categoryModel->category_id = $category_id;
            $categoryModel->name = $name;

            // Thực hiện cập nhật trong database
            if ($categoryModel->update()) {
                SessionHelper::setFlash('success', 'Cập nhật danh mục thành công!');
                header('Location: /admin/categories');
                exit();
            } else {
                SessionHelper::setFlash('error', 'Có lỗi xảy ra khi cập nhật danh mục!');
            }
        }

        // Include view form chỉnh sửa (cho GET request hoặc khi có lỗi)
        include __DIR__ . '/../views/admin/categories/edit.php';
    }

    /**
     * Xử lý xóa danh mục
     * @return void
     */
    public function deleteCategory() {
        // Lấy category_id từ URL parameters
        $category_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;

        // Kiểm tra category_id hợp lệ
        if (!$category_id) {
            header('Location: /admin/categories');
            exit();
        }

        // Khởi tạo model danh mục
        $categoryModel = new Category();

        // Thực hiện xóa danh mục từ database
        if ($categoryModel->delete($category_id)) {
            SessionHelper::setFlash('success', 'Xóa danh mục thành công!');
        } else {
            SessionHelper::setFlash('error', 'Có lỗi xảy ra khi xóa danh mục!');
        }

        // Redirect về danh sách danh mục
        header('Location: /admin/categories');
        exit();
    }

    /**
     * Xử lý chỉnh sửa thương hiệu
     * GET: Hiển thị form chỉnh sửa với dữ liệu hiện tại
     * POST: Xử lý cập nhật thông tin thương hiệu
     * @return void
     */
    public function editBrand() {
        // Lấy brand_id từ URL parameters
        $brand_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;

        // Kiểm tra brand_id hợp lệ
        if (!$brand_id) {
            header('Location: /admin/brands');
            exit();
        }

        // Khởi tạo model thương hiệu
        $brandModel = new Brand();
        // Lấy thông tin thương hiệu hiện tại
        $brand = $brandModel->getBrandById($brand_id);

        // Kiểm tra thương hiệu có tồn tại không
        if (!$brand) {
            header('Location: /admin/brands');
            exit();
        }

        // Xử lý POST request (submit form chỉnh sửa)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lấy và làm sạch tên thương hiệu mới từ form
            $brand_name = trim($_POST['brand_name'] ?? '');

            // Validate dữ liệu đầu vào
            if (empty($brand_name)) {
                SessionHelper::setFlash('error', 'Tên thương hiệu không được để trống!');
                header('Location: /admin/brands/edit/' . $brand_id);
                exit();
            }

            // Gán dữ liệu mới cho model
            $brandModel->brand_id = $brand_id;
            $brandModel->brand_name = $brand_name;

            // Thực hiện cập nhật trong database
            if ($brandModel->update()) {
                SessionHelper::setFlash('success', 'Cập nhật thương hiệu thành công!');
                header('Location: /admin/brands');
                exit();
            } else {
                SessionHelper::setFlash('error', 'Có lỗi xảy ra khi cập nhật thương hiệu!');
            }
        }

        // Include view form chỉnh sửa (cho GET request hoặc khi có lỗi)
        include __DIR__ . '/../views/admin/brands/edit.php';
    }

    /**
     * Xử lý xóa thương hiệu
     * @return void
     */
    public function deleteBrand() {
        // Lấy brand_id từ URL parameters
        $brand_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;

        // Kiểm tra brand_id hợp lệ
        if (!$brand_id) {
            header('Location: /admin/brands');
            exit();
        }

        // Khởi tạo model thương hiệu
        $brandModel = new Brand();

        // Thực hiện xóa thương hiệu từ database
        if ($brandModel->delete($brand_id)) {
            SessionHelper::setFlash('success', 'Xóa thương hiệu thành công!');
        } else {
            SessionHelper::setFlash('error', 'Có lỗi xảy ra khi xóa thương hiệu!');
        }

        // Redirect về danh sách thương hiệu
        header('Location: /admin/brands');
        exit();
    }

    /**
     * Xử lý xóa người dùng
     * @return void
     */
    public function deleteUser() {
        // Lấy user_id từ URL parameters
        $user_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;

        // Kiểm tra user_id hợp lệ
        if (!$user_id) {
            header('Location: /admin/users');
            exit();
        }

        // Khởi tạo model người dùng
        $userModel = new User();

        // Thực hiện xóa người dùng từ database
        if ($userModel->delete($user_id)) {
            SessionHelper::setFlash('success', 'Xóa người dùng thành công!');
        } else {
            SessionHelper::setFlash('error', 'Có lỗi xảy ra khi xóa người dùng!');
        }

        // Redirect về danh sách người dùng
        header('Location: /admin/users');
        exit();
    }


    /**
     * AJAX endpoint để xóa hình ảnh sản phẩm
     * Xóa cả record trong database và file vật lý trên server
     * @return void (trả về JSON response)
     */
    public function deleteImage() {
        // Set header cho JSON response
        header('Content-Type: application/json');

        // Chỉ chấp nhận POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit();
        }

        // Lấy image_id từ POST data
        $image_id = isset($_POST['image_id']) ? (int)$_POST['image_id'] : 0;

        // Validate image_id
        if (!$image_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid image ID']);
            exit();
        }

        // Khởi tạo model hình ảnh
        $imageModel = new Image();

        // Lấy thông tin hình ảnh trước khi xóa (để lấy đường dẫn file)
        $image = $imageModel->getImageById($image_id);
        if (!$image) {
            echo json_encode(['success' => false, 'message' => 'Image not found']);
            exit();
        }

        // Xóa record hình ảnh từ database
        if ($imageModel->deleteImage($image_id)) {
            // Xóa file vật lý khỏi server
            $imagePath = __DIR__ . '/../../' . ltrim($image->image_url, '/');
            if (file_exists($imagePath)) {
                unlink($imagePath);  // Xóa file
            }

            // Trả về response thành công
            echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
        } else {
            // Trả về response lỗi
            echo json_encode(['success' => false, 'message' => 'Failed to delete image']);
        }
        exit();
    }

    /**
     * AJAX endpoint để lấy thông tin về việc xóa sản phẩm
     * Kiểm tra xem sản phẩm có thể xóa an toàn không
     * @return void (trả về JSON response)
     */
    public function getDeletionInfo() {
        // Set header cho JSON response
        header('Content-Type: application/json');

        // Chỉ chấp nhận POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit();
        }

        // Lấy dữ liệu JSON từ request body
        $input = json_decode(file_get_contents('php://input'), true);
        $product_id = isset($input['product_id']) ? (int)$input['product_id'] : 0;

        // Validate product_id
        if (!$product_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
            exit();
        }

        // Khởi tạo model sản phẩm và lấy thông tin xóa
        $productModel = new Product();
        $deletionInfo = $productModel->getDeletionInfo($product_id);

        // Trả về thông tin xóa dưới dạng JSON
        echo json_encode([
            'success' => true,
            'info' => $deletionInfo
        ]);
        exit();
    }
}
?>


