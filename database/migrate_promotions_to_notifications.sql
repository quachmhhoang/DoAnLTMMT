-- Migration script to merge promotions table into notifications table
-- This script adds promotion-specific fields to notifications table and migrates data

-- Step 1: Add promotion-specific columns to notifications table
ALTER TABLE notifications 
ADD COLUMN promotion_name VARCHAR(100) NULL COMMENT 'Promotion name for promotion-type notifications',
ADD COLUMN discount_percent DECIMAL(5,2) NULL COMMENT 'Discount percentage for promotions',
ADD COLUMN start_date DATE NULL COMMENT 'Promotion start date',
ADD COLUMN end_date DATE NULL COMMENT 'Promotion end date';

-- Step 2: Add indexes for better performance
ALTER TABLE notifications 
ADD INDEX idx_type (type),
ADD INDEX idx_promotion_dates (start_date, end_date);

-- Step 3: Migrate existing promotion data to notifications table
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
        'promotion_id', p.promotion_id,
        'discount_percent', p.discount_percent,
        'start_date', p.start_date,
        'end_date', p.end_date,
        'action_url', CONCAT('/promotions/', p.promotion_id),
        'migrated_from_promotions', true
    ) as data
FROM promotions p
WHERE NOT EXISTS (
    SELECT 1 FROM notifications n 
    WHERE n.type = 'promotion' 
    AND JSON_EXTRACT(n.data, '$.promotion_id') = p.promotion_id
);

-- Step 4: Update products_promotions table to reference notification_id instead of promotion_id
-- First, add the new column
ALTER TABLE products_promotions 
ADD COLUMN notification_id INT NULL,
ADD FOREIGN KEY (notification_id) REFERENCES notifications(notification_id);

-- Update the notification_id based on the promotion_id mapping
UPDATE products_promotions pp
JOIN notifications n ON JSON_EXTRACT(n.data, '$.promotion_id') = pp.promotion_id
SET pp.notification_id = n.notification_id
WHERE n.type = 'promotion';

-- Step 5: Create a backup of the original promotions table before dropping
CREATE TABLE promotions_backup AS SELECT * FROM promotions;

-- Note: The following steps should be executed manually after verifying the migration:
-- 1. Test all promotion functionality
-- 2. Verify data integrity
-- 3. Drop the old promotion_id column from products_promotions
-- 4. Drop the promotions table
-- 5. Update application code

-- Commands to execute after verification:
-- ALTER TABLE products_promotions DROP FOREIGN KEY products_promotions_ibfk_1;
-- ALTER TABLE products_promotions DROP COLUMN promotion_id;
-- DROP TABLE promotions;
-- DROP TABLE promotions_backup; -- only after confirming everything works
