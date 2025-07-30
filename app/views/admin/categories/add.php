<?php
require_once __DIR__ . '/../../../helpers/SessionHelper.php';
$title = 'Thêm danh mục - Admin';
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Thêm danh mục</h1>
    <a href="/admin/categories" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/categories/add">
            <div class="mb-3">
                <label for="name" class="form-label">Tên danh mục *</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu danh mục
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/admin.php';
?>
