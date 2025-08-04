<?php
$title = 'Thêm thương hiệu - Admin';
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Thêm thương hiệu</h1>
    <a href="/admin/brands" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/brands/add">
            <div class="mb-3">
                <label for="brand_name" class="form-label">Tên thương hiệu *</label>
                <input type="text" class="form-control" id="brand_name" name="brand_name" required>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu thương hiệu
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/admin.php';
?>
