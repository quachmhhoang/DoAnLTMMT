# WebSocket Server Troubleshooting Guide

## Quick Fix Steps

### 1. **Check Available Ports**
```bash
php check-ports.php
```
This will show you which ports are available and what might be blocking them.

### 2. **Try Different Startup Methods**

**Option A: Batch File (Windows)**
```bash
start-websocket.bat
```

**Option B: PowerShell (Recommended for Windows)**
```powershell
PowerShell -ExecutionPolicy Bypass -File start-websocket.ps1
```

**Option C: Direct PHP**
```bash
php websocket-server.php
```

**Option D: Simple Server (Fallback)**
```bash
php simple-websocket-server.php
```

### 3. **Run as Administrator**
Right-click Command Prompt or PowerShell and select "Run as Administrator", then try again.

## Common Issues and Solutions

### Issue 1: "Failed to listen on tcp://0.0.0.0:8080"

**Causes:**
- Port 8080 is already in use
- Insufficient permissions
- Windows Firewall blocking

**Solutions:**
1. **Check what's using port 8080:**
   ```cmd
   netstat -ano | findstr :8080
   ```

2. **Kill the process using the port:**
   ```cmd
   taskkill /PID [PID_NUMBER] /F
   ```

3. **Try a different port:**
   The scripts automatically try ports 8080-8084

4. **Run as Administrator**

### Issue 2: "Class 'React\Socket\Server' not found"

**Cause:** Missing Ratchet dependencies

**Solution:**
```bash
composer install
```

If that fails, use the simple server:
```bash
php simple-websocket-server.php
```

### Issue 3: Windows Firewall Blocking

**Solution:**
1. Open Windows Defender Firewall
2. Click "Allow an app or feature through Windows Defender Firewall"
3. Click "Change Settings" → "Allow another app"
4. Browse to your PHP executable
5. Check both "Private" and "Public" networks

### Issue 4: Antivirus Software Blocking

**Solution:**
1. Temporarily disable real-time protection
2. Add PHP and your project folder to antivirus exclusions
3. Try running the server again

### Issue 5: "Permission denied" or "Access denied"

**Solutions:**
1. **Run as Administrator**
2. **Check folder permissions:**
   - Right-click project folder → Properties → Security
   - Ensure your user has "Full control"

### Issue 6: PHP Not Found

**Solution:**
1. **Install PHP** if not installed
2. **Add PHP to PATH:**
   - Open System Properties → Environment Variables
   - Add PHP installation directory to PATH
   - Restart Command Prompt

## Alternative Solutions

### Option 1: Use Different Port Range
Edit the port arrays in the scripts to use different ports:
```php
$ports = [3000, 3001, 3002, 9000, 9001];
```

### Option 2: Use Localhost Only
Change `'0.0.0.0'` to `'127.0.0.1'` in the server files for localhost-only access.

### Option 3: Disable WebSocket (Fallback)
If WebSocket continues to fail, the notification system will still work with:
- Page refresh notifications
- Manual notification checking
- Browser notifications (if enabled)

## Testing WebSocket Connection

### 1. **Browser Console Test**
Open browser console and run:
```javascript
const ws = new WebSocket('ws://localhost:8080');
ws.onopen = () => console.log('Connected!');
ws.onerror = (e) => console.log('Error:', e);
```

### 2. **Online WebSocket Tester**
Use online tools like:
- websocket.org/echo.html
- www.websocket.org/echo.html

Connect to: `ws://localhost:8080`

## Server Status Indicators

### ✅ **Success Indicators:**
- "WebSocket server successfully started on port XXXX"
- "Connect to: ws://localhost:XXXX"
- Browser console shows "WebSocket connected"

### ❌ **Failure Indicators:**
- "Failed to listen on tcp://..."
- "Port XXXX is already in use"
- "Permission denied"
- Browser console shows connection errors

## Advanced Troubleshooting

### Check PHP Extensions
```bash
php -m | grep -E "(sockets|json)"
```

### Check PHP Configuration
```bash
php -i | grep -E "(socket|json)"
```

### Manual Port Test
```bash
telnet localhost 8080
```

### Check Network Interfaces
```bash
ipconfig /all
```

## Environment-Specific Solutions

### XAMPP/WAMP/LARAGON Users
- Stop Apache if it's using port 8080
- Use XAMPP Control Panel to stop conflicting services
- Try ports 8081-8084 instead

### Docker Users
- Check if Docker containers are using the ports
- Use `docker ps` to see running containers

### Corporate Networks
- Check if corporate firewall blocks WebSocket connections
- Try using different ports (3000, 9000, etc.)
- Contact IT department if needed

## Getting Help

If none of these solutions work:

1. **Run the diagnostic:**
   ```bash
   php check-ports.php
   ```

2. **Check the error logs:**
   - Look for PHP error logs
   - Check Windows Event Viewer

3. **Provide this information when asking for help:**
   - Operating System version
   - PHP version (`php -v`)
   - Error messages (exact text)
   - Output from `php check-ports.php`
   - Whether you're running as Administrator

## Fallback: Notification System Without WebSocket

If WebSocket cannot be made to work, the notification system will still function with:
- ✅ Database-stored notifications
- ✅ Page-load notification display
- ✅ Browser push notifications
- ✅ Manual refresh for new notifications
- ❌ Real-time updates (will require page refresh)

The core functionality remains intact even without WebSocket connectivity.
