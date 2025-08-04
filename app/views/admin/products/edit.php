<?php
$title = 'Sửa sản phẩm - Admin';
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Sửa sản phẩm</h1>
    <a href="/admin/products" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<?php if (isset($product) && is_object($product)): ?>
<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/products/edit/<?php echo $product->product_id; ?>" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên sản phẩm *</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product->name); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="price" class="form-label">Giá *</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo $product->price; ?>" required>
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
                                <option value="<?php echo $category->category_id; ?>" 
                                        <?php echo ($product->category_id == $category->category_id) ? 'selected' : ''; ?>>
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
                                <option value="<?php echo $brand->brand_id; ?>"
                                        <?php echo ($product->brand_id == $brand->brand_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand->brand_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Mô tả</label>
                <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($product->description); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="new-images" class="form-label">Thêm hình ảnh mới</label>
                <input type="file" class="form-control" id="new-images" name="images[]" multiple accept="image/*">
                <div class="form-text">Chọn nhiều hình ảnh (JPG, PNG, WebP). Tối đa 5MB mỗi file.</div>
                <div id="new-image-preview" class="mt-2">
                    <div class="row" id="new-preview-container"></div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật sản phẩm
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Image Management Section -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-images"></i> Quản lý hình ảnh sản phẩm
        </h5>
    </div>
    <div class="card-body">
        <!-- Current Images -->
        <?php if (!empty($images)): ?>
        <div class="mb-4">
            <h6>Hình ảnh hiện tại:</h6>
            <div class="row" id="current-images">
                <?php foreach ($images as $index => $image): ?>
                <div class="col-md-3 mb-3" data-image-id="<?php echo $image->image_id; ?>">
                    <div class="card position-relative">
                        <?php if ($index === 0): ?>
                        <div class="badge bg-primary position-absolute" style="top: 5px; left: 5px; z-index: 10;">
                            Ảnh chính
                        </div>
                        <?php endif; ?>
                        <img src="<?php echo htmlspecialchars($image->image_url); ?>"
                             class="card-img-top"
                             style="height: 200px; object-fit: cover; cursor: pointer;"
                             alt="Product Image"
                             onclick="previewImage('<?php echo htmlspecialchars($image->image_url); ?>')">
                        <div class="card-body p-2">
                            <button type="button"
                                    class="btn btn-danger btn-sm w-100 delete-image-btn"
                                    data-image-id="<?php echo $image->image_id; ?>"
                                    data-image-url="<?php echo htmlspecialchars($image->image_url); ?>">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info" id="no-images-alert">
            <i class="fas fa-info-circle"></i> Sản phẩm này chưa có hình ảnh nào.
        </div>
        <?php endif; ?>


    </div>
</div>
<?php else: ?>
<div class="alert alert-danger">
    <h4>Lỗi</h4>
    <p>Không tìm thấy sản phẩm để chỉnh sửa.</p>
    <a href="/admin/products" class="btn btn-primary">Quay lại danh sách sản phẩm</a>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/admin.php';
?>

<script src="/assets/js/product-images.js"></script>

<script>
// Additional functionality specific to edit form
document.addEventListener('DOMContentLoaded', function() {
    // Form submission handling
    const form = document.querySelector('form[enctype="multipart/form-data"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';
                submitBtn.disabled = true;
            }
        });
    }

    // Handle delete image buttons
    document.querySelectorAll('.delete-image-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const imageId = this.dataset.imageId;
            const imageUrl = this.dataset.imageUrl;
            const imageCard = this.closest('[data-image-id]');
            deleteProductImage(imageId, imageCard, imageUrl);
        });
    });
});

// Function to delete product image
function deleteProductImage(imageId, imageCard, imageUrl) {
    if (!confirm('Bạn có chắc muốn xóa hình ảnh này? Hành động này không thể hoàn tác.')) {
        return;
    }

    const formData = new FormData();
    formData.append('image_id', imageId);

    // Show loading state
    const deleteBtn = imageCard.querySelector('.delete-image-btn');
    const originalText = deleteBtn.innerHTML;
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xóa...';
    deleteBtn.disabled = true;

    fetch('/admin/ajax/delete-image', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove image card with animation
            imageCard.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            imageCard.style.opacity = '0';
            imageCard.style.transform = 'scale(0.8)';

            setTimeout(() => {
                imageCard.remove();
                showAlert('Đã xóa hình ảnh thành công!', 'success');

                // Check if no images left
                const remainingImages = document.querySelectorAll('#current-images [data-image-id]');
                if (remainingImages.length === 0) {
                    const currentImagesContainer = document.getElementById('current-images').parentElement;
                    currentImagesContainer.innerHTML = `
                        <div class="alert alert-info" id="no-images-alert">
                            <i class="fas fa-info-circle"></i> Sản phẩm này chưa có hình ảnh nào.
                        </div>
                    `;
                }
            }, 300);
        } else {
            showAlert(data.message || 'Có lỗi xảy ra khi xóa hình ảnh!', 'danger');
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Có lỗi xảy ra khi xóa hình ảnh!', 'danger');
        deleteBtn.innerHTML = originalText;
        deleteBtn.disabled = false;
    });
}

// Function to preview image in modal
function previewImage(imageSrc) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xem ảnh chi tiết</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="${imageSrc}" class="img-fluid" alt="Product Image" style="max-height: 70vh;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();

    // Remove modal from DOM when hidden
    modal.addEventListener('hidden.bs.modal', function() {
        modal.remove();
    });
}

// Function to show alerts
function showAlert(message, type = 'info') {
    const alertContainer = document.querySelector('.container-fluid') || document.body;
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;';
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    alertContainer.appendChild(alert);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alert.parentElement) {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 150);
        }
    }, 5000);
}
</script>
