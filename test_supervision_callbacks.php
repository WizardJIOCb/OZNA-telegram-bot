<?php
// Manual webhook test for supervision menu
// This script simulates what happens when you click the supervision menu buttons

header('Content-Type: text/plain; charset=utf-8');

echo "=== РУЧНАЯ ИМИТАЦИЯ НАЖАТИЯ КНОПОК ===\n";
echo "Время: " . date('Y-m-d H:i:s') . "\n\n";

// Simulate the callback that should be sent when clicking "Подготовительные работы"
$testCallbacks = [
    'cat_supervision_prep' => 'Новый тестовый callback (английский)',
    'cat_ШМР_ПодгРаб' => 'Оригинальный callback (кириллица)',
    'cat_РЁРњР _РџРѕРґРіР Р°Р±' => 'Corrupted callback (как получает сервер)'
];

echo "Тестируем обработку различных callback'ов:\n";
echo "===========================================\n\n";

foreach ($testCallbacks as $callbackData => $description) {
    echo "🔍 ТЕСТ: $description\n";
    echo "Callback: '$callbackData'\n";
    echo "Hex: " . bin2hex($callbackData) . "\n";
    echo "Длина: " . strlen($callbackData) . " байт\n";
    
    // Test 1: Early exit check in CallbackqueryCommand
    $passesEarlyExit = (
        strpos($callbackData, 'category_') === 0 || 
        strpos($callbackData, 'adm_req_') === 0 || 
        strpos($callbackData, 'acc_lvl_') === 0 || 
        strpos($callbackData, 'cat_Таб') === 0 ||
        strpos($callbackData, 'cat_РЁРњР ') === 0 ||
        strpos($callbackData, 'cat_supervision_') === 0 ||
        strpos($callbackData, 'proj_ctrl_') === 0 ||
        strpos($callbackData, 'proj_list_') === 0 ||
        strpos($callbackData, 'cat_Гарантии') === 0
    );
    
    echo "Проходит early exit: " . ($passesEarlyExit ? "✅ ДА" : "❌ НЕТ") . "\n";
    
    if ($passesEarlyExit) {
        // Test 2: TabelHandler canHandle check
        $canHandlePatterns = [
            'category_Табель' => strpos($callbackData, 'category_Табель') === 0,
            'cat_ШМР_' => strpos($callbackData, 'cat_ШМР_') === 0,
            'cat_supervision_' => strpos($callbackData, 'cat_supervision_') === 0,
            'cat_Таб' => strpos($callbackData, 'cat_Таб') === 0
        ];
        
        $canHandle = false;
        echo "TabelHandler patterns:\n";
        foreach ($canHandlePatterns as $pattern => $match) {
            echo "  • $pattern: " . ($match ? "✅" : "❌") . "\n";
            if ($match) $canHandle = true;
        }
        
        echo "TabelHandler может обработать: " . ($canHandle ? "✅ ДА" : "❌ НЕТ") . "\n";
        
        if ($canHandle) {
            // Test 3: Category extraction
            if (strpos($callbackData, 'cat_') === 0) {
                $extractedCategory = substr($callbackData, strlen('cat_'));
                echo "Извлеченная категория: '$extractedCategory'\n";
                echo "Результат: ✅ ДОЛЖНО РАБОТАТЬ\n";
            } else {
                echo "Результат: ❌ НЕ CALLBACK ФОРМАТ\n";
            }
        } else {
            echo "Результат: ❌ HANDLER НЕ НАЙДЕН\n";
        }
    } else {
        echo "Результат: ❌ ЗАБЛОКИРОВАНО EARLY EXIT\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

echo "🎯 ФИНАЛЬНЫЕ РЕКОМЕНДАЦИИ:\n";
echo "==========================\n";
echo "1. Если 'cat_supervision_prep' показывает '✅ ДОЛЖНО РАБОТАТЬ' - используйте его для тестирования\n";
echo "2. Если все показывают ❌ - проблема в логике обработки\n";
echo "3. Если локально работает, а в боте нет - проблема с обновлением файлов на сервере\n\n";

echo "📋 ИНСТРУКЦИЯ ДЛЯ ТЕСТИРОВАНИЯ:\n";
echo "===============================\n";
echo "1. Загрузите обновленные файлы на сервер:\n";
echo "   - CustomCommands/CallbackqueryCommand.php\n";
echo "   - CustomCommands/Services/MessageService.php\n";
echo "   - CustomCommands/Handlers/TabelHandler.php\n";
echo "2. Перезапустите PHP/веб-сервер если возможно\n";
echo "3. Попробуйте нажать кнопку 'Подготовительные работы'\n";
echo "4. Если не работает - используйте команду /start для проверки общей работоспособности бота\n";
?>