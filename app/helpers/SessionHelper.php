<?php
class SessionHelper {
    
    public static function start() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // Đăng nhập user
    public static function login($user) {
        self::start();
        $_SESSION['user_id'] = $user->user_id;
        $_SESSION['username'] = $user->username;
        $_SESSION['full_name'] = $user->full_name;
        $_SESSION['role'] = $user->role;
        $_SESSION['logged_in'] = true;
    }
    
    // Đăng xuất
    public static function logout() {
        self::start();
        session_unset();
        session_destroy();
    }
    
    // Kiểm tra đã đăng nhập chưa
    public static function isLoggedIn() {
        self::start();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    // Lấy thông tin user hiện tại
    public static function getCurrentUser() {
        self::start();
        if (self::isLoggedIn()) {
            return (object) [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'full_name' => $_SESSION['full_name'],
                'role' => $_SESSION['role']
            ];
        }
        return null;
    }
    
    // Kiểm tra quyền admin
    public static function isAdmin() {
        self::start();
        return self::isLoggedIn() && $_SESSION['role'] === 'admin';
    }
    
    // Kiểm tra quyền customer
    public static function isCustomer() {
        self::start();
        return self::isLoggedIn() && $_SESSION['role'] === 'customer';
    }
    
    // Yêu cầu đăng nhập
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit();
        }
    }
    
    // Yêu cầu quyền admin
    public static function requireAdmin() {
        self::requireLogin();
        if (!self::isAdmin()) {
            header('Location: /');
            exit();
        }
    }
    
    // Yêu cầu quyền customer
    public static function requireCustomer() {
        self::requireLogin();
        if (!self::isCustomer()) {
            header('Location: /admin');
            exit();
        }
    }
    
    // Set flash message
    public static function setFlash($type, $message) {
        self::start();
        $_SESSION['flash'][$type] = $message;
    }
    
    // Get flash message
    public static function getFlash($type) {
        self::start();
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }
    
    // Set session data
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    // Get session data
    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    // Unset session data
    public static function unset($key) {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
}
?>
