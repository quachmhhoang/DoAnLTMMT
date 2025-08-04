<?php

namespace App\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

/**
 * WebSocket server for handling real-time push notifications
 */
class NotificationServer implements MessageComponentInterface
{
    protected $clients;
    protected $userConnections; // Maps user_id to connections
    protected $connectionUsers; // Maps connection to user_id
    protected $userRoles; // Maps user_id to role

    public function __construct()
    {
        $this->clients = new SplObjectStorage;
        $this->userConnections = [];
        $this->connectionUsers = new SplObjectStorage;
        $this->userRoles = [];

        echo "Notification Server started\n";
    }

    /**
     * Handle new WebSocket connection
     */
    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection
        $this->clients->attach($conn);
        
        echo "New connection! ({$conn->resourceId})\n";
        
        // Send welcome message
        $conn->send(json_encode([
            'type' => 'connection',
            'message' => 'Connected to notification server',
            'connection_id' => $conn->resourceId
        ]));
    }

    /**
     * Handle incoming messages from clients
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        
        if (!$data) {
            echo "Invalid JSON received from {$from->resourceId}\n";
            return;
        }

        switch ($data['type']) {
            case 'auth':
                $this->handleAuthentication($from, $data);
                break;
                
            case 'ping':
                $from->send(json_encode(['type' => 'pong']));
                break;
                
            default:
                echo "Unknown message type: {$data['type']}\n";
        }
    }

    /**
     * Handle client disconnection
     */
    public function onClose(ConnectionInterface $conn)
    {
        // Remove connection from clients
        $this->clients->detach($conn);

        // Remove user mapping if exists
        if (isset($this->connectionUsers[$conn])) {
            $userId = $this->connectionUsers[$conn];
            unset($this->userConnections[$userId]);
            unset($this->userRoles[$userId]);
            unset($this->connectionUsers[$conn]);
            echo "User {$userId} disconnected\n";
        } else {
            echo "Connection {$conn->resourceId} has disconnected\n";
        }
    }

    /**
     * Handle connection errors
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    /**
     * Handle user authentication
     */
    protected function handleAuthentication(ConnectionInterface $conn, $data)
    {
        if (!isset($data['user_id']) || !isset($data['token'])) {
            $conn->send(json_encode([
                'type' => 'auth_error',
                'message' => 'Missing user_id or token'
            ]));
            return;
        }

        $userId = $data['user_id'];
        $token = $data['token'];

        // Validate token and get user role
        $userInfo = $this->validateUserTokenAndGetRole($userId, $token);
        if ($userInfo) {
            // Store user connection mapping
            $this->userConnections[$userId] = $conn;
            $this->connectionUsers[$conn] = $userId;
            $this->userRoles[$userId] = $userInfo['role'];

            $conn->send(json_encode([
                'type' => 'auth_success',
                'message' => 'Authentication successful',
                'user_id' => $userId,
                'role' => $userInfo['role']
            ]));

            echo "User {$userId} ({$userInfo['role']}) authenticated on connection {$conn->resourceId}\n";
        } else {
            $conn->send(json_encode([
                'type' => 'auth_error',
                'message' => 'Invalid credentials'
            ]));
        }
    }

    /**
     * Validate user token and get user role
     */
    protected function validateUserTokenAndGetRole($userId, $token)
    {
        // For now, we'll use a simple validation
        // In production, you should validate against your session/JWT system
        if (empty($userId) || empty($token)) {
            return false;
        }

        // Here you would typically validate the token against your database
        // For this demo, we'll simulate getting user role from database
        try {
            // You should implement proper database connection and validation
            // This is a simplified version
            $pdo = new \PDO('mysql:host=localhost;dbname=web_store', 'root', '');
            $stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if ($user) {
                return ['role' => $user['role']];
            }
        } catch (\Exception $e) {
            echo "Database error: " . $e->getMessage() . "\n";
        }

        return false;
    }

    /**
     * Send notification to specific user
     */
    public function sendNotificationToUser($userId, $notification)
    {
        if (isset($this->userConnections[$userId])) {
            $conn = $this->userConnections[$userId];
            $conn->send(json_encode([
                'type' => 'notification',
                'data' => $notification
            ]));
            
            echo "Notification sent to user {$userId}\n";
            return true;
        }
        
        echo "User {$userId} not connected\n";
        return false;
    }

    /**
     * Send notification to all connected users
     */
    public function broadcastNotification($notification)
    {
        $message = json_encode([
            'type' => 'notification',
            'data' => $notification
        ]);

        foreach ($this->clients as $client) {
            $client->send($message);
        }
        
        echo "Notification broadcasted to " . count($this->clients) . " clients\n";
    }

    /**
     * Send notification to users with specific role
     */
    public function sendNotificationToRole($role, $notification)
    {
        $message = json_encode([
            'type' => 'notification',
            'data' => $notification
        ]);

        $sentCount = 0;
        foreach ($this->userRoles as $userId => $userRole) {
            if ($userRole === $role && isset($this->userConnections[$userId])) {
                $conn = $this->userConnections[$userId];
                $conn->send($message);
                $sentCount++;
            }
        }

        echo "Notification sent to {$sentCount} users with role '{$role}'\n";
        return $sentCount;
    }

    /**
     * Get connected users count
     */
    public function getConnectedUsersCount()
    {
        return count($this->userConnections);
    }

    /**
     * Get all connected user IDs
     */
    public function getConnectedUserIds()
    {
        return array_keys($this->userConnections);
    }
}
