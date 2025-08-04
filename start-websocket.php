<?php

require_once 'vendor/autoload.php';
require_once 'app/websocket/NotificationServer.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\WebSocket\NotificationServer;

// Configuration
$port = 8080;
$host = '0.0.0.0';

echo "Starting WebSocket Notification Server...\n";
echo "Port: {$port}\n";
echo "Host: {$host}\n\n";

try {
    // Create the notification server instance
    $notificationServer = new NotificationServer();
    
    // Wrap with WebSocket server
    $wsServer = new WsServer($notificationServer);
    
    // Wrap with HTTP server
    $httpServer = new HttpServer($wsServer);
    
    // Create IO server
    $server = IoServer::factory($httpServer, $port, $host);
    
    echo "WebSocket Notification Server started successfully!\n";
    echo "Connect to: ws://{$host}:{$port}\n\n";
    
    // Run the server
    $server->run();
    
} catch (Exception $e) {
    echo "Error starting server: " . $e->getMessage() . "\n";
    exit(1);
}
