<?php
/**
 * Complete diagnostic for Automation Прочее button issue
 * This will help identify exactly where the callback routing fails
 */

echo "=== ПОЛНАЯ ДИАГНОСТИКА ПРОБЛЕМЫ АВТОМАТИЗАЦИЯ ПРОЧЕЕ ===\n\n";

// Check all critical files and configurations
$tabelHandler = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');
$tabelEntry = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

echo "1. ПРОВЕРКА КОНФИГУРАЦИИ CALLBACK ROUTING:\n";

// Check if callback is in confirmation handler
$confirmationHandlerFound = false;
$lines = explode("\n", $tabelHandler);
for ($i = 690; $i < 710; $i++) {
    if (isset($lines[$i]) && strpos($lines[$i], 'cat_ШМР_Автом_Проч_yes') !== false) {
        echo "   ✅ Найден в обработчике подтверждения на строке " . ($i + 1) . "\n";
        echo "      Содержание: " . trim($lines[$i]) . "\n";
        $confirmationHandlerFound = true;
        break;
    }
}

if (!$confirmationHandlerFound) {
    echo "   ❌ НЕ НАЙДЕН в обработчике подтверждения\n";
}

// Check shortFormats mapping
echo "\n2. ПРОВЕРКА МАППИНГА В extractCategoryFromCallback:\n";
if (preg_match('/shortFormats\s*=\s*\[(.*?)\];/s', $tabelHandler, $matches)) {
    $shortFormatsContent = $matches[1];
    if (strpos($shortFormatsContent, 'cat_ШМР_Автом_Проч_yes') !== false) {
        echo "   ✅ Найден в shortFormats mapping\n";
    } else {
        echo "   ❌ НЕ НАЙДЕН в shortFormats mapping\n";
        echo "   🔍 Добавляем в shortFormats...\n";
        
        // Show current shortFormats content around automation
        $relevantLines = [];
        foreach (explode("\n", $shortFormatsContent) as $line) {
            if (strpos($line, 'Автом') !== false || strpos($line, 'ШМР') !== false) {
                $relevantLines[] = trim($line);
            }
        }
        echo "   Текущие ШМР маппинги:\n";
        foreach ($relevantLines as $line) {
            echo "      " . $line . "\n";
        }
    }
}

// Check handleNewProcheeConfirmation activity type detection
echo "\n3. ПРОВЕРКА ОПРЕДЕЛЕНИЯ ТИПА АКТИВНОСТИ:\n";
$activityTypePatterns = [
    'cat_ШМР_Автом_Проч_yes' => 'ШМР_Прочее_Автоматизация',
    'cat_ШМР_ЭлОб_Проч_yes' => 'ШМР_Прочее_Электрооборудование',
    'cat_ШМР_ПожСигн_Проч_yes' => 'ШМР_Прочее_ПожарнаяСигнализация',
    'cat_ШМР_ОхрСигн_Проч_yes' => 'ШМР_Прочее_ОхраннаяСигнализация',
    'cat_ШМР_ОтоплВент_Проч_yes' => 'ШМР_Прочее_ОтоплениеИВентиляция'
];

foreach ($activityTypePatterns as $callback => $activityType) {
    $callbackFound = strpos($tabelHandler, "'{$callback}'") !== false;
    $activityFound = strpos($tabelHandler, "'{$activityType}'") !== false;
    
    if ($callbackFound && $activityFound) {
        echo "   ✅ {$callback} → {$activityType}\n";
    } else {
        echo "   ❌ {$callback} → {$activityType}\n";
        if (!$callbackFound) echo "      Отсутствует callback\n";
        if (!$activityFound) echo "      Отсутствует activity type\n";
    }
}

// Check user-friendly names in getConfirmationMessage
echo "\n4. ПРОВЕРКА ПОЛЬЗОВАТЕЛЬСКИХ НАЗВАНИЙ:\n";
$userFriendlyMappings = [
    'ШМР_Прочее_Автоматизация' => 'Прочее (Автоматизация)',
    'ШМР_Прочее_Электрооборудование' => 'Прочее (Электрооборудование)',
    'ШМР_Прочее_ПожарнаяСигнализация' => 'Прочее (Пожарная сигнализация)',
    'ШМР_Прочее_ОхраннаяСигнализация' => 'Прочее (Охранная сигнализация)',
    'ШМР_Прочее_ОтоплениеИВентиляция' => 'Прочее (Отопление и вентиляция)'
];

foreach ($userFriendlyMappings as $internal => $friendly) {
    if (strpos($tabelEntry, "'{$internal}' => '{$friendly}'") !== false) {
        echo "   ✅ {$internal} → {$friendly}\n";
    } else {
        echo "   ❌ {$internal} → {$friendly}\n";
    }
}

// Check isActivityExecution method
echo "\n5. ПРОВЕРКА isActivityExecution МЕТОДА:\n";
if (preg_match('/function isActivityExecution.*?return\s+false;/s', $tabelHandler, $matches)) {
    $isActivityContent = $matches[0];
    if (strpos($isActivityContent, 'cat_ШМР_Автом_Проч_yes') !== false) {
        echo "   ⚠️  cat_ШМР_Автом_Проч_yes найден в isActivityExecution (может блокировать)\n";
    } else {
        echo "   ✅ cat_ШМР_Автом_Проч_yes НЕ найден в isActivityExecution (правильно)\n";
    }
}

echo "\n=== ВОЗМОЖНЫЕ ПРИЧИНЫ ПРОБЛЕМЫ ===\n";
echo "1. Callback не доходит до handleNewProcheeConfirmation\n";
echo "2. Исключение внутри handleNewProcheeConfirmation\n";
echo "3. Отсутствуют данные conversation (custom text)\n";
echo "4. Проблема с данными пользователя (FIO, functional_direction)\n";
echo "5. Callback трансформируется неправильно в extractCategoryFromCallback\n";

echo "\n=== РЕКОМЕНДАЦИИ ===\n";
echo "Если все проверки показывают ✅, то проблема в runtime execution.\n";
echo "Нужно добавить детальное логирование в начало handleCallback метода.\n";

echo "\nДиагностика завершена.\n";