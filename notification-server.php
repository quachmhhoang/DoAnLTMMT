<?php
/**
 * Simple Notification Server
 * Works without any special PHP extensions
 * Uses Server-Sent Events (SSE) for real-time notifications
 */

echo "🚀 Starting Simple Notification Server...\n";
echo "=========================================\n\n";

// Check PHP version
if (version_compare(PHP_VERSION, '7.0.0') < 0) {
    echo "❌ PHP 7.0+ required. Current version: " . PHP_VERSION . "\n";
    exit(1);
}

echo "✅ PHP Version: " . PHP_VERSION . "\n";

// Check required extensions
$required = ['json'];
$missing = [];

foreach ($required as $ext) {
    if (!extension_loaded($ext)) {
        $missing[] = $ext;
    } else {
        echo "✅ Extension loaded: $ext\n";
    }
}

if (!empty($missing)) {
    echo "❌ Missing required extensions: " . implode(', ', $missing) . "\n";
    exit(1);
}

// Find available port
$ports = [8080, 8081, 8082, 8083, 8084, 3000, 3001, 9000, 9001];
$selectedPort = null;

echo "\n🔍 Checking available ports...\n";

foreach ($ports as $port) {
    echo "Checking port $port... ";
    
    $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
    if (!$connection) {
        echo "✅ Available\n";
        $selectedPort = $port;
        break;
    } else {
        echo "❌ In use\n";
        fclose($connection);
    }
}

if (!$selectedPort) {
    echo "\n❌ No available ports found!\n";
    echo "Please free up one of these ports: " . implode(', ', $ports) . "\n";
    echo "\nAlternative: Use your existing web server\n";
    echo "The notification system will work through Laragon/Apache automatically.\n";
    echo "Just access your website normally - SSE notifications will work!\n";
    exit(1);
}

echo "\n🎯 Selected port: $selectedPort\n";
echo "\n📡 Starting HTTP server for notifications...\n";
echo "Server URL: http://localhost:$selectedPort\n";
echo "SSE Endpoint: http://localhost:$selectedPort/sse-notifications.php\n";
echo "Web Interface: http://localhost:$selectedPort\n";
echo "\n🔄 Server will provide:\n";
echo "  ✅ Real-time notifications via Server-Sent Events\n";
echo "  ✅ Web interface for your application\n";
echo "  ✅ API endpoints for notifications\n";
echo "  ✅ Static file serving\n";
echo "\n⚡ Features:\n";
echo "  🔔 Real-time order notifications\n";
echo "  📱 Browser push notifications\n";
echo "  💾 Persistent notification storage\n";
echo "  ⚙️ User notification preferences\n";
echo "  📊 Admin notification management\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "🚀 STARTING SERVER - Press Ctrl+C to stop\n";
echo str_repeat("=", 50) . "\n\n";

// Start the built-in PHP server
$documentRoot = __DIR__;
$command = "php -S 0.0.0.0:$selectedPort -t \"$documentRoot\"";

echo "📝 Command: $command\n\n";

// Execute the server
passthru($command);
?>
