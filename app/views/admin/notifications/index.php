<?php
require_once __DIR__ . '/../../helpers/SessionHelper.php';

if (!SessionHelper::isAdmin()) {
    header('Location: /login');
    exit();
}

$pageTitle = 'Quản lý thông báo';
include __DIR__ . '/../../layout/admin_header.php';
?>

<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="total-notifications">0</h4>
                            <p class="card-text">Tổng thông báo</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-bell fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="unread-notifications">0</h4>
                            <p class="card-text">Chưa đọc</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-bell-slash fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="notifications-24h">0</h4>
                            <p class="card-text">24 giờ qua</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="notifications-7d">0</h4>
                            <p class="card-text">7 ngày qua</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-week fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Send Notification Form -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-paper-plane"></i> Gửi thông báo
                    </h3>
                </div>
                <div class="card-body">
                    <form id="sendNotificationForm">
                        <div class="mb-3">
                            <label for="notificationTitle" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="notificationTitle" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notificationMessage" class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="notificationMessage" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notificationType" class="form-label">Loại thông báo</label>
                            <select class="form-select" id="notificationType">
                                <option value="system">Hệ thống</option>
                                <option value="promotion">Khuyến mãi</option>
                                <option value="product">Sản phẩm</option>
                                <option value="admin">Quản trị</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notificationTarget" class="form-label">Đối tượng</label>
                            <select class="form-select" id="notificationTarget">
                                <option value="all">Tất cả người dùng</option>
                                <option value="role">Theo vai trò</option>
                                <option value="user">Người dùng cụ thể</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="targetValueGroup" style="display: none;">
                            <label for="targetValue" class="form-label">Giá trị đối tượng</label>
                            <select class="form-select" id="targetValue">
                                <option value="admin">Admin</option>
                                <option value="customer">Khách hàng</option>
                            </select>
                            <input type="number" class="form-control" id="targetValueUser" placeholder="ID người dùng" style="display: none;">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane"></i> Gửi thông báo
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Danh sách thông báo
                    </h3>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="typeFilter" style="width: auto;">
                            <option value="">Tất cả loại</option>
                            <option value="order">Đơn hàng</option>
                            <option value="product">Sản phẩm</option>
                            <option value="promotion">Khuyến mãi</option>
                            <option value="system">Hệ thống</option>
                            <option value="admin">Quản trị</option>
                        </select>
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshNotifications()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="notificationsContainer">
                        <div class="text-center py-4">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Đang tải...</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <nav aria-label="Notification pagination" id="paginationContainer" style="display: none;">
                        <ul class="pagination justify-content-center" id="pagination">
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xóa thông báo này?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>

<style>
.notification-item {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 10px;
    padding: 15px;
    transition: all 0.3s ease;
}

.notification-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.notification-type-badge {
    font-size: 0.75em;
    padding: 4px 8px;
}

.notification-meta {
    font-size: 0.875em;
    color: #6c757d;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
}
</style>

<script>
let currentPage = 1;
let currentType = '';
let deleteNotificationId = null;

document.addEventListener('DOMContentLoaded', function() {
    loadStatistics();
    loadNotifications();

    // Form submission
    document.getElementById('sendNotificationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        sendNotification();
    });

    // Target type change
    document.getElementById('notificationTarget').addEventListener('change', function() {
        const targetValueGroup = document.getElementById('targetValueGroup');
        const targetValue = document.getElementById('targetValue');
        const targetValueUser = document.getElementById('targetValueUser');

        if (this.value === 'role') {
            targetValueGroup.style.display = 'block';
            targetValue.style.display = 'block';
            targetValueUser.style.display = 'none';
        } else if (this.value === 'user') {
            targetValueGroup.style.display = 'block';
            targetValue.style.display = 'none';
            targetValueUser.style.display = 'block';
        } else {
            targetValueGroup.style.display = 'none';
        }
    });

    // Type filter change
    document.getElementById('typeFilter').addEventListener('change', function() {
        currentType = this.value;
        currentPage = 1;
        loadNotifications();
    });

    // Delete confirmation
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (deleteNotificationId) {
            deleteNotification(deleteNotificationId);
        }
    });
});

function loadStatistics() {
    fetch('/api/admin/notifications?stats=1')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.stats) {
                document.getElementById('total-notifications').textContent = data.stats.total_notifications || 0;
                document.getElementById('unread-notifications').textContent = data.stats.unread_notifications || 0;
                document.getElementById('notifications-24h').textContent = data.stats.notifications_24h || 0;
                document.getElementById('notifications-7d').textContent = data.stats.notifications_7d || 0;
            }
        })
        .catch(error => console.error('Error loading statistics:', error));
}

function loadNotifications() {
    const limit = 10;
    const offset = (currentPage - 1) * limit;
    const typeParam = currentType ? `&type=${currentType}` : '';

    fetch(`/api/admin/notifications?limit=${limit}&offset=${offset}${typeParam}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayNotifications(data.notifications);
                displayPagination(data.current_page, data.total_pages);
            } else {
                showError('Không thể tải danh sách thông báo');
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            showError('Lỗi khi tải danh sách thông báo');
        });
}

function displayNotifications(notifications) {
    const container = document.getElementById('notificationsContainer');

    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                <p class="text-muted">Không có thông báo nào</p>
            </div>
        `;
        return;
    }

    container.innerHTML = notifications.map(notification => `
        <div class="notification-item">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center mb-2">
                        <h6 class="mb-0 me-2">${escapeHtml(notification.title)}</h6>
                        <span class="badge notification-type-badge ${getTypeBadgeClass(notification.type)}">
                            ${getTypeLabel(notification.type)}
                        </span>
                    </div>
                    <p class="mb-2">${escapeHtml(notification.message)}</p>
                    <div class="notification-meta">
                        <small>
                            <i class="fas fa-clock"></i> ${formatDate(notification.created_at)}
                            ${notification.created_by_username ? `| <i class="fas fa-user"></i> ${escapeHtml(notification.created_by_username)}` : ''}
                            | <i class="fas fa-users"></i> ${getTargetLabel(notification.target_type, notification.target_value)}
                        </small>
                    </div>
                </div>
                <div class="ms-3">
                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteNotification(${notification.notification_id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

function displayPagination(currentPage, totalPages) {
    const container = document.getElementById('paginationContainer');
    const pagination = document.getElementById('pagination');

    if (totalPages <= 1) {
        container.style.display = 'none';
        return;
    }

    container.style.display = 'block';

    let paginationHTML = '';

    // Previous button
    paginationHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Trước</a>
        </li>
    `;

    // Page numbers
    for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
        paginationHTML += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
            </li>
        `;
    }

    // Next button
    paginationHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Sau</a>
        </li>
    `;

    pagination.innerHTML = paginationHTML;
}

function changePage(page) {
    if (page < 1) return;
    currentPage = page;
    loadNotifications();
}

function sendNotification() {
    const title = document.getElementById('notificationTitle').value.trim();
    const message = document.getElementById('notificationMessage').value.trim();
    const type = document.getElementById('notificationType').value;
    const target = document.getElementById('notificationTarget').value;

    let targetValue = null;
    if (target === 'role') {
        targetValue = document.getElementById('targetValue').value;
    } else if (target === 'user') {
        targetValue = document.getElementById('targetValueUser').value;
    }

    if (!title || !message) {
        showError('Vui lòng nhập đầy đủ tiêu đề và nội dung');
        return;
    }

    const data = {
        title: title,
        message: message,
        type: type,
        target: target,
        target_value: targetValue
    };

    fetch('/api/admin/notifications/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Gửi thông báo thành công!');
            document.getElementById('sendNotificationForm').reset();
            document.getElementById('targetValueGroup').style.display = 'none';
            loadNotifications();
            loadStatistics();
        } else {
            showError(data.error || 'Không thể gửi thông báo');
        }
    })
    .catch(error => {
        console.error('Error sending notification:', error);
        showError('Lỗi khi gửi thông báo');
    });
}

function confirmDeleteNotification(notificationId) {
    deleteNotificationId = notificationId;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function deleteNotification(notificationId) {
    fetch('/api/admin/notifications/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ notification_id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Xóa thông báo thành công!');
            loadNotifications();
            loadStatistics();
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
            modal.hide();
        } else {
            showError(data.error || 'Không thể xóa thông báo');
        }
    })
    .catch(error => {
        console.error('Error deleting notification:', error);
        showError('Lỗi khi xóa thông báo');
    });
}

function refreshNotifications() {
    loadNotifications();
    loadStatistics();
}

// Utility functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getTypeBadgeClass(type) {
    const classes = {
        'order': 'bg-primary',
        'product': 'bg-success',
        'promotion': 'bg-warning',
        'system': 'bg-info',
        'admin': 'bg-secondary'
    };
    return classes[type] || 'bg-secondary';
}

function getTypeLabel(type) {
    const labels = {
        'order': 'Đơn hàng',
        'product': 'Sản phẩm',
        'promotion': 'Khuyến mãi',
        'system': 'Hệ thống',
        'admin': 'Quản trị'
    };
    return labels[type] || type;
}

function getTargetLabel(targetType, targetValue) {
    if (targetType === 'all') return 'Tất cả';
    if (targetType === 'role') return `Vai trò: ${targetValue}`;
    if (targetType === 'user') return `Người dùng: ${targetValue}`;
    return targetType;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('vi-VN');
}

function showSuccess(message) {
    // You can implement a toast notification here
    alert(message);
}

function showError(message) {
    // You can implement a toast notification here
    alert(message);
}
</script>

<?php include __DIR__ . '/../../layout/admin_footer.php'; ?>
