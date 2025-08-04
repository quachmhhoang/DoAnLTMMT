/**
 * Notification Polling System
 * Kiểm tra thông báo mới và tự động reload trang khi cần thiết
 */

class NotificationPoller {
    constructor(options = {}) {
        this.pollInterval = options.pollInterval || 30000; // 30 giây
        this.autoReload = options.autoReload !== false; // Mặc định là true
        this.reloadDelay = options.reloadDelay || 3000; // 3 giây delay trước khi reload
        this.lastCheckTime = new Date().toISOString().slice(0, 19).replace('T', ' ');
        this.isPolling = false;
        this.pollTimer = null;
        this.notificationCount = 0;
        
        // Callbacks
        this.onNewNotification = options.onNewNotification || this.defaultNotificationHandler.bind(this);
        this.onReload = options.onReload || this.defaultReloadHandler.bind(this);
        
        this.init();
    }

    init() {
        // Bắt đầu polling nếu user đã đăng nhập
        if (this.isUserLoggedIn()) {
            this.startPolling();
        }

        // Lắng nghe sự kiện visibility change để tạm dừng/tiếp tục polling
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pausePolling();
            } else {
                this.resumePolling();
            }
        });

        // Cleanup khi trang được unload
        window.addEventListener('beforeunload', () => {
            this.stopPolling();
        });
    }

    isUserLoggedIn() {
        // Kiểm tra xem user có đăng nhập không
        return document.body.dataset.userId || 
               document.querySelector('meta[name="user-id"]') ||
               localStorage.getItem('user_id');
    }

    startPolling() {
        if (this.isPolling) return;
        
        this.isPolling = true;
        console.log('🔄 Bắt đầu polling thông báo...');
        
        // Kiểm tra ngay lập tức
        this.checkNotifications();
        
        // Thiết lập interval
        this.pollTimer = setInterval(() => {
            this.checkNotifications();
        }, this.pollInterval);
    }

    pausePolling() {
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
            this.pollTimer = null;
        }
        console.log('⏸️ Tạm dừng polling thông báo');
    }

    resumePolling() {
        if (this.isPolling && !this.pollTimer) {
            this.pollTimer = setInterval(() => {
                this.checkNotifications();
            }, this.pollInterval);
            console.log('▶️ Tiếp tục polling thông báo');
        }
    }

    stopPolling() {
        this.isPolling = false;
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
            this.pollTimer = null;
        }
        console.log('⏹️ Dừng polling thông báo');
    }

    async checkNotifications() {
        try {
            const response = await fetch(`/api/notifications/check-new?last_check=${encodeURIComponent(this.lastCheckTime)}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                if (response.status === 401) {
                    // User không đăng nhập, dừng polling
                    this.stopPolling();
                    return;
                }
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                this.lastCheckTime = data.last_check;
                
                if (data.has_new && data.new_notifications.length > 0) {
                    console.log('🔔 Có thông báo mới:', data.new_notifications);
                    
                    // Xử lý từng thông báo mới
                    data.new_notifications.forEach(notification => {
                        this.onNewNotification(notification);
                    });
                    
                    // Cập nhật số lượng thông báo chưa đọc
                    this.updateUnreadCount(data.unread_count);
                    
                    // Tự động reload nếu được bật
                    if (this.autoReload) {
                        this.scheduleReload();
                    }
                }
            }
        } catch (error) {
            console.error('❌ Lỗi khi kiểm tra thông báo:', error);
            // Không dừng polling, chỉ log lỗi
        }
    }

    defaultNotificationHandler(notification) {
        // Hiển thị toast notification
        this.showToastNotification(notification);
        
        // Cập nhật badge số lượng thông báo
        this.updateNotificationBadge();
        
        // Phát âm thanh thông báo (nếu được phép)
        this.playNotificationSound();
    }

    showToastNotification(notification) {
        // Tạo toast notification
        const toast = document.createElement('div');
        toast.className = 'toast notification-toast show';
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 350px;
            background: white;
            border-left: 4px solid ${this.getNotificationColor(notification.type)};
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border-radius: 8px;
            animation: slideInRight 0.3s ease-out;
        `;
        
        toast.innerHTML = `
            <div class="toast-header">
                <i class="fas ${this.getNotificationIcon(notification.type)} me-2" style="color: ${this.getNotificationColor(notification.type)}"></i>
                <strong class="me-auto">${this.escapeHtml(notification.title)}</strong>
                <small class="text-muted">Vừa xong</small>
                <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
            <div class="toast-body">
                ${this.escapeHtml(notification.message)}
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Tự động xóa sau 8 giây
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'slideOutRight 0.3s ease-in';
                setTimeout(() => toast.remove(), 300);
            }
        }, 8000);
    }

    updateUnreadCount(count) {
        // Cập nhật badge số lượng thông báo chưa đọc
        const badges = document.querySelectorAll('.notification-badge, .unread-count');
        badges.forEach(badge => {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline' : 'none';
        });
    }

    updateNotificationBadge() {
        this.notificationCount++;
        const badges = document.querySelectorAll('.notification-badge');
        badges.forEach(badge => {
            badge.textContent = this.notificationCount;
            badge.style.display = 'inline';
            badge.classList.add('pulse-animation');
        });
    }

    playNotificationSound() {
        // Phát âm thanh thông báo nhẹ
        try {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT');
            audio.volume = 0.3;
            audio.play().catch(() => {}); // Ignore errors
        } catch (e) {
            // Ignore audio errors
        }
    }

    scheduleReload() {
        console.log(`🔄 Sẽ reload trang sau ${this.reloadDelay/1000} giây...`);
        
        // Hiển thị thông báo reload
        this.showReloadNotification();
        
        setTimeout(() => {
            this.onReload();
        }, this.reloadDelay);
    }

    showReloadNotification() {
        const notification = document.createElement('div');
        notification.className = 'alert alert-info alert-dismissible fade show reload-notification';
        notification.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9998;
            min-width: 350px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        `;
        
        notification.innerHTML = `
            <i class="fas fa-sync-alt me-2"></i>
            <strong>Có thông báo mới!</strong> Trang sẽ được làm mới để hiển thị nội dung mới nhất.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
    }

    defaultReloadHandler() {
        window.location.reload();
    }

    getNotificationColor(type) {
        const colors = {
            'order': '#28a745',
            'product': '#007bff', 
            'promotion': '#ffc107',
            'system': '#6c757d',
            'admin': '#dc3545'
        };
        return colors[type] || '#6c757d';
    }

    getNotificationIcon(type) {
        const icons = {
            'order': 'fa-shopping-cart',
            'product': 'fa-box',
            'promotion': 'fa-percentage', 
            'system': 'fa-cog',
            'admin': 'fa-user-shield'
        };
        return icons[type] || 'fa-bell';
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// CSS cho animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    @keyframes pulse-animation {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    
    .pulse-animation {
        animation: pulse-animation 0.6s ease-in-out;
    }
`;
document.head.appendChild(style);

// Export cho sử dụng global
window.NotificationPoller = NotificationPoller;
