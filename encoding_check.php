<?php

echo "=== ПРОВЕРКА КОДИРОВКИ CALLBACK DATA ===\n\n";

// Test the actual button that was pressed
echo "🔍 АНАЛИЗ ПРОБЛЕМЫ КОДИРОВКИ\n";
echo "============================\n";

$corrupted_callback = 'cat_РЁРњР _РџРѕРґРіР Р°Р±';
$expected_callback = 'cat_ШМР_ПодгРаб';

echo "Полученный callback (поврежденный): '{$corrupted_callback}'\n";
echo "Ожидаемый callback: '{$expected_callback}'\n";
echo "Hex поврежденного: " . bin2hex($corrupted_callback) . "\n";
echo "Hex ожидаемого: " . bin2hex($expected_callback) . "\n";

// Check if this is a known encoding issue
echo "\n🔧 ПРОВЕРКА ИСПРАВЛЕНИЯ КОДИРОВКИ\n";
echo "==================================\n";

// Load TabelHandler to test encoding fixes
require_once 'c:\ozna_bot\CustomCommands\Services\CallbackHandlerInterface.php';
require_once 'c:\ozna_bot\CustomCommands\Services\DatabaseService.php';
require_once 'c:\ozna_bot\CustomCommands\Services\MessageService.php';
require_once 'c:\ozna_bot\CustomCommands\Services\ProjectSelector.php';
require_once 'c:\ozna_bot\CustomCommands\Services\TabelEntry.php';
require_once 'c:\ozna_bot\CustomCommands\Services\AdminService.php';
require_once 'c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php';

echo "✅ Файлы загружены успешно\n";

// Check if TabelHandler can handle the corrupted callback
$tabel_handler_content = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');

// Look for encoding fix methods
$has_encoding_fix = strpos($tabel_handler_content, 'fixKnownCyrillicPatterns') !== false;
$has_utf8_fix = strpos($tabel_handler_content, 'ensureUtf8Encoding') !== false;

if ($has_encoding_fix) {
    echo "✅ Найден метод fixKnownCyrillicPatterns\n";
} else {
    echo "❌ Метод fixKnownCyrillicPatterns НЕ найден\n";
}

if ($has_utf8_fix) {
    echo "✅ Найден метод ensureUtf8Encoding\n";
} else {
    echo "❌ Метод ensureUtf8Encoding НЕ найден\n";
}

// Test the preparatory works button specifically
echo "\n🔍 ПРОВЕРКА КНОПКИ 'ПОДГОТОВИТЕЛЬНЫЕ РАБОТЫ'\n";
echo "=============================================\n";

$preparatory_callback = 'cat_ШМР_ПодгРаб';
echo "Правильный callback: '{$preparatory_callback}'\n";
echo "Hex: " . bin2hex($preparatory_callback) . "\n";

// Check if the button exists in MessageService
$message_service_content = file_get_contents('c:\ozna_bot\CustomCommands\Services\MessageService.php');
$button_found = strpos($message_service_content, "cat_ШМР_ПодгРаб") !== false;

if ($button_found) {
    echo "✅ Кнопка найдена в MessageService\n";
} else {
    echo "❌ Кнопка НЕ найдена в MessageService\n";
}

// Check if mapping exists
$mapping_found = strpos($tabel_handler_content, "cat_ШМР_ПодгРаб") !== false;

if ($mapping_found) {
    echo "✅ Mapping найден в TabelHandler\n";
} else {
    echo "❌ Mapping НЕ найден в TabelHandler\n";
}

echo "\n💡 РЕКОМЕНДАЦИИ\n";
echo "===============\n";

if (!$has_encoding_fix || !$has_utf8_fix) {
    echo "❗ КРИТИЧЕСКАЯ ПРОБЛЕМА: Отсутствуют методы исправления кодировки!\n";
    echo "Необходимо добавить методы для исправления поврежденной кодировки.\n";
} elseif (!$button_found || !$mapping_found) {
    echo "❗ ПРОБЛЕМА: Кнопка или mapping отсутствуют!\n";
    echo "Необходимо проверить создание кнопки и ее обработку.\n";
} else {
    echo "ℹ️ Все компоненты на месте. Проблема может быть в:\n";
    echo "1. Кодировке при передаче данных от Telegram\n";
    echo "2. Некорректной обработке UTF-8\n";
    echo "3. Проблемах на сервере\n";
    echo "\nРекомендуется проверить исправление кодировки в реальном времени.\n";
}

?>