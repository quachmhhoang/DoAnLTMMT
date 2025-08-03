# Hệ thống Thông báo Đẩy Thời gian Thực

## Tổng quan

Hệ thống thông báo đẩy thời gian thực đã được tích hợp vào website CellPhone Store với các tính năng sau:

### Tính năng chính

1. **Thông báo WebSocket thời gian thực**
   - Kết nối WebSocket để nhận thông báo ngay lập tức
   - Tự động kết nối lại khi mất kết nối
   - Xác thực người dùng qua WebSocket

2. **Push Notifications**
   - Service Worker để xử lý push notifications
   - Hỗ trợ thông báo ngay cả khi không mở website
   - Quản lý subscription endpoints

3. **Quản lý thông báo**
   - Lưu trữ thông báo trong database
   - Phân loại theo loại (đơn hàng, hệ thống, khuyến mãi)
   - Trạng thái đọc/chưa đọc
   - Lọc và tìm kiếm thông báo

4. **Cài đặt người dùng**
   - Bật/tắt các loại thông báo
   - Cài đặt push notifications
   - Tần suất nhận thông báo

5. **Tích hợp quy trình đơn hàng**
   - Thông báo đơn hàng mới cho admin
   - Xác nhận đơn hàng cho khách hàng
   - Cập nhật trạng thái đơn hàng

6. **Bảo mật và tối ưu hóa**
   - Rate limiting cho API
   - Validation và sanitization
   - CSRF protection
   - Logging các hoạt động bảo mật

## Cài đặt và Sử dụng

### 1. Cài đặt Dependencies

```bash
composer install
```

### 2. Chạy Database Migrations

```bash
php migrate-notifications.php
```

### 3. Khởi động WebSocket Server

**Windows:**
```bash
start-websocket.bat
```

**Linux/Mac:**
```bash
php websocket-server.php
```

### 4. Cấu hình VAPID Keys (cho Push Notifications)

Để sử dụng push notifications, bạn cần tạo VAPID keys:

1. Truy cập: https://vapidkeys.com/
2. Tạo cặp keys
3. Cập nhật public key trong `assets/js/notifications.js`
4. Cập nhật private key trong server configuration

## Cấu trúc Files

### Backend
- `app/models/Notification.php` - Model quản lý thông báo
- `app/controllers/NotificationController.php` - Controller xử lý API
- `app/services/NotificationService.php` - Service gửi thông báo
- `app/websocket/NotificationServer.php` - WebSocket server
- `app/middleware/RateLimitMiddleware.php` - Rate limiting
- `app/middleware/SecurityMiddleware.php` - Bảo mật

### Frontend
- `assets/js/notifications.js` - JavaScript quản lý thông báo
- `assets/js/sw.js` - Service Worker
- `app/views/notification/` - Giao diện thông báo
- `assets/css/style.css` - CSS cho notification system

### Database
- `notifications` - Bảng lưu thông báo
- `notification_settings` - Cài đặt người dùng
- `push_subscriptions` - Push subscription endpoints

## API Endpoints

### Thông báo
- `GET /api/notifications` - Lấy danh sách thông báo
- `GET /api/notifications/unread-count` - Số thông báo chưa đọc
- `POST /api/notifications/mark-read` - Đánh dấu đã đọc
- `POST /api/notifications/mark-all-read` - Đánh dấu tất cả đã đọc

### Cài đặt
- `GET /api/notifications/settings` - Lấy cài đặt
- `POST /api/notifications/settings` - Cập nhật cài đặt

### Push Notifications
- `POST /api/notifications/push-subscription` - Lưu subscription
- `POST /api/notifications/remove-subscription` - Xóa subscription

### Admin
- `POST /api/notifications/create` - Tạo thông báo mới

## Sử dụng trong Code

### Gửi thông báo đơn hàng mới

```php
$notificationService = new NotificationService();
$notificationService->sendNewOrderNotification($order_id, $user_name, $total_amount);
```

### Gửi thông báo cập nhật trạng thái

```php
$notificationService->sendOrderStatusNotification($user_id, $order_id, $status, $status_text);
```

### Gửi thông báo khuyến mãi

```php
$notificationService->sendPromotionNotification($title, $message, $user_id);
```

## WebSocket Events

### Client gửi đến Server
- `auth` - Xác thực người dùng
- `ping` - Kiểm tra kết nối

### Server gửi đến Client
- `connection` - Xác nhận kết nối
- `auth_success` - Xác thực thành công
- `notification` - Thông báo mới
- `admin_notification` - Thông báo admin
- `pong` - Phản hồi ping

## Bảo mật

### Rate Limiting
- API thông báo: 30 requests/phút
- API chung: 60 requests/phút

### Validation
- Kiểm tra input cho tất cả API
- Sanitization dữ liệu
- CSRF protection

### Logging
- Log các hoạt động bảo mật
- Theo dõi hoạt động đáng ngờ

## Tối ưu hóa

### Performance
- Caching thông báo
- Pagination cho danh sách thông báo
- Lazy loading

### Database
- Index trên các cột quan trọng
- Cleanup thông báo cũ

## Troubleshooting

### WebSocket không kết nối được
1. Kiểm tra port 8080 có bị chặn không
2. Đảm bảo WebSocket server đang chạy
3. Kiểm tra firewall settings

### Push notifications không hoạt động
1. Kiểm tra VAPID keys
2. Đảm bảo HTTPS (required cho push notifications)
3. Kiểm tra browser permissions

### Thông báo không hiển thị
1. Kiểm tra JavaScript console
2. Đảm bảo user đã đăng nhập
3. Kiểm tra notification settings

## Browser Support

- Chrome 50+
- Firefox 44+
- Safari 12+
- Edge 17+

## Tính năng tương lai

1. **Email notifications**
2. **SMS notifications**
3. **Advanced analytics**
4. **Notification templates**
5. **Bulk notifications**
6. **Scheduled notifications**

## Liên hệ

Nếu có vấn đề hoặc cần hỗ trợ, vui lòng tạo issue trong repository.
