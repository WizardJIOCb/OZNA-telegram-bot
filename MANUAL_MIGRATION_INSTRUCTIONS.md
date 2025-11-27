# Инструкция по ручной миграции суперадминов в базу данных

## Обзор

Поскольку на текущем компьютере отсутствуют необходимые PHP расширения (mysqli и PDO MySQL), миграция суперадминов должна быть выполнена вручную через прямой доступ к базе данных MySQL.

## Необходимые компоненты

1. Доступ к серверу базы данных MySQL (82.146.42.213)
2. Учетные данные для подключения:
   - Хост: 82.146.42.213
   - Пользователь: wj
   - Пароль: 
   - База данных: tg_bot

## Вариант 1: Использование командной строки MySQL

1. Подключитесь к серверу MySQL:
   ```bash
   mysql -h 82.146.42.213 -u wj -p
   ```

2. Введите пароль: ``

3. Выберите базу данных:
   ```sql
   USE tg_bot;
   ```

4. Выполните SQL-запросы из файла [migrate_super_admins_sql.sql](file:///c:/ozna_bot/migrate_super_admins_sql.sql):
   ```sql
   -- Миграция первого суперадмина (230379668)
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
   ```

## Вариант 2: Использование phpMyAdmin

1. Откройте phpMyAdmin и подключитесь к серверу базы данных
2. Выберите базу данных `tg_bot`
3. Перейдите на вкладку "SQL"
4. Вставьте и выполните SQL-запросы из файла [migrate_super_admins_sql.sql](file:///c:/ozna_bot/migrate_super_admins_sql.sql)

## Проверка результатов

После выполнения миграции проверьте результаты:

```sql
-- Проверка результатов миграции
SELECT user_id, data FROM ozna_data WHERE JSON_UNQUOTE(JSON_EXTRACT(data, '$.is_super_admin')) = 'true';

-- Альтернативная проверка (если JSON_EXTRACT не работает)
SELECT user_id, data FROM ozna_data WHERE data LIKE '%is_super_admin%';
```

## Следующие шаги

После успешной миграции:

1. Убедитесь, что суперадмины могут получить доступ к админке через бот
2. Проверьте, что функции управления уровнями доступа работают корректно
3. Удалите хардкод списка суперадминов из [AdminService.php](file:///c:/ozna_bot/CustomCommands/Services/AdminService.php) (если еще не удален)

## Устранение неполадок

Если возникли проблемы:

1. Убедитесь, что у пользователя `wj` есть права на UPDATE и INSERT в таблице `ozna_data`
2. Проверьте, что функции JSON доступны в вашей версии MySQL (5.7+)
3. Если функции JSON не работают, используйте альтернативные методы обновления JSON-данных