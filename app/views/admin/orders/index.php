<?php
require_once __DIR__ . '/../../../helpers/SessionHelper.php';
$title = 'Quản lý đơn hàng - Admin';
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Quản lý đơn hàng</h1>
</div>

<?php if (!empty($orders)): ?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Mã đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Email</th>
                        <th>Tổng tiền</th>
                        <th>Ngày đặt</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order->order_id; ?></td>
                            <td><?php echo htmlspecialchars($order->full_name); ?></td>
                            <td><?php echo htmlspecialchars($order->email); ?></td>
                            <td><?php echo number_format($order->total_amount, 0, ',', '.'); ?>đ</td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order->order_date)); ?></td>
                            <td>
                                <a href="/orders/<?php echo $order->order_id; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                                <a href="/admin/orders/delete/<?php echo $order->order_id; ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Bạn có chắc muốn xóa đơn hàng này?')">
                                    <i class="fas fa-trash"></i> Xóa
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<div class="text-center">
    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
    <h4>Chưa có đơn hàng nào</h4>
    <p class="text-muted">Đơn hàng sẽ hiển thị ở đây khi khách hàng đặt hàng</p>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/admin.php';
?>
