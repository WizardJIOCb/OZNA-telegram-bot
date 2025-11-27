<?php

/**
 * Скрипт для ручного назначения пользователя суперадмином через прямые SQL-запросы
 */

echo "Назначение пользователя суперадмином (ручной метод)\n";
echo "=============================================\n\n";

// Данные пользователя
$userId = 5261465966; // Ваш user ID
$userFio = "Богданов Борис Сергеевич";

echo "Пользователь ID: {$userId}\n";
echo "ФИО: {$userFio}\n\n";

// SQL-запросы для выполнения на сервере
echo "Для назначения вас суперадмином выполните следующие SQL-запросы на сервере:\n\n";

echo "1. Проверка текущих данных пользователя:\n";
echo "SELECT user_id, data FROM ozna_data WHERE user_id = {$userId};\n\n";

echo "2. Обновление данных пользователя (если запись существует):\n";
echo "UPDATE ozna_data \n";
echo "SET data = JSON_SET(\n";
echo "    COALESCE(data, '{}'), \n";
echo "    '$.fio', '{$userFio}',\n";
echo "    '$.access_level', 4,\n";
echo "    '$.is_super_admin', true\n";
echo ") \n";
echo "WHERE user_id = {$userId};\n\n";

echo "3. Если записи не существует, создайте новую:\n";
echo "INSERT INTO ozna_data (user_id, data) \n";
echo "VALUES ({$userId}, '{\"fio\": \"{$userFio}\", \"access_level\": 4, \"is_super_admin\": true, \"functional_direction\": \"Не указано\"}');\n\n";

echo "4. Проверка результата:\n";
echo "SELECT user_id, data FROM ozna_data WHERE user_id = {$userId};\n\n";

echo "После выполнения этих запросов ваш уровень доступа будет обновлен до суперадмина.\n";
echo "Вы можете выполнить эти запросы через phpMyAdmin или любой другой MySQL клиент.\n";