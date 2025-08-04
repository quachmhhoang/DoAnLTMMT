# WebSocket Push Notifications System

This document describes the real-time push notification system implemented for the CellPhone Store project using WebSocket technology.

## Overview

The notification system provides real-time push notifications to users through WebSocket connections. It supports various types of notifications including order updates, product announcements, promotions, and system messages.

## Architecture

### Components

1. **WebSocket Server** (`app/websocket/NotificationServer.php`)
   - Handles WebSocket connections
   - Manages user authentication
   - Broadcasts notifications to connected clients

2. **Notification Service** (`app/services/NotificationService.php`)
   - Business logic for creating and sending notifications
   - Database operations for notification storage
   - Integration with WebSocket server

3. **Notification Model** (`app/models/Notification.php`)
   - Database model for notifications
   - CRUD operations for notification data

4. **Client-side WebSocket** (`assets/js/websocket-client.js`)
   - JavaScript WebSocket client
   - Handles connection management and reconnection
   - Displays notifications in the UI

5. **Notification Controller** (`app/controllers/NotificationController.php`)
   - HTTP endpoints for notification management
   - AJAX endpoints for frontend integration

## Database Schema

### Notifications Table
```sql
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('order', 'product', 'promotion', 'system', 'admin') NOT NULL DEFAULT 'system',
    target_type ENUM('user', 'role', 'all') NOT NULL DEFAULT 'user',
    target_value VARCHAR(100),
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME NULL,
    user_id INT NULL,
    created_by INT NULL,
    data JSON NULL,
    -- Foreign keys and indexes...
);
```

### Notification Settings Table
```sql
CREATE TABLE notification_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notification_type ENUM('order', 'product', 'promotion', 'system', 'admin') NOT NULL,
    is_enabled BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Foreign keys and unique constraints...
);
```

## Installation & Setup

### 1. Database Setup
Run the migration script to add notification tables:
```sql
mysql -u username -p database_name < database/add_notifications.sql
```

### 2. Install Dependencies
The WebSocket functionality uses the Ratchet library which is already included in composer.json:
```bash
composer install
```

### 3. Start WebSocket Server
Run the WebSocket server:
```bash
# Using PHP directly
php start-websocket.php

# Or using the batch file on Windows
start-websocket.bat
```

The WebSocket server will start on `ws://localhost:8080` by default.

### 4. Configure Client
The WebSocket client is automatically initialized for logged-in users in the main layout (`app/views/layout/app.php`).

## Usage

### Sending Notifications

#### From Controllers
```php
// In any controller
require_once __DIR__ . '/../services/NotificationService.php';

$notificationService = new NotificationService();

// Send to specific user
$notificationService->sendToUser(
    $userId, 
    'Order Confirmation', 
    'Your order #123 has been confirmed', 
    'order'
);

// Send to all users
$notificationService->sendToAll(
    'New Product', 
    'Check out our latest iPhone!', 
    'product'
);

// Send to users with specific role
$notificationService->sendToRole(
    'admin', 
    'New Order', 
    'A new order has been placed', 
    'order'
);
```

#### Predefined Methods
```php
// Order notifications
$notificationService->notifyOrderCreated($orderId, $userId, $totalAmount);
$notificationService->notifyOrderStatusChanged($orderId, $userId, $status);

// Product notifications
$notificationService->notifyProductAdded($productId, $productName);
$notificationService->notifyProductLowStock($productId, $productName, $currentStock);

// Promotion notifications
$notificationService->notifyPromotion($title, $message, $promotionData);

// System notifications
$notificationService->notifySystemMaintenance($message, $scheduledTime);
```

### Client-side Integration

The WebSocket client automatically:
- Connects when a user logs in
- Displays browser notifications (if permission granted)
- Shows in-app toast notifications
- Updates the notification badge in the navigation bar
- Handles connection failures and reconnection

### Notification Types

1. **Order** (`order`) - Order-related notifications
2. **Product** (`product`) - Product updates and announcements
3. **Promotion** (`promotion`) - Marketing and promotional messages
4. **System** (`system`) - System maintenance and updates
5. **Admin** (`admin`) - Administrative notifications

### Target Types

1. **User** (`user`) - Send to specific user
2. **Role** (`role`) - Send to all users with specific role (admin/customer)
3. **All** (`all`) - Send to all users

## API Endpoints

### Get Notifications
```
GET /api/notifications
```
Returns notifications for the current user.

### Mark as Read
```
POST /api/notifications/mark-read
Content-Type: application/json

{
    "notification_id": 123
}
```

### Mark All as Read
```
POST /api/notifications/mark-all-read
```

### Send Test Notification (Admin only)
```
POST /api/notifications/test
Content-Type: application/json

{
    "title": "Test Notification",
    "message": "This is a test",
    "type": "system",
    "target": "all"
}
```

## Configuration

### WebSocket Server Configuration
Edit `start-websocket.php` to change server settings:
```php
$port = 8080;  // WebSocket port
$host = '0.0.0.0';  // Bind address
```

### Client Configuration
Edit `assets/js/websocket-client.js` to change client settings:
```javascript
const options = {
    url: 'ws://localhost:8080',
    reconnectInterval: 5000,
    maxReconnectAttempts: 10
};
```

## Features

### Real-time Notifications
- Instant delivery of notifications to connected users
- Support for different notification types and targeting
- Persistent storage in database

### Connection Management
- Automatic reconnection on connection loss
- Ping/pong heartbeat to maintain connection
- Connection status indicator in UI

### User Experience
- Browser notifications (with permission)
- In-app toast notifications
- Notification dropdown with history
- Unread notification badge
- Mark as read functionality

### Security
- User authentication for WebSocket connections
- Session-based token validation
- Role-based notification targeting

## Troubleshooting

### WebSocket Server Won't Start
1. Check if port 8080 is available
2. Ensure PHP has socket extension enabled
3. Check firewall settings

### Notifications Not Received
1. Verify WebSocket server is running
2. Check browser console for connection errors
3. Ensure user is authenticated
4. Check notification settings in database

### Browser Notifications Not Working
1. Check if notification permission is granted
2. Verify HTTPS is used (required for some browsers)
3. Check browser notification settings

## Development

### Adding New Notification Types
1. Add new type to database enum
2. Update NotificationService methods
3. Add client-side styling for new type
4. Update documentation

### Extending Functionality
- Add notification scheduling
- Implement push notifications for mobile
- Add email/SMS fallback options
- Create notification templates

## Security Considerations

- Validate all user inputs
- Implement proper authentication
- Use HTTPS in production
- Sanitize notification content
- Implement rate limiting
- Monitor for abuse

## Performance

- WebSocket server can handle hundreds of concurrent connections
- Database queries are optimized with proper indexes
- Client-side reconnection prevents connection buildup
- Notification history is paginated

## Future Enhancements

- Mobile push notifications
- Email notifications
- Notification templates
- Advanced targeting rules
- Analytics and reporting
- Notification scheduling
