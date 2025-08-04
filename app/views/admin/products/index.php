<?php
$title = 'Quản lý sản phẩm - Admin';
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Quản lý sản phẩm</h1>
    <a href="/admin/products/add" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm sản phẩm
    </a>
</div>

<?php if (!empty($products)): ?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Danh mục</th>
                        <th>Thương hiệu</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product->product_id; ?></td>
                            <td><?php echo htmlspecialchars($product->name); ?></td>
                            <td><?php echo number_format($product->price, 0, ',', '.'); ?>đ</td>
                            <td><?php echo htmlspecialchars($product->category_name ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($product->brand_name ?? 'N/A'); ?></td>
                            <td>
                                <a href="/admin/products/edit/<?php echo $product->product_id; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger delete-product-btn"
                                        data-product-id="<?php echo $product->product_id; ?>"
                                        data-product-name="<?php echo htmlspecialchars($product->name); ?>">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
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
    <i class="fas fa-box fa-3x text-muted mb-3"></i>
    <h4>Chưa có sản phẩm nào</h4>
    <p class="text-muted">Hãy thêm sản phẩm đầu tiên</p>
    <a href="/admin/products/add" class="btn btn-primary">Thêm sản phẩm</a>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/admin.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete product buttons
    document.querySelectorAll('.delete-product-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            showDeleteConfirmation(productId, productName);
        });
    });
});

function showDeleteConfirmation(productId, productName) {
    // Create confirmation modal
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        Xác nhận xóa sản phẩm
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="deletion-info">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Đang kiểm tra...</span>
                            </div>
                            <p class="mt-2">Đang kiểm tra thông tin sản phẩm...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-btn" disabled>
                        <i class="fas fa-trash"></i> Xóa sản phẩm
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();

    // Fetch deletion info
    fetch('/admin/ajax/get-deletion-info', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        const infoDiv = document.getElementById('deletion-info');
        const confirmBtn = document.getElementById('confirm-delete-btn');

        if (data.success) {
            const info = data.info;
            let content = `<p><strong>Sản phẩm:</strong> ${productName}</p>`;

            if (info.can_delete) {
                content += '<div class="alert alert-info"><i class="fas fa-info-circle"></i> Sản phẩm có thể được xóa an toàn.</div>';

                if (info.cart_items > 0) {
                    content += `<p><i class="fas fa-shopping-cart text-warning"></i> Sẽ xóa ${info.cart_items} mục trong giỏ hàng</p>`;
                }
                if (info.completed_orders > 0) {
                    content += `<p><i class="fas fa-check-circle text-success"></i> Có ${info.completed_orders} đơn hàng đã hoàn thành (sẽ được giữ lại)</p>`;
                }
                if (info.images > 0) {
                    content += `<p><i class="fas fa-images text-info"></i> Sẽ xóa ${info.images} hình ảnh</p>`;
                }

                confirmBtn.disabled = false;
                confirmBtn.onclick = () => deleteProduct(productId);
            } else {
                content += '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Không thể xóa sản phẩm này!</div>';

                if (info.pending_orders > 0) {
                    content += `<p><i class="fas fa-clock text-danger"></i> Có ${info.pending_orders} đơn hàng đang xử lý</p>`;
                }

                content += '<p class="text-muted">Vui lòng hoàn thành hoặc hủy các đơn hàng trước khi xóa sản phẩm.</p>';
            }

            infoDiv.innerHTML = content;
        } else {
            infoDiv.innerHTML = '<div class="alert alert-danger">Có lỗi xảy ra khi kiểm tra thông tin sản phẩm.</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('deletion-info').innerHTML = '<div class="alert alert-danger">Có lỗi xảy ra khi kiểm tra thông tin sản phẩm.</div>';
    });

    // Remove modal from DOM when hidden
    modal.addEventListener('hidden.bs.modal', function() {
        modal.remove();
    });
}

function deleteProduct(productId) {
    window.location.href = `/admin/products/delete/${productId}`;
}
</script>
