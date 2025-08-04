<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Brand.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Promotion.php';
require_once __DIR__ . '/../helpers/SessionHelper.php';
require_once __DIR__ . '/../services/NotificationService.php';

class AdminController {
    
    // Dashboard admin
    public function dashboard() {
        $productModel = new Product();
        $orderModel = new Order();
        $userModel = new User();
        $promotionModel = new Promotion();
        $notificationService = new NotificationService();

        // Thống kê
        $totalProducts = $productModel->countProducts();
        $totalOrders = count($orderModel->getAllOrders());
        $totalUsers = count($userModel->getAllUsers());
        $totalPromotions = $promotionModel->countPromotions();

        // Thống kê thông báo
        $notificationStats = $notificationService->getNotificationStats();

        // Đơn hàng gần đây
        $recentOrders = array_slice($orderModel->getAllOrders(), 0, 5);

        include __DIR__ . '/../views/admin/dashboard.php';
    }
    
    // Quản lý sản phẩm
    public function products() {
        $productModel = new Product();
        $products = $productModel->getAllProducts();
        
        include __DIR__ . '/../views/admin/products/index.php';
    }
    
    // Thêm sản phẩm
    public function addProduct() {
        $categoryModel = new Category();
        $brandModel = new Brand();
        
        $categories = $categoryModel->getAllCategories();
        $brands = $brandModel->getAllBrands();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $category_id = (int)($_POST['category_id'] ?? 0);
            $brand_id = (int)($_POST['brand_id'] ?? 0);
            
            if (empty($name) || $price <= 0 || !$category_id || !$brand_id) {
                SessionHelper::setFlash('error', 'Vui lòng nhập đầy đủ thông tin!');
                header('Location: /admin/products/add');
                exit();
            }
            
            $product = new Product();
            $product->name = $name;
            $product->price = $price;
            $product->description = $description;
            $product->category_id = $category_id;
            $product->brand_id = $brand_id;
            
            $productId = $product->create();
            if ($productId) {
                // Gửi thông báo sản phẩm mới
                try {
                    $notificationService = new NotificationService();
                    $currentUser = SessionHelper::getCurrentUser();
                    $notificationService->notifyProductAdded($productId, $name);
                } catch (Exception $e) {
                    error_log("Failed to send product notification: " . $e->getMessage());
                }

                SessionHelper::setFlash('success', 'Thêm sản phẩm thành công!');
                header('Location: /admin/products');
                exit();
            } else {
                SessionHelper::setFlash('error', 'Có lỗi xảy ra khi thêm sản phẩm!');
            }
        }
        
        include __DIR__ . '/../views/admin/products/add.php';
    }
    
    // Sửa sản phẩm
    public function editProduct() {
        $product_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;
        
        if (!$product_id) {
            header('Location: /admin/products');
            exit();
        }
        
        $productModel = new Product();
        $categoryModel = new Category();
        $brandModel = new Brand();
        
        $product = $productModel->getProductById($product_id);
        $categories = $categoryModel->getAllCategories();
        $brands = $brandModel->getAllBrands();
        
        if (!$product) {
            header('Location: /admin/products');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $category_id = (int)($_POST['category_id'] ?? 0);
            $brand_id = (int)($_POST['brand_id'] ?? 0);
            
            if (empty($name) || $price <= 0 || !$category_id || !$brand_id) {
                SessionHelper::setFlash('error', 'Vui lòng nhập đầy đủ thông tin!');
                header('Location: /admin/products/edit/' . $product_id);
                exit();
            }
            
            $productModel->product_id = $product_id;
            $productModel->name = $name;
            $productModel->price = $price;
            $productModel->description = $description;
            $productModel->category_id = $category_id;
            $productModel->brand_id = $brand_id;
            
            if ($productModel->update()) {
                SessionHelper::setFlash('success', 'Cập nhật sản phẩm thành công!');
                header('Location: /admin/products');
                exit();
            } else {
                SessionHelper::setFlash('error', 'Có lỗi xảy ra khi cập nhật sản phẩm!');
            }
        }
        
        include __DIR__ . '/../views/admin/products/edit.php';
    }
    
    // Xóa sản phẩm
    public function deleteProduct() {
        $product_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;
        
        if (!$product_id) {
            header('Location: /admin/products');
            exit();
        }
        
        $productModel = new Product();
        
        if ($productModel->delete($product_id)) {
            SessionHelper::setFlash('success', 'Xóa sản phẩm thành công!');
        } else {
            SessionHelper::setFlash('error', 'Có lỗi xảy ra khi xóa sản phẩm!');
        }
        
        header('Location: /admin/products');
        exit();
    }
    
    // Quản lý danh mục
    public function categories() {
        $categoryModel = new Category();
        $categories = $categoryModel->getAllCategories();
        
        include __DIR__ . '/../views/admin/categories/index.php';
    }
    
    // Thêm danh mục
    public function addCategory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            
            if (empty($name)) {
                SessionHelper::setFlash('error', 'Tên danh mục không được để trống!');
                header('Location: /admin/categories/add');
                exit();
            }
            
            $category = new Category();
            $category->name = $name;
            
            if ($category->create()) {
                SessionHelper::setFlash('success', 'Thêm danh mục thành công!');
                header('Location: /admin/categories');
                exit();
            } else {
                SessionHelper::setFlash('error', 'Có lỗi xảy ra khi thêm danh mục!');
            }
        }
        
        include __DIR__ . '/../views/admin/categories/add.php';
    }
    
    // Quản lý thương hiệu
    public function brands() {
        $brandModel = new Brand();
        $brands = $brandModel->getAllBrands();
        
        include __DIR__ . '/../views/admin/brands/index.php';
    }
    
    // Thêm thương hiệu
    public function addBrand() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $brand_name = trim($_POST['brand_name'] ?? '');
            
            if (empty($brand_name)) {
                SessionHelper::setFlash('error', 'Tên thương hiệu không được để trống!');
                header('Location: /admin/brands/add');
                exit();
            }
            
            $brand = new Brand();
            $brand->brand_name = $brand_name;
            
            if ($brand->create()) {
                SessionHelper::setFlash('success', 'Thêm thương hiệu thành công!');
                header('Location: /admin/brands');
                exit();
            } else {
                SessionHelper::setFlash('error', 'Có lỗi xảy ra khi thêm thương hiệu!');
            }
        }
        
        include __DIR__ . '/../views/admin/brands/add.php';
    }

    // Quản lý khuyến mãi
    public function promotions() {
        $promotionModel = new Promotion();
        $promotions = $promotionModel->getAllPromotions();

        include __DIR__ . '/../views/admin/promotions/index.php';
    }

    // Thêm khuyến mãi
    public function addPromotion() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $promotion_name = trim($_POST['promotion_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $discount_percent = (float)($_POST['discount_percent'] ?? 0);
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';

            if (empty($promotion_name) || $discount_percent <= 0 || empty($start_date) || empty($end_date)) {
                SessionHelper::setFlash('error', 'Vui lòng nhập đầy đủ thông tin!');
                header('Location: /admin/promotions/add');
                exit();
            }

            // Kiểm tra ngày kết thúc phải sau ngày bắt đầu
            if (strtotime($end_date) <= strtotime($start_date)) {
                SessionHelper::setFlash('error', 'Ngày kết thúc phải sau ngày bắt đầu!');
                header('Location: /admin/promotions/add');
                exit();
            }

            $promotion = new Promotion();
            $promotion->promotion_name = $promotion_name;
            $promotion->description = $description;
            $promotion->discount_percent = $discount_percent;
            $promotion->start_date = $start_date;
            $promotion->end_date = $end_date;

            $promotionId = $promotion->create();
            if ($promotionId) {
                // Gửi thông báo khuyến mãi mới
                try {
                    $notificationService = new NotificationService();
                    $currentUser = SessionHelper::getCurrentUser();

                    $title = "🎉 Khuyến mãi mới: " . $promotion_name;
                    $message = "Giảm giá {$discount_percent}% - {$description}. Có hiệu lực từ " .
                              date('d/m/Y', strtotime($start_date)) . " đến " .
                              date('d/m/Y', strtotime($end_date));

                    $promotionData = [
                        'promotion_id' => $promotionId,
                        'discount_percent' => $discount_percent,
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'action_url' => "/promotions/{$promotionId}"
                    ];

                    $notificationService->notifyPromotion($title, $message, $promotionData);
                } catch (Exception $e) {
                    error_log("Failed to send promotion notification: " . $e->getMessage());
                }

                SessionHelper::setFlash('success', 'Thêm khuyến mãi thành công!');
                header('Location: /admin/promotions');
                exit();
            } else {
                SessionHelper::setFlash('error', 'Có lỗi xảy ra khi thêm khuyến mãi!');
            }
        }

        include __DIR__ . '/../views/admin/promotions/add.php';
    }

    // Xóa khuyến mãi
    public function deletePromotion() {
        $promotion_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;

        if (!$promotion_id) {
            header('Location: /admin/promotions');
            exit();
        }

        $promotionModel = new Promotion();

        if ($promotionModel->delete($promotion_id)) {
            SessionHelper::setFlash('success', 'Xóa khuyến mãi thành công!');
        } else {
            SessionHelper::setFlash('error', 'Có lỗi xảy ra khi xóa khuyến mãi!');
        }

        header('Location: /admin/promotions');
        exit();
    }

    // Quản lý đơn hàng
    public function orders() {
        $orderModel = new Order();
        $orders = $orderModel->getAllOrders();
        
        include __DIR__ . '/../views/admin/orders/index.php';
    }
    
    // Xóa đơn hàng
    public function deleteOrder() {
        $order_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;
        
        if (!$order_id) {
            header('Location: /admin/orders');
            exit();
        }
        
        $orderModel = new Order();
        
        if ($orderModel->delete($order_id)) {
            SessionHelper::setFlash('success', 'Xóa đơn hàng thành công!');
        } else {
            SessionHelper::setFlash('error', 'Có lỗi xảy ra khi xóa đơn hàng!');
        }
        
        header('Location: /admin/orders');
        exit();
    }

    // Quản lý người dùng
    public function users() {
        $userModel = new User();
        $users = $userModel->getAllUsers();

        include __DIR__ . '/../views/admin/users/index.php';
    }

    // Sửa danh mục
    public function editCategory() {
        $category_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;
        
        if (!$category_id) {
            header('Location: /admin/categories');
            exit();
        }
        
        $categoryModel = new Category();
        $category = $categoryModel->getCategoryById($category_id);
        
        if (!$category) {
            header('Location: /admin/categories');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            
            if (empty($name)) {
                SessionHelper::setFlash('error', 'Tên danh mục không được để trống!');
                header('Location: /admin/categories/edit/' . $category_id);
                exit();
            }
            
            $categoryModel->category_id = $category_id;
            $categoryModel->name = $name;
            
            if ($categoryModel->update()) {
                SessionHelper::setFlash('success', 'Cập nhật danh mục thành công!');
                header('Location: /admin/categories');
                exit();
            } else {
                SessionHelper::setFlash('error', 'Có lỗi xảy ra khi cập nhật danh mục!');
            }
        }
        
        include __DIR__ . '/../views/admin/categories/edit.php';
    }
    
    // Xóa danh mục
    public function deleteCategory() {
        $category_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;
        
        if (!$category_id) {
            header('Location: /admin/categories');
            exit();
        }
        
        $categoryModel = new Category();
        
        if ($categoryModel->delete($category_id)) {
            SessionHelper::setFlash('success', 'Xóa danh mục thành công!');
        } else {
            SessionHelper::setFlash('error', 'Có lỗi xảy ra khi xóa danh mục!');
        }
        
        header('Location: /admin/categories');
        exit();
    }
    
    // Sửa thương hiệu
    public function editBrand() {
        $brand_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;
        
        if (!$brand_id) {
            header('Location: /admin/brands');
            exit();
        }
        
        $brandModel = new Brand();
        $brand = $brandModel->getBrandById($brand_id);
        
        if (!$brand) {
            header('Location: /admin/brands');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $brand_name = trim($_POST['brand_name'] ?? '');
            
            if (empty($brand_name)) {
                SessionHelper::setFlash('error', 'Tên thương hiệu không được để trống!');
                header('Location: /admin/brands/edit/' . $brand_id);
                exit();
            }
            
            $brandModel->brand_id = $brand_id;
            $brandModel->brand_name = $brand_name;
            
            if ($brandModel->update()) {
                SessionHelper::setFlash('success', 'Cập nhật thương hiệu thành công!');
                header('Location: /admin/brands');
                exit();
            } else {
                SessionHelper::setFlash('error', 'Có lỗi xảy ra khi cập nhật thương hiệu!');
            }
        }
        
        include __DIR__ . '/../views/admin/brands/edit.php';
    }
    
    // Xóa thương hiệu
    public function deleteBrand() {
        $brand_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;
        
        if (!$brand_id) {
            header('Location: /admin/brands');
            exit();
        }
        
        $brandModel = new Brand();
        
        if ($brandModel->delete($brand_id)) {
            SessionHelper::setFlash('success', 'Xóa thương hiệu thành công!');
        } else {
            SessionHelper::setFlash('error', 'Có lỗi xảy ra khi xóa thương hiệu!');
        }
        
        header('Location: /admin/brands');
        exit();
    }
    
    // Xóa người dùng
    public function deleteUser() {
        $user_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;
        
        if (!$user_id) {
            header('Location: /admin/users');
            exit();
        }
        
        $userModel = new User();
        
        if ($userModel->delete($user_id)) {
            SessionHelper::setFlash('success', 'Xóa người dùng thành công!');
        } else {
            SessionHelper::setFlash('error', 'Có lỗi xảy ra khi xóa người dùng!');
        }
        
        header('Location: /admin/users');
        exit();
    }
}
?>
