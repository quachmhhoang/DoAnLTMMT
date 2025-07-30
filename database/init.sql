-- Script khởi tạo database và insert dữ liệu mẫu

-- Tạo database
CREATE DATABASE IF NOT EXISTS web_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE web_store;

-- Tạo các bảng theo schema đã có
-- (Sử dụng code SQL từ file codeSQL.txt)

-- Insert dữ liệu mẫu

-- Insert Brands
INSERT INTO brands (brand_name) VALUES 
('Apple'),
('Samsung'),
('Xiaomi'),
('Oppo'),
('Vivo'),
('Huawei');

-- Insert Categories  
INSERT INTO categories (name) VALUES 
('Điện thoại'),
('Tablet'),
('Phụ kiện'),
('Laptop'),
('Đồng hồ thông minh');

-- Insert admin user
INSERT INTO users (username, password, full_name, address, phone, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '123 Admin Street', '0123456789', 'admin@cellphonestore.com', 'admin');

-- Password for admin is 'password'

-- Insert sample customer
INSERT INTO users (username, password, full_name, address, phone, email, role) VALUES 
('customer1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', '456 Customer Street, Hà Nội', '0987654321', 'customer@example.com', 'customer');

-- Insert sample products
INSERT INTO products (name, price, description, category_id, brand_id) VALUES 
('iPhone 15 Pro Max', 29990000, 'iPhone 15 Pro Max 256GB - Điện thoại cao cấp nhất của Apple với chip A17 Pro, camera 48MP, màn hình 6.7 inch Super Retina XDR', 1, 1),
('Samsung Galaxy S24 Ultra', 26990000, 'Samsung Galaxy S24 Ultra 256GB - Flagship Android với S Pen, camera 200MP, màn hình 6.8 inch Dynamic AMOLED', 1, 2),
('Xiaomi 14 Ultra', 19990000, 'Xiaomi 14 Ultra 512GB - Điện thoại chụp ảnh chuyên nghiệp với Leica, Snapdragon 8 Gen 3', 1, 3),
('iPad Pro M4', 24990000, 'iPad Pro 11 inch M4 256GB - Tablet cao cấp với chip M4, màn hình OLED Tandem', 2, 1),
('AirPods Pro 2', 5990000, 'AirPods Pro thế hệ 2 với chip H2, chống ồn chủ động cải tiến', 3, 1),
('Apple Watch Series 9', 8990000, 'Apple Watch Series 9 GPS 45mm - Đồng hồ thông minh với chip S9, màn hình sáng hơn', 5, 1);

-- Insert sample images
INSERT INTO images (image_url, product_id) VALUES 
('/assets/images/iphone-15-pro-max.jpg', 1),
('/assets/images/samsung-s24-ultra.jpg', 2),
('/assets/images/xiaomi-14-ultra.jpg', 3),
('/assets/images/ipad-pro-m4.jpg', 4),
('/assets/images/airpods-pro-2.jpg', 5),
('/assets/images/apple-watch-s9.jpg', 6);
