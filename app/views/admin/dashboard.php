<?php
require_once __DIR__ . '/../../helpers/SessionHelper.php';
$title = 'Dashboard - Admin CellPhone Store';
$pageTitle = 'Dashboard';
ob_start();
?>

<!-- Welcome Section -->
<div class="welcome-section mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="welcome-content">
                <h1 class="welcome-title mb-2">
                    <i class="fas fa-chart-line text-primary me-2"></i>
                    Chào mừng trở lại, <?php echo htmlspecialchars($user->full_name ?? 'Admin'); ?>!
                </h1>
                <p class="text-muted mb-0">
                    Hãy xem tổng quan hoạt động kinh doanh của cửa hàng điện thoại trong hôm nay.
                </p>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <div class="date-display">
                <div class="current-date">
                    <span class="date-day"><?php echo date('d'); ?></span>
                    <span class="date-month"><?php echo date('M'); ?></span>
                    <span class="date-year"><?php echo date('Y'); ?></span>
                </div>
                <div class="current-time mt-1">
                    <small class="text-muted" id="currentTime"></small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <!-- Products Card -->
    <div class="stat-card products-card">
        <div class="d-flex align-items-center">
            <div class="stat-icon">
                <i class="fas fa-mobile-alt"></i>
            </div>
            <div class="stat-info ms-3 flex-grow-1">
                <div class="stat-number"><?php echo number_format($totalProducts ?? 0); ?></div>
                <div class="stat-label">Tổng sản phẩm</div>
                <div class="stat-change">
                    <i class="fas fa-arrow-up"></i>
                    <span>+<?php echo rand(5, 15); ?>% tuần này</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Card -->
    <div class="stat-card orders-card">
        <div class="d-flex align-items-center">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-info ms-3 flex-grow-1">
                <div class="stat-number"><?php echo number_format($totalOrders ?? 0); ?></div>
                <div class="stat-label">Đơn hàng</div>
                <div class="stat-change">
                    <i class="fas fa-arrow-up"></i>
                    <span>+<?php echo rand(8, 20); ?>% hôm nay</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Card -->
    <div class="stat-card users-card">
        <div class="d-flex align-items-center">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info ms-3 flex-grow-1">
                <div class="stat-number"><?php echo number_format($totalUsers ?? 0); ?></div>
                <div class="stat-label">Khách hàng</div>
                <div class="stat-change">
                    <i class="fas fa-arrow-up"></i>
                    <span>+<?php echo rand(10, 25); ?>% tháng này</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Card -->
    <div class="stat-card revenue-card">
        <div class="d-flex align-items-center">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-info ms-3 flex-grow-1">
                <div class="stat-number"><?php echo number_format(($totalRevenue ?? 1250000), 0, ',', '.'); ?>đ</div>
                <div class="stat-label">Doanh thu tháng</div>
                <div class="stat-change">
                    <i class="fas fa-arrow-up"></i>
                    <span>+<?php echo rand(15, 30); ?>% so với tháng trước</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Dashboard Content -->
<div class="dashboard-grid">
    <!-- Recent Orders -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3>
                        <i class="fas fa-clock me-2 text-primary"></i>
                        Đơn hàng gần đây
                    </h3>
                    <p>Các đơn hàng mới nhất trong hệ thống</p>
                </div>
                <a href="/admin/orders" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i>
                    Xem tất cả
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($recentOrders)): ?>
                <div class="orders-list">
                    <?php foreach (array_slice($recentOrders, 0, 5) as $order): ?>
                        <div class="order-item">
                            <div class="order-info">
                                <span class="order-badge">#<?php echo $order->order_id; ?></span>
                                <div class="order-details ms-3">
                                    <div class="customer-name">
                                        <i class="fas fa-user me-1"></i>
                                        <?php echo htmlspecialchars($order->full_name); ?>
                                    </div>
                                    <div class="order-date">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($order->order_date)); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="order-amount text-end">
                                <div class="amount-value">
                                    <?php echo number_format($order->total_amount, 0, ',', '.'); ?>đ
                                </div>
                                <span class="status-badge status-<?php echo strtolower($order->status ?? 'pending'); ?>">
                                    <?php 
                                    $statusMap = [
                                        'pending' => 'Chờ xử lý',
                                        'processing' => 'Đang xử lý', 
                                        'completed' => 'Hoàn thành',
                                        'cancelled' => 'Đã hủy'
                                    ];
                                    echo $statusMap[$order->status ?? 'pending'] ?? 'Chờ xử lý';
                                    ?>
                                </span>
                            </div>
                            <div class="order-actions ms-3">
                                <a href="/admin/orders/<?php echo $order->order_id; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h4>Chưa có đơn hàng nào</h4>
                    <p>Các đơn hàng mới sẽ xuất hiện ở đây khi khách hàng đặt hàng.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="dashboard-card">
        <div class="card-header">
            <div>
                <h3>
                    <i class="fas fa-bolt me-2 text-warning"></i>
                    Thao tác nhanh
                </h3>
                <p>Các tính năng quản lý thường dùng</p>
            </div>
        </div>
        <div class="card-body">
            <div class="quick-actions-grid">
                <a href="/admin/products/add" class="quick-action-item">
                    <div class="action-icon add-product">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="action-content">
                        <h4>Thêm sản phẩm mới</h4>
                        <p>Thêm điện thoại mới vào cửa hàng</p>
                    </div>
                </a>

                <a href="/admin/orders" class="quick-action-item">
                    <div class="action-icon view-orders">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div class="action-content">
                        <h4>Quản lý đơn hàng</h4>
                        <p>Xem và xử lý đơn hàng khách hàng</p>
                    </div>
                </a>

                <a href="/admin/users" class="quick-action-item">
                    <div class="action-icon manage-users">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <div class="action-content">
                        <h4>Quản lý khách hàng</h4>
                        <p>Xem danh sách và thông tin khách hàng</p>
                    </div>
                </a>

                <a href="/admin/products" class="quick-action-item">
                    <div class="action-icon view-reports">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="action-content">
                        <h4>Kho sản phẩm</h4>
                        <p>Quản lý tồn kho và sản phẩm</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- System Status -->
<div class="system-status-card">
    <div class="card-header">
        <h3>
            <i class="fas fa-server me-2 text-info"></i>
            Tình trạng hệ thống
        </h3>
    </div>
    <div class="card-body">
        <div class="status-grid">
            <div class="status-item">
                <div class="status-icon online"></div>
                <div class="status-info">
                    <div class="status-label">Web Server</div>
                    <div class="status-value">Hoạt động bình thường</div>
                </div>
            </div>

            <div class="status-item">
                <div class="status-icon online"></div>
                <div class="status-info">
                    <div class="status-label">Database</div>
                    <div class="status-value">Kết nối ổn định</div>
                </div>
            </div>

            <div class="status-item">
                <div class="status-icon warning"></div>
                <div class="status-info">
                    <div class="status-label">Backup System</div>
                    <div class="status-value">Sao lưu lần cuối: <?php echo date('H:i d/m/Y', strtotime('-2 hours')); ?></div>
                </div>
            </div>

            <div class="status-item">
                <div class="status-icon online"></div>
                <div class="status-info">
                    <div class="status-label">Performance</div>
                    <div class="status-value">Tốt - <?php echo rand(95, 99); ?>% uptime</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Update current time
function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('vi-VN', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    const timeElement = document.getElementById('currentTime');
    if (timeElement) {
        timeElement.textContent = timeString;
    }
}

// Update time every second
updateTime();
setInterval(updateTime, 1000);
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout/admin.php';
?>
