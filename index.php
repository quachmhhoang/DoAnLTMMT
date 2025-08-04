<?php
/**
 * File index.php - Entry point chính của ứng dụng
 * Định nghĩa tất cả các routes và dispatch request đến controller tương ứng
 */

// Import các file cần thiết
require_once __DIR__ . '/app/core/Router.php';        // Router class để xử lý routing
require_once __DIR__ . '/app/helpers/SessionHelper.php';  // Helper xử lý session

// Khởi tạo router instance
$router = new Router();

// ===== PUBLIC ROUTES (không cần đăng nhập) =====
$router->get('/', 'HomeController', 'index');                    // Trang chủ
$router->get('/products', 'HomeController', 'products');         // Danh sách sản phẩm
$router->get('/products/{id}', 'HomeController', 'productDetail'); // Chi tiết sản phẩm

// ===== AUTHENTICATION ROUTES =====
$router->get('/login', 'AuthController', 'showLogin', 'guest');      // Hiển thị form đăng nhập (chỉ guest)
$router->post('/login', 'AuthController', 'login', 'guest');         // Xử lý đăng nhập (chỉ guest)
$router->get('/register', 'AuthController', 'showRegister', 'guest'); // Hiển thị form đăng ký (chỉ guest)
$router->post('/register', 'AuthController', 'register', 'guest');    // Xử lý đăng ký (chỉ guest)
$router->get('/logout', 'AuthController', 'logout');                 // Đăng xuất

// ===== CUSTOMER ROUTES (cần đăng nhập với role customer) =====
$router->post('/add-to-cart', 'HomeController', 'addToCart', 'customer');  // Thêm sản phẩm vào giỏ hàng
$router->get('/cart', 'HomeController', 'cart', 'customer');               // Xem giỏ hàng
$router->post('/cart/update', 'HomeController', 'updateCart', 'customer'); // Cập nhật giỏ hàng

// ===== ORDER ROUTES =====
$router->get('/checkout', 'OrderController', 'checkout', 'customer');     // Trang thanh toán (customer)
$router->post('/checkout', 'OrderController', 'placeOrder', 'customer');  // Đặt hàng (customer)
$router->get('/orders', 'OrderController', 'myOrders', 'customer');       // Đơn hàng của tôi (customer)
$router->get('/orders/{id}', 'OrderController', 'orderDetail', 'auth');   // Chi tiết đơn hàng (đã đăng nhập)

// ===== ADMIN ROUTES (cần đăng nhập với role admin) =====
$router->get('/admin', 'AdminController', 'dashboard', 'admin');  // Dashboard admin

// ===== ADMIN PRODUCT MANAGEMENT =====
$router->get('/admin/products', 'AdminController', 'products', 'admin');              // Danh sách sản phẩm
$router->get('/admin/products/add', 'AdminController', 'addProduct', 'admin');        // Form thêm sản phẩm
$router->post('/admin/products/add', 'AdminController', 'addProduct', 'admin');       // Xử lý thêm sản phẩm
$router->get('/admin/products/edit/{id}', 'AdminController', 'editProduct', 'admin'); // Form sửa sản phẩm
$router->post('/admin/products/edit/{id}', 'AdminController', 'editProduct', 'admin');// Xử lý sửa sản phẩm
$router->get('/admin/products/delete/{id}', 'AdminController', 'deleteProduct', 'admin'); // Xóa sản phẩm

// ===== ADMIN CATEGORY MANAGEMENT =====
$router->get('/admin/categories', 'AdminController', 'categories', 'admin');              // Danh sách danh mục
$router->get('/admin/categories/add', 'AdminController', 'addCategory', 'admin');         // Form thêm danh mục
$router->post('/admin/categories/add', 'AdminController', 'addCategory', 'admin');        // Xử lý thêm danh mục
$router->get('/admin/categories/edit/{id}', 'AdminController', 'editCategory', 'admin');  // Form sửa danh mục
$router->post('/admin/categories/edit/{id}', 'AdminController', 'editCategory', 'admin'); // Xử lý sửa danh mục
$router->get('/admin/categories/delete/{id}', 'AdminController', 'deleteCategory', 'admin'); // Xóa danh mục

// ===== ADMIN BRAND MANAGEMENT =====
$router->get('/admin/brands', 'AdminController', 'brands', 'admin');              // Danh sách thương hiệu
$router->get('/admin/brands/add', 'AdminController', 'addBrand', 'admin');        // Form thêm thương hiệu
$router->post('/admin/brands/add', 'AdminController', 'addBrand', 'admin');       // Xử lý thêm thương hiệu
$router->get('/admin/brands/edit/{id}', 'AdminController', 'editBrand', 'admin'); // Form sửa thương hiệu
$router->post('/admin/brands/edit/{id}', 'AdminController', 'editBrand', 'admin');// Xử lý sửa thương hiệu
$router->get('/admin/brands/delete/{id}', 'AdminController', 'deleteBrand', 'admin'); // Xóa thương hiệu

// ===== ADMIN PROMOTION MANAGEMENT =====
$router->get('/admin/promotions', 'AdminController', 'promotions', 'admin');         // Danh sách khuyến mãi
$router->get('/admin/promotions/add', 'AdminController', 'addPromotion', 'admin');   // Form thêm khuyến mãi
$router->post('/admin/promotions/add', 'AdminController', 'addPromotion', 'admin');  // Xử lý thêm khuyến mãi
$router->get('/admin/promotions/delete/{id}', 'AdminController', 'deletePromotion', 'admin'); // Xóa khuyến mãi

// ===== ADMIN ORDER MANAGEMENT =====
$router->get('/admin/orders', 'AdminController', 'orders', 'admin');              // Danh sách đơn hàng
$router->get('/admin/orders/delete/{id}', 'AdminController', 'deleteOrder', 'admin'); // Xóa đơn hàng

// ===== ADMIN USER MANAGEMENT =====
$router->get('/admin/users', 'AdminController', 'users', 'admin');               // Danh sách người dùng
$router->get('/admin/users/delete/{id}', 'AdminController', 'deleteUser', 'admin'); // Xóa người dùng

// ===== ADMIN NOTIFICATION MANAGEMENT =====
$router->get('/admin/notifications', 'NotificationController', 'adminIndex', 'admin');           // Trang quản lý thông báo
$router->get('/api/admin/notifications', 'NotificationController', 'getAllNotifications', 'admin'); // API lấy tất cả thông báo
$router->post('/api/admin/notifications/send', 'NotificationController', 'sendCustomNotification', 'admin'); // API gửi thông báo tùy chỉnh
$router->post('/api/admin/notifications/delete', 'NotificationController', 'deleteNotification', 'admin'); // API xóa thông báo

// ===== NOTIFICATION API ROUTES (cho user) =====
$router->get('/api/notifications', 'NotificationController', 'getNotifications', 'auth');         // Lấy thông báo của user
$router->post('/api/notifications/mark-read', 'NotificationController', 'markAsRead', 'auth');    // Đánh dấu đã đọc
$router->post('/api/notifications/mark-all-read', 'NotificationController', 'markAllAsRead', 'auth'); // Đánh dấu tất cả đã đọc
$router->get('/api/notifications/unread-count', 'NotificationController', 'getUnreadCount', 'auth'); // Đếm thông báo chưa đọc
$router->post('/api/notifications/test', 'NotificationController', 'sendTestNotification', 'admin'); // Gửi thông báo test

// ===== NOTIFICATION SETTINGS =====
$router->get('/notifications/settings', 'NotificationController', 'settings', 'auth');           // Trang cài đặt thông báo
$router->get('/api/notifications/settings', 'NotificationController', 'getSettings', 'auth');    // API lấy cài đặt
$router->post('/api/notifications/settings', 'NotificationController', 'updateSettings', 'auth'); // API cập nhật cài đặt

// ===== ADMIN AJAX ROUTES (các endpoint AJAX cho admin) =====
$router->post('/admin/ajax/delete-image', 'AdminController', 'deleteImage', 'admin');        // Xóa hình ảnh qua AJAX
$router->post('/admin/ajax/get-deletion-info', 'AdminController', 'getDeletionInfo', 'admin'); // Lấy thông tin xóa qua AJAX

// ===== DISPATCH REQUEST =====
// Xử lý request hiện tại và chuyển đến controller/method tương ứng
$router->dispatch();
?>
