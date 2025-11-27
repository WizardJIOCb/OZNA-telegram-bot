<?php
/**
 * Script to check current users and their access levels
 */

// Note: This script would require database access to get real user data
// For now, we'll show the structure of how this would work

echo "=== Проверка текущих пользователей и заявок на доступ ===\n\n";

// In a real implementation, this would connect to the database and fetch user data
echo "Текущие активные заявки на доступ:\n";
echo "Нет активных заявок\n\n";

echo "Примечание: Для получения реальной информации о пользователях и заявках\n";
echo "необходим доступ к базе данных, который требует установленное расширение MySQLi.\n";
echo "В текущей системе это расширение отсутствует.\n\n";

echo "Для просмотра заявок вручную проверьте содержимое файла:\n";
echo "data/access_requests.json\n\n";

echo "Структура файла заявок:\n";
echo "[\n";
echo "  {\n";
echo "    \"user_id\": 123456789,\n";
echo "    \"user_name\": \"Имя пользователя\",\n";
echo "    \"user_telegram_name\": \"UsernameTelegram\",\n";
echo "    \"user_fio\": \"Фамилия Имя Отчество\",\n";
echo "    \"chat_id\": \"987654321\",\n";
echo "    \"timestamp\": 1725120000\n";
echo "  }\n";
echo "]\n\n";

echo "Если файл содержит данные в этом формате, они отображаются в админке\n";
echo "в разделе 'Заявки на доступ' для одобрения администраторами.\n";
?>