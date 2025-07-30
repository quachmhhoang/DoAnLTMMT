<?php
require_once __DIR__ . '/../../helpers/SessionHelper.php';
$title = 'Đơn hàng của tôi - CellPhone Store';
ob_start();
?>

<h2>Đơn hàng của tôi</h2>

<?php if (!empty($orders)): ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order->order_id; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order->order_date)); ?></td>
                                <td><?php echo number_format($order->total_amount, 0, ',', '.'); ?>đ</td>
                                <td>
                                    <a href="/orders/<?php echo $order->order_id; ?>" class="btn btn-sm btn-outline-primary">
                                        Xem chi tiết
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
        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
        <h4>Bạn chưa có đơn hàng nào</h4>
        <p class="text-muted">Hãy đặt hàng để theo dõi đơn hàng của bạn</p>
        <a href="/products" class="btn btn-primary">Mua sắm ngay</a>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout/app.php';
?>
