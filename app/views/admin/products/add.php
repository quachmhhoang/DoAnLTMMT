<?php
$title = 'Thêm sản phẩm - Admin';
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Thêm sản phẩm</h1>
    <a href="/admin/products" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/products/add" enctype="multipart/form-data" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên sản phẩm *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="price" class="form-label">Giá *</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Danh mục *</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Chọn danh mục</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category->category_id; ?>">
                                    <?php echo htmlspecialchars($category->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="brand_id" class="form-label">Thương hiệu *</label>
                        <select class="form-select" id="brand_id" name="brand_id" required>
                            <option value="">Chọn thương hiệu</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand->brand_id; ?>">
                                    <?php echo htmlspecialchars($brand->brand_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Mô tả</label>
                <textarea class="form-control" id="description" name="description" rows="4"></textarea>
            </div>
            
            <div class="mb-3">
                <label for="images" class="form-label">Hình ảnh sản phẩm</label>
                <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                <div class="form-text">Chọn nhiều hình ảnh (JPG, PNG, WebP). Tối đa 5MB mỗi file. Hình ảnh đầu tiên sẽ là hình ảnh chính.</div>
                <div id="image-preview" class="mt-3">
                    <div class="row" id="preview-container"></div>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu sản phẩm
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/admin.php';
?>

<script src="/assets/js/product-images.js"></script>

<style>
.image-preview-item {
    position: relative;
    margin-bottom: 15px;
}

.image-preview-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid #e9ecef;
    transition: border-color 0.3s ease;
}

.image-preview-item:first-child img {
    border-color: #007bff;
}

.image-preview-item .badge {
    position: absolute;
    top: 5px;
    left: 5px;
    z-index: 10;
}

.image-preview-item .remove-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    z-index: 10;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.drag-over {
    border: 2px dashed #007bff !important;
    background-color: rgba(0, 123, 255, 0.1);
}
</style>



