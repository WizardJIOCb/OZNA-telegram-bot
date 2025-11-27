<?php
/**
 * Тест исправленного парсинга
 */

// Подключаем ProjectHandler
require_once __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "====================================\n";
echo "ТЕСТ ИСПРАВЛЕННОГО ПАРСИНГА\n";
echo "====================================\n\n";

// Тестовые сообщения
$testMessages = [
    '<b>Сабиров Олег Фаатович</b>
😨 ⚠️ 01.10.2025, Проект <b>ОИ796</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⚠️ ⏸️ Простой (Готовность Заказчика (Отсутствие рабочих сред))</b>',
    '<b>Габдулхаков Руслан Рамилевич</b>
😨 ⚠️ 02.10.2025, Проект <b>ОИ785</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⚠️ ⏸️ Простой (Готовность Заказчика (Отсутствие энергоресурсов))</b>'
];

// Создаем ProjectHandler
$messageService = new MessageService();
$adminService = new AdminService($messageService);
$projectHandler = new ProjectHandler($adminService, $messageService);

// Используем Reflection чтобы получить доступ к приватному методу
$reflection = new ReflectionClass($projectHandler);
$method = $reflection->getMethod('parseReportEntry');
$method->setAccessible(true);

foreach ($testMessages as $index => $message) {
    echo "Тест #" . ($index + 1) . ":\n";
    
    // Парсинг сообщения
    $result = $method->invoke($projectHandler, $message, 'Пусконаладка');
    
    echo "Проект: " . $result['project'] . "\n";
    echo "Дата: " . $result['date'] . "\n";
    echo "Процесс: " . $result['process'] . "\n";
    echo "Вид деятельности: " . $result['vid'] . "\n";
    echo "Основная активность: " . $result['osnovnaya'] . "\n";
    echo "Тип работ (tip1): " . $result['tip1'] . "\n";
    echo "Тип работ 2 (tip2): " . $result['tip2'] . "\n";
    echo "Тип работ 3 (tip3): " . $result['tip3'] . "\n";
    
    // Проверка правильности
    if ($result['osnovnaya'] === 'Простой' && 
        !empty($result['tip2']) && 
        strpos($result['tip2'], 'Готовн') !== false) {
        echo "✅ ПРАВИЛЬНО: Готовность заказчика найдена в tip2\n";
    } else {
        echo "❌ ОШИБКА: Готовность заказчика не найдена\n";
    }
    
    echo "----------------------------------------\n\n";
}
?>