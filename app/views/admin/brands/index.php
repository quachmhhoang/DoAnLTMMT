<?php
require_once __DIR__ . '/../../../helpers/SessionHelper.php';
$title = 'Quản lý thương hiệu - Admin';
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Quản lý thương hiệu</h1>
    <a href="/admin/brands/add" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm thương hiệu
    </a>
</div>

<?php if (!empty($brands)): ?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên thương hiệu</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($brands as $brand): ?>
                        <tr>
                            <td><?php echo $brand->brand_id; ?></td>
                            <td><?php echo htmlspecialchars($brand->brand_name); ?></td>
                            <td>
                                <a href="/admin/brands/edit/<?php echo $brand->brand_id; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <a href="/admin/brands/delete/<?php echo $brand->brand_id; ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Bạn có chắc muốn xóa thương hiệu này?')">
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
    <i class="fas fa-copyright fa-3x text-muted mb-3"></i>
    <h4>Chưa có thương hiệu nào</h4>
    <p class="text-muted">Hãy thêm thương hiệu đầu tiên</p>
    <a href="/admin/brands/add" class="btn btn-primary">Thêm thương hiệu</a>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/admin.php';
?>
