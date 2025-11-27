<?php
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Custome/DB.php';

echo "====================================\n";
echo "ПОЛНЫЙ ТЕСТ ПАРСИНГА РК И ГАРАНТИИ\n";
echo "====================================\n\n";

// Создаем инстанс ProjectHandler через рефлексию
$db = CustomCommands\Services\DatabaseService::getInstance();
$msgService = CustomCommands\Services\MessageService::getInstance();
$adminService = CustomCommands\Services\AdminService::getInstance();
$handler = new CustomCommands\Handlers\ProjectHandler($db, $msgService, $adminService);

$reflection = new ReflectionClass('CustomCommands\\Handlers\\ProjectHandler');
$parseMethod = $reflection->getMethod('parseReportEntry');
$parseMethod->setAccessible(true);

$testMessages = [
    [
        'type' => 'Гарантия (новый формат)',
        'fn' => 'Пусконаладка',
        'message' => "<b>Мышеев Дмитрий Сергеевич</b>\n✅25.10.2025, Проект <b>ОИ766</b>: Добавлена запись в табель: <b>⚡ Работы по гарантии (Электрооборудование)</b>"
    ],
    [
        'type' => 'РК (новый формат)',
        'fn' => 'Шеф-монтаж',
        'message' => "<b>Тест тест тест</b>\n✅24.09.2025, Проект <b>ОИ711</b>: Добавлена запись в табель: <b>⚙️ Устранения замечаний РК (Раздел ТХ)</b>"
    ],
    [
        'type' => 'РК (старый формат)',
        'fn' => 'Шеф-монтаж',
        'message' => "<b>Тест тест тест</b>\n✅ Запись в табель от <b>22.09.2025</b>, в проект <b>ОИ795</b> о <b>🔧 Устранение замечаний РК (Шеф-монтаж) добавлена</b>"
    ],
    [
        'type' => 'РК с деталями',
        'fn' => 'Шеф-монтаж',
        'message' => "<b>Тест тест тест</b>\n✅24.09.2025, Проект <b>ОИ848</b>: Добавлена запись в табель: <b>📦 Устранения замечаний РК (Прочее оборудование) (ТЕСТ)</b>"
    ]
];

foreach ($testMessages as $idx => $test) {
    echo "========================================\n";
    echo "ТЕСТ #" . ($idx + 1) . ": {$test['type']}\n";
    echo "========================================\n";
    
    $parsed = $parseMethod->invoke($handler, $test['message'], $test['fn']);
    
    echo "Проект: " . ($parsed['project'] ?: '(пусто)') . "\n";
    echo "Дата: " . ($parsed['date'] ?: '(пусто)') . "\n";
    echo "Процесс: " . ($parsed['process'] ?: '(пусто)') . "\n";
    echo "Вид деятельности: " . ($parsed['vid'] ?: '(пусто)') . "\n";
    echo "Основная активность: " . ($parsed['osnovnaya'] ?: '(пусто)') . "\n";
    echo "Тип работ (tip1): " . ($parsed['tip1'] ?: '(пусто)') . "\n";
    echo "Тип работ 2 (tip2): " . ($parsed['tip2'] ?: '(пусто)') . "\n";
    echo "Тип работ 3 (tip3): " . ($parsed['tip3'] ?: '(пусто)') . "\n";
    
    // Проверка успешности
    if (!empty($parsed['vid']) && !empty($parsed['osnovnaya'])) {
        echo "✅ УСПЕШНО СПАРСЕНО\n";
    } else {
        echo "❌ ОШИБКА: vid или osnovnaya пустые\n";
    }
    
    echo "\n";
}
