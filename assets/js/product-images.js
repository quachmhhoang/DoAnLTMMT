// Product Image Management JavaScript

function initializeImagePreview(inputId, previewContainerId) {
    const imageInput = document.getElementById(inputId);
    const previewContainer = document.getElementById(previewContainerId);
    
    if (!imageInput || !previewContainer) {
        return;
    }

    imageInput.addEventListener('change', function(e) {
        previewImages(e.target.files, previewContainer);
    });

    // Initialize empty state
    showEmptyState(previewContainer);
}

function previewImages(files, container) {
    container.innerHTML = '';
    
    if (!files || files.length === 0) {
        showEmptyState(container);
        return;
    }

    Array.from(files).forEach((file, index) => {
        if (!file.type.startsWith('image/')) {
            return;
        }

        // Validate file size
        if (file.size > 5 * 1024 * 1024) { // 5MB
            showAlert(`File "${file.name}" quá lớn. Vui lòng chọn file nhỏ hơn 5MB.`, 'warning');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const col = document.createElement('div');
            col.className = 'col-md-3 col-sm-4 col-6 mb-3';
            
            col.innerHTML = `
                <div class="card">
                    ${index === 0 ? '<div class="badge bg-primary position-absolute" style="top: 5px; left: 5px; z-index: 10;">Ảnh chính</div>' : ''}
                    <img src="${e.target.result}" 
                         class="card-img-top" 
                         style="height: 150px; object-fit: cover;" 
                         alt="Preview">
                    <div class="card-body p-2">
                        <small class="text-muted">${file.name}</small>
                        <div class="text-muted" style="font-size: 0.8em;">
                            ${formatFileSize(file.size)}
                        </div>
                    </div>
                </div>
            `;
            
            container.appendChild(col);
        };
        reader.readAsDataURL(file);
    });
}

function showEmptyState(container) {
    container.innerHTML = `
        <div class="col-12">
            <div class="text-center text-muted py-4 border border-dashed rounded">
                <i class="fas fa-images fa-3x mb-3"></i>
                <p class="mb-0">Chưa có hình ảnh nào được chọn</p>
                <small>Chọn file để xem trước</small>
            </div>
        </div>
    `;
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function deleteProductImage(imageId, imageCard) {
    if (!confirm('Bạn có chắc muốn xóa hình ảnh này? Hành động này không thể hoàn tác.')) {
        return;
    }

    const formData = new FormData();
    formData.append('image_id', imageId);
    
    // Show loading state
    const deleteBtn = imageCard.querySelector('.delete-image-btn');
    const originalText = deleteBtn.innerHTML;
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    deleteBtn.disabled = true;
    
    fetch('/admin/ajax/delete-image', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove image card with animation
            imageCard.style.transition = 'opacity 0.3s ease';
            imageCard.style.opacity = '0';
            setTimeout(() => {
                imageCard.remove();
                showAlert('Đã xóa hình ảnh thành công!', 'success');
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

function showAlert(message, type = 'info') {
    const alertContainer = document.querySelector('.container-fluid') || document.body;
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alert);
    
    setTimeout(() => {
        if (alert.parentElement) {
            alert.remove();
        }
    }, 5000);
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize image preview for add product form
    initializeImagePreview('images', 'preview-container');
    
    // Initialize image preview for edit product form
    initializeImagePreview('new-images', 'new-preview-container');
    
    // Handle delete image buttons
    document.querySelectorAll('.delete-image-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const imageId = this.dataset.imageId;
            const imageCard = this.closest('[data-image-id]');
            deleteProductImage(imageId, imageCard);
        });
    });
});
