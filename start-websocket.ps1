# PowerShell script to start WebSocket server
Write-Host "Starting WebSocket Server for CellPhone Store..." -ForegroundColor Green
Write-Host "=================================================" -ForegroundColor Green
Write-Host ""

# Check if running as administrator
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")

if (-not $isAdmin) {
    Write-Host "⚠️  Warning: Not running as Administrator" -ForegroundColor Yellow
    Write-Host "   Some ports might be blocked. Consider running as Administrator." -ForegroundColor Yellow
    Write-Host ""
}

# Check if PHP is available
try {
    $phpVersion = php -v 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ PHP is available" -ForegroundColor Green
    } else {
        Write-Host "❌ PHP not found in PATH" -ForegroundColor Red
        Write-Host "   Please make sure PHP is installed and added to PATH" -ForegroundColor Red
        Read-Host "Press Enter to exit"
        exit 1
    }
} catch {
    Write-Host "❌ PHP not found" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""
Write-Host "Checking available ports..." -ForegroundColor Cyan

# Function to test if port is available
function Test-Port {
    param([int]$Port)
    
    try {
        $listener = [System.Net.Sockets.TcpListener]::new([System.Net.IPAddress]::Any, $Port)
        $listener.Start()
        $listener.Stop()
        return $true
    } catch {
        return $false
    }
}

# Check ports
$ports = @(8080, 8081, 8082, 8083, 8084)
$availablePorts = @()

foreach ($port in $ports) {
    if (Test-Port -Port $port) {
        Write-Host "✅ Port $port is available" -ForegroundColor Green
        $availablePorts += $port
    } else {
        Write-Host "❌ Port $port is in use" -ForegroundColor Red
    }
}

if ($availablePorts.Count -eq 0) {
    Write-Host ""
    Write-Host "❌ No available ports found!" -ForegroundColor Red
    Write-Host "   Try running as Administrator or check firewall settings" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""
Write-Host "Starting notification server..." -ForegroundColor Cyan
Write-Host ""

try {
    Write-Host "🚀 Launching CellPhone Store Notification Server..." -ForegroundColor Green
    Start-Process -FilePath "php" -ArgumentList "notification-server.php" -NoNewWindow -Wait
} catch {
    Write-Host "❌ Error starting notification server: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
    Write-Host "💡 Troubleshooting tips:" -ForegroundColor Yellow
    Write-Host "1. Make sure PHP is installed and in PATH" -ForegroundColor White
    Write-Host "2. Try running as Administrator" -ForegroundColor White
    Write-Host "3. Check if ports 8080-8084 are available" -ForegroundColor White
    Write-Host "4. Run: php check-ports.php" -ForegroundColor White
}

Write-Host ""
Write-Host "Server stopped." -ForegroundColor Yellow
Read-Host "Press Enter to exit"
