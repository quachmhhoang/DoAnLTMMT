<?php
require_once __DIR__ . '/../../helpers/SessionHelper.php';
$title = 'Đăng nhập';
ob_start();
?>

<div class="auth-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8 col-xl-7">
                <div class="card auth-card">
                    <div class="card-header">
                        <h4><i class="fas fa-sign-in-alt me-2"></i>Đăng nhập</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="/login" id="loginForm">
                            <div class="mb-5">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user me-2"></i>Tên đăng nhập
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       placeholder="Nhập tên đăng nhập của bạn" required>
                            </div>
                            
                            <div class="mb-5">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Mật khẩu
                                </label>
                                <div class="password-field-container">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Nhập mật khẩu của bạn" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="password-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="d-grid mb-5">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập ngay
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-5">
                            <p class="mb-0">Chưa có tài khoản? 
                                <a href="/register">
                                    <i class="fas fa-user-plus me-1"></i>Đăng ký ngay
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout/app.php';
?>
