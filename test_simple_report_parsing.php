<?php
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use CustomCommands\Handlers\ProjectHandler;
use CustomCommands\Services\DatabaseService;
use CustomCommands\Services\MessageService;
use CustomCommands\Services\AdminService;

// Тестовые сообщения
$testMessages = [
    [
        'fn' => 'Пусконаладка',
        'message' => "<b>Зангаров Денис Исламович</b>\n✅Запись в табель от <b>25.10.2025</b>: Текущая деятельность (<b>📊 Общая деятельность</b>) добавлена"
    ],
    [
        'fn' => 'Пусконаладка',
        'message' => "<b>Сибагатуллин Ирек Нургаязович</b>\n✅24.10.2025, Проект <b>ОИ833</b>: Добавлена запись в табель: <b>📝 Прочее (Проектная деятельность) (Ознакомление с проектом. Подготовка к командировке.)</b>"
    ]
];

// Создаем рефлексию для доступа к приватным методам
$reflection = new ReflectionClass(ProjectHandler::class);
$parseMethod = $reflection->getMethod('parseReportEntry');
$parseMethod->setAccessible(true);

// Создаем инстанс ProjectHandler
$db = DatabaseService::getInstance();
$msgService = MessageService::getInstance();
$adminService = AdminService::getInstance();
$handler = new ProjectHandler($db, $msgService, $adminService);

foreach ($testMessages as $idx => $test) {
    echo "\n========================================\n";
    echo "ТЕСТ #" . ($idx + 1) . "\n";
    echo "========================================\n";
    echo "ФН: {$test['fn']}\n";
    echo "Сообщение:\n{$test['message']}\n\n";
    
    $parsed = $parseMethod->invoke($handler, $test['message'], $test['fn']);
    
    echo "РЕЗУЛЬТАТ ПАРСИНГА:\n";
    echo "Проект: " . ($parsed['project'] ?: '(пусто)') . "\n";
    echo "Дата: " . ($parsed['date'] ?: '(пусто)') . "\n";
    echo "Процесс: " . ($parsed['process'] ?: '(пусто)') . "\n";
    echo "Вид деятельности: " . ($parsed['vid'] ?: '(пусто)') . "\n";
    echo "Основная активность: " . ($parsed['osnovnaya'] ?: '(пусто)') . "\n";
    echo "Тип работ (tip1): " . ($parsed['tip1'] ?: '(пусто)') . "\n";
    echo "Тип работ 2 (tip2): " . ($parsed['tip2'] ?: '(пусто)') . "\n";
    echo "Тип работ 3 (tip3): " . ($parsed['tip3'] ?: '(пусто)') . "\n";
    echo "========================================\n";
}
