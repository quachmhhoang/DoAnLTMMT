<?php
// Check if sockets extension is loaded
if (!extension_loaded('sockets')) {
    echo "❌ PHP Sockets extension is not loaded!\n";
    echo "==========================================\n\n";
    echo "The Ratchet WebSocket server requires the PHP sockets extension.\n";
    echo "Starting fallback notification system instead...\n\n";

    // Start the SSE-based notification system
    echo "✅ Starting Server-Sent Events (SSE) notification system...\n";
    echo "This provides real-time notifications without requiring sockets extension.\n\n";

    // Check if we can start a simple HTTP server for SSE
    $ports = [8080, 8081, 8082, 8083, 8084];
    $selectedPort = null;

    foreach ($ports as $port) {
        $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
        if (!$connection) {
            $selectedPort = $port;
            break;
        } else {
            fclose($connection);
        }
    }

    if ($selectedPort) {
        echo "✅ Starting HTTP server on port $selectedPort for SSE notifications...\n";
        echo "Connect to: http://localhost:$selectedPort\n";
        echo "SSE endpoint: http://localhost:$selectedPort/sse-notifications.php\n";
        echo "Press Ctrl+C to stop the server\n";
        echo "----------------------------------------\n";

        // Start PHP built-in server
        $command = "php -S 0.0.0.0:$selectedPort";
        passthru($command);
    } else {
        echo "❌ No available ports found for HTTP server\n";
        echo "\nAlternative: Your website can still use SSE notifications\n";
        echo "through your existing web server (Laragon/Apache).\n";
        echo "The SSE endpoint at /sse-notifications.php will work automatically.\n";
    }

    exit(0);
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/websocket/NotificationServer.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\WebSocket\NotificationServer;

echo "✅ PHP Sockets extension is loaded\n";
echo "Starting Ratchet WebSocket server...\n\n";

// Try different ports if 8080 is in use
$ports = [8080, 8081, 8082, 8083, 8084];
$server = null;
$selectedPort = null;

foreach ($ports as $port) {
    try {
        echo "Trying to start WebSocket server on port $port...\n";

        // Check if port is available first
        $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket) {
            $bind = @socket_bind($socket, '0.0.0.0', $port);
            @socket_close($socket);

            if (!$bind) {
                echo "✗ Port $port is already in use\n";
                continue;
            }
        }

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new NotificationServer()
                )
            ),
            $port,
            '0.0.0.0'  // Listen on all interfaces
        );

        $selectedPort = $port;
        echo "✓ WebSocket server successfully started on port $port\n";
        echo "Connect to: ws://localhost:$port\n";
        echo "Press Ctrl+C to stop the server\n";
        echo "----------------------------------------\n";
        break;

    } catch (Exception $e) {
        echo "✗ Port $port failed: " . $e->getMessage() . "\n";
        continue;
    } catch (Error $e) {
        echo "✗ Port $port error: " . $e->getMessage() . "\n";
        continue;
    }
}

if (!$server) {
    echo "❌ Failed to start WebSocket server on any available port.\n";
    echo "Please check if ports 8080-8084 are available or run as administrator.\n";
    exit(1);
}

$server->run();
