-- ========================================
-- Fix ОИ 815-01 project spacing issue
-- Remove entries with space between "ОИ" and "815-01"
-- ========================================

USE tg_bot;

-- Step 1: Check current state
SELECT '=== BEFORE FIX ===' AS status;
SELECT id, project_code, original_name, current_name, status, is_active
FROM project_moderation
WHERE current_name LIKE '%815%'
ORDER BY current_name;

-- Step 2: Delete any entries with "ОИ 815-01" (with space)
DELETE FROM project_moderation
WHERE current_name = 'ОИ 815-01' 
   OR current_name LIKE 'ОИ 815-%';

-- Step 3: Check if there's already a correct entry "ОИ815-01" (without space)
SELECT '=== CHECKING FOR CORRECT ENTRY ===' AS status;
SELECT id, project_code, original_name, current_name, status, is_active
FROM project_moderation
WHERE current_name = 'ОИ815-01';

-- Step 4: Verify the fix
SELECT '=== AFTER FIX ===' AS status;
SELECT id, project_code, original_name, current_name, status, is_active
FROM project_moderation
WHERE current_name LIKE '%815%'
ORDER BY current_name;

-- Step 5: Also update project_notes table if needed
UPDATE project_notes
SET project_id = 'ОИ815-01'
WHERE project_id LIKE 'ОИ 815%';

-- Step 6: Check project_notes
SELECT '=== NOTES TABLE ===' AS status;
SELECT DISTINCT project_id
FROM project_notes
WHERE project_id LIKE '%815%';

-- Step 7: Also check ozna_data for user personal lists
SELECT '=== USER DATA CHECK ===' AS status;
SELECT user_id, data
FROM ozna_data
WHERE data LIKE '%815%'
LIMIT 5;

SELECT '✅ Fix complete! Project "ОИ 815-01" entries have been removed/updated to "ОИ815-01"' AS result;
