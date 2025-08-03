<?php
namespace App\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

class NotificationServer implements MessageComponentInterface {
    protected $clients;
    protected $userConnections;

    public function __construct() {
        $this->clients = new SplObjectStorage;
        $this->userConnections = [];
        echo "WebSocket Server started\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection
        $this->clients->attach($conn);
        
        echo "New connection! ({$conn->resourceId})\n";
        
        // Send welcome message
        $conn->send(json_encode([
            'type' => 'connection',
            'message' => 'Connected to notification server',
            'connectionId' => $conn->resourceId
        ]));
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if (!$data) {
            return;
        }

        switch ($data['type']) {
            case 'auth':
                $this->handleAuth($from, $data);
                break;
            case 'ping':
                $from->send(json_encode(['type' => 'pong']));
                break;
            default:
                echo "Unknown message type: {$data['type']}\n";
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // Remove the connection
        $this->clients->detach($conn);
        
        // Remove from user connections
        foreach ($this->userConnections as $userId => $connections) {
            if (($key = array_search($conn, $connections)) !== false) {
                unset($this->userConnections[$userId][$key]);
                if (empty($this->userConnections[$userId])) {
                    unset($this->userConnections[$userId]);
                }
                break;
            }
        }
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    private function handleAuth(ConnectionInterface $conn, $data) {
        if (!isset($data['userId'])) {
            $conn->send(json_encode([
                'type' => 'error',
                'message' => 'User ID required for authentication'
            ]));
            return;
        }

        $userId = $data['userId'];
        
        // Store user connection
        if (!isset($this->userConnections[$userId])) {
            $this->userConnections[$userId] = [];
        }
        $this->userConnections[$userId][] = $conn;
        
        $conn->send(json_encode([
            'type' => 'auth_success',
            'message' => 'Authentication successful',
            'userId' => $userId
        ]));
        
        echo "User {$userId} authenticated on connection {$conn->resourceId}\n";
    }

    // Send notification to specific user
    public function sendToUser($userId, $notification) {
        if (!isset($this->userConnections[$userId])) {
            return false;
        }

        $message = json_encode([
            'type' => 'notification',
            'data' => $notification
        ]);

        foreach ($this->userConnections[$userId] as $conn) {
            $conn->send($message);
        }

        return true;
    }

    // Send notification to all users
    public function sendToAll($notification) {
        $message = json_encode([
            'type' => 'notification',
            'data' => $notification
        ]);

        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }

    // Send notification to admin users only
    public function sendToAdmins($notification) {
        $message = json_encode([
            'type' => 'admin_notification',
            'data' => $notification
        ]);

        // In a real implementation, you would check user roles
        // For now, we'll send to all connected users
        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }

    // Get connected users count
    public function getConnectedUsersCount() {
        return count($this->userConnections);
    }

    // Get total connections count
    public function getTotalConnectionsCount() {
        return count($this->clients);
    }
}
