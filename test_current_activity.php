<?php

echo "🔥 ТЕСТИРОВАНИЕ ТЕКУЩЕЙ ДЕЯТЕЛЬНОСТИ 🔥\n";
echo "====================================\n\n";

// Проверяем MessageService
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

$messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();

echo "📁 MessageService.php:\n";
try {
    $currentActivityKeyboard = $messageService->createCurrentActivityKeyboard();
    echo "✅ Метод createCurrentActivityKeyboard() успешно создан\n";
    echo "   Количество кнопок: " . count($currentActivityKeyboard) . "\n";
    
    foreach ($currentActivityKeyboard as $row) {
        foreach ($row as $button) {
            echo "   - " . $button['text'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Ошибка в createCurrentActivityKeyboard(): " . $e->getMessage() . "\n";
}

// Проверяем TabelHandler
echo "\n📁 TabelHandler.php:\n";
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';

try {
    $reflection = new ReflectionClass('\Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler');
    $activityTypesProperty = $reflection->getProperty('activityTypes');
    $activityTypesProperty->setAccessible(true);
    
    // Создаем фиктивные зависимости
    $databaseService = new \stdClass();
    $projectSelector = new \stdClass();
    $tabelEntry = new \stdClass();
    
    $handler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler(
        $databaseService, $messageService, $projectSelector, $tabelEntry
    );
    
    $activityTypes = $activityTypesProperty->getValue($handler);
    echo "✅ TabelHandler успешно создан\n";
    echo "   Количество типов активности: " . count($activityTypes) . "\n";
    echo "   Новые типы активности:\n";
    
    $newTypes = ['МедОсмотр', 'Аттестация', 'ПодготовкаТКП', 'Общая'];
    foreach ($newTypes as $type) {
        if (in_array($type, $activityTypes)) {
            echo "   ✅ " . $type . "\n";
        } else {
            echo "   ❌ " . $type . " - НЕ НАЙДЕН\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка в TabelHandler: " . $e->getMessage() . "\n";
}

// Проверяем backup файл
echo "\n📁 CallbackqueryCommand_Original_Backup.php:\n";
$backupFile = __DIR__ . '/CustomCommands/CallbackqueryCommand_Original_Backup.php';
$backupContent = file_get_contents($backupFile);

$newCases = [
    'Табель_МедОсмотр',
    'Табель_Аттестация', 
    'Табель_ПодготовкаТКП',
    'Табель_Общая',
    'Табель_Текущая_ШифрПроекта_ОИ834'
];

foreach ($newCases as $case) {
    if (strpos($backupContent, 'case("' . $case . '")') !== false) {
        echo "   ✅ Case '{$case}' найден\n";
    } else {
        echo "   ❌ Case '{$case}' НЕ найден\n";
    }
}

echo "\n====================================\n";
echo "🎯 ЧТО ТЕПЕРЬ НУЖНО СДЕЛАТЬ:\n\n";
echo "1. Зайти в Telegram бота\n";
echo "2. Написать команду 'табель'\n";
echo "3. Выбрать 'Текущая деятельность'\n";
echo "4. Выбрать дату (Сегодня или Вчера)\n";
echo "5. Выбрать проект (например, ОИ834)\n";
echo "6. Должны появиться новые кнопки:\n";
echo "   - 🏥 Медицинский осмотр\n";
echo "   - 📋 Аттестация\n";
echo "   - 📝 Подготовка материала к ТКП\n";
echo "   - 📊 Общая\n";
echo "7. Попробовать нажать на каждую кнопку и подтвердить\n";
echo "====================================\n";

?>