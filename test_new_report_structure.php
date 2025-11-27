<?php
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Custome/DB.php';

echo "====================================\n";
echo "ТЕСТ НОВОЙ СТРУКТУРЫ ОТЧЕТА\n";
echo "====================================\n\n";

$db = CustomCommands\Services\DatabaseService::getInstance();
$msgService = CustomCommands\Services\MessageService::getInstance();
$adminService = CustomCommands\Services\AdminService::getInstance();
$handler = new CustomCommands\Handlers\ProjectHandler($db, $msgService, $adminService);

$reflection = new ReflectionClass('CustomCommands\\Handlers\\ProjectHandler');
$parseMethod = $reflection->getMethod('parseReportEntry');
$parseMethod->setAccessible(true);

$testMessages = [
    [
        'type' => 'Мобильная бригада - Электромонтажные',
        'fn' => 'Мобильная бригада',
        'message' => "<b>Постовский Сергей Николаевич</b>\n✅25.10.2025, Проект <b>ОИ846</b>: Добавлена запись в табель: <b>⚡️Электромонтажные работы (Досборка)</b>"
    ],
    [
        'type' => 'Пусконаладка - Изменение схемы',
        'fn' => 'Пусконаладка',
        'message' => "<b>Бабурин Дмитрий Николаевич</b>\n✅26.10.2025, Проект <b>БКНС120</b>: Добавлена запись в табель: <b>🔀 Изменение схемы подключения (Электрооборудование, Дополнительные работы)</b>"
    ],
    [
        'type' => 'Шеф-монтаж - Прочее',
        'fn' => 'Шеф-монтаж',
        'message' => "<b>Коземаслов Сергей Васильевич</b>\n✅26.10.2025, Проект <b>ОИ779</b>: Добавлена запись в табель: <b>📝 Прочее (Технологическое оборудование) (Монтаж НА)</b>"
    ],
    [
        'type' => 'Простой - 2 уровня',
        'fn' => 'Пусконаладка',
        'message' => "<b>Тест</b>\n😨 ⚠️ 01.10.2025, Проект <b>ОИ796</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⚠️ ⏸️ Простой (Готовность Заказчика (Отсутствие рабочих сред))</b>"
    ],
    [
        'type' => 'Работы по гарантии',
        'fn' => 'Пусконаладка',
        'message' => "<b>Мышеев Дмитрий Сергеевич</b>\n✅25.10.2025, Проект <b>ОИ766</b>: Добавлена запись в табель: <b>⚡ Работы по гарантии (Электрооборудование)</b>"
    ]
];

echo "НОВЫЕ ЗАГОЛОВКИ КОЛОНОК:\n";
echo "1. Проект\n";
echo "2. Дата\n";
echo "3. Процесс (ФН)\n";
echo "4. Основной вид работ\n";
echo "5. Подтип работ\n";
echo "6. Детали\n";
echo "7. Примечание\n";
echo "8. Фамилия\n\n";

foreach ($testMessages as $idx => $test) {
    echo "========================================\n";
    echo "ТЕСТ #" . ($idx + 1) . ": {$test['type']}\n";
    echo "========================================\n";
    
    $parsed = $parseMethod->invoke($handler, $test['message'], $test['fn']);
    
    echo "Проект: " . ($parsed['project'] ?: '(пусто)') . "\n";
    echo "Дата: " . ($parsed['date'] ?: '(пусто)') . "\n";
    echo "Процесс (ФН): " . ($parsed['process'] ?: '(пусто)') . "\n";
    echo "Основной вид работ: " . ($parsed['osnovnaya'] ?: '(пусто)') . "\n";
    echo "Подтип работ: " . ($parsed['tip1'] ?: '(пусто)') . "\n";
    echo "Детали: " . ($parsed['tip2'] ?: '(пусто)') . "\n";
    echo "Примечание: " . ($parsed['tip3'] ?: '(пусто)') . "\n";
    echo "\n";
}
