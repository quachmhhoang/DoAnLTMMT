<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title . ' - ' : '' ?>CellPhone Store</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="/assets/css/animations.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-mobile-alt"></i> CellPhone Store
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/products">Sản phẩm</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (SessionHelper::isLoggedIn()): ?>
                        <?php $user = SessionHelper::getCurrentUser(); ?>

                        <?php if (SessionHelper::isCustomer()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/cart">
                                    <i class="fas fa-shopping-cart"></i> Giỏ hàng
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/orders">Đơn hàng</a>
                            </li>
                        <?php endif; ?>

                        <!-- Notification Bell -->
                        <li class="nav-item dropdown">
                            <a class="nav-link notification-bell" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                <span class="notification-badge badge bg-danger" style="display: none;">0</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
                                <div class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span>Thông báo</span>
                                    <button class="btn btn-sm btn-link mark-all-read p-0">Đánh dấu tất cả đã đọc</button>
                                </div>
                                <div class="dropdown-divider"></div>
                                <div class="notifications-list">
                                    <div class="text-center text-muted p-3">Đang tải...</div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <div class="dropdown-footer text-center">
                                    <a href="/notifications" class="btn btn-sm btn-primary">Xem tất cả</a>
                                </div>
                            </div>
                        </li>

                        <?php if (SessionHelper::isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/admin">Quản trị</a>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($user->full_name); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/notifications">Thông báo</a></li>
                                <li><a class="dropdown-item" href="/notification-settings">Cài đặt thông báo</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/logout">Đăng xuất</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login">Đăng nhập</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/register">Đăng ký</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if ($error = SessionHelper::getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($success = SessionHelper::getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="container mt-4">
        <?php echo $content ?? ''; ?>
    </main>

    <!-- Footer -->
    <footer class="footer mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>CellPhone Store</h5>
                    <p>Cửa hàng điện thoại uy tín hàng đầu Việt Nam</p>
                </div>
                <div class="col-md-6">
                    <h5>Liên hệ</h5>
                    <p>
                        <i class="fas fa-phone"></i> 0123-456-789<br>
                        <i class="fas fa-envelope"></i> info@cellphonestore.com
                    </p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; 2025 CellPhone Store. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Chart.js for future use -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="/assets/js/app.js"></script>
    <script src="/assets/js/animations.js"></script>

    <!-- Notification System -->
    <?php if (SessionHelper::isLoggedIn()): ?>
    <script src="/assets/js/notifications.js"></script>
    <script>
        // Set current user ID for notifications
        window.currentUserId = <?php echo SessionHelper::getCurrentUser()->user_id; ?>;
    </script>
    <?php endif; ?>

    <!-- Authentication JavaScript (only for auth pages) -->
    <?php if (isset($title) && (strpos($title, 'Đăng nhập') !== false || strpos($title, 'Đăng ký') !== false)): ?>
    <script src="/assets/js/auth.js"></script>
    <?php endif; ?>


</body>
</html>
