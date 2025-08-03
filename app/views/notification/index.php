<?php
$content = ob_start();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-bell"></i> Thông báo</h2>
                <div>
                    <button class="btn btn-outline-primary btn-sm mark-all-read">
                        <i class="fas fa-check-double"></i> Đánh dấu tất cả đã đọc
                    </button>
                    <a href="/notification-settings" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-cog"></i> Cài đặt
                    </a>
                </div>
            </div>

            <!-- Notification Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="btn-group" role="group" aria-label="Notification filters">
                                <input type="radio" class="btn-check" name="filter" id="filter-all" value="all" checked>
                                <label class="btn btn-outline-primary" for="filter-all">Tất cả</label>

                                <input type="radio" class="btn-check" name="filter" id="filter-unread" value="unread">
                                <label class="btn btn-outline-primary" for="filter-unread">Chưa đọc</label>

                                <input type="radio" class="btn-check" name="filter" id="filter-read" value="read">
                                <label class="btn btn-outline-primary" for="filter-read">Đã đọc</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="btn-group" role="group" aria-label="Notification types">
                                <input type="radio" class="btn-check" name="type-filter" id="type-all" value="all" checked>
                                <label class="btn btn-outline-secondary" for="type-all">Tất cả loại</label>

                                <input type="radio" class="btn-check" name="type-filter" id="type-order" value="order">
                                <label class="btn btn-outline-secondary" for="type-order">Đơn hàng</label>

                                <input type="radio" class="btn-check" name="type-filter" id="type-system" value="system">
                                <label class="btn btn-outline-secondary" for="type-system">Hệ thống</label>

                                <input type="radio" class="btn-check" name="type-filter" id="type-info" value="info">
                                <label class="btn btn-outline-secondary" for="type-info">Thông tin</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Debug Info (remove in production) -->
            <div class="card mb-3" id="debug-info" style="display: none;">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-bug"></i> Debug Information
                        <button class="btn btn-sm btn-outline-secondary float-end" onclick="toggleDebug()">
                            Toggle
                        </button>
                    </h6>
                </div>
                <div class="card-body">
                    <div id="debug-content">
                        <p><strong>Current User:</strong> <span id="debug-user">Loading...</span></p>
                        <p><strong>API Endpoint:</strong> <span id="debug-endpoint">/api/notifications</span></p>
                        <p><strong>Last Request:</strong> <span id="debug-request">None</span></p>
                        <p><strong>Last Response:</strong> <span id="debug-response">None</span></p>
                        <button class="btn btn-sm btn-primary" onclick="testAPI()">Test API</button>
                        <button class="btn btn-sm btn-success" onclick="createTestNotification()">Create Test Notification</button>
                    </div>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="notifications-container">
                <div class="notifications-list">
                    <!-- Notifications will be loaded here -->
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                        <p class="mt-2 text-muted">Đang tải thông báo...</p>
                    </div>
                </div>

                <!-- Load More Button -->
                <div class="text-center mt-4">
                    <button class="btn btn-outline-primary load-more-notifications" style="display: none;">
                        <i class="fas fa-plus"></i> Tải thêm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Detail Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalLabel">Chi tiết thông báo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="notification-detail-content">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary mark-notification-read">Đánh dấu đã đọc</button>
            </div>
        </div>
    </div>
</div>

<style>
.notification-item {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 12px;
    padding: 16px;
    background: #fff;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.notification-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.notification-item.unread {
    border-left: 4px solid #007bff;
    background: #f8f9ff;
}

.notification-item.unread::before {
    content: '';
    position: absolute;
    top: 16px;
    right: 16px;
    width: 8px;
    height: 8px;
    background: #007bff;
    border-radius: 50%;
}

.notification-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.notification-message {
    color: #666;
    margin-bottom: 8px;
    line-height: 1.5;
}

.notification-time {
    font-size: 0.875rem;
    color: #999;
}

.notification-type-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    margin-bottom: 8px;
}

.notification-type-order {
    background: #e3f2fd;
    color: #1976d2;
}

.notification-type-system {
    background: #f3e5f5;
    color: #7b1fa2;
}

.notification-type-info {
    background: #e8f5e8;
    color: #388e3c;
}

.notification-type-warning {
    background: #fff3e0;
    color: #f57c00;
}

.notification-type-error {
    background: #ffebee;
    color: #d32f2f;
}

.notification-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    font-size: 0.75rem;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-dropdown {
    border: none;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.notification-dropdown .dropdown-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    font-weight: 600;
}

.notification-dropdown .dropdown-footer {
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 8px;
}

.notification-dropdown .notification-item {
    border: none;
    border-bottom: 1px solid #f0f0f0;
    border-radius: 0;
    margin-bottom: 0;
    padding: 12px 16px;
}

.notification-dropdown .notification-item:last-child {
    border-bottom: none;
}

.mark-all-read {
    color: #007bff;
    text-decoration: none;
    font-size: 0.875rem;
}

.mark-all-read:hover {
    color: #0056b3;
    text-decoration: underline;
}

@media (max-width: 768px) {
    .notification-dropdown {
        width: 300px !important;
    }
    
    .btn-group {
        flex-wrap: wrap;
    }
    
    .btn-group .btn {
        margin-bottom: 4px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let currentFilter = 'all';
    let currentTypeFilter = 'all';
    let isLoading = false;

    // Load notifications
    function loadNotifications(page = 1, append = false) {
        if (isLoading) return;

        isLoading = true;

        const params = new URLSearchParams({
            page: page,
            filter: currentFilter,
            type: currentTypeFilter,
            limit: 10
        });

        console.log('Loading notifications:', `/api/notifications?${params}`);

        fetch(`/api/notifications?${params}`)
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Error response body:', text);
                        throw new Error(`HTTP ${response.status}: ${response.statusText}\nResponse: ${text}`);
                    });
                }

                return response.text().then(text => {
                    console.log('Raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        console.error('Response text:', text);
                        throw new Error('Invalid JSON response: ' + e.message);
                    }
                });
            })
            .then(data => {
                console.log('Notifications data:', data);

                if (!data || !Array.isArray(data.notifications)) {
                    throw new Error('Invalid response format');
                }

                displayNotifications(data.notifications, append);

                // Show/hide load more button
                const loadMoreBtn = document.querySelector('.load-more-notifications');
                if (data.notifications.length < 10) {
                    loadMoreBtn.style.display = 'none';
                } else {
                    loadMoreBtn.style.display = 'block';
                }

                isLoading = false;
            })
            .catch(error => {
                console.error('Error loading notifications:', error);

                // Show error message to user
                const container = document.querySelector('.notifications-list');
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle"></i> Lỗi tải thông báo</h5>
                        <p>Không thể tải danh sách thông báo. Vui lòng thử lại.</p>
                        <p><small>Chi tiết lỗi: ${error.message}</small></p>
                        <button class="btn btn-outline-danger btn-sm" onclick="loadNotifications()">
                            <i class="fas fa-redo"></i> Thử lại
                        </button>
                    </div>
                `;

                isLoading = false;
            });
    }

    function displayNotifications(notifications, append = false) {
        const container = document.querySelector('.notifications-list');
        
        if (!append) {
            container.innerHTML = '';
        }

        if (notifications.length === 0 && !append) {
            container.innerHTML = `
                <div class="text-center p-4">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Không có thông báo nào</p>
                </div>
            `;
            return;
        }

        notifications.forEach(notification => {
            const item = createNotificationItem(notification);
            container.appendChild(item);
        });
    }

    function createNotificationItem(notification) {
        const item = document.createElement('div');
        item.className = `notification-item ${notification.is_read ? '' : 'unread'}`;
        item.dataset.notificationId = notification.notification_id;
        
        const typeClass = `notification-type-${notification.type}`;
        const typeName = getTypeName(notification.type);
        
        item.innerHTML = `
            <div class="notification-type-badge ${typeClass}">${typeName}</div>
            <div class="notification-title">${notification.title}</div>
            <div class="notification-message">${notification.message}</div>
            <div class="notification-time">${formatTime(notification.created_at)}</div>
        `;
        
        item.addEventListener('click', () => {
            showNotificationDetail(notification);
            if (!notification.is_read) {
                markAsRead(notification.notification_id);
                item.classList.remove('unread');
            }
        });
        
        return item;
    }

    function getTypeName(type) {
        const types = {
            'order': 'Đơn hàng',
            'system': 'Hệ thống',
            'info': 'Thông tin',
            'warning': 'Cảnh báo',
            'error': 'Lỗi',
            'success': 'Thành công'
        };
        return types[type] || 'Thông tin';
    }

    function formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) {
            return 'Vừa xong';
        } else if (diff < 3600000) {
            return `${Math.floor(diff / 60000)} phút trước`;
        } else if (diff < 86400000) {
            return `${Math.floor(diff / 3600000)} giờ trước`;
        } else {
            return date.toLocaleDateString('vi-VN');
        }
    }

    function showNotificationDetail(notification) {
        const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
        const content = document.querySelector('.notification-detail-content');
        
        content.innerHTML = `
            <div class="notification-type-badge notification-type-${notification.type}">
                ${getTypeName(notification.type)}
            </div>
            <h5 class="mt-3">${notification.title}</h5>
            <p class="text-muted">${formatTime(notification.created_at)}</p>
            <div class="mt-3">${notification.message}</div>
            ${notification.data ? `<div class="mt-3"><small class="text-muted">Dữ liệu bổ sung: ${notification.data}</small></div>` : ''}
        `;
        
        modal.show();
    }

    function markAsRead(notificationId) {
        fetch('/api/notifications/mark-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ notification_id: notificationId })
        });
    }

    // Event listeners
    document.querySelectorAll('input[name="filter"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            currentFilter = e.target.value;
            currentPage = 1;
            loadNotifications(1, false);
        });
    });

    document.querySelectorAll('input[name="type-filter"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            currentTypeFilter = e.target.value;
            currentPage = 1;
            loadNotifications(1, false);
        });
    });

    document.querySelector('.load-more-notifications').addEventListener('click', () => {
        currentPage++;
        loadNotifications(currentPage, true);
    });

    document.querySelector('.mark-all-read').addEventListener('click', () => {
        fetch('/api/notifications/mark-all-read', {
            method: 'POST'
        }).then(() => {
            loadNotifications(1, false);
            if (window.notificationManager) {
                window.notificationManager.updateUnreadCount();
            }
        });
    });

    // Debug functions
    window.toggleDebug = function() {
        const debugInfo = document.getElementById('debug-info');
        debugInfo.style.display = debugInfo.style.display === 'none' ? 'block' : 'none';
    };

    window.testAPI = function() {
        console.log('Testing API endpoint...');
        document.getElementById('debug-request').textContent = 'Testing...';

        fetch('/api/notifications?page=1&limit=5')
            .then(response => {
                document.getElementById('debug-request').textContent = `GET /api/notifications?page=1&limit=5 - Status: ${response.status}`;
                return response.text();
            })
            .then(text => {
                document.getElementById('debug-response').textContent = text.substring(0, 200) + (text.length > 200 ? '...' : '');
                console.log('API Response:', text);

                try {
                    const data = JSON.parse(text);
                    if (data.notifications) {
                        alert(`API Working! Found ${data.notifications.length} notifications`);
                    } else {
                        alert('API responded but no notifications array found');
                    }
                } catch (e) {
                    alert('API responded but invalid JSON: ' + e.message);
                }
            })
            .catch(error => {
                document.getElementById('debug-response').textContent = 'Error: ' + error.message;
                alert('API Error: ' + error.message);
            });
    };

    window.createTestNotification = function() {
        if (!confirm('Create a test notification?')) return;

        const testData = {
            title: 'Test Notification',
            message: 'This is a test notification created at ' + new Date().toLocaleString(),
            type: 'info'
        };

        fetch('/api/notifications/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(testData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Test notification created successfully!');
                loadNotifications();
            } else {
                alert('Failed to create test notification: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error creating test notification: ' + error.message);
        });
    };

    // Show debug info if there are URL parameters
    if (window.location.search.includes('debug')) {
        document.getElementById('debug-info').style.display = 'block';
    }

    // Update debug user info
    document.getElementById('debug-user').textContent = '<?php echo SessionHelper::getCurrentUser()->full_name ?? "Unknown"; ?> (ID: <?php echo SessionHelper::getCurrentUser()->user_id ?? "Unknown"; ?>)';

    // Initial load
    loadNotifications();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout/app.php';
?>
