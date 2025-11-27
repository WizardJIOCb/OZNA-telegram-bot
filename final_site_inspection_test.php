<?php

echo "=== FINAL SITE INSPECTION TEST ===\n\n";

// Test 1: PHP syntax check
echo "🔄 ТЕСТ 1: Проверка синтаксиса PHP\n";
echo "==================================\n";

$key_files = [
    'c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php',
    'c:\ozna_bot\CustomCommands\Services\MessageService.php',
    'c:\ozna_bot\CustomCommands\Services\TabelEntry.php'
];

$syntax_ok = true;
foreach ($key_files as $file) {
    $output = [];
    $return_var = 0;
    exec("php -l \"$file\" 2>&1", $output, $return_var);
    
    if ($return_var === 0) {
        echo "✅ " . basename($file) . " - синтаксис корректен\n";
    } else {
        echo "❌ " . basename($file) . " - ошибки синтаксиса:\n";
        foreach ($output as $line) {
            echo "   $line\n";
        }
        $syntax_ok = false;
    }
}

if (!$syntax_ok) {
    echo "\n❌ ОБНАРУЖЕНЫ ОШИБКИ СИНТАКСИСА - БОТ НЕ БУДЕТ РАБОТАТЬ!\n";
    exit(1);
}

// Test 2: Check callback mapping
echo "\n🔄 ТЕСТ 2: Проверка callback mapping\n";
echo "===================================\n";

$tabel_handler_content = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');

$callback_mapping_found = strpos($tabel_handler_content, "'cat_ШМР_ПровМонтПлощ' => 'Табель_ПроверкаМонтажнойПлощадки'") !== false;
$handler_found = strpos($tabel_handler_content, "if (\$category === 'Табель_ПроверкаМонтажнойПлощадки')") !== false;

if ($callback_mapping_found) {
    echo "✅ Callback mapping найден\n";
} else {
    echo "❌ Callback mapping НЕ найден\n";
}

if ($handler_found) {
    echo "✅ Handler method найден\n";
} else {
    echo "❌ Handler method НЕ найден\n";
}

// Test 3: Check createSiteInspectionKeyboard method
echo "\n🔄 ТЕСТ 3: Проверка метода createSiteInspectionKeyboard\n";
echo "======================================================\n";

$message_service_content = file_get_contents('c:\ozna_bot\CustomCommands\Services\MessageService.php');
$keyboard_method_found = strpos($message_service_content, "public function createSiteInspectionKeyboard()") !== false;

if ($keyboard_method_found) {
    echo "✅ Метод createSiteInspectionKeyboard найден\n";
} else {
    echo "❌ Метод createSiteInspectionKeyboard НЕ найден\n";
}

// Test 4: Check activity types in TabelEntry
echo "\n🔄 ТЕСТ 4: Проверка activity types в TabelEntry\n";
echo "===============================================\n";

$tabel_entry_content = file_get_contents('c:\ozna_bot\CustomCommands\Services\TabelEntry.php');
$activity_type_found = strpos($tabel_entry_content, "ШМР_ПроверкаМонтажнойПлощадки") !== false;

if ($activity_type_found) {
    echo "✅ Activity types найдены\n";
} else {
    echo "❌ Activity types НЕ найдены\n";
}

// Final result
echo "\n📋 ИТОГОВЫЙ РЕЗУЛЬТАТ\n";
echo "====================\n";

if ($syntax_ok && $callback_mapping_found && $handler_found && $keyboard_method_found && $activity_type_found) {
    echo "🎉 ВСЕ ТЕСТЫ ПРОЙДЕНЫ УСПЕШНО!\n";
    echo "\nРеализация 'Проверка монтажной площадки' должна работать корректно.\n";
    echo "\nДля тестирования:\n";
    echo "1. Перейдите в бот\n";
    echo "2. Выберите Шеф-монтаж\n";
    echo "3. Выберите дату и проект\n";
    echo "4. Выберите 'Подготовительные работы'\n";
    echo "5. Нажмите 'Проверка монтажной площадки'\n";
    echo "6. Должно появиться подменю с опциями\n";
} else {
    echo "❌ НЕ ВСЕ ТЕСТЫ ПРОЙДЕНЫ!\n";
    echo "\nПроблемы:\n";
    if (!$syntax_ok) echo "- Ошибки синтаксиса в файлах\n";
    if (!$callback_mapping_found) echo "- Отсутствует callback mapping\n";
    if (!$handler_found) echo "- Отсутствует handler method\n";
    if (!$keyboard_method_found) echo "- Отсутствует метод клавиатуры\n";
    if (!$activity_type_found) echo "- Отсутствуют activity types\n";
}

?>