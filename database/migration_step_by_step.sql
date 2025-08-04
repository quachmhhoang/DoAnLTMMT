-- STEP-BY-STEP MIGRATION GUIDE
-- Execute these scripts one by one and verify each step

-- =====================================================
-- STEP 1: Add promotion fields to notifications table
-- =====================================================
-- Run this first to extend the notifications table structure

ALTER TABLE notifications 
ADD COLUMN promotion_name VARCHAR(100) NULL COMMENT 'Promotion name for promotion-type notifications',
ADD COLUMN discount_percent DECIMAL(5,2) NULL COMMENT 'Discount percentage for promotions', 
ADD COLUMN start_date DATE NULL COMMENT 'Promotion start date',
ADD COLUMN end_date DATE NULL COMMENT 'Promotion end date';

-- Add indexes for better performance
ALTER TABLE notifications 
ADD INDEX idx_type (type),
ADD INDEX idx_promotion_dates (start_date, end_date);

-- Verify: Check if columns were added successfully
-- SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'notifications' AND TABLE_SCHEMA = DATABASE();

-- =====================================================
-- STEP 2: Backup existing data
-- =====================================================
-- Create backup tables before migration

CREATE TABLE promotions_backup AS SELECT * FROM promotions;
CREATE TABLE products_promotions_backup AS SELECT * FROM products_promotions;

-- Verify: Check backup tables were created
-- SELECT COUNT(*) FROM promotions_backup;
-- SELECT COUNT(*) FROM products_promotions_backup;

-- =====================================================
-- STEP 3: Migrate promotion data to notifications
-- =====================================================
-- This will create notification records for each promotion

INSERT INTO notifications (
    title, 
    message, 
    type, 
    target_type, 
    target_value, 
    user_id, 
    created_by, 
    promotion_name, 
    discount_percent, 
    start_date, 
    end_date,
    created_at,
    data
)
SELECT 
    CONCAT('🎉 Khuyến mãi: ', p.promotion_name) as title,
    CONCAT('Giảm giá ', p.discount_percent, '% - ', COALESCE(p.description, ''), '. Có hiệu lực từ ', 
           DATE_FORMAT(p.start_date, '%d/%m/%Y'), ' đến ', DATE_FORMAT(p.end_date, '%d/%m/%Y')) as message,
    'promotion' as type,
    'all' as target_type,
    NULL as target_value,
    NULL as user_id,
    NULL as created_by,
    p.promotion_name,
    p.discount_percent,
    p.start_date,
    p.end_date,
    NOW() as created_at,
    JSON_OBJECT(
        'original_promotion_id', p.promotion_id,
        'discount_percent', p.discount_percent,
        'start_date', p.start_date,
        'end_date', p.end_date,
        'action_url', CONCAT('/promotions/', p.promotion_id),
        'migrated_from_promotions', true
    ) as data
FROM promotions p;

-- Verify: Check if promotion data was migrated
-- SELECT COUNT(*) FROM notifications WHERE type = 'promotion';
-- SELECT * FROM notifications WHERE type = 'promotion' LIMIT 5;

-- =====================================================
-- STEP 4: Update products_promotions relationship
-- =====================================================
-- Add notification_id column to products_promotions

ALTER TABLE products_promotions 
ADD COLUMN notification_id INT NULL;

-- Update notification_id based on original promotion_id
UPDATE products_promotions pp
JOIN notifications n ON JSON_EXTRACT(n.data, '$.original_promotion_id') = pp.promotion_id
SET pp.notification_id = n.notification_id
WHERE n.type = 'promotion';

-- Add foreign key constraint
ALTER TABLE products_promotions 
ADD FOREIGN KEY fk_products_promotions_notification (notification_id) REFERENCES notifications(notification_id);

-- Verify: Check if notification_id was populated correctly
-- SELECT pp.*, n.promotion_name FROM products_promotions pp 
-- JOIN notifications n ON pp.notification_id = n.notification_id 
-- WHERE n.type = 'promotion' LIMIT 5;

-- =====================================================
-- STEP 5: Verification queries
-- =====================================================
-- Run these to verify the migration was successful

-- Check promotion counts match
-- SELECT 'Original promotions' as source, COUNT(*) as count FROM promotions
-- UNION ALL
-- SELECT 'Migrated notifications' as source, COUNT(*) as count FROM notifications WHERE type = 'promotion';

-- Check products_promotions relationships
-- SELECT 'Original relationships' as source, COUNT(*) as count FROM products_promotions WHERE promotion_id IS NOT NULL
-- UNION ALL  
-- SELECT 'New relationships' as source, COUNT(*) as count FROM products_promotions WHERE notification_id IS NOT NULL;

-- =====================================================
-- STEP 6: Cleanup (Execute only after testing!)
-- =====================================================
-- WARNING: Only run these after thoroughly testing the application

-- Remove old foreign key constraint
-- ALTER TABLE products_promotions DROP FOREIGN KEY products_promotions_ibfk_1;

-- Drop old promotion_id column
-- ALTER TABLE products_promotions DROP COLUMN promotion_id;

-- Drop promotions table
-- DROP TABLE promotions;

-- Drop backup tables (only after confirming everything works)
-- DROP TABLE promotions_backup;
-- DROP TABLE products_promotions_backup;
