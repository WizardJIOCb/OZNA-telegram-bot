<?php

/**
 * Manual Input Diagnostic Test
 * Проверяет полный путь ручного ввода ID группы
 */

echo "=== Диагностика проблемы с ID группы ===\n\n";

// Include required files
require_once 'config.php';
require_once 'CustomCommands/Services/ManualInputStateManager.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager;

$stateManager = new ManualInputStateManager();

echo "1. Проверка основной функциональности...\n";

// Test user ID (используем реальный ID из логов)
$testUserId = 5261465966;
$testProject = "ОИ789";

// Test 1: Set state
$result = $stateManager->setState($testUserId, 'group_id', $testProject);
echo "   Установка состояния: " . ($result ? "✅ УСПЕХ" : "❌ ОШИБКА") . "\n";

// Test 2: Check state
$isAwaiting = $stateManager->isAwaitingGroupInput($testUserId);
echo "   Проверка состояния: " . ($isAwaiting ? "✅ ОЖИДАЕТ" : "❌ НЕ ОЖИДАЕТ") . "\n";

// Test 3: Get project
$projectCode = $stateManager->getProjectCode($testUserId);
echo "   Код проекта: " . ($projectCode ? "✅ {$projectCode}" : "❌ НЕТ") . "\n";

echo "\n2. Проверка файлового хранилища...\n";

$stateDir = __DIR__ . '/data/manual_input_states';
$stateFile = $stateDir . "/user_{$testUserId}.json";

echo "   Путь к файлу: {$stateFile}\n";
echo "   Директория существует: " . (is_dir($stateDir) ? "✅ ДА" : "❌ НЕТ") . "\n";
echo "   Файл существует: " . (file_exists($stateFile) ? "✅ ДА" : "❌ НЕТ") . "\n";

if (file_exists($stateFile)) {
    $content = file_get_contents($stateFile);
    $data = json_decode($content, true);
    echo "   Содержимое: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n3. Симуляция пользовательского сценария...\n";

// Очистим состояние
$stateManager->clearState($testUserId);
echo "   Состояние очищено\n";

// Установим новое состояние (как это делает ProjectHandler)
$success = $stateManager->setState($testUserId, 'group_id', 'ОИ789');
echo "   Новое состояние установлено: " . ($success ? "✅ ДА" : "❌ НЕТ") . "\n";

// Проверим, что GenericmessageCommand может его обнаружить
$canDetect = $stateManager->isAwaitingGroupInput($testUserId);
echo "   GenericmessageCommand может обнаружить: " . ($canDetect ? "✅ ДА" : "❌ НЕТ") . "\n";

// Симулируем ввод группы
$testGroupId = "-1001234567890";
echo "   Тестовый ID: {$testGroupId}\n";

// Проверяем валидацию
$isValid = preg_match('/^-100\\d+$/', $testGroupId);
echo "   ID валиден: " . ($isValid ? "✅ ДА" : "❌ НЕТ") . "\n";

// Очистим состояние после теста
$stateManager->clearState($testUserId);
echo "   Тестовое состояние очищено\n";

echo "\n4. Проверка логов...\n";

$logFile = __DIR__ . '/telegram_bot_error.log';
if (file_exists($logFile)) {
    echo "   Лог файл существует: ✅ ДА\n";
    
    // Проверим последние записи о manual input
    $command = "cd " . __DIR__ . "; Get-Content telegram_bot_error.log | Select-Object -Last 50 | Where-Object { \$_ -match \"DEBUG_MANUAL_INPUT|ProjectHandler.*FIXED\" }";
    echo "   Команда поиска логов: {$command}\n";
} else {
    echo "   Лог файл существует: ❌ НЕТ\n";
}

echo "\n=== Выводы ===\n\n";

echo "Если все тесты показывают ✅, то проблема не в коде ручного ввода.\n";
echo "Возможные причины:\n";
echo "1. Файл ProjectHandler на сервере не обновился\n";
echo "2. Есть кэширование PHP файлов\n";
echo "3. Пользователь не дошел до настроек проекта из-за ошибки\n\n";

echo "Попросите пользователя повторить попытку и проверьте логи на наличие:\n";
echo "- 'ProjectHandler: FIXED VERSION 2025-09-17'\n";
echo "- 'DEBUG_MANUAL_INPUT' записей\n\n";

echo "=== Тест завершен ===\n";