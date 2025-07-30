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
                        <i class="fas fa-box fa-2x"></i>
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
                        <h4>100%</h4>
                        <p>Hệ thống</p>
                    </div>
                    <div>
                        <i class="fas fa-chart-line fa-2x"></i>
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

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout/admin.php';
?>
