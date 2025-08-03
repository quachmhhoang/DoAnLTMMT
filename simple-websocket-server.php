<?php
/**
 * Simple WebSocket Server for Notifications
 * This is a fallback server that doesn't require Ratchet
 */

class SimpleWebSocketServer {
    private $socket;
    private $clients = [];
    private $userConnections = [];
    
    public function __construct($host = '0.0.0.0', $port = 8080) {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        
        if (!socket_bind($this->socket, $host, $port)) {
            throw new Exception("Could not bind to $host:$port");
        }
        
        if (!socket_listen($this->socket, 5)) {
            throw new Exception("Could not listen on socket");
        }
        
        echo "Simple WebSocket server started on $host:$port\n";
        echo "Connect to: ws://$host:$port\n";
        echo "Press Ctrl+C to stop\n";
        echo "----------------------------------------\n";
    }
    
    public function run() {
        while (true) {
            $read = array_merge([$this->socket], $this->clients);
            $write = null;
            $except = null;
            
            if (socket_select($read, $write, $except, 0, 10000) < 1) {
                continue;
            }
            
            if (in_array($this->socket, $read)) {
                $newClient = socket_accept($this->socket);
                if ($newClient !== false) {
                    $this->handleNewConnection($newClient);
                }
                
                $key = array_search($this->socket, $read);
                unset($read[$key]);
            }
            
            foreach ($read as $client) {
                $data = @socket_read($client, 1024);
                if ($data === false || $data === '') {
                    $this->disconnectClient($client);
                } else {
                    $this->handleMessage($client, $data);
                }
            }
        }
    }
    
    private function handleNewConnection($client) {
        $request = socket_read($client, 1024);
        $this->performHandshake($client, $request);
        $this->clients[] = $client;
        
        echo "New client connected\n";
        
        // Send welcome message
        $this->sendMessage($client, json_encode([
            'type' => 'connection',
            'message' => 'Connected to notification server'
        ]));
    }
    
    private function performHandshake($client, $request) {
        $lines = explode("\n", $request);
        $key = '';
        
        foreach ($lines as $line) {
            if (strpos($line, 'Sec-WebSocket-Key:') !== false) {
                $key = trim(substr($line, 18));
                break;
            }
        }
        
        $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        
        $response = "HTTP/1.1 101 Switching Protocols\r\n";
        $response .= "Upgrade: websocket\r\n";
        $response .= "Connection: Upgrade\r\n";
        $response .= "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";
        
        socket_write($client, $response);
    }
    
    private function handleMessage($client, $data) {
        $message = $this->decodeMessage($data);
        if (!$message) return;
        
        try {
            $data = json_decode($message, true);
            if (!$data) return;
            
            switch ($data['type']) {
                case 'auth':
                    $this->handleAuth($client, $data);
                    break;
                case 'ping':
                    $this->sendMessage($client, json_encode(['type' => 'pong']));
                    break;
                default:
                    echo "Unknown message type: {$data['type']}\n";
            }
        } catch (Exception $e) {
            echo "Error handling message: " . $e->getMessage() . "\n";
        }
    }
    
    private function handleAuth($client, $data) {
        if (!isset($data['userId'])) {
            $this->sendMessage($client, json_encode([
                'type' => 'error',
                'message' => 'User ID required for authentication'
            ]));
            return;
        }
        
        $userId = $data['userId'];
        
        if (!isset($this->userConnections[$userId])) {
            $this->userConnections[$userId] = [];
        }
        $this->userConnections[$userId][] = $client;
        
        $this->sendMessage($client, json_encode([
            'type' => 'auth_success',
            'message' => 'Authentication successful',
            'userId' => $userId
        ]));
        
        echo "User $userId authenticated\n";
    }
    
    private function sendMessage($client, $message) {
        $encodedMessage = $this->encodeMessage($message);
        @socket_write($client, $encodedMessage);
    }
    
    private function encodeMessage($message) {
        $length = strlen($message);
        $header = chr(129); // Text frame
        
        if ($length <= 125) {
            $header .= chr($length);
        } elseif ($length <= 65535) {
            $header .= chr(126) . pack('n', $length);
        } else {
            $header .= chr(127) . pack('N', 0) . pack('N', $length);
        }
        
        return $header . $message;
    }
    
    private function decodeMessage($data) {
        if (strlen($data) < 2) return false;
        
        $firstByte = ord($data[0]);
        $secondByte = ord($data[1]);
        
        $opcode = $firstByte & 15;
        $masked = ($secondByte >> 7) & 1;
        $payloadLength = $secondByte & 127;
        
        if ($opcode !== 1) return false; // Only handle text frames
        
        $offset = 2;
        
        if ($payloadLength === 126) {
            $payloadLength = unpack('n', substr($data, $offset, 2))[1];
            $offset += 2;
        } elseif ($payloadLength === 127) {
            $payloadLength = unpack('N', substr($data, $offset + 4, 4))[1];
            $offset += 8;
        }
        
        if ($masked) {
            $mask = substr($data, $offset, 4);
            $offset += 4;
            $payload = substr($data, $offset, $payloadLength);
            
            for ($i = 0; $i < $payloadLength; $i++) {
                $payload[$i] = $payload[$i] ^ $mask[$i % 4];
            }
        } else {
            $payload = substr($data, $offset, $payloadLength);
        }
        
        return $payload;
    }
    
    private function disconnectClient($client) {
        $key = array_search($client, $this->clients);
        if ($key !== false) {
            unset($this->clients[$key]);
        }
        
        foreach ($this->userConnections as $userId => $connections) {
            $key = array_search($client, $connections);
            if ($key !== false) {
                unset($this->userConnections[$userId][$key]);
                if (empty($this->userConnections[$userId])) {
                    unset($this->userConnections[$userId]);
                }
                break;
            }
        }
        
        @socket_close($client);
        echo "Client disconnected\n";
    }
    
    public function sendToUser($userId, $notification) {
        if (!isset($this->userConnections[$userId])) {
            return false;
        }
        
        $message = json_encode([
            'type' => 'notification',
            'data' => $notification
        ]);
        
        foreach ($this->userConnections[$userId] as $client) {
            $this->sendMessage($client, $message);
        }
        
        return true;
    }
    
    public function sendToAll($notification) {
        $message = json_encode([
            'type' => 'notification',
            'data' => $notification
        ]);
        
        foreach ($this->clients as $client) {
            $this->sendMessage($client, $message);
        }
    }
}

// Try different ports
$ports = [8080, 8081, 8082, 8083, 8084];
$server = null;

foreach ($ports as $port) {
    try {
        echo "Trying to start server on port $port...\n";
        $server = new SimpleWebSocketServer('0.0.0.0', $port);
        break;
    } catch (Exception $e) {
        echo "Port $port failed: " . $e->getMessage() . "\n";
        continue;
    }
}

if (!$server) {
    echo "❌ Failed to start server on any port\n";
    exit(1);
}

try {
    $server->run();
} catch (Exception $e) {
    echo "Server error: " . $e->getMessage() . "\n";
}
?>
