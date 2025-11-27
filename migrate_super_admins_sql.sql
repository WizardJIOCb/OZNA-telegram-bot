-- Скрипт миграции суперадминов в базу данных
-- Выполнить эти запросы в MySQL клиенте или через phpMyAdmin

-- Проверка текущих суперадминов (до миграции)
SELECT user_id, data FROM ozna_data WHERE user_id IN (230379668, 5261465966);

-- Миграция первого суперадмина (230379668)
-- Если запись существует, обновляем её
UPDATE ozna_data 
SET data = JSON_SET(
    COALESCE(data, '{}'), 
    '$.is_super_admin', true,
    '$.access_level', 4
) 
WHERE user_id = 230379668;

-- Если записи не существует, создаем новую
INSERT INTO ozna_data (user_id, data) 
SELECT 230379668, '{"is_super_admin": true, "access_level": 4, "fio": "", "functional_direction": "Не указано"}'
WHERE NOT EXISTS (SELECT 1 FROM ozna_data WHERE user_id = 230379668);

-- Миграция второго суперадмина (5261465966)
-- Если запись существует, обновляем её
UPDATE ozna_data 
SET data = JSON_SET(
    COALESCE(data, '{}'), 
    '$.is_super_admin', true,
    '$.access_level', 4
) 
WHERE user_id = 5261465966;

-- Если записи не существует, создаем новую
INSERT INTO ozna_data (user_id, data) 
SELECT 5261465966, '{"is_super_admin": true, "access_level": 4, "fio": "", "functional_direction": "Не указано"}'
WHERE NOT EXISTS (SELECT 1 FROM ozna_data WHERE user_id = 5261465966);

-- Проверка результатов миграции
SELECT user_id, data FROM ozna_data WHERE JSON_UNQUOTE(JSON_EXTRACT(data, '$.is_super_admin')) = 'true';

-- Альтернативная проверка (если JSON_EXTRACT не работает)
SELECT user_id, data FROM ozna_data WHERE data LIKE '%is_super_admin%';