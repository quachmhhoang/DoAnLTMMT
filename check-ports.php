<?php
/**
 * Port Checker for WebSocket Server
 * This script checks which ports are available for the WebSocket server
 */

echo "Checking available ports for WebSocket server...\n";
echo "================================================\n\n";

$ports = [8080, 8081, 8082, 8083, 8084, 3000, 3001, 9000, 9001];

foreach ($ports as $port) {
    echo "Checking port $port... ";

    // Use fsockopen to test port availability
    $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);

    if ($connection) {
        echo "❌ In use\n";
        fclose($connection);
    } else {
        // Port is likely available (or blocked by firewall)
        echo "✅ Available (or blocked by firewall)\n";
    }
}

echo "\n";
echo "Checking what's using common ports...\n";
echo "=====================================\n";

// Check what's using port 8080 specifically
echo "Checking what's using port 8080:\n";
$output = [];
$return_var = 0;

// Try netstat command
exec('netstat -ano | findstr :8080', $output, $return_var);

if (!empty($output)) {
    foreach ($output as $line) {
        echo "  $line\n";
    }
} else {
    echo "  No processes found using port 8080\n";
}

echo "\n";
echo "Common solutions:\n";
echo "=================\n";
echo "1. Try running as Administrator\n";
echo "2. Check if another web server is running (Apache, IIS, etc.)\n";
echo "3. Check if Windows Defender or antivirus is blocking\n";
echo "4. Try disabling Windows Firewall temporarily\n";
echo "5. Use a different port (the script will try multiple ports)\n";

echo "\n";
echo "Testing socket creation capability...\n";
echo "====================================\n";

// Check if sockets extension is available
if (extension_loaded('sockets')) {
    echo "✅ Sockets extension is loaded\n";
    try {
        $testSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($testSocket) {
            echo "✅ Socket creation works\n";
            socket_close($testSocket);
        } else {
            echo "❌ Socket creation failed: " . socket_strerror(socket_last_error()) . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Socket creation error: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Sockets extension is NOT loaded\n";
    echo "   This means the Ratchet WebSocket server won't work\n";
    echo "   Use the simple-websocket-server.php instead\n";
}

echo "\n";
echo "PHP Extensions check...\n";
echo "=======================\n";

$required_extensions = ['sockets', 'json'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext extension loaded\n";
    } else {
        echo "❌ $ext extension NOT loaded\n";
    }
}

echo "\n";
echo "Recommended next steps:\n";
echo "======================\n";
echo "1. Run: start-websocket.bat (it will try multiple ports)\n";
echo "2. If that fails, try: php simple-websocket-server.php\n";
echo "3. Check Windows Firewall settings\n";
echo "4. Run PowerShell as Administrator and try again\n";

?>
