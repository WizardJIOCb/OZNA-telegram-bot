<?php
/**
 * Диагностика проблемы одновременного доступа к настройкам проектов
 * Проверка возможных проблем с гонками данных (race conditions)
 */

echo "=== ДИАГНОСТИКА ОДНОВРЕМЕННОГО ДОСТУПА ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

// Тестовые пользователи
$user1 = 230379668; // Первый пользователь
$user2 = 4225504;   // Второй пользователь

$databaseService = new DatabaseService();

echo "1. ПРОВЕРКА ТЕКУЩЕГО МЕХАНИЗМА ОБНОВЛЕНИЯ ДАННЫХ:\n";
echo "   Метод updateUserData использует простой UPDATE без блокировок\n";
echo "   Это может приводить к потере данных при одновременных обновлениях\n\n";

echo "2. СИМУЛЯЦИЯ ОДНОВРЕМЕННОГО ДОСТУПА:\n";

// Получаем данные для обоих пользователей
echo "   Получение данных пользователя 1 ({$user1})...\n";
$userData1 = $databaseService->getUserData($user1);
$controlProjects1 = $userData1['control_projects'] ?? [];
echo "   - Проектов на контроле: " . count($controlProjects1) . "\n";

echo "   Получение данных пользователя 2 ({$user2})...\n";
$userData2 = $databaseService->getUserData($user2);
$controlProjects2 = $userData2['control_projects'] ?? [];
echo "   - Проектов на контроле: " . count($controlProjects2) . "\n\n";

echo "3. АНАЛИЗ ПОТЕНЦИАЛЬНЫХ ПРОБЛЕМ:\n";

// Проблема 1: Race Condition
echo "   ❗ ПРОБЛЕМА 1: Race Condition\n";
echo "   Если два пользователя одновременно:\n";
echo "   - Получают данные (getUserData)\n";
echo "   - Модифицируют настройки\n";
echo "   - Сохраняют данные (updateUserData)\n";
echo "   То последний сохранивший перезапишет изменения первого!\n\n";

// Проблема 2: Отсутствие транзакций
echo "   ❗ ПРОБЛЕМА 2: Отсутствие транзакций\n";
echo "   Операция не атомарна - может быть прервана в середине\n\n";

// Проблема 3: Отсутствие блокировок
echo "   ❗ ПРОБЛЕМА 3: Отсутствие блокировок\n";
echo "   Нет механизма блокировки записей при чтении/изменении\n\n";

echo "4. ДЕМОНСТРАЦИЯ ПРОБЛЕМЫ:\n";

// Имитируем одновременное добавление проекта
$testProject1 = "ТЕСТ_ПРОЕКТ_1";
$testProject2 = "ТЕСТ_ПРОЕКТ_2";

echo "   Пользователь 1 читает данные и добавляет проект {$testProject1}...\n";
$userData1['control_projects'][$testProject1] = [
    'group_id' => '-1001234567890',
    'chat_id' => 'personal',
    'created_at' => date('Y-m-d H:i:s')
];

echo "   Пользователь 2 читает данные и добавляет проект {$testProject2}...\n";
$userData2['control_projects'][$testProject2] = [
    'group_id' => '-1001234567891',
    'chat_id' => 'personal',
    'created_at' => date('Y-m-d H:i:s')
];

// Если бы они сохранялись одновременно, один перезаписал бы данные другого
echo "   ⚠️ При одновременном сохранении один перезапишет изменения другого!\n\n";

echo "5. РЕКОМЕНДУЕМЫЕ РЕШЕНИЯ:\n\n";

echo "   ✅ РЕШЕНИЕ 1: Использование блокировок (SELECT ... FOR UPDATE)\n";
echo "   Код:\n";
echo "   \$query = \"SELECT data FROM ozna_data WHERE user_id = ? FOR UPDATE\";\n";
echo "   // Модификация данных\n";
echo "   \$query = \"UPDATE ozna_data SET data = ? WHERE user_id = ?\";\n\n";

echo "   ✅ РЕШЕНИЕ 2: Версионирование записей\n";
echo "   Добавить поле version, увеличивать при каждом обновлении\n";
echo "   Проверять версию перед сохранением\n\n";

echo "   ✅ РЕШЕНИЕ 3: Использование транзакций\n";
echo "   Обернуть операции чтения-изменения-записи в транзакцию\n\n";

echo "   ✅ РЕШЕНИЕ 4: Атомарные операции на уровне базы\n";
echo "   Использовать JSON_SET для обновления только нужных полей\n\n";

echo "6. ПРОВЕРКА ТЕКУЩЕГО СОСТОЯНИЯ БАЗЫ:\n";

// Проверяем структуру таблицы
try {
    $mysqli = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
    
    $result = $mysqli->query("SHOW CREATE TABLE ozna_data");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "   Структура таблицы ozna_data:\n";
        echo "   " . str_replace(',', ",\n   ", $row['Create Table']) . "\n\n";
    }
    
    // Проверяем движок таблицы
    $result = $mysqli->query("SHOW TABLE STATUS LIKE 'ozna_data'");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "   Движок таблицы: " . $row['Engine'] . "\n";
        echo "   Поддержка транзакций: " . ($row['Engine'] === 'InnoDB' ? 'ДА' : 'НЕТ') . "\n\n";
    }
    
    $mysqli->close();
} catch (Exception $e) {
    echo "   Ошибка подключения к базе: " . $e->getMessage() . "\n\n";
}

echo "7. ПРАКТИЧЕСКИЙ ТЕСТ ПРОБЛЕМЫ:\n";
echo "   Для проверки проблемы нужно:\n";
echo "   1. Два пользователя одновременно заходят в настройки проектов\n";
echo "   2. Оба добавляют разные проекты одновременно\n";
echo "   3. Проверяют сохранились ли оба проекта\n\n";

echo "8. СРОЧНОСТЬ ИСПРАВЛЕНИЯ:\n";
echo "   🔴 ВЫСОКАЯ - потеря данных пользователей\n";
echo "   🔴 Рекомендуется немедленно внедрить блокировки\n\n";

echo "=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";
?>