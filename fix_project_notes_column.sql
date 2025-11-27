-- Fix project_notes table to use project CODE instead of numeric ID
-- The table was created with INT but we need VARCHAR to store project codes like 'ХХХ', 'ОИ834', etc.

USE tg_bot;

-- Change project_id column from INT to VARCHAR
ALTER TABLE project_notes 
MODIFY COLUMN project_id VARCHAR(50) NOT NULL COMMENT 'Код проекта (например: ХХХ, ОИ834)';

-- Verify the change
DESCRIBE project_notes;

SELECT 'project_notes table updated successfully! project_id is now VARCHAR(50)' AS status;
