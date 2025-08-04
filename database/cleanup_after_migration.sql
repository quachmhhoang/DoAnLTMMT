-- CLEANUP SCRIPT - Execute ONLY after thorough testing
-- This script removes the old promotions table and cleans up references
-- WARNING: This is irreversible! Make sure everything works before running this.

-- =====================================================
-- STEP 1: Final verification before cleanup
-- =====================================================
-- Run these queries to verify migration was successful

-- Check that all promotions were migrated to notifications
SELECT 
    'Promotions in old table' as source, 
    COUNT(*) as count 
FROM promotions
UNION ALL
SELECT 
    'Promotions in notifications table' as source, 
    COUNT(*) as count 
FROM notifications 
WHERE type = 'promotion';

-- Check that products_promotions relationships were updated
SELECT 
    'Old promotion relationships' as source, 
    COUNT(*) as count 
FROM products_promotions 
WHERE promotion_id IS NOT NULL
UNION ALL
SELECT 
    'New notification relationships' as source, 
    COUNT(*) as count 
FROM products_promotions 
WHERE notification_id IS NOT NULL;

-- Verify that all promotion data is accessible through the new structure
SELECT 
    n.notification_id,
    n.promotion_name,
    n.discount_percent,
    n.start_date,
    n.end_date,
    n.title,
    n.message
FROM notifications n
WHERE n.type = 'promotion'
LIMIT 5;

-- =====================================================
-- STEP 2: Remove old foreign key constraints
-- =====================================================
-- Remove the foreign key constraint from products_promotions to promotions

-- First, find the constraint name (it might be different)
-- SELECT CONSTRAINT_NAME 
-- FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
-- WHERE TABLE_NAME = 'products_promotions' 
-- AND COLUMN_NAME = 'promotion_id' 
-- AND REFERENCED_TABLE_NAME = 'promotions';

-- Drop the foreign key constraint (replace constraint name if different)
ALTER TABLE products_promotions 
DROP FOREIGN KEY products_promotions_ibfk_1;

-- =====================================================
-- STEP 3: Remove old promotion_id column
-- =====================================================
-- Remove the promotion_id column from products_promotions table

ALTER TABLE products_promotions 
DROP COLUMN promotion_id;

-- =====================================================
-- STEP 4: Drop the promotions table
-- =====================================================
-- This is the final step - dropping the old promotions table

DROP TABLE promotions;

-- =====================================================
-- STEP 5: Update table schema documentation
-- =====================================================
-- Update the main schema file to reflect the new structure

-- You should manually update codeSQL.txt to:
-- 1. Remove the promotions table definition
-- 2. Update products_promotions table to only reference notification_id
-- 3. Add comments about the new promotion-notification structure

-- =====================================================
-- STEP 6: Optional - Drop backup tables
-- =====================================================
-- Only run these after confirming everything works perfectly for several days

-- DROP TABLE promotions_backup;
-- DROP TABLE products_promotions_backup;

-- =====================================================
-- VERIFICATION QUERIES (Run after cleanup)
-- =====================================================
-- These queries should work after cleanup to verify everything is working

-- Test getting all promotions
-- SELECT 
--     notification_id as promotion_id,
--     promotion_name,
--     discount_percent,
--     start_date,
--     end_date,
--     created_at
-- FROM notifications 
-- WHERE type = 'promotion'
-- ORDER BY start_date DESC;

-- Test getting active promotions
-- SELECT 
--     notification_id as promotion_id,
--     promotion_name,
--     discount_percent,
--     start_date,
--     end_date
-- FROM notifications 
-- WHERE type = 'promotion'
-- AND start_date <= CURDATE() 
-- AND end_date >= CURDATE()
-- ORDER BY start_date DESC;

-- Test products_promotions relationships
-- SELECT 
--     pp.product_promotion_id,
--     pp.product_id,
--     pp.notification_id,
--     n.promotion_name,
--     n.discount_percent
-- FROM products_promotions pp
-- JOIN notifications n ON pp.notification_id = n.notification_id
-- WHERE n.type = 'promotion'
-- LIMIT 5;

-- =====================================================
-- ROLLBACK PLAN (In case of emergency)
-- =====================================================
-- If something goes wrong, you can restore from backups:

-- 1. Restore promotions table from backup:
-- CREATE TABLE promotions AS SELECT * FROM promotions_backup;

-- 2. Restore products_promotions table:
-- ALTER TABLE products_promotions ADD COLUMN promotion_id INT;
-- UPDATE products_promotions pp 
-- JOIN notifications n ON pp.notification_id = n.notification_id
-- SET pp.promotion_id = JSON_EXTRACT(n.data, '$.original_promotion_id')
-- WHERE n.type = 'promotion';
-- ALTER TABLE products_promotions 
-- ADD FOREIGN KEY (promotion_id) REFERENCES promotions(promotion_id);

-- 3. Update application code to use old structure
-- 4. Remove promotion fields from notifications table if needed
