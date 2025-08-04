<?php

require_once __DIR__ . '/../config/database.php';

class Notification
{
    private $conn;
    private $table_name = "notifications";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Create a new notification
     */
    public function create($data)
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  (title, message, type, target_type, target_value, user_id, created_by, data) 
                  VALUES (:title, :message, :type, :target_type, :target_value, :user_id, :created_by, :data)";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':message', $data['message']);
        $stmt->bindParam(':type', $data['type']);
        $stmt->bindParam(':target_type', $data['target_type']);
        $stmt->bindParam(':target_value', $data['target_value']);
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':created_by', $data['created_by']);
        $stmt->bindParam(':data', $data['data']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Get notifications for a specific user
     */
    public function getByUserId($userId, $limit = 20, $offset = 0)
    {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE (target_type = 'user' AND user_id = :user_id) 
                     OR (target_type = 'all') 
                     OR (target_type = 'role' AND target_value = (
                         SELECT role FROM users WHERE user_id = :user_id2
                     ))
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id2', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get unread notifications count for a user
     */
    public function getUnreadCount($userId)
    {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE is_read = FALSE 
                    AND ((target_type = 'user' AND user_id = :user_id) 
                         OR (target_type = 'all') 
                         OR (target_type = 'role' AND target_value = (
                             SELECT role FROM users WHERE user_id = :user_id2
                         )))";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id2', $userId, PDO::PARAM_INT);

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId = null)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_read = TRUE, read_at = NOW() 
                  WHERE notification_id = :notification_id";

        if ($userId) {
            $query .= " AND (user_id = :user_id OR target_type IN ('all', 'role'))";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':notification_id', $notificationId, PDO::PARAM_INT);
        
        if ($userId) {
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        }

        return $stmt->execute();
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_read = TRUE, read_at = NOW() 
                  WHERE is_read = FALSE 
                    AND ((target_type = 'user' AND user_id = :user_id) 
                         OR (target_type = 'all') 
                         OR (target_type = 'role' AND target_value = (
                             SELECT role FROM users WHERE user_id = :user_id2
                         )))";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id2', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete notification
     */
    public function delete($notificationId)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE notification_id = :notification_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':notification_id', $notificationId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Get all notifications (admin view)
     */
    public function getAll($limit = 50, $offset = 0)
    {
        $query = "SELECT n.*, u.username as created_by_username 
                  FROM " . $this->table_name . " n 
                  LEFT JOIN users u ON n.created_by = u.user_id 
                  ORDER BY n.created_at DESC 
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create notification for specific user
     */
    public function createForUser($userId, $title, $message, $type = 'system', $data = null, $createdBy = null)
    {
        return $this->create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'target_type' => 'user',
            'target_value' => null,
            'user_id' => $userId,
            'created_by' => $createdBy,
            'data' => $data ? json_encode($data) : null
        ]);
    }

    /**
     * Create notification for all users
     */
    public function createForAll($title, $message, $type = 'system', $data = null, $createdBy = null)
    {
        return $this->create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'target_type' => 'all',
            'target_value' => null,
            'user_id' => null,
            'created_by' => $createdBy,
            'data' => $data ? json_encode($data) : null
        ]);
    }

    /**
     * Create notification for users with specific role
     */
    public function createForRole($role, $title, $message, $type = 'system', $data = null, $createdBy = null)
    {
        return $this->create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'target_type' => 'role',
            'target_value' => $role,
            'user_id' => null,
            'created_by' => $createdBy,
            'data' => $data ? json_encode($data) : null
        ]);
    }

    /**
     * Get notification by ID
     */
    public function getById($notificationId)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE notification_id = :notification_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':notification_id', $notificationId, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all notifications for admin with pagination and filtering
     */
    public function getAllNotifications($limit = 50, $offset = 0, $type = '')
    {
        $whereClause = '';
        if (!empty($type)) {
            $whereClause = "WHERE type = :type";
        }

        $query = "SELECT n.*, u.username as created_by_username
                  FROM " . $this->table_name . " n
                  LEFT JOIN users u ON n.created_by = u.user_id
                  {$whereClause}
                  ORDER BY n.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if (!empty($type)) {
            $stmt->bindParam(':type', $type);
        }

        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get total notification count for admin
     */
    public function getTotalNotificationCount($type = '')
    {
        $whereClause = '';
        if (!empty($type)) {
            $whereClause = "WHERE type = :type";
        }

        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " {$whereClause}";
        $stmt = $this->conn->prepare($query);

        if (!empty($type)) {
            $stmt->bindParam(':type', $type);
        }

        $stmt->execute();
        $row = $stmt->fetch();
        return $row->total;
    }

    /**
     * Delete notification (admin only)
     */
    public function deleteNotification($notificationId)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE notification_id = :notification_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':notification_id', $notificationId);

        return $stmt->execute();
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats()
    {
        $query = "SELECT
                    COUNT(*) as total_notifications,
                    COUNT(CASE WHEN is_read = FALSE THEN 1 END) as unread_notifications,
                    COUNT(CASE WHEN type = 'order' THEN 1 END) as order_notifications,
                    COUNT(CASE WHEN type = 'product' THEN 1 END) as product_notifications,
                    COUNT(CASE WHEN type = 'promotion' THEN 1 END) as promotion_notifications,
                    COUNT(CASE WHEN type = 'system' THEN 1 END) as system_notifications,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as notifications_24h,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as notifications_7d
                  FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Get user notification settings
     */
    public function getUserSettings($userId)
    {
        $query = "SELECT notification_type, is_enabled
                  FROM notification_settings
                  WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row->notification_type] = (bool)$row->is_enabled;
        }

        // Ensure all notification types are present
        $defaultTypes = ['order', 'product', 'promotion', 'system', 'admin'];
        foreach ($defaultTypes as $type) {
            if (!isset($settings[$type])) {
                $settings[$type] = true; // Default to enabled
            }
        }

        return $settings;
    }

    /**
     * Update user notification settings
     */
    public function updateUserSettings($userId, $settings)
    {
        try {
            $this->conn->beginTransaction();

            foreach ($settings as $type => $enabled) {
                $query = "INSERT INTO notification_settings (user_id, notification_type, is_enabled)
                          VALUES (:user_id, :type, :enabled)
                          ON DUPLICATE KEY UPDATE is_enabled = :enabled2";

                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':type', $type);
                $stmt->bindParam(':enabled', $enabled, PDO::PARAM_BOOL);
                $stmt->bindParam(':enabled2', $enabled, PDO::PARAM_BOOL);
                $stmt->execute();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Check if user has enabled notifications for a specific type
     */
    public function isNotificationEnabled($userId, $type)
    {
        $query = "SELECT is_enabled FROM notification_settings
                  WHERE user_id = :user_id AND notification_type = :type";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':type', $type);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            return (bool)$row->is_enabled;
        }

        // Default to enabled if no setting found
        return true;
    }
}
