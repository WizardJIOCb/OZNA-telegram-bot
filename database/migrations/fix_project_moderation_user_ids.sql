-- Fix project_moderation table to use BIGINT for user IDs
-- This is required because Telegram user IDs can exceed INT max value (2,147,483,647)

-- Modify added_by column from INT to BIGINT
ALTER TABLE project_moderation 
MODIFY COLUMN added_by BIGINT NOT NULL COMMENT 'ID пользователя, добавившего проект';

-- Modify moderated_by column from INT to BIGINT  
ALTER TABLE project_moderation 
MODIFY COLUMN moderated_by BIGINT NULL COMMENT 'ID администратора, проверившего проект';

-- Verify the changes
DESCRIBE project_moderation;
