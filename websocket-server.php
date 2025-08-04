<?php

require_once 'vendor/autoload.php';
require_once 'app/websocket/NotificationServer.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\App;
use App\WebSocket\NotificationServer;

// Configuration
$websocketPort = 8080;
$httpPort = 8081; // For HTTP notifications from the application
$host = '0.0.0.0';

echo "Starting WebSocket Notification Server...\n";
echo "WebSocket Port: {$websocketPort}\n";
echo "HTTP Notification Port: {$httpPort}\n";
echo "Host: {$host}\n\n";

try {
    // Create the notification server instance
    $notificationServer = new NotificationServer();
    
    // Create Ratchet application
    $app = new App($host, $websocketPort, $host);
    
    // Add WebSocket route
    $app->route('/notifications', $notificationServer, ['*']);
    
    // Create HTTP server for receiving notification requests from the application
    $httpServer = new HttpNotificationHandler($notificationServer);
    $httpLoop = \React\EventLoop\Factory::create();
    $httpSocket = new \React\Socket\Server("{$host}:{$httpPort}", $httpLoop);
    
    // Handle HTTP requests for sending notifications
    $httpSocket->on('connection', function ($connection) use ($httpServer) {
        $connection->on('data', function ($data) use ($connection, $httpServer) {
            $httpServer->handleRequest($data, $connection);
        });
    });
    
    // Start HTTP server in background
    $httpLoop->addTimer(0.1, function() use ($httpLoop) {
        $httpLoop->run();
    });
    
    echo "Notification server started successfully!\n";
    echo "WebSocket endpoint: ws://{$host}:{$websocketPort}/notifications\n";
    echo "HTTP notification endpoint: http://{$host}:{$httpPort}/notify\n\n";
    
    // Start the WebSocket server
    $app->run();
    
} catch (Exception $e) {
    echo "Error starting server: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * HTTP handler for receiving notification requests from the application
 */
class HttpNotificationHandler
{
    private $notificationServer;
    
    public function __construct($notificationServer)
    {
        $this->notificationServer = $notificationServer;
    }
    
    public function handleRequest($data, $connection)
    {
        try {
            // Parse HTTP request
            $lines = explode("\r\n", $data);
            $requestLine = $lines[0];
            
            if (strpos($requestLine, 'POST /notify') === 0) {
                // Find the JSON body
                $bodyStart = strpos($data, "\r\n\r\n");
                if ($bodyStart !== false) {
                    $body = substr($data, $bodyStart + 4);
                    $requestData = json_decode($body, true);
                    
                    if ($requestData && isset($requestData['action']) && $requestData['action'] === 'send_notification') {
                        $this->processNotification($requestData);
                        
                        // Send HTTP response
                        $response = "HTTP/1.1 200 OK\r\n";
                        $response .= "Content-Type: application/json\r\n";
                        $response .= "Access-Control-Allow-Origin: *\r\n";
                        $response .= "Content-Length: 25\r\n";
                        $response .= "\r\n";
                        $response .= '{"status": "success"}';
                        
                        $connection->write($response);
                    }
                }
            }
            
            $connection->end();
            
        } catch (Exception $e) {
            echo "Error handling HTTP request: " . $e->getMessage() . "\n";
            
            $response = "HTTP/1.1 500 Internal Server Error\r\n";
            $response .= "Content-Type: application/json\r\n";
            $response .= "Content-Length: 23\r\n";
            $response .= "\r\n";
            $response .= '{"status": "error"}';
            
            $connection->write($response);
            $connection->end();
        }
    }
    
    private function processNotification($requestData)
    {
        $targetType = $requestData['target_type'];
        $targetValue = $requestData['target_value'];
        $notification = $requestData['notification'];
        
        switch ($targetType) {
            case 'user':
                $this->notificationServer->sendNotificationToUser($targetValue, $notification);
                break;
                
            case 'all':
                $this->notificationServer->broadcastNotification($notification);
                break;
                
            case 'role':
                $this->notificationServer->sendNotificationToRole($targetValue, $notification);
                break;
                
            default:
                echo "Unknown target type: {$targetType}\n";
        }
    }
}

// Handle graceful shutdown
function handleShutdown() {
    echo "\nShutting down WebSocket server...\n";
    exit(0);
}

// Register shutdown handlers
register_shutdown_function('handleShutdown');
pcntl_signal(SIGTERM, 'handleShutdown');
pcntl_signal(SIGINT, 'handleShutdown');
