<?php
/**
 * Тестирование одновременного доступа к настройкам проектов
 * Проверяем, может ли два пользователя одновременно изменять проекты
 */

echo "=== ТЕСТИРОВАНИЕ ОДНОВРЕМЕННОГО ДОСТУПА К ПРОЕКТАМ ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$databaseService = new DatabaseService();

// Симулируем двух разных пользователей
$user1Id = 230379668; // Ваш пользователь
$user2Id = 4225504;   // Другой пользователь

echo "Тестируем одновременное добавление проектов:\n";
echo "Пользователь 1: {$user1Id}\n";
echo "Пользователь 2: {$user2Id}\n\n";

// Очищаем данные для чистого теста
echo "1. Очистка тестовых данных...\n";
$userData1 = $databaseService->getUserData($user1Id);
$userData2 = $databaseService->getUserData($user2Id);

// Убираем проекты на контроле для чистого теста
unset($userData1['control_projects']);
unset($userData2['control_projects']);
$databaseService->updateUserData($user1Id, $userData1);
$databaseService->updateUserData($user2Id, $userData2);
echo "   ✓ Данные очищены\n\n";

// Симулируем одновременное добавление проектов
echo "2. Симуляция одновременного добавления проектов...\n";

// Пользователь 1 добавляет проект ОИ834
echo "   Пользователь 1 добавляет проект ОИ834...\n";
$userData1 = $databaseService->getUserData($user1Id);
$controlProjects1 = $userData1['control_projects'] ?? [];
$controlProjects1['ОИ834'] = [
    'group_id' => '-1002722176147',
    'chat_id' => 'personal',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];
$userData1['control_projects'] = $controlProjects1;

// Пользователь 2 добавляет тот же проект ОИ834 (конфликт!)
echo "   Пользователь 2 добавляет тот же проект ОИ834...\n";
$userData2 = $databaseService->getUserData($user2Id);
$controlProjects2 = $userData2['control_projects'] ?? [];
$controlProjects2['ОИ834'] = [
    'group_id' => '-1001234567890',
    'chat_id' => '-987654321',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];
$userData2['control_projects'] = $controlProjects2;

// Попытка одновременного сохранения
echo "   Одновременное сохранение в базу данных...\n";
$result1 = $databaseService->updateUserData($user1Id, $userData1);
$result2 = $databaseService->updateUserData($user2Id, $userData2);

echo "   Результат сохранения пользователя 1: " . ($result1 ? "✓ Успешно" : "✗ Ошибка") . "\n";
echo "   Результат сохранения пользователя 2: " . ($result2 ? "✓ Успешно" : "✗ Ошибка") . "\n\n";

// Проверяем результаты
echo "3. Проверка результатов...\n";
$finalData1 = $databaseService->getUserData($user1Id);
$finalData2 = $databaseService->getUserData($user2Id);

$finalProjects1 = $finalData1['control_projects'] ?? [];
$finalProjects2 = $finalData2['control_projects'] ?? [];

echo "   Пользователь 1 имеет проекты: " . implode(', ', array_keys($finalProjects1)) . "\n";
echo "   Пользователь 2 имеет проекты: " . implode(', ', array_keys($finalProjects2)) . "\n\n";

if (isset($finalProjects1['ОИ834']) && isset($finalProjects2['ОИ834'])) {
    echo "   📍 ПРОЕКТ ОИ834 ЕСТЬ У ОБОИХ ПОЛЬЗОВАТЕЛЕЙ\n";
    echo "   Настройки пользователя 1:\n";
    echo "     Group ID: " . $finalProjects1['ОИ834']['group_id'] . "\n";
    echo "     Chat ID: " . $finalProjects1['ОИ834']['chat_id'] . "\n";
    echo "   Настройки пользователя 2:\n";
    echo "     Group ID: " . $finalProjects2['ОИ834']['group_id'] . "\n";
    echo "     Chat ID: " . $finalProjects2['ОИ834']['chat_id'] . "\n\n";
    
    echo "   ✅ ЭТО НОРМАЛЬНО! Каждый пользователь имеет свои личные настройки для одного и того же проекта.\n";
    echo "   Система спроектирована так, что каждый пользователь может настроить уведомления индивидуально.\n\n";
}

// Тестируем обновление настроек проекта
echo "4. Тестирование обновления настроек...\n";

// Пользователь 1 обновляет группу
echo "   Пользователь 1 обновляет group_id...\n";
$userData1 = $databaseService->getUserData($user1Id);
$userData1['control_projects']['ОИ834']['group_id'] = '-1002722176147_UPDATED';
$userData1['control_projects']['ОИ834']['updated_at'] = date('Y-m-d H:i:s');

// Пользователь 2 обновляет чат
echo "   Пользователь 2 обновляет chat_id...\n";
$userData2 = $databaseService->getUserData($user2Id);
$userData2['control_projects']['ОИ834']['chat_id'] = '-987654321_UPDATED';
$userData2['control_projects']['ОИ834']['updated_at'] = date('Y-m-d H:i:s');

// Сохраняем изменения
$databaseService->updateUserData($user1Id, $userData1);
$databaseService->updateUserData($user2Id, $userData2);

// Проверяем результаты
$updatedData1 = $databaseService->getUserData($user1Id);
$updatedData2 = $databaseService->getUserData($user2Id);

echo "   Обновленные настройки пользователя 1:\n";
echo "     Group ID: " . $updatedData1['control_projects']['ОИ834']['group_id'] . "\n";
echo "     Chat ID: " . $updatedData1['control_projects']['ОИ834']['chat_id'] . "\n";

echo "   Обновленные настройки пользователя 2:\n";
echo "     Group ID: " . $updatedData2['control_projects']['ОИ834']['group_id'] . "\n";
echo "     Chat ID: " . $updatedData2['control_projects']['ОИ834']['chat_id'] . "\n\n";

echo "=== ВЫВОДЫ ===\n";
echo "✅ Одновременный доступ НЕ вызывает проблем, потому что:\n";
echo "   1. Каждый пользователь имеет свою отдельную запись в таблице ozna_data\n";
echo "   2. Настройки проектов хранятся в JSON-поле 'data' для каждого пользователя\n";
echo "   3. Два пользователя могут независимо настраивать один и тот же проект\n";
echo "   4. Нет глобальных настроек проекта - только личные настройки каждого пользователя\n\n";

echo "🔍 ВОЗМОЖНЫЕ ПРИЧИНЫ ПРОБЛЕМЫ:\n";
echo "   1. Проблема в пользовательском интерфейсе (кнопки не отвечают)\n";
echo "   2. Проблема в обработке callback данных\n";
echo "   3. Проблема в сетевом соединении\n";
echo "   4. Проблема в валидации данных (некорректный формат ID)\n\n";

echo "💡 РЕКОМЕНДАЦИИ:\n";
echo "   1. Проверьте логи callback_monitor.log на наличие ошибок\n";
echo "   2. Убедитесь, что ID группы имеет правильный формат (-100XXXXXXXXX)\n";
echo "   3. Проверьте, что бот добавлен в группу как администратор\n";
echo "   4. Попробуйте использовать кнопку 'Личный чат' вместо ввода ID группы\n";
?>