<?php
require_once __DIR__ . '/../../helpers/SessionHelper.php';
$title = 'Thanh toán - CellPhone Store';
ob_start();
?>

<h2>Thanh toán</h2>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Thông tin đơn hàng</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($item->image_url): ?>
                                                <img src="<?php echo htmlspecialchars($item->image_url); ?>" alt="<?php echo htmlspecialchars($item->name); ?>" style="width: 50px; height: 50px; object-fit: cover;" class="me-3">
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($item->name); ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo number_format($item->price, 0, ',', '.'); ?>đ</td>
                                    <td><?php echo $item->quantity; ?></td>
                                    <td><?php echo number_format($item->subtotal, 0, ',', '.'); ?>đ</td>
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
                <h5>Tổng kết đơn hàng</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Tạm tính:</span>
                    <span><?php echo number_format($cartTotal, 0, ',', '.'); ?>đ</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Phí vận chuyển:</span>
                    <span>Miễn phí</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <strong>Tổng cộng:</strong>
                    <strong class="text-danger"><?php echo number_format($cartTotal, 0, ',', '.'); ?>đ</strong>
                </div>
                
                <form method="POST" action="/checkout">
                    <div class="mb-3">
                        <label class="form-label">Phương thức thanh toán</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                            <label class="form-check-label" for="cod">
                                Thanh toán khi nhận hàng (COD)
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">Đặt hàng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout/app.php';
?>
