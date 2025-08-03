<?php
/**
 * Debug notifications - direct database test
 */

require_once __DIR__ . '/app/models/Notification.php';
require_once __DIR__ . '/app/helpers/SessionHelper.php';

header('Content-Type: text/plain; charset=utf-8');

echo "🔍 NOTIFICATION SYSTEM DEBUG\n";
echo "============================\n\n";

// Test 1: Database connection
echo "1. Testing database connection...\n";
try {
    $notification = new Notification();
    echo "✅ Database connection successful\n\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Check if notifications table exists and has data
echo "2. Checking notifications table...\n";
try {
    $conn = $notification->conn;
    
    // Check table structure
    $stmt = $conn->prepare("DESCRIBE notifications");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Table structure: " . implode(', ', $columns) . "\n";
    
    // Count total notifications
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications");
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_OBJ)->count;
    echo "✅ Total notifications in database: $total\n\n";
    
} catch (Exception $e) {
    echo "❌ Table check failed: " . $e->getMessage() . "\n\n";
}

// Test 3: Session and user
echo "3. Testing session and user...\n";
try {
    SessionHelper::start();
    
    if (SessionHelper::isLoggedIn()) {
        $user = SessionHelper::getCurrentUser();
        echo "✅ User logged in: {$user->full_name} (ID: {$user->user_id})\n";
        
        // Test user notifications
        $userNotifications = $notification->getUserNotifications($user->user_id, 10, 0);
        echo "✅ User notifications found: " . count($userNotifications) . "\n";
        
        $unreadCount = $notification->getUnreadCount($user->user_id);
        echo "✅ Unread notifications: $unreadCount\n\n";
        
        // Show sample notifications
        if (!empty($userNotifications)) {
            echo "📋 Sample notifications:\n";
            foreach (array_slice($userNotifications, 0, 3) as $notif) {
                $status = $notif->is_read ? '✓' : '●';
                echo "   $status [{$notif->type}] {$notif->title} - {$notif->created_at}\n";
            }
            echo "\n";
        }
        
    } else {
        echo "❌ User not logged in\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Session test failed: " . $e->getMessage() . "\n\n";
}

// Test 4: Direct API simulation
echo "4. Testing API controller...\n";
try {
    if (SessionHelper::isLoggedIn()) {
        $user = SessionHelper::getCurrentUser();
        
        // Simulate API call
        $_GET['page'] = 1;
        $_GET['limit'] = 5;
        
        $controller = new NotificationController();
        
        echo "✅ Controller created\n";
        
        // Capture output
        ob_start();
        $controller->getNotifications();
        $apiOutput = ob_get_clean();
        
        echo "✅ API method called\n";
        echo "📤 API Response length: " . strlen($apiOutput) . " characters\n";
        
        // Try to parse JSON
        $data = json_decode($apiOutput, true);
        if ($data) {
            echo "✅ JSON parsed successfully\n";
            echo "   - Notifications: " . count($data['notifications']) . "\n";
            echo "   - Unread count: " . $data['unreadCount'] . "\n";
            if (isset($data['debug'])) {
                echo "   - Debug info: " . json_encode($data['debug']) . "\n";
            }
        } else {
            echo "❌ JSON parse failed\n";
            echo "Raw output (first 500 chars):\n";
            echo substr($apiOutput, 0, 500) . "\n";
        }
        
    } else {
        echo "❌ Cannot test API - user not logged in\n";
    }
    
} catch (Exception $e) {
    echo "❌ API test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n🎯 SUMMARY\n";
echo "==========\n";
echo "If all tests above show ✅, the notification system should work.\n";
echo "If you see ❌, those are the issues to fix.\n\n";

echo "💡 NEXT STEPS:\n";
echo "1. Visit /notifications in your browser\n";
echo "2. Open browser console (F12) to see JavaScript errors\n";
echo "3. Check network tab for API call responses\n";
echo "4. Add ?debug=1 to URL to see debug info\n";
?>
