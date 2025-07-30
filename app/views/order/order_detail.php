<?php
require_once __DIR__ . '/../../helpers/SessionHelper.php';
$title = 'Chi tiết đơn hàng';
if (isset($orderInfo) && is_object($orderInfo)) {
    $title = 'Chi tiết đơn hàng #' . $orderInfo->order_id;
}
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Chi tiết đơn hàng <?php echo isset($orderInfo) && is_object($orderInfo) ? '#' . $orderInfo->order_id : ''; ?></h2>
    <a href="/orders" class="btn btn-secondary">Quay lại</a>
</div>

<?php if (isset($orderInfo) && is_object($orderInfo) && isset($orderDetails) && is_array($orderDetails)): ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Sản phẩm đã đặt</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderDetails as $detail): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (isset($detail->image_url) && $detail->image_url): ?>
                                                <img src="<?php echo htmlspecialchars($detail->image_url); ?>" alt="<?php echo htmlspecialchars($detail->name); ?>" style="width: 50px; height: 50px; object-fit: cover;" class="me-3">
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($detail->name); ?></h6>
                                                <small class="text-muted"><?php echo substr(htmlspecialchars($detail->description), 0, 50) . '...'; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $detail->quantity; ?></td>
                                    <td><?php echo number_format($detail->total_price, 0, ',', '.'); ?>đ</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Thông tin đơn hàng</h5>
            </div>
            <div class="card-body">
                <p><strong>Mã đơn hàng:</strong> #<?php echo $orderInfo->order_id; ?></p>
                <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($orderInfo->order_date)); ?></p>
                <p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($orderInfo->full_name); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($orderInfo->email); ?></p>
                <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($orderInfo->phone); ?></p>
                <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($orderInfo->address); ?></p>
                <hr>
                <div class="d-flex justify-content-between">
                    <strong>Tổng tiền:</strong>
                    <strong class="text-danger"><?php echo number_format($orderInfo->total_amount, 0, ',', '.'); ?>đ</strong>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-warning">
    <h4>Không tìm thấy đơn hàng</h4>
    <p>Đơn hàng không tồn tại hoặc bạn không có quyền xem đơn hàng này.</p>
    <a href="/orders" class="btn btn-primary">Quay lại danh sách đơn hàng</a>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout/app.php';
?>
