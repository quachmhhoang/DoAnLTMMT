<?php
require_once __DIR__ . '/../../helpers/SessionHelper.php';
$title = 'Đăng ký';
ob_start();
?>

<div class="auth-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-9 col-xl-8">
                <div class="card auth-card">
                    <div class="card-header">
                        <h4><i class="fas fa-user-plus me-2"></i>Đăng ký tài khoản</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="/register" id="registerForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="username" class="form-label">
                                            <i class="fas fa-user me-2"></i>Tên đăng nhập *
                                        </label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               placeholder="Nhập tên đăng nhập của bạn" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="full_name" class="form-label">
                                            <i class="fas fa-id-card me-2"></i>Họ và tên *
                                        </label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" 
                                               placeholder="Nhập họ và tên đầy đủ" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="password" class="form-label">
                                            <i class="fas fa-lock me-2"></i>Mật khẩu *
                                        </label>
                                        <div class="password-field-container">
                                            <input type="password" class="form-control" id="password" name="password" 
                                                   placeholder="Nhập mật khẩu bảo mật" required>
                                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                                <i class="fas fa-eye" id="password-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="confirm_password" class="form-label">
                                            <i class="fas fa-lock me-2"></i>Xác nhận mật khẩu *
                                        </label>
                                        <div class="password-field-container">
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                                   placeholder="Nhập lại mật khẩu" required>
                                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                                <i class="fas fa-eye" id="confirm_password-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope me-2"></i>Email *
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               placeholder="Nhập địa chỉ email của bạn" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="phone" class="form-label">
                                            <i class="fas fa-phone me-2"></i>Số điện thoại *
                                        </label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               placeholder="Nhập số điện thoại liên hệ" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="address" class="form-label">
                                    <i class="fas fa-map-marker-alt me-2"></i>Địa chỉ *
                                </label>
                                <textarea class="form-control" id="address" name="address" rows="3" 
                                          placeholder="Nhập địa chỉ đầy đủ của bạn" required></textarea>
                            </div>
                            
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-2"></i>Đăng ký tài khoản ngay
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0">Đã có tài khoản? 
                                <a href="/login">
                                    <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập ngay
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
