<?php
class SecurityMiddleware {
    
    // Validate and sanitize notification input
    public static function validateNotificationInput($data) {
        $errors = [];
        
        // Validate title
        if (!isset($data['title']) || empty(trim($data['title']))) {
            $errors[] = 'Title is required';
        } elseif (strlen($data['title']) > 255) {
            $errors[] = 'Title must be less than 255 characters';
        }
        
        // Validate message
        if (!isset($data['message']) || empty(trim($data['message']))) {
            $errors[] = 'Message is required';
        } elseif (strlen($data['message']) > 1000) {
            $errors[] = 'Message must be less than 1000 characters';
        }
        
        // Validate type
        $allowedTypes = ['info', 'success', 'warning', 'error', 'order', 'system'];
        if (isset($data['type']) && !in_array($data['type'], $allowedTypes)) {
            $errors[] = 'Invalid notification type';
        }
        
        // Validate user_id if provided
        if (isset($data['user_id']) && $data['user_id'] !== null) {
            if (!is_numeric($data['user_id']) || $data['user_id'] <= 0) {
                $errors[] = 'Invalid user ID';
            }
        }
        
        return $errors;
    }
    
    // Sanitize notification data
    public static function sanitizeNotificationData($data) {
        $sanitized = [];
        
        if (isset($data['title'])) {
            $sanitized['title'] = htmlspecialchars(trim($data['title']), ENT_QUOTES, 'UTF-8');
        }
        
        if (isset($data['message'])) {
            $sanitized['message'] = htmlspecialchars(trim($data['message']), ENT_QUOTES, 'UTF-8');
        }
        
        if (isset($data['type'])) {
            $sanitized['type'] = trim($data['type']);
        }
        
        if (isset($data['user_id'])) {
            $sanitized['user_id'] = $data['user_id'] === null ? null : (int)$data['user_id'];
        }
        
        if (isset($data['data'])) {
            // Validate JSON data
            if (is_array($data['data'])) {
                $sanitized['data'] = json_encode($data['data']);
            } elseif (is_string($data['data'])) {
                $decoded = json_decode($data['data'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $sanitized['data'] = $data['data'];
                } else {
                    $sanitized['data'] = null;
                }
            }
        }
        
        return $sanitized;
    }
    
    // Check CSRF token for POST requests
    public static function checkCSRF() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            
            if (!$token || !self::validateCSRFToken($token)) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Invalid CSRF token']);
                exit();
            }
        }
        
        return true;
    }
    
    // Generate CSRF token
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    // Validate CSRF token
    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // Validate push subscription data
    public static function validatePushSubscription($data) {
        $errors = [];
        
        if (!isset($data['endpoint']) || empty($data['endpoint'])) {
            $errors[] = 'Endpoint is required';
        } elseif (!filter_var($data['endpoint'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid endpoint URL';
        }
        
        if (!isset($data['keys']) || !is_array($data['keys'])) {
            $errors[] = 'Keys are required';
        } else {
            if (!isset($data['keys']['p256dh']) || empty($data['keys']['p256dh'])) {
                $errors[] = 'p256dh key is required';
            }
            
            if (!isset($data['keys']['auth']) || empty($data['keys']['auth'])) {
                $errors[] = 'auth key is required';
            }
        }
        
        return $errors;
    }
    
    // Check if user can access notification
    public static function canAccessNotification($notification, $userId, $isAdmin = false) {
        // Admin can access all notifications
        if ($isAdmin) {
            return true;
        }
        
        // User can access their own notifications or global notifications
        return $notification->user_id === null || $notification->user_id == $userId;
    }
    
    // Validate notification settings
    public static function validateNotificationSettings($data) {
        $errors = [];
        $allowedSettings = [
            'push_enabled',
            'email_enabled',
            'order_notifications',
            'system_notifications',
            'marketing_notifications'
        ];
        
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedSettings)) {
                $errors[] = "Invalid setting: $key";
            } elseif (!is_bool($value) && $value !== '0' && $value !== '1' && $value !== 0 && $value !== 1) {
                $errors[] = "Setting $key must be a boolean value";
            }
        }
        
        return $errors;
    }
    
    // Log security events
    public static function logSecurityEvent($event, $details = []) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details
        ];
        
        // In a production environment, you would log this to a secure log file
        error_log("Security Event: " . json_encode($logData));
    }
    
    // Check for suspicious activity
    public static function checkSuspiciousActivity($userId) {
        // This is a simple implementation
        // In production, you would have more sophisticated detection
        
        $suspiciousPatterns = [
            'rapid_requests' => self::checkRapidRequests($userId),
            'unusual_endpoints' => self::checkUnusualEndpoints($userId),
            'invalid_tokens' => self::checkInvalidTokens($userId)
        ];
        
        foreach ($suspiciousPatterns as $pattern => $detected) {
            if ($detected) {
                self::logSecurityEvent('suspicious_activity', [
                    'user_id' => $userId,
                    'pattern' => $pattern
                ]);
                
                // You could implement additional actions here like temporary blocking
                return true;
            }
        }
        
        return false;
    }
    
    private static function checkRapidRequests($userId) {
        // Check if user is making too many requests in a short time
        // This would typically use a more sophisticated tracking system
        return false;
    }
    
    private static function checkUnusualEndpoints($userId) {
        // Check if user is accessing unusual endpoints
        return false;
    }
    
    private static function checkInvalidTokens($userId) {
        // Check if user is sending invalid tokens frequently
        return false;
    }
}
