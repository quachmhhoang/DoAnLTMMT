<?php
require_once __DIR__ . '/../models/Notification.php';

class NotificationService {
    private $notification;
    private $websocketUrl;
    
    public function __construct() {
        $this->notification = new Notification();
        $this->websocketUrl = 'ws://localhost:8080';
    }
    
    // Gửi thông báo đơn hàng mới cho admin
    public function sendNewOrderNotification($order_id, $user_name, $total_amount) {
        $title = "Đơn hàng mới #$order_id";
        $message = "Khách hàng $user_name vừa đặt đơn hàng trị giá " . number_format($total_amount, 0, ',', '.') . " VNĐ";
        $data = json_encode([
            'order_id' => $order_id,
            'action' => 'new_order',
            'url' => "/admin/orders/$order_id"
        ]);
        
        // Tạo thông báo trong database (gửi cho admin - user_id = null)
        $notification_id = $this->notification->create(null, $title, $message, 'order', $data);
        
        if ($notification_id) {
            // Gửi qua WebSocket cho admin
            $this->sendWebSocketNotification([
                'id' => $notification_id,
                'title' => $title,
                'message' => $message,
                'type' => 'order',
                'data' => json_decode($data, true),
                'target' => 'admin'
            ]);
            
            return true;
        }
        
        return false;
    }
    
    // Gửi thông báo cập nhật trạng thái đơn hàng cho khách hàng
    public function sendOrderStatusNotification($user_id, $order_id, $status, $status_text) {
        $title = "Cập nhật đơn hàng #$order_id";
        $message = "Trạng thái đơn hàng của bạn đã được cập nhật: $status_text";
        $data = json_encode([
            'order_id' => $order_id,
            'status' => $status,
            'action' => 'order_status_update',
            'url' => "/orders/$order_id"
        ]);
        
        // Tạo thông báo trong database
        $notification_id = $this->notification->create($user_id, $title, $message, 'order', $data);
        
        if ($notification_id) {
            // Gửi qua WebSocket
            $this->sendWebSocketNotification([
                'id' => $notification_id,
                'title' => $title,
                'message' => $message,
                'type' => 'order',
                'data' => json_decode($data, true),
                'user_id' => $user_id
            ]);
            
            // Gửi push notification nếu user đã đăng ký
            $this->sendPushNotification($user_id, $title, $message, [
                'url' => "/orders/$order_id",
                'order_id' => $order_id
            ]);
            
            return true;
        }
        
        return false;
    }
    
    // Gửi thông báo xác nhận đơn hàng cho khách hàng
    public function sendOrderConfirmationNotification($user_id, $order_id, $total_amount) {
        $title = "Đặt hàng thành công!";
        $message = "Cảm ơn bạn đã đặt hàng. Đơn hàng #$order_id trị giá " . number_format($total_amount, 0, ',', '.') . " VNĐ đã được xác nhận.";
        $data = json_encode([
            'order_id' => $order_id,
            'action' => 'order_confirmation',
            'url' => "/orders/$order_id"
        ]);
        
        // Tạo thông báo trong database
        $notification_id = $this->notification->create($user_id, $title, $message, 'success', $data);
        
        if ($notification_id) {
            // Gửi qua WebSocket
            $this->sendWebSocketNotification([
                'id' => $notification_id,
                'title' => $title,
                'message' => $message,
                'type' => 'success',
                'data' => json_decode($data, true),
                'user_id' => $user_id
            ]);
            
            // Gửi push notification
            $this->sendPushNotification($user_id, $title, $message, [
                'url' => "/orders/$order_id",
                'order_id' => $order_id
            ]);
            
            return true;
        }
        
        return false;
    }
    
    // Gửi thông báo khuyến mãi
    public function sendPromotionNotification($title, $message, $user_id = null) {
        $data = json_encode([
            'action' => 'promotion',
            'url' => "/products"
        ]);
        
        // Tạo thông báo trong database
        $notification_id = $this->notification->create($user_id, $title, $message, 'info', $data);
        
        if ($notification_id) {
            // Gửi qua WebSocket
            $this->sendWebSocketNotification([
                'id' => $notification_id,
                'title' => $title,
                'message' => $message,
                'type' => 'info',
                'data' => json_decode($data, true),
                'user_id' => $user_id
            ]);
            
            // Gửi push notification
            if ($user_id) {
                $this->sendPushNotification($user_id, $title, $message, [
                    'url' => "/products"
                ]);
            }
            
            return true;
        }
        
        return false;
    }
    
    // Gửi thông báo hệ thống
    public function sendSystemNotification($title, $message, $user_id = null) {
        $data = json_encode([
            'action' => 'system',
            'url' => "/"
        ]);
        
        // Tạo thông báo trong database
        $notification_id = $this->notification->create($user_id, $title, $message, 'system', $data);
        
        if ($notification_id) {
            // Gửi qua WebSocket
            $this->sendWebSocketNotification([
                'id' => $notification_id,
                'title' => $title,
                'message' => $message,
                'type' => 'system',
                'data' => json_decode($data, true),
                'user_id' => $user_id
            ]);
            
            return true;
        }
        
        return false;
    }
    
    // Gửi thông báo qua WebSocket
    private function sendWebSocketNotification($notificationData) {
        try {
            // Trong thực tế, bạn sẽ cần một cách để gửi dữ liệu đến WebSocket server
            // Ở đây chúng ta sẽ sử dụng một file tạm thời hoặc Redis để giao tiếp
            
            // Tạo file tạm thời để WebSocket server đọc
            $tempFile = sys_get_temp_dir() . '/websocket_notification_' . uniqid() . '.json';
            file_put_contents($tempFile, json_encode($notificationData));
            
            // Hoặc bạn có thể sử dụng cURL để gửi đến một endpoint của WebSocket server
            // $this->sendToWebSocketEndpoint($notificationData);
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to send WebSocket notification: " . $e->getMessage());
            return false;
        }
    }
    
    // Gửi push notification
    private function sendPushNotification($user_id, $title, $message, $data = []) {
        try {
            // Lấy push subscriptions của user
            $subscriptions = $this->notification->getUserPushSubscriptions($user_id);
            
            if (empty($subscriptions)) {
                return false;
            }
            
            // Chuẩn bị payload
            $payload = json_encode([
                'title' => $title,
                'body' => $message,
                'icon' => '/assets/images/icon-192x192.png',
                'badge' => '/assets/images/badge-72x72.png',
                'data' => array_merge($data, [
                    'timestamp' => time(),
                    'notificationId' => uniqid()
                ])
            ]);
            
            // Gửi đến từng subscription
            foreach ($subscriptions as $subscription) {
                $this->sendPushToSubscription($subscription, $payload);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to send push notification: " . $e->getMessage());
            return false;
        }
    }
    
    // Gửi push notification đến một subscription cụ thể
    private function sendPushToSubscription($subscription, $payload) {
        // Trong thực tế, bạn sẽ cần sử dụng thư viện như web-push-php
        // Ở đây chúng ta chỉ mô phỏng việc gửi
        
        try {
            // Mô phỏng gửi push notification
            // Trong thực tế, bạn sẽ sử dụng VAPID keys và gửi đến endpoint của browser
            
            $endpoint = $subscription->endpoint;
            $p256dh = $subscription->p256dh_key;
            $auth = $subscription->auth_key;
            
            // Log để debug
            error_log("Sending push notification to: " . $endpoint);
            error_log("Payload: " . $payload);
            
            // TODO: Implement actual push notification sending using web-push library
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to send push to subscription: " . $e->getMessage());
            return false;
        }
    }
    
    // Lấy thống kê thông báo
    public function getNotificationStats() {
        try {
            $conn = $this->notification->conn ?? (new Database())->getConnection();
            
            $query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
                        SUM(CASE WHEN type = 'order' THEN 1 ELSE 0 END) as orders,
                        SUM(CASE WHEN type = 'system' THEN 1 ELSE 0 END) as system,
                        SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) as last_24h
                      FROM notifications";
            
            $stmt = $conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            error_log("Failed to get notification stats: " . $e->getMessage());
            return null;
        }
    }
}
