<?php

require_once __DIR__ . '/../models/Notification.php';

class NotificationService
{
    private $notificationModel;
    private $websocketUrl;
    private $websocketPort;

    public function __construct($websocketUrl = 'localhost', $websocketPort = 8080)
    {
        $this->notificationModel = new Notification();
        $this->websocketUrl = $websocketUrl;
        $this->websocketPort = $websocketPort;
    }

    /**
     * Send notification to specific user
     */
    public function sendToUser($userId, $title, $message, $type = 'system', $data = null, $createdBy = null)
    {
        // Check if user has enabled this type of notification
        if (!$this->isNotificationEnabled($userId, $type)) {
            return false; // User has disabled this type of notification
        }

        // Save to database
        $notificationId = $this->notificationModel->createForUser($userId, $title, $message, $type, $data, $createdBy);

        if ($notificationId) {
            // Send via WebSocket
            $notification = [
                'id' => $notificationId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'data' => $data,
                'created_at' => date('Y-m-d H:i:s'),
                'target_type' => 'user',
                'user_id' => $userId
            ];

            $this->sendWebSocketNotification('user', $userId, $notification);
            return $notificationId;
        }

        return false;
    }

    /**
     * Send notification to all users
     */
    public function sendToAll($title, $message, $type = 'system', $data = null, $createdBy = null)
    {
        // Save to database
        $notificationId = $this->notificationModel->createForAll($title, $message, $type, $data, $createdBy);
        
        if ($notificationId) {
            // Send via WebSocket
            $notification = [
                'id' => $notificationId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'data' => $data,
                'created_at' => date('Y-m-d H:i:s'),
                'target_type' => 'all'
            ];

            $this->sendWebSocketNotification('all', null, $notification);
            return $notificationId;
        }

        return false;
    }

    /**
     * Send notification to users with specific role
     */
    public function sendToRole($role, $title, $message, $type = 'system', $data = null, $createdBy = null)
    {
        // Save to database
        $notificationId = $this->notificationModel->createForRole($role, $title, $message, $type, $data, $createdBy);
        
        if ($notificationId) {
            // Send via WebSocket
            $notification = [
                'id' => $notificationId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'data' => $data,
                'created_at' => date('Y-m-d H:i:s'),
                'target_type' => 'role',
                'role' => $role
            ];

            $this->sendWebSocketNotification('role', $role, $notification);
            return $notificationId;
        }

        return false;
    }

    /**
     * Send WebSocket notification to the notification server
     */
    private function sendWebSocketNotification($targetType, $targetValue, $notification)
    {
        try {
            // Create a simple HTTP request to notify the WebSocket server
            // In a production environment, you might want to use a message queue
            $postData = json_encode([
                'action' => 'send_notification',
                'target_type' => $targetType,
                'target_value' => $targetValue,
                'notification' => $notification
            ]);

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => $postData,
                    'timeout' => 5
                ]
            ]);

            // Try to send to WebSocket server's HTTP endpoint
            $result = @file_get_contents("http://{$this->websocketUrl}:8081/notify", false, $context);
            
            if ($result === false) {
                error_log("Failed to send WebSocket notification");
            }
        } catch (Exception $e) {
            error_log("WebSocket notification error: " . $e->getMessage());
        }
    }

    /**
     * Order-related notifications
     */
    public function notifyOrderCreated($orderId, $userId, $totalAmount)
    {
        $title = "Đơn hàng mới";
        $message = "Đơn hàng #{$orderId} đã được tạo thành công với tổng giá trị " . number_format($totalAmount, 0, ',', '.') . " VNĐ";
        $data = [
            'order_id' => $orderId,
            'total_amount' => $totalAmount,
            'action_url' => "/orders/{$orderId}"
        ];

        // Notify customer
        $this->sendToUser($userId, $title, $message, 'order', $data);

        // Notify admins
        $adminTitle = "Đơn hàng mới từ khách hàng";
        $adminMessage = "Có đơn hàng mới #{$orderId} với giá trị " . number_format($totalAmount, 0, ',', '.') . " VNĐ";
        $this->sendToRole('admin', $adminTitle, $adminMessage, 'order', $data);
    }

    public function notifyOrderStatusChanged($orderId, $userId, $status)
    {
        $statusMessages = [
            'processing' => 'đang được xử lý',
            'shipped' => 'đã được giao cho đơn vị vận chuyển',
            'delivered' => 'đã được giao thành công',
            'cancelled' => 'đã bị hủy'
        ];

        $statusMessage = $statusMessages[$status] ?? $status;
        $title = "Cập nhật đơn hàng";
        $message = "Đơn hàng #{$orderId} {$statusMessage}";
        $data = [
            'order_id' => $orderId,
            'status' => $status,
            'action_url' => "/orders/{$orderId}"
        ];

        $this->sendToUser($userId, $title, $message, 'order', $data);
    }

    /**
     * Product-related notifications
     */
    public function notifyProductAdded($productId, $productName)
    {
        $title = "Sản phẩm mới";
        $message = "Sản phẩm mới '{$productName}' đã được thêm vào cửa hàng";
        $data = [
            'product_id' => $productId,
            'action_url' => "/products/{$productId}"
        ];

        $this->sendToAll($title, $message, 'product', $data);
    }

    public function notifyProductLowStock($productId, $productName, $currentStock)
    {
        $title = "Cảnh báo tồn kho";
        $message = "Sản phẩm '{$productName}' chỉ còn {$currentStock} sản phẩm trong kho";
        $data = [
            'product_id' => $productId,
            'current_stock' => $currentStock,
            'action_url' => "/admin/products/edit/{$productId}"
        ];

        $this->sendToRole('admin', $title, $message, 'product', $data);
    }

    /**
     * Promotion-related notifications
     */
    public function notifyPromotion($title, $message, $promotionData = null)
    {
        $this->sendToAll($title, $message, 'promotion', $promotionData);
    }

    /**
     * System notifications
     */
    public function notifySystemMaintenance($message, $scheduledTime = null)
    {
        $title = "Bảo trì hệ thống";
        $data = $scheduledTime ? ['scheduled_time' => $scheduledTime] : null;
        
        $this->sendToAll($title, $message, 'system', $data);
    }

    /**
     * Admin notifications
     */
    public function notifyAdminAction($title, $message, $data = null, $createdBy = null)
    {
        $this->sendToRole('admin', $title, $message, 'admin', $data, $createdBy);
    }

    /**
     * Get notifications for a user
     */
    public function getUserNotifications($userId, $limit = 20, $offset = 0)
    {
        return $this->notificationModel->getByUserId($userId, $limit, $offset);
    }

    /**
     * Get unread count for a user
     */
    public function getUnreadCount($userId)
    {
        return $this->notificationModel->getUnreadCount($userId);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId = null)
    {
        return $this->notificationModel->markAsRead($notificationId, $userId);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId)
    {
        return $this->notificationModel->markAllAsRead($userId);
    }

    /**
     * Get all notifications for admin (with pagination and filtering)
     */
    public function getAllNotifications($limit = 50, $offset = 0, $type = '')
    {
        return $this->notificationModel->getAllNotifications($limit, $offset, $type);
    }

    /**
     * Get total notification count for admin
     */
    public function getTotalNotificationCount($type = '')
    {
        return $this->notificationModel->getTotalNotificationCount($type);
    }

    /**
     * Delete notification (admin only)
     */
    public function deleteNotification($notificationId)
    {
        return $this->notificationModel->deleteNotification($notificationId);
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats()
    {
        return $this->notificationModel->getNotificationStats();
    }

    /**
     * Get user notification settings
     */
    public function getUserSettings($userId)
    {
        return $this->notificationModel->getUserSettings($userId);
    }

    /**
     * Update user notification settings
     */
    public function updateUserSettings($userId, $settings)
    {
        return $this->notificationModel->updateUserSettings($userId, $settings);
    }

    /**
     * Check if user has enabled notifications for a specific type
     */
    public function isNotificationEnabled($userId, $type)
    {
        return $this->notificationModel->isNotificationEnabled($userId, $type);
    }
}
