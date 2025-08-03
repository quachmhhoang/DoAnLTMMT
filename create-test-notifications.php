<?php
/**
 * Script to create test notifications for debugging
 */

require_once __DIR__ . '/app/models/Notification.php';
require_once __DIR__ . '/app/config/database.php';

echo "Creating test notifications...\n";
echo "==============================\n\n";

// Get a test user ID from database
try {
    $database = new Database();
    $conn = $database->getConnection();

    $stmt = $conn->prepare("SELECT user_id, full_name FROM users LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$user) {
        echo "❌ No users found in database. Please create a user account first.\n";
        exit(1);
    }

    echo "✅ Using test user: {$user->full_name} (ID: {$user->user_id})\n\n";

} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

$notification = new Notification();

// Create test notifications
$testNotifications = [
    [
        'title' => 'Chào mừng bạn đến với hệ thống!',
        'message' => 'Đây là thông báo chào mừng. Hệ thống thông báo đang hoạt động bình thường.',
        'type' => 'info'
    ],
    [
        'title' => 'Đơn hàng mới #12345',
        'message' => 'Bạn có một đơn hàng mới cần xử lý. Khách hàng: Nguyễn Văn A, Tổng tiền: 2,500,000 VNĐ',
        'type' => 'order',
        'data' => json_encode(['order_id' => 12345, 'customer' => 'Nguyễn Văn A', 'total' => 2500000])
    ],
    [
        'title' => 'Cập nhật hệ thống',
        'message' => 'Hệ thống sẽ được bảo trì vào lúc 2:00 AM ngày mai. Thời gian dự kiến: 30 phút.',
        'type' => 'system'
    ],
    [
        'title' => 'Khuyến mãi đặc biệt',
        'message' => 'Giảm giá 20% cho tất cả sản phẩm iPhone. Áp dụng từ hôm nay đến hết tuần.',
        'type' => 'info'
    ],
    [
        'title' => 'Thanh toán thành công',
        'message' => 'Đơn hàng #12344 đã được thanh toán thành công. Số tiền: 1,800,000 VNĐ',
        'type' => 'order',
        'data' => json_encode(['order_id' => 12344, 'amount' => 1800000, 'payment_method' => 'credit_card'])
    ]
];

echo "Creating notifications...\n";
echo "------------------------\n";

foreach ($testNotifications as $index => $notif) {
    $notificationId = $notification->create(
        $user->user_id,
        $notif['title'],
        $notif['message'],
        $notif['type'],
        $notif['data'] ?? null
    );
    
    if ($notificationId) {
        echo "✅ Created notification #{$notificationId}: {$notif['title']}\n";
    } else {
        echo "❌ Failed to create notification: {$notif['title']}\n";
    }
}

// Create a global notification (user_id = null)
$globalNotificationId = $notification->create(
    null, // Global notification
    'Thông báo toàn hệ thống',
    'Đây là thông báo gửi đến tất cả người dùng trong hệ thống.',
    'system'
);

if ($globalNotificationId) {
    echo "✅ Created global notification #{$globalNotificationId}\n";
} else {
    echo "❌ Failed to create global notification\n";
}

echo "\n📊 Checking notification counts...\n";
echo "-----------------------------------\n";

// Get notification counts
$userNotifications = $notification->getUserNotifications($user->user_id, 50, 0);
$unreadCount = $notification->getUnreadCount($user->user_id);

echo "✅ Total notifications for user: " . count($userNotifications) . "\n";
echo "✅ Unread notifications: $unreadCount\n";

echo "\n📋 Recent notifications:\n";
echo "------------------------\n";

foreach (array_slice($userNotifications, 0, 5) as $notif) {
    $status = $notif->is_read ? '✓' : '●';
    echo "$status [{$notif->type}] {$notif->title} - {$notif->created_at}\n";
}

echo "\n🎉 Test notifications created successfully!\n";
echo "Now you can:\n";
echo "1. Visit /notifications to see them in the web interface\n";
echo "2. Test the API endpoint: /api/notifications\n";
echo "3. Check the notification bell in the header\n";

// API endpoint can be tested through the web interface

echo "\n✅ All done! Your notification system should now be working.\n";
?>
