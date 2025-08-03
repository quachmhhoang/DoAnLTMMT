<?php
class RateLimitMiddleware {
    private static $requests = [];
    private static $maxRequests = 60; // Max requests per minute
    private static $timeWindow = 60; // Time window in seconds
    
    public static function check($identifier = null) {
        if (!$identifier) {
            $identifier = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
        
        $currentTime = time();
        $windowStart = $currentTime - self::$timeWindow;
        
        // Initialize if not exists
        if (!isset(self::$requests[$identifier])) {
            self::$requests[$identifier] = [];
        }
        
        // Remove old requests outside the time window
        self::$requests[$identifier] = array_filter(
            self::$requests[$identifier],
            function($timestamp) use ($windowStart) {
                return $timestamp > $windowStart;
            }
        );
        
        // Check if limit exceeded
        if (count(self::$requests[$identifier]) >= self::$maxRequests) {
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Rate limit exceeded',
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => self::$timeWindow
            ]);
            exit();
        }
        
        // Add current request
        self::$requests[$identifier][] = $currentTime;
        
        return true;
    }
    
    public static function checkNotificationAPI($userId) {
        // More restrictive rate limiting for notification APIs
        $identifier = "notification_api_user_$userId";
        $maxRequests = 30; // Max 30 requests per minute for notification APIs
        
        $currentTime = time();
        $windowStart = $currentTime - self::$timeWindow;
        
        if (!isset(self::$requests[$identifier])) {
            self::$requests[$identifier] = [];
        }
        
        self::$requests[$identifier] = array_filter(
            self::$requests[$identifier],
            function($timestamp) use ($windowStart) {
                return $timestamp > $windowStart;
            }
        );
        
        if (count(self::$requests[$identifier]) >= $maxRequests) {
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Notification API rate limit exceeded',
                'message' => 'Too many notification requests. Please try again later.',
                'retry_after' => self::$timeWindow
            ]);
            exit();
        }
        
        self::$requests[$identifier][] = $currentTime;
        return true;
    }
}
