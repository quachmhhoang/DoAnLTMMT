-- Fix MySQL ONLY_FULL_GROUP_BY mode
-- This script removes the ONLY_FULL_GROUP_BY from sql_mode to allow more flexible GROUP BY queries

-- Check current sql_mode
SELECT @@sql_mode;

-- Set sql_mode without ONLY_FULL_GROUP_BY for current session
SET sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY,',''));

-- To make this permanent, you can add this to your MySQL configuration file (my.cnf or my.ini):
-- [mysqld]
-- sql_mode = "STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"

-- Or you can set it globally (requires SUPER privilege):
-- SET GLOBAL sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY,',''));

-- Verify the change
SELECT @@sql_mode;
