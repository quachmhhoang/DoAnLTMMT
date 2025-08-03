<?php
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../helpers/SessionHelper.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/../middleware/SecurityMiddleware.php';

class NotificationController {
    
    // Lấy danh sách thông báo của user
    public function getNotifications() {
        try {
            if (!SessionHelper::isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized', 'debug' => 'User not logged in']);
                return;
            }

            $user = SessionHelper::getCurrentUser();

            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'User not found', 'debug' => 'getCurrentUser returned null']);
                return;
            }

            // Temporarily disable middleware for debugging
            try {
                // Apply rate limiting
                if (class_exists('RateLimitMiddleware')) {
                    RateLimitMiddleware::checkNotificationAPI($user->user_id);
                }

                // Check for suspicious activity
                if (class_exists('SecurityMiddleware')) {
                    SecurityMiddleware::checkSuspiciousActivity($user->user_id);
                }
            } catch (Exception $e) {
                // Log middleware errors but don't fail the request
                error_log("Middleware error: " . $e->getMessage());
            }

            $notification = new Notification();

            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = ($page - 1) * $limit;

            // Add filter support
            $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
            $typeFilter = isset($_GET['type']) ? $_GET['type'] : 'all';

            $notifications = $notification->getUserNotifications($user->user_id, $limit, $offset);
            $unreadCount = $notification->getUnreadCount($user->user_id);

            // Apply filters
            if ($filter === 'unread') {
                $notifications = array_filter($notifications, function($n) { return !$n->is_read; });
            } elseif ($filter === 'read') {
                $notifications = array_filter($notifications, function($n) { return $n->is_read; });
            }

            if ($typeFilter !== 'all') {
                $notifications = array_filter($notifications, function($n) use ($typeFilter) {
                    return $n->type === $typeFilter;
                });
            }

            // Convert to array to ensure proper JSON encoding
            $notifications = array_values($notifications);

            header('Content-Type: application/json');
            echo json_encode([
                'notifications' => $notifications,
                'unreadCount' => $unreadCount,
                'page' => $page,
                'limit' => $limit,
                'filter' => $filter,
                'typeFilter' => $typeFilter,
                'debug' => [
                    'user_id' => $user->user_id,
                    'total_found' => count($notifications),
                    'sql_offset' => $offset
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Internal server error',
                'message' => $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ]);
        }
    }
    
    // Đánh dấu thông báo đã đọc
    public function markAsRead() {
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
        $notification = new Notification();
        
        $input = json_decode(file_get_contents('php://input'), true);
        $notification_id = isset($input['notification_id']) ? (int)$input['notification_id'] : 0;
        
        if (!$notification_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Notification ID required']);
            return;
        }
        
        $result = $notification->markAsRead($notification_id, $user->user_id);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
    }
    
    // Đánh dấu tất cả thông báo đã đọc
    public function markAllAsRead() {
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
        $notification = new Notification();
        
        $result = $notification->markAllAsRead($user->user_id);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
    }
    
    // Lấy số lượng thông báo chưa đọc
    public function getUnreadCount() {
        if (!SessionHelper::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        $user = SessionHelper::getCurrentUser();
        $notification = new Notification();
        
        $count = $notification->getUnreadCount($user->user_id);
        
        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
    }
    
    // Lấy cài đặt thông báo
    public function getSettings() {
        if (!SessionHelper::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        $user = SessionHelper::getCurrentUser();
        $notification = new Notification();
        
        $settings = $notification->getUserSettings($user->user_id);
        
        header('Content-Type: application/json');
        echo json_encode($settings);
    }
    
    // Cập nhật cài đặt thông báo
    public function updateSettings() {
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
        $notification = new Notification();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $result = $notification->updateSettings($user->user_id, $input);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
    }
    
    // Lưu push subscription
    public function savePushSubscription() {
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
        $notification = new Notification();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $required = ['endpoint', 'keys'];
        foreach ($required as $field) {
            if (!isset($input[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Field $field is required"]);
                return;
            }
        }
        
        $endpoint = $input['endpoint'];
        $p256dh_key = $input['keys']['p256dh'] ?? '';
        $auth_key = $input['keys']['auth'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $result = $notification->savePushSubscription($user->user_id, $endpoint, $p256dh_key, $auth_key, $user_agent);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
    }
    
    // Xóa push subscription
    public function removePushSubscription() {
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
        $notification = new Notification();
        
        $input = json_decode(file_get_contents('php://input'), true);
        $endpoint = $input['endpoint'] ?? '';
        
        if (!$endpoint) {
            http_response_code(400);
            echo json_encode(['error' => 'Endpoint is required']);
            return;
        }
        
        $result = $notification->removePushSubscription($user->user_id, $endpoint);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
    }
    
    // Tạo thông báo mới (cho admin)
    public function create() {
        if (!SessionHelper::isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $notification = new Notification();
        $input = json_decode(file_get_contents('php://input'), true);
        
        $required = ['title', 'message'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Field $field is required"]);
                return;
            }
        }
        
        $user_id = $input['user_id'] ?? null; // null = gửi cho tất cả
        $title = $input['title'];
        $message = $input['message'];
        $type = $input['type'] ?? 'info';
        $data = isset($input['data']) ? json_encode($input['data']) : null;
        
        $notification_id = $notification->create($user_id, $title, $message, $type, $data);
        
        if ($notification_id) {
            // TODO: Gửi thông báo qua WebSocket và Push Notification
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'notification_id' => $notification_id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create notification']);
        }
    }
    
    // Hiển thị trang thông báo
    public function index() {
        if (!SessionHelper::isLoggedIn()) {
            header('Location: /login');
            exit();
        }
        
        $title = 'Thông báo';
        include __DIR__ . '/../views/notification/index.php';
    }
    
    // Hiển thị trang cài đặt thông báo
    public function settings() {
        if (!SessionHelper::isLoggedIn()) {
            header('Location: /login');
            exit();
        }
        
        $title = 'Cài đặt thông báo';
        include __DIR__ . '/../views/notification/settings.php';
    }
}
