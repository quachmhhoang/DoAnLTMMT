<?php

// Import các file cần thiết
require_once __DIR__ . '/../services/NotificationService.php';
require_once __DIR__ . '/../helpers/SessionHelper.php';

class NotificationController
{
    private $notificationService;

    public function __construct()
    {
        // Khởi tạo NotificationService
        $this->notificationService = new NotificationService();
    }

    /**
     * API: Lấy danh sách thông báo của người dùng hiện tại
     */
    public function getNotifications()
    {
        // Kiểm tra người dùng đã đăng nhập chưa
        if (!SessionHelper::isLoggedIn()) {
            http_response_code(401); // Unauthorized
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        // Lấy thông tin người dùng hiện tại
        $user = SessionHelper::getCurrentUser();

        // Lấy tham số limit và offset từ query string (GET)
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        try {
            // Gọi service để lấy danh sách thông báo
            $notifications = $this->notificationService->getUserNotifications($user->user_id, $limit, $offset);
            // Gọi service để lấy số lượng thông báo chưa đọc
            $unreadCount = $this->notificationService->getUnreadCount($user->user_id);

            // Trả kết quả thành công dưới dạng JSON
            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
        } catch (Exception $e) {
            // Trả lỗi nếu có exception
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch notifications']);
        }
    }

    /**
     * API: Đánh dấu một thông báo là đã đọc
     */
    public function markAsRead()
    {
        if (!SessionHelper::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        // Lấy user và dữ liệu từ body request
        $user = SessionHelper::getCurrentUser();
        $input = json_decode(file_get_contents('php://input'), true);
        $notificationId = isset($input['notification_id']) ? (int)$input['notification_id'] : 0;

        if (!$notificationId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid notification ID']);
            return;
        }

        try {
            $result = $this->notificationService->markAsRead($notificationId, $user->user_id);

            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Notification not found']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to mark notification as read']);
        }
    }

    /**
     * API: Đánh dấu tất cả thông báo là đã đọc
     */
    public function markAllAsRead()
    {
        if (!SessionHelper::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $user = SessionHelper::getCurrentUser();

        try {
            $result = $this->notificationService->markAllAsRead($user->user_id);

            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to mark notifications as read']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to mark notifications as read']);
        }
    }

    /**
     * API: Gửi thông báo thử nghiệm (chỉ admin)
     */
    public function sendTestNotification()
    {
        if (!SessionHelper::isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        // Nhận dữ liệu từ body
        $input = json_decode(file_get_contents('php://input'), true);
        $title = $input['title'] ?? 'Test Notification';
        $message = $input['message'] ?? 'This is a test notification';
        $type = $input['type'] ?? 'system';
        $target = $input['target'] ?? 'all';

        $currentUser = SessionHelper::getCurrentUser();

        try {
            switch ($target) {
                case 'all':
                    $result = $this->notificationService->sendToAll($title, $message, $type, null, $currentUser->user_id);
                    break;
                case 'admin':
                    $result = $this->notificationService->sendToRole('admin', $title, $message, $type, null, $currentUser->user_id);
                    break;
                case 'customer':
                    $result = $this->notificationService->sendToRole('customer', $title, $message, $type, null, $currentUser->user_id);
                    break;
                default:
                    if (is_numeric($target)) {
                        $result = $this->notificationService->sendToUser($target, $title, $message, $type, null, $currentUser->user_id);
                    } else {
                        throw new Exception('Invalid target');
                    }
            }

            if ($result) {
                echo json_encode(['success' => true, 'notification_id' => $result]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to send notification']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to send notification: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Lấy số lượng thông báo chưa đọc
     */
    public function getUnreadCount()
    {
        if (!SessionHelper::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $user = SessionHelper::getCurrentUser();

        try {
            $count = $this->notificationService->getUnreadCount($user->user_id);
            echo json_encode(['success' => true, 'count' => $count]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to get unread count']);
        }
    }

    /**
     * Trang quản lý thông báo cho admin
     */
    public function adminIndex()
    {
        if (!SessionHelper::isAdmin()) {
            header('Location: /login');
            exit();
        }

        // Load view quản lý thông báo
        include __DIR__ . '/../views/admin/notifications/index.php';
    }

    /**
     * API: Lấy tất cả thông báo (dành cho admin)
     */
    public function getAllNotifications()
    {
        if (!SessionHelper::isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }

        // Nếu yêu cầu lấy thống kê
        if (isset($_GET['stats'])) {
            try {
                $stats = $this->notificationService->getNotificationStats();
                echo json_encode(['success' => true, 'stats' => $stats]);
                return;
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to fetch statistics']);
                return;
            }
        }

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $type = isset($_GET['type']) ? $_GET['type'] : '';

        try {
            $notifications = $this->notificationService->getAllNotifications($limit, $offset, $type);
            $totalCount = $this->notificationService->getTotalNotificationCount($type);

            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'total_count' => $totalCount,
                'current_page' => floor($offset / $limit) + 1,
                'total_pages' => ceil($totalCount / $limit)
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch notifications']);
        }
    }

    /**
     * API: Gửi thông báo tuỳ chỉnh (Admin)
     */
    public function sendCustomNotification()
    {
        if (!SessionHelper::isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $title = trim($input['title'] ?? '');
        $message = trim($input['message'] ?? '');
        $type = $input['type'] ?? 'system';
        $target = $input['target'] ?? 'all';
        $targetValue = $input['target_value'] ?? null;

        if (empty($title) || empty($message)) {
            http_response_code(400);
            echo json_encode(['error' => 'Title and message are required']);
            return;
        }

        $currentUser = SessionHelper::getCurrentUser();

        try {
            $result = false;

            switch ($target) {
                case 'all':
                    $result = $this->notificationService->sendToAll($title, $message, $type, null, $currentUser->user_id);
                    break;
                case 'role':
                    if (!$targetValue) {
                        throw new Exception('Role is required for role-based notifications');
                    }
                    $result = $this->notificationService->sendToRole($targetValue, $title, $message, $type, null, $currentUser->user_id);
                    break;
                case 'user':
                    if (!$targetValue || !is_numeric($targetValue)) {
                        throw new Exception('Valid user ID is required for user-specific notifications');
                    }
                    $result = $this->notificationService->sendToUser($targetValue, $title, $message, $type, null, $currentUser->user_id);
                    break;
                default:
                    throw new Exception('Invalid target type');
            }

            if ($result) {
                echo json_encode(['success' => true, 'notification_id' => $result]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to send notification']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to send notification: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Xoá thông báo (Admin)
     */
    public function deleteNotification()
    {
        if (!SessionHelper::isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $notificationId = isset($input['notification_id']) ? (int)$input['notification_id'] : 0;

        if (!$notificationId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid notification ID']);
            return;
        }

        try {
            $result = $this->notificationService->deleteNotification($notificationId);

            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Notification not found']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete notification']);
        }
    }

    /**
     * Trang thiết lập thông báo của người dùng
     */
    public function settings()
    {
        if (!SessionHelper::isLoggedIn()) {
            header('Location: /login');
            exit();
        }

        include __DIR__ . '/../views/notifications/settings.php';
    }

    /**
     * API: Lấy thiết lập thông báo của người dùng
     */
    public function getSettings()
    {
        if (!SessionHelper::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $user = SessionHelper::getCurrentUser();

        try {
            $settings = $this->notificationService->getUserSettings($user->user_id);
            echo json_encode(['success' => true, 'settings' => $settings]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch settings']);
        }
    }

    /**
     * API: Cập nhật thiết lập thông báo của người dùng
     */
    public function updateSettings()
    {
        if (!SessionHelper::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $user = SessionHelper::getCurrentUser();
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['settings']) || !is_array($input['settings'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid settings data']);
            return;
        }

        try {
            $result = $this->notificationService->updateUserSettings($user->user_id, $input['settings']);

            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update settings']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update settings: ' . $e->getMessage()]);
        }
    }
}
