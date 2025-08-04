/**
 * WebSocket Client for Real-time Notifications
 */
class NotificationWebSocket {
    constructor(options = {}) {
        this.url = options.url || 'ws://localhost:8080';
        this.userId = options.userId || null;
        this.token = options.token || null;
        this.reconnectInterval = options.reconnectInterval || 5000;
        this.maxReconnectAttempts = options.maxReconnectAttempts || 10;
        
        this.socket = null;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.reconnectTimer = null;
        this.pingTimer = null;
        
        // Event callbacks
        this.onNotification = options.onNotification || this.defaultNotificationHandler;
        this.onConnect = options.onConnect || (() => {});
        this.onDisconnect = options.onDisconnect || (() => {});
        this.onError = options.onError || (() => {});

        // UI elements
        this.notificationBadge = null;
        this.connectionStatus = null;
        this.notificationDropdown = null;

        // Initialize UI elements
        this.initializeUI();

        // Auto-connect if user info is provided
        if (this.userId && this.token) {
            this.connect();
        }
    }

    /**
     * Initialize UI elements for real-time updates
     */
    initializeUI() {
        // Find notification badge
        this.notificationBadge = document.querySelector('#notification-badge, .notification-badge');

        // Find connection status indicator
        this.connectionStatus = document.querySelector('#connection-status, .connection-status');

        // Find notification dropdown
        this.notificationDropdown = document.querySelector('#notification-dropdown, .notification-dropdown');

        // Create connection status indicator if it doesn't exist
        if (!this.connectionStatus) {
            this.createConnectionStatusIndicator();
        }

        // Create notification sound
        this.notificationSound = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT');
    }

    /**
     * Create connection status indicator
     */
    createConnectionStatusIndicator() {
        const indicator = document.createElement('div');
        indicator.id = 'connection-status';
        indicator.className = 'connection-status';
        indicator.innerHTML = `
            <div class="connection-dot disconnected"></div>
            <span class="connection-text">Đang kết nối...</span>
        `;

        // Add to top of page
        document.body.insertBefore(indicator, document.body.firstChild);
        this.connectionStatus = indicator;

        // Add CSS styles
        this.addConnectionStatusStyles();
    }

    /**
     * Add CSS styles for connection status
     */
    addConnectionStatusStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .connection-status {
                position: fixed;
                top: 10px;
                right: 10px;
                background: rgba(0,0,0,0.8);
                color: white;
                padding: 8px 12px;
                border-radius: 20px;
                font-size: 12px;
                z-index: 9999;
                display: flex;
                align-items: center;
                gap: 8px;
                transition: all 0.3s ease;
            }

            .connection-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                animation: pulse 2s infinite;
            }

            .connection-dot.connected {
                background: #28a745;
            }

            .connection-dot.disconnected {
                background: #dc3545;
            }

            .connection-dot.connecting {
                background: #ffc107;
            }

            @keyframes pulse {
                0% { opacity: 1; }
                50% { opacity: 0.5; }
                100% { opacity: 1; }
            }

            .connection-status.hidden {
                opacity: 0;
                transform: translateX(100%);
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Connect to WebSocket server
     */
    connect() {
        if (this.socket && this.socket.readyState === WebSocket.OPEN) {
            console.log('WebSocket already connected');
            return;
        }

        try {
            console.log('Connecting to WebSocket server...');
            this.socket = new WebSocket(this.url);
            
            this.socket.onopen = (event) => {
                console.log('WebSocket connected');
                this.isConnected = true;
                this.reconnectAttempts = 0;

                // Update connection status
                this.updateConnectionStatus('connected', 'Đã kết nối');

                // Authenticate user
                if (this.userId && this.token) {
                    this.authenticate();
                }

                // Start ping timer
                this.startPing();

                this.onConnect(event);

                // Hide connection status after 3 seconds if connected
                setTimeout(() => {
                    if (this.isConnected && this.connectionStatus) {
                        this.connectionStatus.classList.add('hidden');
                    }
                }, 3000);
            };

            this.socket.onmessage = (event) => {
                this.handleMessage(event.data);
            };

            this.socket.onclose = (event) => {
                console.log('WebSocket disconnected');
                this.isConnected = false;
                this.stopPing();

                // Update connection status
                this.updateConnectionStatus('disconnected', 'Mất kết nối');

                this.onDisconnect(event);

                // Attempt to reconnect
                if (this.reconnectAttempts < this.maxReconnectAttempts) {
                    this.scheduleReconnect();
                }
            };

            this.socket.onerror = (error) => {
                console.error('WebSocket error:', error);
                this.updateConnectionStatus('disconnected', 'Lỗi kết nối');
                this.onError(error);
            };

        } catch (error) {
            console.error('Failed to create WebSocket connection:', error);
            this.onError(error);
        }
    }

    /**
     * Disconnect from WebSocket server
     */
    disconnect() {
        if (this.reconnectTimer) {
            clearTimeout(this.reconnectTimer);
            this.reconnectTimer = null;
        }
        
        this.stopPing();
        
        if (this.socket) {
            this.socket.close();
            this.socket = null;
        }
        
        this.isConnected = false;
    }

    /**
     * Authenticate user with the server
     */
    authenticate() {
        if (!this.isConnected || !this.userId || !this.token) {
            return;
        }

        const authMessage = {
            type: 'auth',
            user_id: this.userId,
            token: this.token
        };

        this.send(authMessage);
    }

    /**
     * Send message to server
     */
    send(message) {
        if (this.socket && this.socket.readyState === WebSocket.OPEN) {
            this.socket.send(JSON.stringify(message));
            return true;
        }
        return false;
    }

    /**
     * Handle incoming messages
     */
    handleMessage(data) {
        try {
            const message = JSON.parse(data);
            
            switch (message.type) {
                case 'connection':
                    console.log('Connection established:', message.message);
                    break;
                    
                case 'auth_success':
                    console.log('Authentication successful');
                    break;
                    
                case 'auth_error':
                    console.error('Authentication failed:', message.message);
                    break;
                    
                case 'notification':
                    this.handleNotification(message.data);
                    break;
                    
                case 'pong':
                    // Server responded to ping
                    break;
                    
                default:
                    console.log('Unknown message type:', message.type);
            }
        } catch (error) {
            console.error('Failed to parse WebSocket message:', error);
        }
    }

    /**
     * Handle incoming notification
     */
    handleNotification(notification) {
        console.log('Received notification:', notification);
        this.onNotification(notification);
    }

    /**
     * Default notification handler
     */
    defaultNotificationHandler(notification) {
        console.log('Received real-time notification:', notification);

        // Play notification sound
        this.playNotificationSound();

        // Show browser notification if permission granted
        if (Notification.permission === 'granted') {
            const browserNotification = new Notification(notification.title, {
                body: notification.message,
                icon: '/assets/images/logo.png',
                badge: '/assets/images/logo.png',
                tag: 'cellphone-store-notification-' + notification.id,
                requireInteraction: true,
                silent: false
            });

            browserNotification.onclick = () => {
                window.focus();
                this.handleNotificationClick(notification);
                browserNotification.close();
            };

            // Auto-close after 10 seconds
            setTimeout(() => {
                browserNotification.close();
            }, 10000);
        }

        // Show in-app notification toast
        this.showInAppNotification(notification);

        // Update notification badge/counter
        this.updateNotificationBadge();

        // Update notification dropdown if visible
        this.updateNotificationDropdown(notification);
    }

    /**
     * Play notification sound
     */
    playNotificationSound() {
        if (this.notificationSound) {
            this.notificationSound.currentTime = 0;
            this.notificationSound.play().catch(e => {
                console.log('Could not play notification sound:', e);
            });
        }
    }

    /**
     * Show in-app notification toast
     */
    showInAppNotification(notification) {
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = `notification-toast notification-${notification.type}`;
        toast.innerHTML = `
            <div class="notification-toast-header">
                <i class="fas fa-${this.getNotificationIcon(notification.type)}"></i>
                <strong>${notification.title}</strong>
                <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
            <div class="notification-toast-body">
                ${notification.message}
            </div>
        `;

        // Add to page
        let toastContainer = document.querySelector('.notification-toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'notification-toast-container';
            document.body.appendChild(toastContainer);
            this.addToastStyles();
        }

        toastContainer.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        // Auto-remove after 8 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }, 8000);
    }

    /**
     * Handle notification click
     */
    handleNotificationClick(notification) {
        // Mark as read
        if (notification.id) {
            this.markNotificationAsRead(notification.id);
        }

        // Navigate to relevant page based on notification type
        if (notification.data && notification.data.action_url) {
            window.location.href = notification.data.action_url;
        } else {
            // Default navigation based on type
            switch (notification.type) {
                case 'order':
                    window.location.href = '/orders';
                    break;
                case 'product':
                    window.location.href = '/products';
                    break;
                case 'promotion':
                    window.location.href = '/promotions';
                    break;
                default:
                    // Just focus the window
                    break;
            }
        }
    }

    /**
     * Update notification dropdown with new notification
     */
    updateNotificationDropdown(notification) {
        if (!this.notificationDropdown) return;

        const notificationItem = document.createElement('div');
        notificationItem.className = 'notification-item unread';
        notificationItem.innerHTML = `
            <div class="notification-icon">
                <i class="fas fa-${this.getNotificationIcon(notification.type)}"></i>
            </div>
            <div class="notification-content">
                <div class="notification-title">${notification.title}</div>
                <div class="notification-message">${notification.message}</div>
                <div class="notification-time">Vừa xong</div>
            </div>
        `;

        // Add to top of dropdown
        const firstItem = this.notificationDropdown.querySelector('.notification-item');
        if (firstItem) {
            this.notificationDropdown.insertBefore(notificationItem, firstItem);
        } else {
            this.notificationDropdown.appendChild(notificationItem);
        }

        // Limit to 10 items
        const items = this.notificationDropdown.querySelectorAll('.notification-item');
        if (items.length > 10) {
            items[items.length - 1].remove();
        }
    }

    /**
     * Get notification icon based on type
     */
    getNotificationIcon(type) {
        const iconMap = {
            'order': 'shopping-cart',
            'product': 'mobile-alt',
            'promotion': 'percentage',
            'system': 'cogs',
            'admin': 'user-shield'
        };
        return iconMap[type] || 'bell';
    }

    /**
     * Get Bootstrap alert class for notification type
     */
    getNotificationTypeClass(type) {
        const typeMap = {
            'order': 'success',
            'product': 'info',
            'promotion': 'warning',
            'system': 'info',
            'admin': 'primary'
        };
        return typeMap[type] || 'info';
    }

    /**
     * Add toast notification styles
     */
    addToastStyles() {
        if (document.querySelector('#notification-toast-styles')) return;

        const style = document.createElement('style');
        style.id = 'notification-toast-styles';
        style.textContent = `
            .notification-toast-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                max-width: 350px;
            }

            .notification-toast {
                background: white;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                margin-bottom: 10px;
                opacity: 0;
                transform: translateX(100%);
                transition: all 0.3s ease;
                border-left: 4px solid #007bff;
            }

            .notification-toast.show {
                opacity: 1;
                transform: translateX(0);
            }

            .notification-toast.notification-order {
                border-left-color: #28a745;
            }

            .notification-toast.notification-product {
                border-left-color: #17a2b8;
            }

            .notification-toast.notification-promotion {
                border-left-color: #ffc107;
            }

            .notification-toast.notification-system {
                border-left-color: #6c757d;
            }

            .notification-toast.notification-admin {
                border-left-color: #dc3545;
            }

            .notification-toast-header {
                padding: 12px 16px 8px;
                display: flex;
                align-items: center;
                gap: 8px;
                font-weight: 600;
                color: #333;
            }

            .notification-toast-header i {
                color: #007bff;
            }

            .notification-toast-header .btn-close {
                margin-left: auto;
                background: none;
                border: none;
                font-size: 18px;
                cursor: pointer;
                opacity: 0.5;
            }

            .notification-toast-header .btn-close:hover {
                opacity: 1;
            }

            .notification-toast-body {
                padding: 0 16px 12px;
                color: #666;
                font-size: 14px;
                line-height: 1.4;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Mark notification as read
     */
    markNotificationAsRead(notificationId) {
        fetch('/api/notifications/mark-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ notification_id: notificationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Notification marked as read');
                this.updateNotificationBadge();
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }

    /**
     * Update notification badge in UI
     */
    updateNotificationBadge() {
        // This would typically fetch the unread count from the server
        // For now, we'll just increment the existing badge
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            const currentCount = parseInt(badge.textContent) || 0;
            badge.textContent = currentCount + 1;
            badge.style.display = 'inline';
        }

        // Also update with real count from server
        if (window.loadUnreadCount) {
            window.loadUnreadCount();
        }
    }

    /**
     * Start ping timer to keep connection alive
     */
    startPing() {
        this.pingTimer = setInterval(() => {
            if (this.isConnected) {
                this.send({ type: 'ping' });
            }
        }, 30000); // Ping every 30 seconds
    }

    /**
     * Stop ping timer
     */
    stopPing() {
        if (this.pingTimer) {
            clearInterval(this.pingTimer);
            this.pingTimer = null;
        }
    }

    /**
     * Update connection status indicator
     */
    updateConnectionStatus(status, text) {
        if (!this.connectionStatus) return;

        const dot = this.connectionStatus.querySelector('.connection-dot');
        const textElement = this.connectionStatus.querySelector('.connection-text');

        if (dot) {
            dot.className = `connection-dot ${status}`;
        }

        if (textElement) {
            textElement.textContent = text;
        }

        // Show the status indicator
        this.connectionStatus.classList.remove('hidden');
    }

    /**
     * Schedule reconnection attempt
     */
    scheduleReconnect() {
        this.reconnectAttempts++;
        console.log(`Scheduling reconnect attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts}`);

        this.updateConnectionStatus('connecting', `Đang kết nối lại... (${this.reconnectAttempts}/${this.maxReconnectAttempts})`);

        this.reconnectTimer = setTimeout(() => {
            this.connect();
        }, this.reconnectInterval);
    }

    /**
     * Set user credentials for authentication
     */
    setCredentials(userId, token) {
        this.userId = userId;
        this.token = token;
        
        if (this.isConnected) {
            this.authenticate();
        }
    }

    /**
     * Get connection status
     */
    getStatus() {
        return {
            connected: this.isConnected,
            reconnectAttempts: this.reconnectAttempts,
            socketState: this.socket ? this.socket.readyState : null
        };
    }
}

// Global notification WebSocket instance
window.notificationWS = null;

/**
 * Initialize WebSocket connection
 */
function initializeNotificationWebSocket(userId, token) {
    // Request notification permission
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // Load initial notifications
    loadNotifications();

    // Load unread count
    loadUnreadCount();

    // Create WebSocket connection
    window.notificationWS = new NotificationWebSocket({
        url: 'ws://localhost:8080',
        userId: userId,
        token: token,
        onNotification: (notification) => {
            // Custom notification handler
            console.log('Notification received:', notification);

            // Show browser notification
            if (Notification.permission === 'granted') {
                const browserNotification = new Notification(notification.title, {
                    body: notification.message,
                    icon: '/assets/images/logo.png',
                    tag: 'cellphone-store'
                });

                // Auto-close after 5 seconds
                setTimeout(() => {
                    browserNotification.close();
                }, 5000);
            }

            // Show in-app notification
            if (window.showNotification) {
                const type = getNotificationTypeClass(notification.type);
                window.showNotification(`${notification.title}: ${notification.message}`, type);
            }

            // Reload notifications and update badge
            loadNotifications();
            loadUnreadCount();
        },
        onConnect: () => {
            console.log('Connected to notification server');
            updateConnectionStatus(true);
        },
        onDisconnect: () => {
            console.log('Disconnected from notification server');
            updateConnectionStatus(false);
        }
    });
}

/**
 * Update connection status indicator
 */
function updateConnectionStatus(connected) {
    const indicator = document.querySelector('.ws-connection-status');
    if (indicator) {
        indicator.className = `ws-connection-status ${connected ? 'connected' : 'disconnected'}`;
        indicator.title = connected ? 'Connected to notification server' : 'Disconnected from notification server';
    }
}

/**
 * Get notification type class for styling
 */
function getNotificationTypeClass(type) {
    const typeMap = {
        'order': 'success',
        'product': 'info',
        'promotion': 'warning',
        'system': 'info',
        'admin': 'primary'
    };
    return typeMap[type] || 'info';
}

/**
 * Load notifications from API
 */
async function loadNotifications() {
    console.log('Loading notifications...');
    try {
        const response = await fetch('/api/notifications');
        console.log('Response status:', response.status);

        if (!response.ok) {
            throw new Error(`Failed to fetch notifications: ${response.status}`);
        }

        const data = await response.json();
        console.log('Notifications data:', data);

        if (data.success) {
            displayNotifications(data.notifications);
        } else {
            console.error('API returned error:', data.error);
            displayNotifications([]);
        }
    } catch (error) {
        console.error('Error loading notifications:', error);
        // Show error in dropdown
        const notificationList = document.querySelector('.notification-list');
        if (notificationList) {
            notificationList.innerHTML = `
                <div class="text-center p-3 text-danger">
                    <i class="fas fa-exclamation-triangle"></i><br>
                    Lỗi tải thông báo
                </div>
            `;
        }
    }
}

/**
 * Load unread notification count
 */
async function loadUnreadCount() {
    console.log('Loading unread count...');
    try {
        const response = await fetch('/api/notifications/unread-count');
        console.log('Unread count response status:', response.status);

        if (!response.ok) {
            throw new Error(`Failed to fetch unread count: ${response.status}`);
        }

        const data = await response.json();
        console.log('Unread count data:', data);

        if (data.success) {
            updateNotificationBadge(data.count);
        } else {
            console.error('API returned error for unread count:', data.error);
        }
    } catch (error) {
        console.error('Error loading unread count:', error);
    }
}

/**
 * Display notifications in dropdown
 */
function displayNotifications(notifications) {
    const notificationList = document.querySelector('.notification-list');
    if (!notificationList) return;

    if (notifications.length === 0) {
        notificationList.innerHTML = `
            <div class="text-center p-3 text-muted">
                <i class="fas fa-bell-slash"></i><br>
                Không có thông báo mới
            </div>
        `;
        return;
    }

    let html = '';
    notifications.forEach(notification => {
        const isUnread = !notification.is_read;
        const timeAgo = formatTimeAgo(notification.created_at);

        html += `
            <div class="notification-item ${isUnread ? 'unread' : ''} type-${notification.type}"
                 data-notification-id="${notification.notification_id}"
                 onclick="markNotificationAsRead(${notification.notification_id})">
                <div class="notification-title">${escapeHtml(notification.title)}</div>
                <div class="notification-message">${escapeHtml(notification.message)}</div>
                <div class="notification-time">${timeAgo}</div>
            </div>
        `;
    });

    notificationList.innerHTML = html;
}

/**
 * Update notification badge
 */
function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-badge');
    if (!badge) return;

    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = 'inline';
    } else {
        badge.style.display = 'none';
    }
}

/**
 * Mark notification as read
 */
async function markNotificationAsRead(notificationId) {
    try {
        const response = await fetch('/api/notifications/mark-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                notification_id: notificationId
            })
        });

        if (response.ok) {
            // Update UI
            const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationElement) {
                notificationElement.classList.remove('unread');
            }

            // Reload unread count
            loadUnreadCount();
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
}

/**
 * Mark all notifications as read
 */
async function markAllNotificationsRead() {
    try {
        const response = await fetch('/api/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (response.ok) {
            // Reload notifications and count
            loadNotifications();
            loadUnreadCount();
        }
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
}

/**
 * Format time ago
 */
function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) {
        return 'Vừa xong';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `${minutes} phút trước`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `${hours} giờ trước`;
    } else {
        const days = Math.floor(diffInSeconds / 86400);
        return `${days} ngày trước`;
    }
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Test function for debugging notifications
 */
window.testNotifications = function() {
    console.log('Testing notification system...');
    console.log('Current user logged in:', document.querySelector('#notificationDropdown') !== null);

    // Test API endpoints
    loadNotifications();
    loadUnreadCount();

    console.log('Test completed. Check console for results.');
};
