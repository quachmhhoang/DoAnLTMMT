<?php
/**
 * Server-Sent Events (SSE) Notification Endpoint
 * This provides real-time notifications without requiring WebSocket
 */

require_once __DIR__ . '/app/models/Notification.php';
require_once __DIR__ . '/app/helpers/SessionHelper.php';

// Set headers for SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Cache-Control');

// Prevent timeout
set_time_limit(0);
ini_set('max_execution_time', 0);

// Check if user is logged in
SessionHelper::start();
if (!SessionHelper::isLoggedIn()) {
    echo "event: error\n";
    echo "data: {\"error\": \"Not authenticated\"}\n\n";
    exit();
}

$user = SessionHelper::getCurrentUser();
$notification = new Notification();
$lastCheck = time();
$heartbeatInterval = 30; // Send heartbeat every 30 seconds
$lastHeartbeat = time();

// Send initial connection message
echo "event: connected\n";
echo "data: {\"message\": \"Connected to notification stream\", \"userId\": {$user->user_id}}\n\n";
flush();

while (true) {
    // Check for new notifications
    try {
        // Get unread notifications count
        $unreadCount = $notification->getUnreadCount($user->user_id);
        
        // Get recent notifications (last 5 minutes)
        $recentNotifications = $notification->getUserNotifications($user->user_id, 10, 0);
        
        // Filter notifications created in the last check period
        $newNotifications = array_filter($recentNotifications, function($notif) use ($lastCheck) {
            return strtotime($notif->created_at) > $lastCheck;
        });
        
        // Send new notifications
        if (!empty($newNotifications)) {
            foreach ($newNotifications as $notif) {
                $data = [
                    'id' => $notif->notification_id,
                    'title' => $notif->title,
                    'message' => $notif->message,
                    'type' => $notif->type,
                    'created_at' => $notif->created_at,
                    'is_read' => $notif->is_read,
                    'data' => $notif->data ? json_decode($notif->data, true) : null
                ];
                
                echo "event: notification\n";
                echo "data: " . json_encode($data) . "\n\n";
                flush();
            }
        }
        
        // Send unread count update
        echo "event: unread_count\n";
        echo "data: {\"count\": $unreadCount}\n\n";
        flush();
        
        // Send heartbeat
        if (time() - $lastHeartbeat >= $heartbeatInterval) {
            echo "event: heartbeat\n";
            echo "data: {\"timestamp\": " . time() . "}\n\n";
            flush();
            $lastHeartbeat = time();
        }
        
        $lastCheck = time();
        
    } catch (Exception $e) {
        echo "event: error\n";
        echo "data: {\"error\": \"" . addslashes($e->getMessage()) . "\"}\n\n";
        flush();
    }
    
    // Check if client disconnected
    if (connection_aborted()) {
        break;
    }
    
    // Sleep for 2 seconds before next check
    sleep(2);
}
?>
