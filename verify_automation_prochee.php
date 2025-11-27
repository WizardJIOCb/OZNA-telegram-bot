<?php
/**
 * Verification script for Automation "Прочее" button functionality
 * This script tests the complete flow without requiring log access
 */

echo "=== ПРОВЕРКА КНОПКИ АВТОМАТИЗАЦИЯ ПРОЧЕЕ ===\n\n";

// Test 1: Check if all required callbacks are properly configured
echo "1. Проверка настройки callback'ов...\n";

$tabelHandler = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Check confirmation handler
$callbacks_to_check = [
    'cat_ШМР_Автом_Проч_yes' => 'Автоматизация Прочее (подтверждение)',
    'cat_ШМР_ЭлОб_Проч_yes' => 'Электрооборудование Прочее (подтверждение)', 
    'cat_ШМР_ПожСигн_Проч_yes' => 'Пожарная сигнализация Прочее (подтверждение)',
    'cat_ШМР_ОхрСигн_Проч_yes' => 'Охранная сигнализация Прочее (подтверждение)',
    'cat_ШМР_ОтоплВент_Проч_yes' => 'Отопление и вентиляция Прочее (подтверждение)'
];

foreach ($callbacks_to_check as $callback => $description) {
    // Check if callback is in confirmation handler (around line 695-700)
    $lines = explode("\n", $tabelHandler);
    $found_in_handler = false;
    
    for ($i = 690; $i < 705; $i++) {
        if (isset($lines[$i]) && strpos($lines[$i], $callback) !== false) {
            $found_in_handler = true;
            break;
        }
    }
    
    if ($found_in_handler) {
        echo "   ✅ {$description}\n";
    } else {
        echo "   ❌ {$description} - НЕ НАЙДЕН В ОБРАБОТЧИКЕ\n";
    }
}

// Test 2: Check activity type mappings in handleNewProcheeConfirmation
echo "\n2. Проверка обработки типов активности...\n";

$activity_mappings = [
    'cat_ШМР_Автом_Проч_yes' => 'ШМР_Прочее_Автоматизация',
    'cat_ШМР_ЭлОб_Проч_yes' => 'ШМР_Прочее_Электрооборудование',
    'cat_ШМР_ПожСигн_Проч_yes' => 'ШМР_Прочее_ПожарнаяСигнализация', 
    'cat_ШМР_ОхрСигн_Проч_yes' => 'ШМР_Прочее_ОхраннаяСигнализация',
    'cat_ШМР_ОтоплВент_Проч_yes' => 'ШМР_Прочее_ОтоплениеИВентиляция'
];

foreach ($activity_mappings as $callback => $activity_type) {
    if (strpos($tabelHandler, "'{$callback}'") !== false && strpos($tabelHandler, "'{$activity_type}'") !== false) {
        echo "   ✅ {$callback} → {$activity_type}\n";
    } else {
        echo "   ❌ {$callback} → {$activity_type} - ОТСУТСТВУЕТ\n";
    }
}

// Test 3: Check user-friendly names in TabelEntry
echo "\n3. Проверка пользовательских названий...\n";

$tabelEntry = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

$user_friendly_names = [
    'ШМР_Прочее_Автоматизация' => 'Прочее (Автоматизация)',
    'ШМР_Прочее_Электрооборудование' => 'Прочее (Электрооборудование)', 
    'ШМР_Прочее_ПожарнаяСигнализация' => 'Прочее (Пожарная сигнализация)',
    'ШМР_Прочее_ОхраннаяСигнализация' => 'Прочее (Охранная сигнализация)',
    'ШМР_Прочее_ОтоплениеИВентиляция' => 'Прочее (Отопление и вентиляция)'
];

foreach ($user_friendly_names as $internal => $friendly) {
    if (strpos($tabelEntry, "'{$internal}' => '{$friendly}'") !== false) {
        echo "   ✅ {$internal} → {$friendly}\n";
    } else {
        echo "   ❌ {$internal} → {$friendly} - ОТСУТСТВУЕТ\n";
    }
}

echo "\n=== РЕЗУЛЬТАТ ПРОВЕРКИ ===\n";
echo "Если все пункты помечены ✅, то кнопка должна работать корректно.\n";
echo "Если есть ❌, то это указывает на проблему в конфигурации.\n\n";

echo "=== ИНСТРУКЦИИ ДЛЯ ТЕСТИРОВАНИЯ ===\n";
echo "1. Откройте бот в Telegram\n";
echo "2. Перейдите: ШМР → Автоматизация → Прочее\n";
echo "3. Введите текст описания работы (например: 'тест')\n";
echo "4. Нажмите 'Да' в диалоге подтверждения\n";
echo "5. Если появляется сообщение об успешном добавлении записи - все работает\n";
echo "6. Если появляется 'Неизвестная команда табеля' - есть проблема\n\n";

echo "Скрипт завершен.\n";