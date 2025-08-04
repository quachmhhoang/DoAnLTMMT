<?php
if (!SessionHelper::isAdmin()) {
    header('Location: /login');
    exit();
}

$pageTitle = 'Thêm khuyến mãi';
include __DIR__ . '/../../layout/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tags"></i> Thêm khuyến mãi mới
                    </h3>
                    <div class="card-tools">
                        <a href="/admin/promotions" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (SessionHelper::getFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= SessionHelper::getFlash('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/admin/promotions/add" id="promotionForm">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="promotion_name" class="form-label">
                                        <i class="fas fa-tag"></i> Tên khuyến mãi <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="promotion_name" 
                                           name="promotion_name" 
                                           placeholder="Nhập tên khuyến mãi"
                                           required>
                                    <div class="form-text">Ví dụ: Giảm giá mùa hè, Black Friday Sale</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">
                                        <i class="fas fa-align-left"></i> Mô tả
                                    </label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="3"
                                              placeholder="Nhập mô tả chi tiết về khuyến mãi"></textarea>
                                    <div class="form-text">Mô tả chi tiết về chương trình khuyến mãi</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="discount_percent" class="form-label">
                                        <i class="fas fa-percentage"></i> Phần trăm giảm giá <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control" 
                                               id="discount_percent" 
                                               name="discount_percent" 
                                               min="1" 
                                               max="100" 
                                               step="0.01"
                                               placeholder="0"
                                               required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="form-text">Từ 1% đến 100%</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">
                                        <i class="fas fa-calendar-alt"></i> Ngày bắt đầu <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="start_date" 
                                           name="start_date" 
                                           min="<?= date('Y-m-d') ?>"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">
                                        <i class="fas fa-calendar-check"></i> Ngày kết thúc <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="end_date" 
                                           name="end_date" 
                                           min="<?= date('Y-m-d') ?>"
                                           required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Lưu ý:</strong> Khi tạo khuyến mãi, hệ thống sẽ tự động gửi thông báo đến tất cả người dùng về chương trình khuyến mãi mới.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="/admin/promotions" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu khuyến mãi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    startDateInput.min = today;
    endDateInput.min = today;
    
    // Update end date minimum when start date changes
    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = '';
        }
    });
    
    // Form validation
    document.getElementById('promotionForm').addEventListener('submit', function(e) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        
        if (endDate <= startDate) {
            e.preventDefault();
            alert('Ngày kết thúc phải sau ngày bắt đầu!');
            return false;
        }
        
        const discountPercent = parseFloat(document.getElementById('discount_percent').value);
        if (discountPercent <= 0 || discountPercent > 100) {
            e.preventDefault();
            alert('Phần trăm giảm giá phải từ 1% đến 100%!');
            return false;
        }
    });
});
</script>

<style>
.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.card-header .btn-secondary {
    background-color: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.3);
    color: white;
}

.card-header .btn-secondary:hover {
    background-color: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.4);
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.form-label i {
    margin-right: 5px;
    color: #6c757d;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
}

.alert-info {
    border-left: 4px solid #17a2b8;
}
</style>

<?php include __DIR__ . '/../../layout/admin_footer.php'; ?>
