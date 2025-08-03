<?php
require_once __DIR__ . '/../config/database.php';

class Notification {
    public $conn; // Make it public for NotificationService access
    private $table_name = "notifications";
    private $settings_table = "notification_settings";
    private $subscriptions_table = "push_subscriptions";

    public $notification_id;
    public $user_id;
    public $title;
    public $message;
    public $type;
    public $is_read;
    public $created_at;
    public $read_at;
    public $data;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Tạo thông báo mới
    public function create($user_id, $title, $message, $type = 'info', $data = null) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, title, message, type, data) 
                  VALUES (:user_id, :title, :message, :type, :data)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':data', $data);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    // Lấy thông báo của user
    public function getUserNotifications($user_id, $limit = 20, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id OR user_id IS NULL 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Lấy số lượng thông báo chưa đọc
    public function getUnreadCount($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE (user_id = :user_id OR user_id IS NULL) AND is_read = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result->count;
    }

    // Đánh dấu thông báo đã đọc
    public function markAsRead($notification_id, $user_id = null) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_read = 1, read_at = NOW() 
                  WHERE notification_id = :notification_id";
        
        if ($user_id) {
            $query .= " AND (user_id = :user_id OR user_id IS NULL)";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':notification_id', $notification_id);
        
        if ($user_id) {
            $stmt->bindParam(':user_id', $user_id);
        }
        
        return $stmt->execute();
    }

    // Đánh dấu tất cả thông báo đã đọc
    public function markAllAsRead($user_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_read = 1, read_at = NOW() 
                  WHERE (user_id = :user_id OR user_id IS NULL) AND is_read = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    }

    // Xóa thông báo
    public function delete($notification_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE notification_id = :notification_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':notification_id', $notification_id);
        
        return $stmt->execute();
    }

    // Lấy cài đặt thông báo của user
    public function getUserSettings($user_id) {
        $query = "SELECT * FROM " . $this->settings_table . " WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        // Nếu chưa có cài đặt, tạo mặc định
        if (!$result) {
            $this->createDefaultSettings($user_id);
            return $this->getUserSettings($user_id);
        }
        
        return $result;
    }

    // Tạo cài đặt mặc định
    private function createDefaultSettings($user_id) {
        $query = "INSERT INTO " . $this->settings_table . " (user_id) VALUES (:user_id)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    }

    // Cập nhật cài đặt thông báo
    public function updateSettings($user_id, $settings) {
        $query = "UPDATE " . $this->settings_table . " SET ";
        $params = [];
        $setParts = [];
        
        foreach ($settings as $key => $value) {
            if (in_array($key, ['push_enabled', 'email_enabled', 'order_notifications', 'system_notifications', 'marketing_notifications'])) {
                $setParts[] = "$key = :$key";
                $params[$key] = $value;
            }
        }
        
        if (empty($setParts)) {
            return false;
        }
        
        $query .= implode(', ', $setParts) . " WHERE user_id = :user_id";
        $params['user_id'] = $user_id;
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        return $stmt->execute();
    }

    // Lưu push subscription
    public function savePushSubscription($user_id, $endpoint, $p256dh_key, $auth_key, $user_agent = null) {
        // Kiểm tra xem subscription đã tồn tại chưa
        $checkQuery = "SELECT subscription_id FROM " . $this->subscriptions_table . " 
                       WHERE user_id = :user_id AND endpoint = :endpoint";
        
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(':user_id', $user_id);
        $checkStmt->bindParam(':endpoint', $endpoint);
        $checkStmt->execute();
        
        if ($checkStmt->fetch()) {
            // Cập nhật thời gian sử dụng cuối
            $updateQuery = "UPDATE " . $this->subscriptions_table . " 
                           SET last_used = NOW() 
                           WHERE user_id = :user_id AND endpoint = :endpoint";
            
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':user_id', $user_id);
            $updateStmt->bindParam(':endpoint', $endpoint);
            
            return $updateStmt->execute();
        }
        
        // Tạo subscription mới
        $query = "INSERT INTO " . $this->subscriptions_table . " 
                  (user_id, endpoint, p256dh_key, auth_key, user_agent) 
                  VALUES (:user_id, :endpoint, :p256dh_key, :auth_key, :user_agent)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':endpoint', $endpoint);
        $stmt->bindParam(':p256dh_key', $p256dh_key);
        $stmt->bindParam(':auth_key', $auth_key);
        $stmt->bindParam(':user_agent', $user_agent);
        
        return $stmt->execute();
    }

    // Lấy push subscriptions của user
    public function getUserPushSubscriptions($user_id) {
        $query = "SELECT * FROM " . $this->subscriptions_table . " WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Xóa push subscription
    public function removePushSubscription($user_id, $endpoint) {
        $query = "DELETE FROM " . $this->subscriptions_table . " 
                  WHERE user_id = :user_id AND endpoint = :endpoint";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':endpoint', $endpoint);
        
        return $stmt->execute();
    }

    // Lấy tất cả thông báo (cho admin)
    public function getAllNotifications($limit = 50, $offset = 0) {
        $query = "SELECT n.*, u.full_name as user_name 
                  FROM " . $this->table_name . " n 
                  LEFT JOIN users u ON n.user_id = u.user_id 
                  ORDER BY n.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
