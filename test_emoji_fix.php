<?php
/**
 * Тест исправления проблемы с эмодзи в отчётах
 */

require_once __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Создаем мок-объекты для тестирования
class MockAdminService {
    public function getUserAccessLevel($userId) {
        return 1; // Минимальный уровень доступа
    }
    
    public function getAllProjects() {
        return ['ОИ819', 'ОИ743-03', 'ОИ696', 'БКНС120', 'ОИ846'];
    }
}

class MockMessageService {
    public function sendMessage($chatId, $text, $keyboard = null) {
        // Просто возвращаем успешный результат
        return new class {
            public function isOk() { return true; }
        };
    }
}

// Создаем экземпляр ProjectHandler
$adminService = new MockAdminService();
$messageService = new MockMessageService();
$projectHandler = new ProjectHandler($adminService, $messageService);

// Тестовое сообщение с эмодзи из INCIDENT формата
$testMessage = '<b>Нигматзянов Азат Закиевич</b>\r\n😨 ⚠️ 25.10.2025, Проект <b>ОИ819</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⏸️ Простой (Шеф-монтаж) (Прочее (Отсутствует готовность ГИ заказчиком))</b>';
$functionalDirection = 'Шеф-монтаж';

echo "====================================\n";
echo "ТЕСТ ИСПРАВЛЕНИЯ ПРОБЛЕМЫ С ЭМОДЗИ\n";
echo "====================================\n\n";

echo "Тестовое сообщение:\n";
echo $testMessage . "\n\n";

// Вызываем метод parseReportEntry через рефлексию, так как он приватный
$reflection = new ReflectionClass($projectHandler);
$method = $reflection->getMethod('parseReportEntry');
$method->setAccessible(true);

$result = $method->invokeArgs($projectHandler, [$testMessage, $functionalDirection]);

echo "Результат парсинга:\n";
foreach ($result as $key => $value) {
    echo "  {$key}: '{$value}'\n";
}

echo "\n";

// Проверяем, что эмодзи больше не появляются
$hasEmojiInVid = strpos($result['vid'], '⏸️') !== false;
$hasEmojiInOsnovnaya = strpos($result['osnovnaya'], '⏸️') !== false;
$hasEmojiInTip1 = strpos($result['tip1'], '⏸️') !== false;
$hasEmojiInTip2 = strpos($result['tip2'], '⏸️') !== false;
$hasEmojiInTip3 = strpos($result['tip3'], '⏸️') !== false;

if ($hasEmojiInVid || $hasEmojiInOsnovnaya || $hasEmojiInTip1 || $hasEmojiInTip2 || $hasEmojiInTip3) {
    echo "❌ ОШИБКА: Эмодзи все еще присутствуют в результатах!\n";
    if ($hasEmojiInVid) echo "  - Вид деятельности содержит эмодзи\n";
    if ($hasEmojiInOsnovnaya) echo "  - Основная активность содержит эмодзи\n";
    if ($hasEmojiInTip1) echo "  - Тип работ 1 содержит эмодзи\n";
    if ($hasEmojiInTip2) echo "  - Тип работ 2 содержит эмодзи\n";
    if ($hasEmojiInTip3) echo "  - Тип работ 3 содержит эмодзи\n";
} else {
    echo "✅ УСПЕХ: Эмодзи успешно удалены из результатов!\n";
    
    // Проверяем, что значения правильные
    $expected = [
        'project' => 'ОИ819',
        'date' => '25.10.2025',
        'process' => 'Шеф-монтаж',
        'vid' => '', // Для простоя должно быть пусто
        'osnovnaya' => 'Простой',
        'tip1' => 'Шеф-монтаж',
        'tip2' => 'Прочее',
        'tip3' => 'Отсутствует готовность ГИ заказчиком'
    ];
    
    $allCorrect = true;
    foreach ($expected as $key => $expectedValue) {
        if ($result[$key] !== $expectedValue) {
            echo "  ❌ Несоответствие в {$key}: '{$result[$key]}' (ожидалось: '{$expectedValue}')\n";
            $allCorrect = false;
        }
    }
    
    if ($allCorrect) {
        echo "✅ Все значения корректны!\n";
    }
}

echo "\n====================================\n";
echo "ТЕСТ ЗАВЕРШЕН\n";
echo "====================================\n";