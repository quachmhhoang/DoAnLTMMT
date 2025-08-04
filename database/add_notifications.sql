-- Add notification tables to existing database

-- Tạo bảng Notifications (Extended to include promotion data)
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('order', 'product', 'promotion', 'system', 'admin') NOT NULL DEFAULT 'system',
    target_type ENUM('user', 'role', 'all') NOT NULL DEFAULT 'user',
    target_value VARCHAR(100), -- user_id for 'user', role name for 'role', null for 'all'
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME NULL,
    user_id INT NULL, -- specific user for user-targeted notifications
    created_by INT NULL, -- admin who created the notification
    data JSON NULL, -- additional data for the notification

    -- Promotion-specific fields (only used when type = 'promotion')
    promotion_name VARCHAR(100) NULL, -- promotion name for promotion-type notifications
    discount_percent DECIMAL(5,2) NULL, -- discount percentage for promotions
    start_date DATE NULL, -- promotion start date
    end_date DATE NULL, -- promotion end date

    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    INDEX idx_user_id (user_id),
    INDEX idx_target_type_value (target_type, target_value),
    INDEX idx_created_at (created_at),
    INDEX idx_is_read (is_read),
    INDEX idx_type (type),
    INDEX idx_promotion_dates (start_date, end_date) -- for querying active promotions
);

-- Tạo bảng Notification Settings (user preferences)
CREATE TABLE IF NOT EXISTS notification_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notification_type ENUM('order', 'product', 'promotion', 'system', 'admin') NOT NULL,
    is_enabled BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    UNIQUE KEY unique_user_type (user_id, notification_type)
);

-- Insert default notification settings for existing users
INSERT IGNORE INTO notification_settings (user_id, notification_type, is_enabled)
SELECT u.user_id, 'order', TRUE FROM users u;

INSERT IGNORE INTO notification_settings (user_id, notification_type, is_enabled)
SELECT u.user_id, 'product', TRUE FROM users u;

INSERT IGNORE INTO notification_settings (user_id, notification_type, is_enabled)
SELECT u.user_id, 'promotion', TRUE FROM users u;

INSERT IGNORE INTO notification_settings (user_id, notification_type, is_enabled)
SELECT u.user_id, 'system', TRUE FROM users u;

INSERT IGNORE INTO notification_settings (user_id, notification_type, is_enabled)
SELECT u.user_id, 'admin', TRUE FROM users u WHERE u.role = 'admin';

-- Insert some sample notifications
INSERT INTO notifications (title, message, type, target_type, target_value, user_id, created_by) VALUES
('Chào mừng đến với CellPhone Store!', 'Cảm ơn bạn đã tham gia cộng đồng của chúng tôi. Khám phá những sản phẩm điện thoại tuyệt vời nhất!', 'system', 'all', NULL, NULL, NULL),
('Khuyến mãi đặc biệt', 'Giảm giá 20% cho tất cả sản phẩm iPhone trong tuần này!', 'promotion', 'all', NULL, NULL, NULL);
