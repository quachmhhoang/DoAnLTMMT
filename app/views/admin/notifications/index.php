<?php
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
            <div class="card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%) !important; border: none !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="total-notifications" style="color: #ffffff !important; font-weight: 900 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.5) !important; font-size: 2.5rem !important;">0</h4>
                            <p class="card-text" style="color: #ffffff !important; font-weight: 600 !important; text-shadow: 1px 1px 2px rgba(0,0,0,0.3) !important;">Tổng thông báo</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-bell fa-2x" style="color: #ffffff !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.5) !important; filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3)) !important;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%) !important; border: none !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="unread-notifications" style="color: #ffffff !important; font-weight: 900 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.5) !important; font-size: 2.5rem !important;">0</h4>
                            <p class="card-text" style="color: #ffffff !important; font-weight: 600 !important; text-shadow: 1px 1px 2px rgba(0,0,0,0.3) !important;">Chưa đọc</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-bell-slash fa-2x" style="color: #ffffff !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.5) !important; filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3)) !important;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%) !important; border: none !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="notifications-24h" style="color: #ffffff !important; font-weight: 900 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.5) !important; font-size: 2.5rem !important;">0</h4>
                            <p class="card-text" style="color: #ffffff !important; font-weight: 600 !important; text-shadow: 1px 1px 2px rgba(0,0,0,0.3) !important;">24 giờ qua</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x" style="color: #ffffff !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.5) !important; filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3)) !important;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%) !important; border: none !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="notifications-7d" style="color: #ffffff !important; font-weight: 900 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.5) !important; font-size: 2.5rem !important;">0</h4>
                            <p class="card-text" style="color: #ffffff !important; font-weight: 600 !important; text-shadow: 1px 1px 2px rgba(0,0,0,0.3) !important;">7 ngày qua</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-week fa-2x" style="color: #ffffff !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.5) !important; filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3)) !important;"></i>
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
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: #fff3cd !important;
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

// Auto-update notification counts
let lastUpdateTime = new Date().toISOString().slice(0, 19).replace('T', ' ');
let updateInterval = null;

function startAutoUpdate() {
    // Cập nhật mỗi 30 giây
    updateInterval = setInterval(() => {
        updateNotificationCounts();
    }, 30000);

    console.log('✅ Auto-update notification counts started (30s interval)');
}

function stopAutoUpdate() {
    if (updateInterval) {
        clearInterval(updateInterval);
        updateInterval = null;
        console.log('⏹️ Auto-update notification counts stopped');
    }
}

async function updateNotificationCounts() {
    try {
        // Lấy thống kê mới
        const response = await fetch('/api/admin/notifications?stats=1');
        if (!response.ok) return;

        const data = await response.json();
        if (!data.success) return;

        const stats = data.stats;
        const currentStats = {
            total: parseInt(document.getElementById('total-notifications').textContent) || 0,
            unread: parseInt(document.getElementById('unread-notifications').textContent) || 0,
            today: parseInt(document.getElementById('notifications-24h').textContent) || 0,
            thisWeek: parseInt(document.getElementById('notifications-week').textContent) || 0
        };

        // Kiểm tra có thay đổi không
        let hasChanges = false;

        if (stats.total !== currentStats.total) {
            updateCountWithAnimation('total-notifications', stats.total);
            hasChanges = true;
        }

        if (stats.unread !== currentStats.unread) {
            updateCountWithAnimation('unread-notifications', stats.unread);
            hasChanges = true;

            // Nếu có thông báo chưa đọc mới, hiển thị notification
            if (stats.unread > currentStats.unread) {
                showNewNotificationAlert(stats.unread - currentStats.unread);
            }
        }

        if (stats.today !== currentStats.today) {
            updateCountWithAnimation('notifications-24h', stats.today);
            hasChanges = true;
        }

        if (stats.this_week !== currentStats.thisWeek) {
            updateCountWithAnimation('notifications-week', stats.this_week);
            hasChanges = true;
        }

        // Nếu có thay đổi, reload danh sách thông báo
        if (hasChanges) {
            console.log('🔔 Detected notification changes, reloading list...');
            loadNotifications();
        }

        lastUpdateTime = new Date().toISOString().slice(0, 19).replace('T', ' ');

    } catch (error) {
        console.error('❌ Error updating notification counts:', error);
    }
}

function updateCountWithAnimation(elementId, newValue) {
    const element = document.getElementById(elementId);
    if (!element) return;

    const currentValue = parseInt(element.textContent) || 0;

    // Thêm animation class
    element.parentElement.parentElement.parentElement.classList.add('notification-update');

    // Animate số từ current đến new value
    animateCount(element, currentValue, newValue);

    // Xóa animation class sau 1 giây
    setTimeout(() => {
        element.parentElement.parentElement.parentElement.classList.remove('notification-update');
    }, 1000);
}

function animateCount(element, start, end) {
    const duration = 800; // 0.8 giây
    const startTime = performance.now();

    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);

        // Easing function (ease-out)
        const easeOut = 1 - Math.pow(1 - progress, 3);
        const current = Math.round(start + (end - start) * easeOut);

        element.textContent = current;

        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }

    requestAnimationFrame(update);
}

function showNewNotificationAlert(newCount) {
    // Tạo toast notification
    const toast = document.createElement('div');
    toast.className = 'toast notification-alert show';
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        background: linear-gradient(135deg, #28a745, #20c997);
        color: #fff3cd !important;
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(40, 167, 69, 0.3);
        animation: slideInRight 0.3s ease-out;
    `;

    toast.innerHTML = `
        <div class="toast-header" style="background: rgba(231,76,60,0.1); border-bottom: 1px solid rgba(231,76,60,0.2);">
            <i class="fas fa-bell me-2" style="color: #fff3cd !important;"></i>
            <strong class="me-auto" style="color: #fff3cd !important;">Thông báo mới</strong>
            <button type="button" class="btn-close btn-close-white" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
        <div class="toast-body" style="background: rgba(231,76,60,0.05);">
            Có <strong>${newCount}</strong> thông báo chưa đọc mới!
        </div>
    `;

    document.body.appendChild(toast);

    // Tự động xóa sau 5 giây
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => toast.remove(), 300);
        }
    }, 5000);
}

// Khởi động auto-update khi trang load
document.addEventListener('DOMContentLoaded', function() {
    // Delay 2 giây để trang load xong
    setTimeout(() => {
        startAutoUpdate();
    }, 2000);
});

// Dừng auto-update khi trang unload
window.addEventListener('beforeunload', function() {
    stopAutoUpdate();
});

// Tạm dừng khi tab không active
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopAutoUpdate();
    } else {
        startAutoUpdate();
    }
});
</script>

<!-- CSS cho animations -->
<style>
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes slideOutRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.notification-update {
    animation: pulse 0.6s ease-in-out;
    box-shadow: 0 0 20px rgba(40, 167, 69, 0.3) !important;
    transition: all 0.3s ease;
}

.notification-alert .toast-header,
.notification-alert .toast-body {
    border: none;
}

/* Enhanced Statistics Cards */
.card {
    border-radius: 15px !important;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    overflow: hidden !important;
    position: relative !important;
}

.card:hover {
    transform: translateY(-5px) !important;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2) !important;
}

.card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.05) 100%);
    pointer-events: none;
    z-index: 1;
}

.card-body {
    position: relative;
    z-index: 2;
    padding: 2rem !important;
}

.card-title {
    letter-spacing: 1px !important;
    margin-bottom: 0.5rem !important;
}

.card-text {
    font-size: 1.1rem !important;
    margin: 0 !important;
    opacity: 0.95 !important;
}

.fas {
    transition: all 0.3s ease !important;
}

.card:hover .fas {
    transform: scale(1.1) !important;
}

/* Pulse animation for numbers */
@keyframes numberPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.card-title {
    animation: numberPulse 2s ease-in-out infinite;
}

/* Gradient text effect */
.card-title {
    background: linear-gradient(45deg, #ffffff, #f8f9fa);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    filter: brightness(1.1);
}
</style>
</script>

<?php include __DIR__ . '/../../layout/admin_footer.php'; ?>
