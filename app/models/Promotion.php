<?php

require_once __DIR__ . '/../config/database.php';

class Promotion {
    private $conn;
    private $table_name = "notifications"; // Changed to use notifications table

    public $notification_id; // Changed from promotion_id
    public $promotion_name;
    public $description;
    public $discount_percent;
    public $start_date;
    public $end_date;

    // Additional notification fields
    public $title;
    public $message;
    public $type = 'promotion';
    public $target_type = 'all';
    public $created_by;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Tạo promotion mới (stored as notification)
    public function create() {
        // Generate title and message for the notification
        $this->title = "🎉 Khuyến mãi mới: " . $this->promotion_name;
        $this->message = "Giảm giá {$this->discount_percent}% - {$this->description}. Có hiệu lực từ " .
                        date('d/m/Y', strtotime($this->start_date)) . " đến " .
                        date('d/m/Y', strtotime($this->end_date));

        // Create data JSON with promotion details
        $data = json_encode([
            'discount_percent' => $this->discount_percent,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'action_url' => "/promotions/" // Will be updated with ID after creation
        ]);

        $query = "INSERT INTO " . $this->table_name . "
                  (title, message, type, target_type, target_value, user_id, created_by, data,
                   promotion_name, discount_percent, start_date, end_date)
                  VALUES (:title, :message, :type, :target_type, :target_value, :user_id, :created_by, :data,
                          :promotion_name, :discount_percent, :start_date, :end_date)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':message', $this->message);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':target_type', $this->target_type);
        $stmt->bindValue(':target_value', null);
        $stmt->bindValue(':user_id', null);
        $stmt->bindParam(':created_by', $this->created_by);
        $stmt->bindParam(':data', $data);
        $stmt->bindParam(':promotion_name', $this->promotion_name);
        $stmt->bindParam(':discount_percent', $this->discount_percent);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);

        if($stmt->execute()) {
            $this->notification_id = $this->conn->lastInsertId();

            // Update the action_url in data with the actual ID
            $updatedData = json_encode([
                'discount_percent' => $this->discount_percent,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'action_url' => "/promotions/{$this->notification_id}"
            ]);

            $updateQuery = "UPDATE " . $this->table_name . " SET data = :data WHERE notification_id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':data', $updatedData);
            $updateStmt->bindParam(':id', $this->notification_id);
            $updateStmt->execute();

            return $this->notification_id;
        }
        return false;
    }

    // Lấy tất cả promotions (from notifications table)
    public function getAllPromotions() {
        $query = "SELECT notification_id as promotion_id, promotion_name,
                         COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.description')),
                                 SUBSTRING_INDEX(SUBSTRING_INDEX(message, ' - ', 2), ' - ', -1)) as description,
                         discount_percent, start_date, end_date, created_at
                  FROM " . $this->table_name . "
                  WHERE type = 'promotion'
                  ORDER BY start_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy promotion theo ID (from notifications table)
    public function getPromotionById($id) {
        $query = "SELECT notification_id as promotion_id, promotion_name, title, message,
                         COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.description')),
                                 SUBSTRING_INDEX(SUBSTRING_INDEX(message, ' - ', 2), ' - ', -1)) as description,
                         discount_percent, start_date, end_date, created_at, data
                  FROM " . $this->table_name . "
                  WHERE notification_id = :id AND type = 'promotion'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $this->notification_id = $row->promotion_id; // Map to notification_id
            $this->promotion_name = $row->promotion_name;
            $this->description = $row->description;
            $this->discount_percent = $row->discount_percent;
            $this->start_date = $row->start_date;
            $this->end_date = $row->end_date;
            $this->title = $row->title;
            $this->message = $row->message;
            return $row;
        }
        return false;
    }

    // Lấy promotions đang hoạt động (from notifications table)
    public function getActivePromotions() {
        $query = "SELECT notification_id as promotion_id, promotion_name,
                         COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.description')),
                                 SUBSTRING_INDEX(SUBSTRING_INDEX(message, ' - ', 2), ' - ', -1)) as description,
                         discount_percent, start_date, end_date, created_at
                  FROM " . $this->table_name . "
                  WHERE type = 'promotion'
                  AND start_date <= CURDATE() AND end_date >= CURDATE()
                  ORDER BY start_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Cập nhật promotion (in notifications table)
    public function update() {
        // Update title and message
        $this->title = "🎉 Khuyến mãi mới: " . $this->promotion_name;
        $this->message = "Giảm giá {$this->discount_percent}% - {$this->description}. Có hiệu lực từ " .
                        date('d/m/Y', strtotime($this->start_date)) . " đến " .
                        date('d/m/Y', strtotime($this->end_date));

        // Update data JSON
        $data = json_encode([
            'discount_percent' => $this->discount_percent,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'action_url' => "/promotions/{$this->notification_id}"
        ]);

        $query = "UPDATE " . $this->table_name . "
                  SET title = :title, message = :message, data = :data,
                      promotion_name = :promotion_name,
                      discount_percent = :discount_percent,
                      start_date = :start_date,
                      end_date = :end_date
                  WHERE notification_id = :notification_id AND type = 'promotion'";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':message', $this->message);
        $stmt->bindParam(':data', $data);
        $stmt->bindParam(':promotion_name', $this->promotion_name);
        $stmt->bindParam(':discount_percent', $this->discount_percent);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':notification_id', $this->notification_id);

        return $stmt->execute();
    }

    // Xóa promotion (from notifications table)
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE notification_id = :id AND type = 'promotion'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Đếm số lượng promotions (from notifications table)
    public function countPromotions() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE type = 'promotion'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row->total;
    }

    // Helper method to get promotion ID (notification_id) for backward compatibility
    public function getPromotionId() {
        return $this->notification_id;
    }

    // Helper method to set promotion ID (notification_id) for backward compatibility
    public function setPromotionId($id) {
        $this->notification_id = $id;
    }
}
?>
