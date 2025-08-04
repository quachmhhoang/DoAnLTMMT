<?php
$title = 'Xóa sản phẩm - Admin';
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Xóa sản phẩm</h1>
    <a href="/admin/products" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="alert alert-danger">
    <h4 class="alert-heading">Bạn có chắc chắn muốn xóa sản phẩm này?</h4>
    <p>Sản phẩm sẽ bị xóa vĩnh viễn và không thể khôi phục.</p>
</div>

<div class="card mb-4">
    <div class="card-body d-flex align-items-center">
        <?php if (!empty($product->thumbnail)): ?>
            <img src="<?php echo htmlspecialchars($product->thumbnail); ?>" alt="<?php echo htmlspecialchars($product->name); ?>" class="me-3" style="width: 150px; height: auto; border-radius: 5px;">
        <?php endif; ?>
        <div>
            <h5><?php echo htmlspecialchars($product->name); ?></h5>
            <p class="mb-1"><strong>Giá:</strong> <?php echo number_format($product->price, 0, ',', '.') . ' ₫'; ?></p>
            <p class="mb-1"><strong>Danh mục:</strong> <?php echo htmlspecialchars($product->category_name ?? ''); ?></p>
            <p class="mb-0"><strong>Thương hiệu:</strong> <?php echo htmlspecialchars($product->brand_name ?? ''); ?></p>
        </div>
    </div>
</div>

<form method="POST" action="/admin/products/delete/<?php echo $product->product_id; ?>">
    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <a href="/admin/products" class="btn btn-secondary">
            <i class="fas fa-times"></i> Hủy
        </a>
        <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash-alt"></i> Xác nhận xóa
        </button>
    </div>
</form>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/admin.php';
?>
