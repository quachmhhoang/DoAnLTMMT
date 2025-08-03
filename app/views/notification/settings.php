<?php
$content = ob_start();
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-cog"></i> Cài đặt thông báo</h4>
                </div>
                <div class="card-body">
                    <form id="notificationSettingsForm">
                        <!-- Push Notifications -->
                        <div class="mb-4">
                            <h5 class="mb-3">Thông báo đẩy</h5>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="pushEnabled" name="push_enabled">
                                <label class="form-check-label" for="pushEnabled">
                                    <strong>Bật thông báo đẩy</strong>
                                    <br><small class="text-muted">Nhận thông báo ngay cả khi không mở website</small>
                                </label>
                            </div>
                            
                            <div id="pushPermissionStatus" class="alert" style="display: none;">
                                <!-- Permission status will be shown here -->
                            </div>
                            
                            <button type="button" id="requestPushPermission" class="btn btn-outline-primary btn-sm" style="display: none;">
                                <i class="fas fa-bell"></i> Cho phép thông báo đẩy
                            </button>
                        </div>

                        <hr>

                        <!-- Email Notifications -->
                        <div class="mb-4">
                            <h5 class="mb-3">Thông báo email</h5>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="emailEnabled" name="email_enabled">
                                <label class="form-check-label" for="emailEnabled">
                                    <strong>Bật thông báo email</strong>
                                    <br><small class="text-muted">Nhận thông báo qua email</small>
                                </label>
                            </div>
                        </div>

                        <hr>

                        <!-- Notification Types -->
                        <div class="mb-4">
                            <h5 class="mb-3">Loại thông báo</h5>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="orderNotifications" name="order_notifications">
                                <label class="form-check-label" for="orderNotifications">
                                    <strong>Thông báo đơn hàng</strong>
                                    <br><small class="text-muted">Cập nhật trạng thái đơn hàng, xác nhận thanh toán</small>
                                </label>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="systemNotifications" name="system_notifications">
                                <label class="form-check-label" for="systemNotifications">
                                    <strong>Thông báo hệ thống</strong>
                                    <br><small class="text-muted">Bảo trì, cập nhật tính năng, thông báo bảo mật</small>
                                </label>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="marketingNotifications" name="marketing_notifications">
                                <label class="form-check-label" for="marketingNotifications">
                                    <strong>Thông báo khuyến mãi</strong>
                                    <br><small class="text-muted">Ưu đãi đặc biệt, sản phẩm mới, chương trình khuyến mãi</small>
                                </label>
                            </div>
                        </div>

                        <hr>

                        <!-- Notification Frequency -->
                        <div class="mb-4">
                            <h5 class="mb-3">Tần suất thông báo</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="notificationFrequency" class="form-label">Tần suất gửi email</label>
                                    <select class="form-select" id="notificationFrequency" name="notification_frequency">
                                        <option value="immediate">Ngay lập tức</option>
                                        <option value="daily">Tổng hợp hàng ngày</option>
                                        <option value="weekly">Tổng hợp hàng tuần</option>
                                        <option value="never">Không gửi</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="quietHours" class="form-label">Giờ im lặng</label>
                                    <select class="form-select" id="quietHours" name="quiet_hours">
                                        <option value="none">Không</option>
                                        <option value="22-08">22:00 - 08:00</option>
                                        <option value="23-07">23:00 - 07:00</option>
                                        <option value="00-06">00:00 - 06:00</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu cài đặt
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Test Notification Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-vial"></i> Kiểm tra thông báo</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Gửi thông báo thử nghiệm để kiểm tra cài đặt của bạn</p>
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" id="testPushNotification" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-mobile-alt"></i> Thử thông báo đẩy
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" id="testEmailNotification" class="btn btn-outline-secondary w-100 mb-2">
                                <i class="fas fa-envelope"></i> Thử thông báo email
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Browser Support Info -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle"></i> Thông tin hỗ trợ</h5>
                </div>
                <div class="card-body">
                    <div id="browserSupport">
                        <!-- Browser support info will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-check-label strong {
    color: #333;
}

.form-check-label small {
    display: block;
    margin-top: 2px;
}

.alert-success {
    border-color: #d4edda;
}

.alert-warning {
    border-color: #ffeaa7;
}

.alert-danger {
    border-color: #f8d7da;
}

.support-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}

.support-item i {
    margin-right: 8px;
    width: 20px;
}

.support-yes {
    color: #28a745;
}

.support-no {
    color: #dc3545;
}

.support-partial {
    color: #ffc107;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('notificationSettingsForm');
    
    // Load current settings
    loadSettings();
    
    // Check browser support
    checkBrowserSupport();
    
    // Check push permission status
    checkPushPermissionStatus();

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        saveSettings();
    });

    // Request push permission
    document.getElementById('requestPushPermission').addEventListener('click', function() {
        requestPushPermission();
    });

    // Test notifications
    document.getElementById('testPushNotification').addEventListener('click', function() {
        testPushNotification();
    });

    document.getElementById('testEmailNotification').addEventListener('click', function() {
        testEmailNotification();
    });

    function loadSettings() {
        fetch('/api/notifications/settings')
            .then(response => response.json())
            .then(settings => {
                document.getElementById('pushEnabled').checked = settings.push_enabled;
                document.getElementById('emailEnabled').checked = settings.email_enabled;
                document.getElementById('orderNotifications').checked = settings.order_notifications;
                document.getElementById('systemNotifications').checked = settings.system_notifications;
                document.getElementById('marketingNotifications').checked = settings.marketing_notifications;
                
                // Set default values for new fields if they don't exist
                if (settings.notification_frequency) {
                    document.getElementById('notificationFrequency').value = settings.notification_frequency;
                }
                if (settings.quiet_hours) {
                    document.getElementById('quietHours').value = settings.quiet_hours;
                }
            })
            .catch(error => {
                console.error('Error loading settings:', error);
                showAlert('Không thể tải cài đặt', 'danger');
            });
    }

    function saveSettings() {
        const formData = new FormData(form);
        const settings = {};
        
        // Convert FormData to object
        for (let [key, value] of formData.entries()) {
            if (form.querySelector(`[name="${key}"]`).type === 'checkbox') {
                settings[key] = true;
            } else {
                settings[key] = value;
            }
        }
        
        // Add unchecked checkboxes as false
        form.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            if (!checkbox.checked) {
                settings[checkbox.name] = false;
            }
        });

        fetch('/api/notifications/settings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(settings)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showAlert('Cài đặt đã được lưu thành công!', 'success');
            } else {
                showAlert('Có lỗi xảy ra khi lưu cài đặt', 'danger');
            }
        })
        .catch(error => {
            console.error('Error saving settings:', error);
            showAlert('Có lỗi xảy ra khi lưu cài đặt', 'danger');
        });
    }

    function checkPushPermissionStatus() {
        const statusDiv = document.getElementById('pushPermissionStatus');
        const requestBtn = document.getElementById('requestPushPermission');
        
        if (!('Notification' in window)) {
            statusDiv.className = 'alert alert-warning';
            statusDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Trình duyệt không hỗ trợ thông báo đẩy';
            statusDiv.style.display = 'block';
            return;
        }

        const permission = Notification.permission;
        
        switch (permission) {
            case 'granted':
                statusDiv.className = 'alert alert-success';
                statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> Thông báo đẩy đã được cho phép';
                statusDiv.style.display = 'block';
                break;
            case 'denied':
                statusDiv.className = 'alert alert-danger';
                statusDiv.innerHTML = '<i class="fas fa-times-circle"></i> Thông báo đẩy đã bị từ chối. Vui lòng bật trong cài đặt trình duyệt.';
                statusDiv.style.display = 'block';
                break;
            case 'default':
                requestBtn.style.display = 'block';
                break;
        }
    }

    async function requestPushPermission() {
        try {
            const permission = await Notification.requestPermission();
            checkPushPermissionStatus();
            
            if (permission === 'granted') {
                // Subscribe to push notifications
                if (window.notificationManager) {
                    await window.notificationManager.subscribeToPush();
                }
                showAlert('Thông báo đẩy đã được bật thành công!', 'success');
            }
        } catch (error) {
            console.error('Error requesting permission:', error);
            showAlert('Có lỗi xảy ra khi yêu cầu quyền thông báo', 'danger');
        }
    }

    function testPushNotification() {
        if (Notification.permission === 'granted') {
            new Notification('CellPhone Store - Thông báo thử nghiệm', {
                body: 'Đây là thông báo thử nghiệm. Cài đặt của bạn đang hoạt động tốt!',
                icon: '/assets/images/icon-192x192.png',
                tag: 'test-notification'
            });
            showAlert('Thông báo thử nghiệm đã được gửi!', 'success');
        } else {
            showAlert('Vui lòng cho phép thông báo đẩy trước', 'warning');
        }
    }

    function testEmailNotification() {
        fetch('/api/notifications/test-email', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showAlert('Email thử nghiệm đã được gửi!', 'success');
            } else {
                showAlert('Có lỗi xảy ra khi gửi email thử nghiệm', 'danger');
            }
        })
        .catch(error => {
            console.error('Error sending test email:', error);
            showAlert('Có lỗi xảy ra khi gửi email thử nghiệm', 'danger');
        });
    }

    function checkBrowserSupport() {
        const supportDiv = document.getElementById('browserSupport');
        const features = [
            {
                name: 'Thông báo đẩy',
                supported: 'Notification' in window,
                icon: 'fas fa-bell'
            },
            {
                name: 'Service Worker',
                supported: 'serviceWorker' in navigator,
                icon: 'fas fa-cogs'
            },
            {
                name: 'Push Manager',
                supported: 'PushManager' in window,
                icon: 'fas fa-paper-plane'
            },
            {
                name: 'Background Sync',
                supported: 'serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype,
                icon: 'fas fa-sync'
            }
        ];

        let html = '';
        features.forEach(feature => {
            const statusClass = feature.supported ? 'support-yes' : 'support-no';
            const statusIcon = feature.supported ? 'fas fa-check' : 'fas fa-times';
            const statusText = feature.supported ? 'Hỗ trợ' : 'Không hỗ trợ';
            
            html += `
                <div class="support-item">
                    <i class="${feature.icon}"></i>
                    <span>${feature.name}</span>
                    <span class="ms-auto ${statusClass}">
                        <i class="${statusIcon}"></i> ${statusText}
                    </span>
                </div>
            `;
        });

        supportDiv.innerHTML = html;
    }

    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert at the top of the container
        const container = document.querySelector('.container');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentElement) {
                alertDiv.remove();
            }
        }, 5000);
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout/app.php';
?>
