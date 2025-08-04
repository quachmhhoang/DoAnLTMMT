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
                                <a href="/admin/products/delete/<?php echo $product->product_id; ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
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
