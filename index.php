<?php
require_once __DIR__ . '/app/core/Router.php';
require_once __DIR__ . '/app/helpers/SessionHelper.php';

// Khởi tạo router
$router = new Router();

// Public routes
$router->get('/', 'HomeController', 'index');
$router->get('/products', 'HomeController', 'products');
$router->get('/products/{id}', 'HomeController', 'productDetail');

// Auth routes
$router->get('/login', 'AuthController', 'showLogin', 'guest');
$router->post('/login', 'AuthController', 'login', 'guest');
$router->get('/register', 'AuthController', 'showRegister', 'guest');
$router->post('/register', 'AuthController', 'register', 'guest');
$router->get('/logout', 'AuthController', 'logout');

// Customer routes
$router->post('/add-to-cart', 'HomeController', 'addToCart', 'customer');
$router->get('/cart', 'HomeController', 'cart', 'customer');
$router->post('/cart/update', 'HomeController', 'updateCart', 'customer');

// Order routes
$router->get('/checkout', 'OrderController', 'checkout', 'customer');
$router->post('/checkout', 'OrderController', 'placeOrder', 'customer');
$router->get('/orders', 'OrderController', 'myOrders', 'customer');
$router->get('/orders/{id}', 'OrderController', 'orderDetail', 'auth');

// Admin routes
$router->get('/admin', 'AdminController', 'dashboard', 'admin');

// Admin product routes
$router->get('/admin/products', 'AdminController', 'products', 'admin');
$router->get('/admin/products/add', 'AdminController', 'addProduct', 'admin');
$router->post('/admin/products/add', 'AdminController', 'addProduct', 'admin');
$router->get('/admin/products/edit/{id}', 'AdminController', 'editProduct', 'admin');
$router->post('/admin/products/edit/{id}', 'AdminController', 'editProduct', 'admin');
$router->get('/admin/products/delete/{id}', 'AdminController', 'deleteProduct', 'admin');

// Admin category routes
$router->get('/admin/categories', 'AdminController', 'categories', 'admin');
$router->get('/admin/categories/add', 'AdminController', 'addCategory', 'admin');
$router->post('/admin/categories/add', 'AdminController', 'addCategory', 'admin');
$router->get('/admin/categories/edit/{id}', 'AdminController', 'editCategory', 'admin');
$router->post('/admin/categories/edit/{id}', 'AdminController', 'editCategory', 'admin');
$router->get('/admin/categories/delete/{id}', 'AdminController', 'deleteCategory', 'admin');

// Admin brand routes
$router->get('/admin/brands', 'AdminController', 'brands', 'admin');
$router->get('/admin/brands/add', 'AdminController', 'addBrand', 'admin');
$router->post('/admin/brands/add', 'AdminController', 'addBrand', 'admin');
$router->get('/admin/brands/edit/{id}', 'AdminController', 'editBrand', 'admin');
$router->post('/admin/brands/edit/{id}', 'AdminController', 'editBrand', 'admin');
$router->get('/admin/brands/delete/{id}', 'AdminController', 'deleteBrand', 'admin');

// Admin promotion routes
$router->get('/admin/promotions', 'AdminController', 'promotions', 'admin');
$router->get('/admin/promotions/add', 'AdminController', 'addPromotion', 'admin');
$router->post('/admin/promotions/add', 'AdminController', 'addPromotion', 'admin');
$router->get('/admin/promotions/delete/{id}', 'AdminController', 'deletePromotion', 'admin');

// Admin order routes
$router->get('/admin/orders', 'AdminController', 'orders', 'admin');
$router->get('/admin/orders/delete/{id}', 'AdminController', 'deleteOrder', 'admin');

// Admin user routes
$router->get('/admin/users', 'AdminController', 'users', 'admin');
$router->get('/admin/users/delete/{id}', 'AdminController', 'deleteUser', 'admin');

// Admin notification routes
$router->get('/admin/notifications', 'NotificationController', 'adminIndex', 'admin');
$router->get('/api/admin/notifications', 'NotificationController', 'getAllNotifications', 'admin');
$router->post('/api/admin/notifications/send', 'NotificationController', 'sendCustomNotification', 'admin');
$router->post('/api/admin/notifications/delete', 'NotificationController', 'deleteNotification', 'admin');

// Notification API routes
$router->get('/api/notifications', 'NotificationController', 'getNotifications', 'auth');
$router->post('/api/notifications/mark-read', 'NotificationController', 'markAsRead', 'auth');
$router->post('/api/notifications/mark-all-read', 'NotificationController', 'markAllAsRead', 'auth');
$router->get('/api/notifications/unread-count', 'NotificationController', 'getUnreadCount', 'auth');
$router->post('/api/notifications/test', 'NotificationController', 'sendTestNotification', 'admin');

// Notification settings routes
$router->get('/notifications/settings', 'NotificationController', 'settings', 'auth');
$router->get('/api/notifications/settings', 'NotificationController', 'getSettings', 'auth');
$router->post('/api/notifications/settings', 'NotificationController', 'updateSettings', 'auth');

// Dispatch the request
$router->dispatch();
?>
