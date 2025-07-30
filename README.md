# CellPhone Store - Website Bán Điện Thoại

Website bán hàng điện thoại được xây dựng bằng PHP với kiến trúc MVC, tích hợp xác thực người dùng và phân quyền admin/customer.

## Tính năng chính

### Cho khách hàng (Customer):
- Xem danh sách sản phẩm với bộ lọc theo danh mục
- Tìm kiếm sản phẩm
- Xem chi tiết sản phẩm
- Thêm sản phẩm vào giỏ hàng
- Quản lý giỏ hàng (cập nhật số lượng, xóa sản phẩm)
- Thanh toán và đặt hàng
- Xem lịch sử đơn hàng
- Đăng ký/Đăng nhập

### Cho quản trị viên (Admin):
- Dashboard với thống kê tổng quan
- Quản lý sản phẩm (thêm, sửa, xóa)
- Quản lý danh mục sản phẩm
- Quản lý thương hiệu
- Xem danh sách đơn hàng
- Quản lý người dùng

## Cấu trúc thư mục

```
DoAnLTMMT/
├── app/
│   ├── config/
│   │   └── database.php          # Cấu hình kết nối database
│   ├── controllers/
│   │   ├── AuthController.php    # Xử lý đăng nhập/đăng ký
│   │   ├── HomeController.php    # Trang chủ và sản phẩm
│   │   ├── OrderController.php   # Quản lý đơn hàng
│   │   └── AdminController.php   # Quản trị admin
│   ├── models/
│   │   ├── User.php             # Model người dùng
│   │   ├── Product.php          # Model sản phẩm
│   │   ├── Category.php         # Model danh mục
│   │   ├── Brand.php            # Model thương hiệu
│   │   ├── Cart.php             # Model giỏ hàng
│   │   └── Order.php            # Model đơn hàng
│   ├── views/
│   │   ├── layout/              # Layout chung
│   │   ├── auth/                # Trang đăng nhập/đăng ký
│   │   ├── home/                # Trang chủ, sản phẩm
│   │   ├── order/               # Đơn hàng, thanh toán
│   │   └── admin/               # Trang quản trị
│   ├── helpers/
│   │   └── SessionHelper.php    # Quản lý session và xác thực
│   └── core/
│       └── Router.php           # Hệ thống routing
├── database/
│   └── init.sql                 # Script khởi tạo database
├── codeSQL.txt                  # Schema database
├── .htaccess                    # URL rewriting
└── index.php                    # Entry point
```

## Cài đặt

### 1. Yêu cầu hệ thống
- PHP 7.4+
- MySQL 5.7+
- Apache với mod_rewrite

### 2. Cài đặt database
1. Tạo database MySQL với tên `web_store`
2. Import file `codeSQL.txt` để tạo các bảng
3. Chạy file `database/init.sql` để thêm dữ liệu mẫu

### 3. Cấu hình
1. Cập nhật thông tin database trong `app/config/database.php`
2. Đảm bảo Apache đã bật mod_rewrite
3. Cấu hình virtual host trỏ về thư mục gốc của project

### 4. Tài khoản mặc định
- **Admin**: 
  - Username: `admin`
  - Password: `password`
- **Customer**: 
  - Username: `customer1`
  - Password: `password`

## Kiến trúc

### MVC Pattern
- **Models**: Xử lý logic nghiệp vụ và truy cập database
- **Views**: Hiển thị giao diện người dùng
- **Controllers**: Điều khiển luồng xử lý giữa Model và View

### Routing System
- Clean URLs với URL rewriting
- Middleware cho phân quyền
- Dynamic routing với parameters

### Authentication & Authorization
- Session-based authentication
- Role-based access control (Admin/Customer)
- Password hashing với bcrypt
- CSRF protection

### Security Features
- SQL injection prevention với PDO prepared statements
- XSS protection với htmlspecialchars
- Input validation và sanitization
- Session management bảo mật

## API Routes

### Public Routes
- `GET /` - Trang chủ
- `GET /products` - Danh sách sản phẩm
- `GET /products/{id}` - Chi tiết sản phẩm
- `GET /login` - Trang đăng nhập
- `POST /login` - Xử lý đăng nhập
- `GET /register` - Trang đăng ký
- `POST /register` - Xử lý đăng ký

### Customer Routes (Requires Authentication)
- `GET /cart` - Giỏ hàng
- `POST /add-to-cart` - Thêm vào giỏ hàng
- `POST /cart/update` - Cập nhật giỏ hàng
- `GET /checkout` - Trang thanh toán
- `POST /checkout` - Xử lý đặt hàng
- `GET /orders` - Lịch sử đơn hàng
- `GET /orders/{id}` - Chi tiết đơn hàng

### Admin Routes (Requires Admin Role)
- `GET /admin` - Dashboard
- `GET /admin/products` - Quản lý sản phẩm
- `GET /admin/products/add` - Thêm sản phẩm
- `POST /admin/products/add` - Xử lý thêm sản phẩm
- `GET /admin/products/edit/{id}` - Sửa sản phẩm
- `POST /admin/products/edit/{id}` - Xử lý sửa sản phẩm
- `GET /admin/products/delete/{id}` - Xóa sản phẩm
- `GET /admin/categories` - Quản lý danh mục
- `GET /admin/brands` - Quản lý thương hiệu
- `GET /admin/orders` - Quản lý đơn hàng
- `GET /admin/users` - Quản lý người dùng

## Database Schema

Xem chi tiết trong file `codeSQL.txt`

### Các bảng chính:
- `users` - Thông tin người dùng
- `products` - Sản phẩm
- `categories` - Danh mục sản phẩm
- `brands` - Thương hiệu
- `carts` & `carts_detail` - Giỏ hàng
- `orders` & `orders_detail` - Đơn hàng
- `images` - Hình ảnh sản phẩm
- `reviews` - Đánh giá sản phẩm

## Technologies Used

- **Backend**: PHP 7.4+, PDO MySQL
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Database**: MySQL
- **Authentication**: Session-based với bcrypt
- **Architecture**: MVC Pattern
- **Security**: PDO Prepared Statements, Input Validation

## Tác giả

Dự án được phát triển cho môn Lập Trình Mạng Máy Tính (LTMMT)

## License

Educational Use Only
