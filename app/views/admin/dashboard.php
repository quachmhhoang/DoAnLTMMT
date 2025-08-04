<?php
require_once __DIR__ . '/../../helpers/SessionHelper.php';
$title = 'Dashboard - Admin';
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $totalProducts ?? 0; ?></h4>
                        <p>Sản phẩm</p>
                    </div>
                    <div>
                        <i class="fas fa-mobile-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $totalOrders ?? 0; ?></h4>
                        <p>Đơn hàng</p>
                    </div>
                    <div>
                        <i class="fas fa-shopping-cart fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $totalUsers ?? 0; ?></h4>
                        <p>Người dùng</p>
                    </div>
                    <div>
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $totalPromotions ?? 0; ?></h4>
                        <p>Khuyến mãi</p>
                    </div>
                    <div>
                        <i class="fas fa-percentage fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Statistics -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-bell"></i> Quản lý thông báo
                </h5>
                <a href="/admin/notifications" class="btn btn-primary btn-sm">
                    <i class="fas fa-cog"></i> Quản lý chi tiết
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-primary"><?php echo $notificationStats->total_notifications ?? 0; ?></h4>
                            <small class="text-muted">Tổng thông báo</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-warning"><?php echo $notificationStats->unread_notifications ?? 0; ?></h4>
                            <small class="text-muted">Chưa đọc</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-success"><?php echo $notificationStats->notifications_24h ?? 0; ?></h4>
                            <small class="text-muted">24 giờ qua</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-info"><?php echo $notificationStats->order_notifications ?? 0; ?></h4>
                            <small class="text-muted">Đơn hàng</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-success"><?php echo $notificationStats->product_notifications ?? 0; ?></h4>
                            <small class="text-muted">Sản phẩm</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-warning"><?php echo $notificationStats->promotion_notifications ?? 0; ?></h4>
                            <small class="text-muted">Khuyến mãi</small>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-primary btn-sm" onclick="showQuickNotificationModal()">
                                <i class="fas fa-paper-plane"></i> Gửi thông báo nhanh
                            </button>
                            <a href="/admin/notifications" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-list"></i> Xem tất cả
                            </a>
                            <a href="/admin/promotions/add" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-plus"></i> Tạo khuyến mãi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<?php if (!empty($recentOrders)): ?>
<div class="card">
    <div class="card-header">
        <h5>Đơn hàng gần đây</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Mã đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Ngày đặt</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>#<?php echo $order->order_id; ?></td>
                            <td><?php echo htmlspecialchars($order->full_name); ?></td>
                            <td><?php echo number_format($order->total_amount, 0, ',', '.'); ?>đ</td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order->order_date)); ?></td>
                            <td>
                                <a href="/orders/<?php echo $order->order_id; ?>" class="btn btn-sm btn-outline-primary">Xem</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Quick Notification Modal -->
<div class="modal fade" id="quickNotificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-paper-plane"></i> Gửi thông báo nhanh
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickNotificationForm">
                    <div class="mb-3">
                        <label for="quickTitle" class="form-label">Tiêu đề</label>
                        <input type="text" class="form-control" id="quickTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="quickMessage" class="form-label">Nội dung</label>
                        <textarea class="form-control" id="quickMessage" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="quickTarget" class="form-label">Đối tượng</label>
                        <select class="form-select" id="quickTarget">
                            <option value="all">Tất cả người dùng</option>
                            <option value="customer">Khách hàng</option>
                            <option value="admin">Quản trị viên</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="sendQuickNotification()">
                    <i class="fas fa-paper-plane"></i> Gửi ngay
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showQuickNotificationModal() {
    const modal = new bootstrap.Modal(document.getElementById('quickNotificationModal'));
    modal.show();
}

function sendQuickNotification() {
    const title = document.getElementById('quickTitle').value.trim();
    const message = document.getElementById('quickMessage').value.trim();
    const target = document.getElementById('quickTarget').value;

    if (!title || !message) {
        alert('Vui lòng nhập đầy đủ tiêu đề và nội dung');
        return;
    }

    const data = {
        title: title,
        message: message,
        type: 'system',
        target: target === 'all' ? 'all' : 'role',
        target_value: target === 'all' ? null : target
    };

    fetch('/api/admin/notifications/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Gửi thông báo thành công!');
            const modal = bootstrap.Modal.getInstance(document.getElementById('quickNotificationModal'));
            modal.hide();
            document.getElementById('quickNotificationForm').reset();
            // Refresh page to update statistics
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            alert('Không thể gửi thông báo: ' + (data.error || 'Lỗi không xác định'));
        }
    })
    .catch(error => {
        console.error('Error sending notification:', error);
        alert('Lỗi khi gửi thông báo');
    });
}

// Auto-refresh notification statistics every 30 seconds
setInterval(function() {
    // You can implement AJAX refresh here if needed
}, 30000);
</script>

<style>
.card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 10px;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px 10px 0 0 !important;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
}

.text-primary { color: #667eea !important; }
.text-warning { color: #f39c12 !important; }
.text-success { color: #27ae60 !important; }
.text-info { color: #3498db !important; }

.gap-2 {
    gap: 0.5rem !important;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout/admin.php';
?>
