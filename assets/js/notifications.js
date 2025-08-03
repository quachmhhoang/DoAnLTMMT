// Notification Management System
class NotificationManager {
    constructor() {
        this.websocket = null;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 1000;
        this.userId = null;
        this.unreadCount = 0;
        
        this.init();
    }

    async init() {
        // Initialize service worker
        await this.initServiceWorker();

        // Initialize push notifications
        await this.initPushNotifications();

        // Try WebSocket first, fallback to SSE
        try {
            this.initWebSocket();
        } catch (error) {
            console.log('WebSocket failed, using SSE fallback');
            this.initSSE();
        }

        // Load initial notifications
        this.loadNotifications();

        // Update unread count
        this.updateUnreadCount();

        // Set up UI event listeners
        this.setupEventListeners();
    }

    async initServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/assets/js/sw.js');
                console.log('Service Worker registered:', registration);
                
                // Listen for service worker updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            // New service worker available
                            this.showUpdateAvailable();
                        }
                    });
                });
                
                return registration;
            } catch (error) {
                console.error('Service Worker registration failed:', error);
            }
        }
    }

    async initPushNotifications() {
        if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) {
            console.warn('Push notifications not supported');
            return;
        }

        // Check current permission
        let permission = Notification.permission;
        
        if (permission === 'default') {
            // Request permission
            permission = await Notification.requestPermission();
        }

        if (permission === 'granted') {
            await this.subscribeToPush();
        } else {
            console.warn('Push notification permission denied');
        }
    }

    async subscribeToPush() {
        try {
            const registration = await navigator.serviceWorker.ready;
            
            // Check if already subscribed
            let subscription = await registration.pushManager.getSubscription();
            
            if (!subscription) {
                // Create new subscription
                const vapidPublicKey = 'BEl62iUYgUivxIkv69yViEuiBIa40HI80NM9f4LiKiOiWjjS5Q4tiYPiWfnBBmjMQR6QS-I2b-t5-tx2-cp5bng'; // Replace with your VAPID public key
                
                subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: this.urlBase64ToUint8Array(vapidPublicKey)
                });
            }

            // Send subscription to server
            await this.sendSubscriptionToServer(subscription);
            
            console.log('Push subscription successful:', subscription);
        } catch (error) {
            console.error('Failed to subscribe to push notifications:', error);
        }
    }

    async sendSubscriptionToServer(subscription) {
        try {
            const response = await fetch('/api/notifications/push-subscription', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(subscription)
            });

            if (!response.ok) {
                throw new Error('Failed to send subscription to server');
            }

            console.log('Subscription sent to server successfully');
        } catch (error) {
            console.error('Error sending subscription to server:', error);
        }
    }

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    initWebSocket() {
        // Try different ports in case the server is running on a different port
        const ports = [8080, 8081, 8082, 8083, 8084];
        let currentPortIndex = 0;

        const tryConnection = () => {
            if (currentPortIndex >= ports.length) {
                console.error('Failed to connect to WebSocket server on any port');
                this.showConnectionError();
                return;
            }

            const port = ports[currentPortIndex];
            console.log(`Trying to connect to WebSocket on port ${port}...`);

            try {
                this.websocket = new WebSocket(`ws://localhost:${port}`);

                this.websocket.onopen = (event) => {
                    console.log(`WebSocket connected on port ${port}`);
                    this.isConnected = true;
                    this.reconnectAttempts = 0;
                    this.hideConnectionError();

                    // Authenticate with user ID if logged in
                    if (this.userId) {
                        this.websocket.send(JSON.stringify({
                            type: 'auth',
                            userId: this.userId
                        }));
                    }
                };

                this.websocket.onerror = (error) => {
                    console.log(`Failed to connect on port ${port}, trying next...`);
                    currentPortIndex++;
                    setTimeout(tryConnection, 1000);
                };

                this.websocket.onmessage = (event) => {
                    try {
                        const data = JSON.parse(event.data);
                        this.handleWebSocketMessage(data);
                    } catch (error) {
                        console.error('Error parsing WebSocket message:', error);
                    }
                };

                this.websocket.onclose = (event) => {
                    console.log('WebSocket disconnected');
                    this.isConnected = false;
                    this.attemptReconnect();
                };

            } catch (error) {
                console.error(`Failed to initialize WebSocket on port ${port}:`, error);
                currentPortIndex++;
                setTimeout(tryConnection, 1000);
            }
        };

        tryConnection();
    }

    initSSE() {
        if (!window.EventSource) {
            console.warn('Server-Sent Events not supported');
            this.showConnectionError();
            return;
        }

        console.log('Initializing Server-Sent Events...');

        // Try different ports for SSE endpoint
        const sseUrls = [
            '/sse-notifications.php',  // Current domain
            'http://localhost:8081/sse-notifications.php',  // Notification server
            'http://localhost:8082/sse-notifications.php',
            'http://localhost:8083/sse-notifications.php'
        ];

        this.trySSEConnection(sseUrls, 0);
    }

    trySSEConnection(urls, index) {
        if (index >= urls.length) {
            console.error('Failed to connect to SSE on any URL');
            this.showConnectionError();
            return;
        }

        const url = urls[index];
        console.log(`Trying SSE connection to: ${url}`);

        try {
            this.eventSource = new EventSource(url);

            this.eventSource.onopen = (event) => {
            console.log('SSE connected');
            this.isConnected = true;
            this.reconnectAttempts = 0;
            this.hideConnectionError();
        };

        this.eventSource.addEventListener('connected', (event) => {
            const data = JSON.parse(event.data);
            console.log('SSE authentication successful:', data);
        });

        this.eventSource.addEventListener('notification', (event) => {
            const notification = JSON.parse(event.data);
            this.handleNewNotification(notification);
        });

        this.eventSource.addEventListener('unread_count', (event) => {
            const data = JSON.parse(event.data);
            this.unreadCount = data.count;
            this.updateUnreadCountDisplay();
        });

        this.eventSource.addEventListener('heartbeat', (event) => {
            // Keep connection alive
            console.log('SSE heartbeat received');
        });

        this.eventSource.addEventListener('error', (event) => {
            console.error('SSE error:', event);
            this.isConnected = false;

            if (this.eventSource.readyState === EventSource.CLOSED) {
                console.log('SSE connection closed, attempting to reconnect...');
                this.attemptSSEReconnect();
            }
        });

            this.eventSource.onerror = (event) => {
                console.error(`SSE connection error on ${url}:`, event);
                this.isConnected = false;

                if (this.eventSource.readyState === EventSource.CLOSED) {
                    console.log(`SSE connection closed on ${url}, trying next URL...`);
                    this.eventSource.close();
                    setTimeout(() => {
                        this.trySSEConnection(urls, index + 1);
                    }, 1000);
                } else {
                    this.attemptSSEReconnect();
                }
            };

        } catch (error) {
            console.error(`Failed to create SSE connection to ${url}:`, error);
            setTimeout(() => {
                this.trySSEConnection(urls, index + 1);
            }, 1000);
        }
    }

    attemptSSEReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Attempting to reconnect SSE (${this.reconnectAttempts}/${this.maxReconnectAttempts})`);

            setTimeout(() => {
                if (this.eventSource) {
                    this.eventSource.close();
                }
                this.initSSE();
            }, this.reconnectDelay * this.reconnectAttempts);
        } else {
            console.error('Max SSE reconnection attempts reached');
            this.showConnectionError();
        }
    }

    handleWebSocketMessage(data) {
        switch (data.type) {
            case 'connection':
                console.log('WebSocket connection established:', data.message);
                break;
                
            case 'auth_success':
                console.log('WebSocket authentication successful');
                break;
                
            case 'notification':
                this.handleNewNotification(data.data);
                break;
                
            case 'admin_notification':
                this.handleAdminNotification(data.data);
                break;
                
            case 'pong':
                // Handle ping/pong for connection health
                break;
                
            default:
                console.log('Unknown WebSocket message type:', data.type);
        }
    }

    handleNewNotification(notification) {
        // Update unread count
        this.unreadCount++;
        this.updateUnreadCountDisplay();
        
        // Show browser notification if permission granted
        if (Notification.permission === 'granted') {
            new Notification(notification.title, {
                body: notification.message,
                icon: '/assets/images/icon-192x192.png',
                tag: `notification-${notification.id}`
            });
        }
        
        // Show in-app notification
        this.showInAppNotification(notification);
        
        // Add to notification list if on notifications page
        this.addToNotificationList(notification);
    }

    handleAdminNotification(notification) {
        // Handle admin-specific notifications
        this.handleNewNotification(notification);
    }

    showInAppNotification(notification) {
        const notificationElement = document.createElement('div');
        notificationElement.className = `alert alert-${notification.type || 'info'} alert-dismissible fade show notification-toast`;
        notificationElement.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 400px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        `;
        
        notificationElement.innerHTML = `
            <strong>${notification.title}</strong><br>
            ${notification.message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notificationElement);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notificationElement.parentElement) {
                notificationElement.remove();
            }
        }, 5000);
    }

    attemptReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Attempting to reconnect WebSocket (${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
            
            setTimeout(() => {
                this.initWebSocket();
            }, this.reconnectDelay * this.reconnectAttempts);
        } else {
            console.error('Max reconnection attempts reached');
        }
    }

    async loadNotifications() {
        try {
            const response = await fetch('/api/notifications');
            if (response.ok) {
                const data = await response.json();
                this.displayNotifications(data.notifications);
                this.unreadCount = data.unreadCount;
                this.updateUnreadCountDisplay();
            }
        } catch (error) {
            console.error('Failed to load notifications:', error);
        }
    }

    async updateUnreadCount() {
        try {
            const response = await fetch('/api/notifications/unread-count');
            if (response.ok) {
                const data = await response.json();
                this.unreadCount = data.count;
                this.updateUnreadCountDisplay();
            }
        } catch (error) {
            console.error('Failed to update unread count:', error);
        }
    }

    updateUnreadCountDisplay() {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            if (this.unreadCount > 0) {
                badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch('/api/notifications/mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ notification_id: notificationId })
            });

            if (response.ok) {
                this.unreadCount = Math.max(0, this.unreadCount - 1);
                this.updateUnreadCountDisplay();
            }
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch('/api/notifications/mark-all-read', {
                method: 'POST'
            });

            if (response.ok) {
                this.unreadCount = 0;
                this.updateUnreadCountDisplay();
                
                // Update UI to show all notifications as read
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                });
            }
        } catch (error) {
            console.error('Failed to mark all notifications as read:', error);
        }
    }

    setupEventListeners() {
        // Notification bell click
        document.addEventListener('click', (e) => {
            if (e.target.closest('.notification-bell')) {
                this.toggleNotificationDropdown();
            }
            
            if (e.target.closest('.mark-all-read')) {
                this.markAllAsRead();
            }
            
            if (e.target.closest('.notification-item')) {
                const notificationId = e.target.closest('.notification-item').dataset.notificationId;
                if (notificationId) {
                    this.markAsRead(notificationId);
                }
            }
        });

        // Set user ID from global variable if available
        if (window.currentUserId) {
            this.userId = window.currentUserId;
        }
    }

    toggleNotificationDropdown() {
        const dropdown = document.querySelector('.notification-dropdown');
        if (dropdown) {
            dropdown.classList.toggle('show');
        }
    }

    displayNotifications(notifications) {
        const container = document.querySelector('.notifications-list');
        if (!container) return;

        container.innerHTML = '';
        
        if (notifications.length === 0) {
            container.innerHTML = '<div class="text-center text-muted p-3">Không có thông báo nào</div>';
            return;
        }

        notifications.forEach(notification => {
            const item = this.createNotificationItem(notification);
            container.appendChild(item);
        });
    }

    createNotificationItem(notification) {
        const item = document.createElement('div');
        item.className = `notification-item ${notification.is_read ? '' : 'unread'}`;
        item.dataset.notificationId = notification.notification_id;
        
        item.innerHTML = `
            <div class="notification-content">
                <div class="notification-title">${notification.title}</div>
                <div class="notification-message">${notification.message}</div>
                <div class="notification-time">${this.formatTime(notification.created_at)}</div>
            </div>
            ${!notification.is_read ? '<div class="notification-indicator"></div>' : ''}
        `;
        
        return item;
    }

    addToNotificationList(notification) {
        const container = document.querySelector('.notifications-list');
        if (!container) return;

        const item = this.createNotificationItem(notification);
        container.insertBefore(item, container.firstChild);
    }

    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) { // Less than 1 minute
            return 'Vừa xong';
        } else if (diff < 3600000) { // Less than 1 hour
            return `${Math.floor(diff / 60000)} phút trước`;
        } else if (diff < 86400000) { // Less than 1 day
            return `${Math.floor(diff / 3600000)} giờ trước`;
        } else {
            return date.toLocaleDateString('vi-VN');
        }
    }

    showUpdateAvailable() {
        const updateBanner = document.createElement('div');
        updateBanner.className = 'alert alert-info alert-dismissible fade show';
        updateBanner.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; z-index: 10000; margin: 0; border-radius: 0;';
        updateBanner.innerHTML = `
            Có phiên bản mới của ứng dụng.
            <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="location.reload()">
                Cập nhật ngay
            </button>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.insertBefore(updateBanner, document.body.firstChild);
    }

    showConnectionError() {
        // Remove existing error banner
        this.hideConnectionError();

        const errorBanner = document.createElement('div');
        errorBanner.className = 'alert alert-warning alert-dismissible fade show websocket-error';
        errorBanner.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; z-index: 10000; margin: 0; border-radius: 0;';
        errorBanner.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            Không thể kết nối đến server thông báo. Một số tính năng có thể không hoạt động.
            <button type="button" class="btn btn-sm btn-outline-warning ms-2" onclick="location.reload()">
                Thử lại
            </button>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.insertBefore(errorBanner, document.body.firstChild);
    }

    hideConnectionError() {
        const existingError = document.querySelector('.websocket-error');
        if (existingError) {
            existingError.remove();
        }
    }
}

// Initialize notification manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.notificationManager = new NotificationManager();
});
