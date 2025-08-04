<?php
$title = 'Dashboard - Admin';
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<!-- Statistics Cards -->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Dashboard Cards</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #ff6b6b 0%, #ffa726 100%);
            min-height: 100vh;
            padding: 20px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .dashboard-card {
            border-radius: 20px;
            border: none !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
            margin-bottom: 25px;
            background-color: transparent !important;
        }

        .dashboard-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            pointer-events: none;
        }

        .card-primary {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%) !important;
            border: none !important;
        }

        .card-success {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%) !important;
            border: none !important;
        }

        .card-info {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%) !important;
            border: none !important;
        }

        .card-warning {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%) !important;
            border: none !important;
        }

        /* Đảm bảo các card hiển thị đúng màu */
        .dashboard-card.card-primary,
        .card.dashboard-card.card-primary {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%) !important;
        }

        .dashboard-card.card-success,
        .card.dashboard-card.card-success {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%) !important;
        }

        .dashboard-card.card-info,
        .card.dashboard-card.card-info {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%) !important;
        }

        .dashboard-card.card-warning,
        .card.dashboard-card.card-warning {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%) !important;
        }

        .card-body {
            padding: 30px 25px;
            position: relative;
            z-index: 2;
        }

        .card-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-text h4 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .card-text p {
            font-size: 1.1rem;
            margin: 5px 0 0 0;
            opacity: 0.9;
            font-weight: 500;
        }

        .card-icon {
            opacity: 0.8;
            transition: all 0.3s ease;
        }

        .dashboard-card:hover .card-icon {
            opacity: 1;
            transform: scale(1.1);
        }

        .card-icon i {
            font-size: 3rem;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        /* Animation cho số */
        @keyframes countUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-text h4 {
            animation: countUp 0.6s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card-text h4 {
                font-size: 2rem;
            }
            
            .card-icon i {
                font-size: 2.5rem;
            }
            
            .card-body {
                padding: 20px 15px;
            }
        }

        /* Thêm hiệu ứng pulse cho icon */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }

        .card-icon {
            border-radius: 50%;
            padding: 10px;
            animation: pulse 2s infinite;
        }

        /* Loading skeleton effect */
        @keyframes shimmer {
            0% {
                background-position: -200px 0;
            }
            100% {
                background-position: calc(200px + 100%) 0;
            }
        }

        .loading {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            background-size: 200px 100%;
            animation: shimmer 1.5s infinite;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="card dashboard-card card-primary" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%) !important; border: none !important; color: #fff3cd !important;">
                    <div class="card-body">
                        <div class="card-content">
                            <div class="card-text">
                                <h4 id="totalProducts">125</h4>
                                <p>Sản phẩm</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card dashboard-card card-success" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%) !important; border: none !important; color: #d1ecf1 !important;">
                    <div class="card-body">
                        <div class="card-content">
                            <div class="card-text">
                                <h4 id="totalOrders">89</h4>
                                <p>Đơn hàng</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card dashboard-card card-info" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%) !important; border: none !important; color: #f8d7da !important;">
                    <div class="card-body">
                        <div class="card-content">
                            <div class="card-text">
                                <h4 id="totalUsers">456</h4>
                                <p>Người dùng</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card dashboard-card card-warning" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%) !important; border: none !important; color: #d4edda !important;">
                    <div class="card-body">
                        <div class="card-content">
                            <div class="card-text">
                                <h4 id="totalPromotions">12</h4>
                                <p>Khuyến mãi</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-percentage"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Hiệu ứng count up cho số
        function animateCount(element, target) {
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current);
            }, 30);
        }

        // Chạy animation khi trang load
        window.addEventListener('load', () => {
            animateCount(document.getElementById('totalProducts'), <?php echo $totalProducts; ?>);
            animateCount(document.getElementById('totalOrders'),  <?php echo $totalOrders; ?>);
            animateCount(document.getElementById('totalUsers'), <?php echo $totalUsers; ?>);
            animateCount(document.getElementById('totalPromotions'), <?php echo $totalPromotions; ?>);
        });
    </script>
</body>
</html>

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
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: #fff3cd !important;
    border-radius: 10px 10px 0 0 !important;
}

.btn-primary {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #d62c1a 0%, #a93226 100%);
    transform: translateY(-1px);
}

.text-primary { color: #e74c3c !important; }
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
