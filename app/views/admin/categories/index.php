<?php
$title = 'Quản lý danh mục - Admin';
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Quản lý danh mục</h1>
    <a href="/admin/categories/add" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm danh mục
    </a>
</div>

<?php if (!empty($categories)): ?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên danh mục</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo $category->category_id; ?></td>
                            <td><?php echo htmlspecialchars($category->name); ?></td>
                            <td>
                                <a href="/admin/categories/edit/<?php echo $category->category_id; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <a href="/admin/categories/delete/<?php echo $category->category_id; ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Bạn có chắc muốn xóa danh mục này?')">
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
    <i class="fas fa-tags fa-3x text-muted mb-3"></i>
    <h4>Chưa có danh mục nào</h4>
    <p class="text-muted">Hãy thêm danh mục đầu tiên</p>
    <a href="/admin/categories/add" class="btn btn-primary">Thêm danh mục</a>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/admin.php';
?>
