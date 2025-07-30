<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/SessionHelper.php';

class AuthController {
    
    // Hiển thị trang đăng nhập
    public function showLogin() {
        if (SessionHelper::isLoggedIn()) {
            if (SessionHelper::isAdmin()) {
                header('Location: /admin');
            } else {
                header('Location: /');
            }
            exit();
        }
        
        include __DIR__ . '/../views/auth/login.php';
    }
    
    // Xử lý đăng nhập
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                SessionHelper::setFlash('error', 'Vui lòng nhập đầy đủ thông tin!');
                header('Location: /login');
                exit();
            }
            
            $user = new User();
            if ($user->login($username, $password)) {
                SessionHelper::login($user);
                
                if ($user->role === 'admin') {
                    header('Location: /admin');
                } else {
                    header('Location: /');
                }
                exit();
            } else {
                SessionHelper::setFlash('error', 'Tên đăng nhập hoặc mật khẩu không đúng!');
                header('Location: /login');
                exit();
            }
        }
        
        $this->showLogin();
    }
    
    // Hiển thị trang đăng ký
    public function showRegister() {
        if (SessionHelper::isLoggedIn()) {
            if (SessionHelper::isAdmin()) {
                header('Location: /admin');
            } else {
                header('Location: /');
            }
            exit();
        }
        
        include __DIR__ . '/../views/auth/register.php';
    }
    
    // Xử lý đăng ký
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $full_name = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            
            // Validation
            $errors = [];
            
            if (empty($username)) $errors[] = 'Tên đăng nhập không được để trống!';
            if (empty($password)) $errors[] = 'Mật khẩu không được để trống!';
            if ($password !== $confirm_password) $errors[] = 'Mật khẩu xác nhận không khớp!';
            if (empty($full_name)) $errors[] = 'Họ tên không được để trống!';
            if (empty($email)) $errors[] = 'Email không được để trống!';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ!';
            if (empty($phone)) $errors[] = 'Số điện thoại không được để trống!';
            if (empty($address)) $errors[] = 'Địa chỉ không được để trống!';
            
            if (!empty($errors)) {
                SessionHelper::setFlash('error', implode('<br>', $errors));
                header('Location: /register');
                exit();
            }
            
            $user = new User();
            $user->username = $username;
            $user->password = $password;
            $user->full_name = $full_name;
            $user->email = $email;
            $user->phone = $phone;
            $user->address = $address;
            $user->role = 'customer';
            
            if ($user->register()) {
                SessionHelper::setFlash('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
                header('Location: /login');
                exit();
            } else {
                SessionHelper::setFlash('error', 'Đăng ký thất bại! Tên đăng nhập có thể đã tồn tại.');
                header('Location: /register');
                exit();
            }
        }
        
        $this->showRegister();
    }
    
    // Đăng xuất
    public function logout() {
        SessionHelper::logout();
        header('Location: /login');
        exit();
    }
}
?>
