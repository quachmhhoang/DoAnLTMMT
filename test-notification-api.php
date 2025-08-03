<?php
/**
 * Direct API test for notifications
 */

require_once __DIR__ . '/app/controllers/NotificationController.php';
require_once __DIR__ . '/app/helpers/SessionHelper.php';

// Set content type
header('Content-Type: application/json');

echo "Testing Notification API...\n";
echo "===========================\n\n";

try {
    // Start session
    SessionHelper::start();
    
    if (!SessionHelper::isLoggedIn()) {
        echo json_encode([
            'error' => 'Not logged in',
            'message' => 'Please log in first',
            'login_url' => '/login'
        ]);
        exit();
    }
    
    $user = SessionHelper::getCurrentUser();
    echo "User: {$user->full_name} (ID: {$user->user_id})\n\n";
    
    // Test the controller directly
    $controller = new NotificationController();
    
    // Set up GET parameters
    $_GET['page'] = 1;
    $_GET['limit'] = 10;
    
    echo "Calling getNotifications()...\n";
    
    // Capture the output
    ob_start();
    $controller->getNotifications();
    $output = ob_get_clean();
    
    echo "Raw output:\n";
    echo $output . "\n\n";
    
    // Try to parse as JSON
    $data = json_decode($output, true);
    
    if ($data) {
        echo "Parsed JSON successfully:\n";
        echo "- Notifications count: " . count($data['notifications']) . "\n";
        echo "- Unread count: " . $data['unreadCount'] . "\n";
        echo "- Page: " . $data['page'] . "\n";
        echo "- Limit: " . $data['limit'] . "\n\n";
        
        if (!empty($data['notifications'])) {
            echo "Sample notifications:\n";
            foreach (array_slice($data['notifications'], 0, 3) as $notif) {
                echo "- [{$notif['type']}] {$notif['title']}\n";
            }
        }
    } else {
        echo "Failed to parse JSON. Raw output above.\n";
    }
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Exception occurred',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
