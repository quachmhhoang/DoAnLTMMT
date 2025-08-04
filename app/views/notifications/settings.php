<?php
require_once __DIR__ . '/../helpers/SessionHelper.php';

if (!SessionHelper::isLoggedIn()) {
    header('Location: /login');
    exit();
}

$pageTitle = 'Cài đặt thông báo';
include __DIR__ . '/../layout/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog"></i> Cài đặt thông báo
                    </h3>
                </div>
                <div class="card-body">
                    <div id="loading" class="text-center py-4">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                        <p class="mt-2">Đang tải cài đặt...</p>
                    </div>

                    <div id="settingsForm" style="display: none;">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Thông tin:</strong> Bạn có thể tùy chỉnh loại thông báo nào bạn muốn nhận. Các thay đổi sẽ được áp dụng ngay lập tức.
                        </div>

                        <form id="notificationSettingsForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <i class="fas fa-shopping-cart text-primary"></i> Thông báo đơn hàng
                                            </h5>
                                            <p class="card-text text-muted">Nhận thông báo về trạng thái đơn hàng của bạn</p>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="order" name="order">
                                                <label class="form-check-label" for="order">
                                                    Bật thông báo đơn hàng
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <i class="fas fa-mobile-alt text-success"></i> Thông báo sản phẩm
                                            </h5>
                                            <p class="card-text text-muted">Nhận thông báo về sản phẩm mới và cập nhật</p>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="product" name="product">
                                                <label class="form-check-label" for="product">
                                                    Bật thông báo sản phẩm
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <i class="fas fa-percentage text-warning"></i> Thông báo khuyến mãi
                                            </h5>
                                            <p class="card-text text-muted">Nhận thông báo về các chương trình khuyến mãi</p>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="promotion" name="promotion">
                                                <label class="form-check-label" for="promotion">
                                                    Bật thông báo khuyến mãi
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <i class="fas fa-cogs text-info"></i> Thông báo hệ thống
                                            </h5>
                                            <p class="card-text text-muted">Nhận thông báo về bảo trì và cập nhật hệ thống</p>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="system" name="system">
                                                <label class="form-check-label" for="system">
                                                    Bật thông báo hệ thống
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if (SessionHelper::isAdmin()): ?>
                                <div class="col-md-12">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <i class="fas fa-user-shield text-danger"></i> Thông báo quản trị
                                            </h5>
                                            <p class="card-text text-muted">Nhận thông báo dành riêng cho quản trị viên</p>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="admin" name="admin">
                                                <label class="form-check-label" for="admin">
                                                    Bật thông báo quản trị
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="/" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Quay lại trang chủ
                                </a>
                                <button type="submit" class="btn btn-primary" id="saveButton">
                                    <i class="fas fa-save"></i> Lưu cài đặt
                                </button>
                            </div>
                        </form>
                    </div>

                    <div id="errorMessage" class="alert alert-danger" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span id="errorText"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 10px;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px 10px 0 0 !important;
}

.form-switch .form-check-input {
    width: 3em;
    height: 1.5em;
}

.form-switch .form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

.card-body .card {
    transition: all 0.3s ease;
}

.card-body .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
}

.spinner-border {
    color: #667eea;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadSettings();
    
    document.getElementById('notificationSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveSettings();
    });
});

function loadSettings() {
    fetch('/api/notifications/settings')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateSettings(data.settings);
                document.getElementById('loading').style.display = 'none';
                document.getElementById('settingsForm').style.display = 'block';
            } else {
                showError('Không thể tải cài đặt: ' + (data.error || 'Lỗi không xác định'));
            }
        })
        .catch(error => {
            console.error('Error loading settings:', error);
            showError('Lỗi khi tải cài đặt');
        });
}

function populateSettings(settings) {
    for (const [type, enabled] of Object.entries(settings)) {
        const checkbox = document.getElementById(type);
        if (checkbox) {
            checkbox.checked = enabled;
        }
    }
}

function saveSettings() {
    const saveButton = document.getElementById('saveButton');
    const originalText = saveButton.innerHTML;
    
    saveButton.disabled = true;
    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
    
    const formData = new FormData(document.getElementById('notificationSettingsForm'));
    const settings = {};
    
    // Get all checkboxes and their states
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        settings[checkbox.name] = checkbox.checked;
    });
    
    fetch('/api/notifications/settings', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ settings: settings })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Cài đặt đã được lưu thành công!');
        } else {
            showError('Không thể lưu cài đặt: ' + (data.error || 'Lỗi không xác định'));
        }
    })
    .catch(error => {
        console.error('Error saving settings:', error);
        showError('Lỗi khi lưu cài đặt');
    })
    .finally(() => {
        saveButton.disabled = false;
        saveButton.innerHTML = originalText;
    });
}

function showError(message) {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('settingsForm').style.display = 'none';
    document.getElementById('errorText').textContent = message;
    document.getElementById('errorMessage').style.display = 'block';
}

function showSuccess(message) {
    // Create a temporary success alert
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.innerHTML = `
        <i class="fas fa-check-circle"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const cardBody = document.querySelector('.card-body');
    cardBody.insertBefore(alertDiv, cardBody.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
