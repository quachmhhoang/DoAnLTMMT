<?php
require_once __DIR__ . '/../../helpers/SessionHelper.php';
$title = 'Giỏ hàng - CellPhone Store';
ob_start();
?>

<h2>Giỏ hàng của bạn</h2>

<?php if (!empty($cartItems)): ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($item->image_url): ?>
                                            <img src="<?php echo htmlspecialchars($item->image_url); ?>" alt="<?php echo htmlspecialchars($item->name); ?>" style="width: 60px; height: 60px; object-fit: cover;" class="me-3">
                                        <?php endif; ?>
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($item->name); ?></h6>
                                            <small class="text-muted"><?php echo substr(htmlspecialchars($item->description), 0, 50) . '...'; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo number_format($item->price, 0, ',', '.'); ?>đ</td>
                                <td>
                                    <form method="POST" action="/cart/update" class="d-inline">
                                        <input type="hidden" name="cart_detail_id" value="<?php echo $item->cart_detail_id; ?>">
                                        <div class="input-group" style="width: 120px;">
                                            <input type="number" class="form-control" name="quantity" value="<?php echo $item->quantity; ?>" min="1" max="10">
                                            <button type="submit" name="update_quantity" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                        </div>
                                    </form>
                                </td>
                                <td class="fw-bold"><?php echo number_format($item->subtotal, 0, ',', '.'); ?>đ</td>
                                <td>
                                    <form method="POST" action="/cart/update" class="d-inline">
                                        <input type="hidden" name="cart_detail_id" value="<?php echo $item->cart_detail_id; ?>">
                                        <button type="submit" name="remove_item" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="row">
                <div class="col-md-8"></div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5>Tổng cộng</h5>
                            <div class="d-flex justify-content-between">
                                <span>Tạm tính:</span>
                                <span><?php echo number_format($cartTotal, 0, ',', '.'); ?>đ</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Tổng tiền:</strong>
                                <strong class="text-danger"><?php echo number_format($cartTotal, 0, ',', '.'); ?>đ</strong>
                            </div>
                            <div class="d-grid mt-3">
                                <a href="/checkout" class="btn btn-success">Thanh toán</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="text-center">
        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
        <h4>Giỏ hàng của bạn đang trống</h4>
        <p class="text-muted">Hãy thêm một số sản phẩm vào giỏ hàng</p>
        <a href="/products" class="btn btn-primary">Tiếp tục mua sắm</a>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout/app.php';
?>
