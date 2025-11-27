<?php

/**
 * Server File Verification Script
 * Проверяет, какая версия файлов используется на сервере
 */

echo "=== Проверка файлов на сервере ===\n\n";

// Проверяем ProjectHandler
$projectHandlerFile = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';

if (file_exists($projectHandlerFile)) {
    $content = file_get_contents($projectHandlerFile);
    
    echo "1. Проверка ProjectHandler.php:\n";
    
    // Проверяем наличие исправления
    $hasFixedVersion = strpos($content, 'FIXED VERSION 2025-09-17') !== false;
    echo "   Исправленная версия: " . ($hasFixedVersion ? "✅ ДА" : "❌ НЕТ") . "\n";
    
    // Проверяем наличие try-catch блока
    $hasTryCatch = strpos($content, 'try {') !== false && strpos($content, 'getUserControlProjects') !== false;
    echo "   Try-catch блок: " . ($hasTryCatch ? "✅ ЕСТЬ" : "❌ НЕТ") . "\n";
    
    // Проверяем наличие метода getUserControlProjects
    $hasMethod = strpos($content, 'private function getUserControlProjects') !== false;
    echo "   Метод getUserControlProjects: " . ($hasMethod ? "✅ ЕСТЬ" : "❌ НЕТ") . "\n";
    
    // Показываем размер файла
    $fileSize = filesize($projectHandlerFile);
    echo "   Размер файла: {$fileSize} байт\n";
    
    // Показываем дату изменения
    $modTime = filemtime($projectHandlerFile);
    $modDate = date('Y-m-d H:i:s', $modTime);
    echo "   Дата изменения: {$modDate}\n";
    
} else {
    echo "❌ Файл ProjectHandler.php не найден!\n";
}

echo "\n2. Проверка GenericmessageCommand.php:\n";

$genericCommandFile = __DIR__ . '/CustomCommands/GenericmessageCommand.php';
if (file_exists($genericCommandFile)) {
    $content = file_get_contents($genericCommandFile);
    
    // Проверяем наличие отладочного логирования
    $hasDebugLogging = strpos($content, 'DEBUG_MANUAL_INPUT') !== false;
    echo "   Debug логирование: " . ($hasDebugLogging ? "✅ ЕСТЬ" : "❌ НЕТ") . "\n";
    
    // Проверяем обработку группового ввода
    $hasGroupHandler = strpos($content, 'handleManualGroupInputFromFile') !== false;
    echo "   Обработчик группового ввода: " . ($hasGroupHandler ? "✅ ЕСТЬ" : "❌ НЕТ") . "\n";
    
} else {
    echo "❌ Файл GenericmessageCommand.php не найден!\n";
}

echo "\n3. Проверка ManualInputStateManager.php:\n";

$stateManagerFile = __DIR__ . '/CustomCommands/Services/ManualInputStateManager.php';
if (file_exists($stateManagerFile)) {
    echo "   ManualInputStateManager: ✅ ЕСТЬ\n";
    
    // Быстрый тест
    require_once $stateManagerFile;
    try {
        $manager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
        echo "   Может создать экземпляр: ✅ ДА\n";
    } catch (Exception $e) {
        echo "   Может создать экземпляр: ❌ НЕТ - " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Файл ManualInputStateManager.php не найден!\n";
}

echo "\n4. Проверка логов:\n";

$logFile = __DIR__ . '/telegram_bot_error.log';
if (file_exists($logFile)) {
    echo "   Лог файл: ✅ ЕСТЬ\n";
    
    // Ищем последние ошибки
    $lines = file($logFile);
    $recentLines = array_slice($lines, -20);
    
    $hasProjectHandlerError = false;
    $hasFixedVersionLog = false;
    
    foreach ($recentLines as $line) {
        if (strpos($line, 'getUserControlProjects') !== false) {
            $hasProjectHandlerError = true;
        }
        if (strpos($line, 'FIXED VERSION 2025-09-17') !== false) {
            $hasFixedVersionLog = true;
        }
    }
    
    echo "   Ошибка ProjectHandler: " . ($hasProjectHandlerError ? "❌ ЕСТЬ" : "✅ НЕТ") . "\n";
    echo "   Лог исправленной версии: " . ($hasFixedVersionLog ? "✅ ЕСТЬ" : "❌ НЕТ") . "\n";
    
} else {
    echo "❌ Лог файл не найден!\n";
}

echo "\n=== ДИАГНОЗ ===\n\n";

// Читаем содержимое ProjectHandler снова для финального анализа
if (file_exists($projectHandlerFile)) {
    $content = file_get_contents($projectHandlerFile);
    $hasFixedVersion = strpos($content, 'FIXED VERSION 2025-09-17') !== false;
    $hasTryCatch = strpos($content, 'try {') !== false && strpos($content, 'getUserControlProjects') !== false;
    $hasMethod = strpos($content, 'private function getUserControlProjects') !== false;
    
    if ($hasFixedVersion && $hasTryCatch && $hasMethod) {
        echo "✅ Файл ProjectHandler исправлен правильно\n";
        echo "📋 Рекомендация: Попросите пользователя попробовать снова\n";
        echo "🔍 Если не работает, проверьте логи на наличие 'FIXED VERSION 2025-09-17'\n";
    } else {
        echo "❌ Файл ProjectHandler требует обновления\n";
        echo "📋 Рекомендация: Загрузите исправленную версию на сервер\n";
    }
} else {
    echo "❌ Критическая ошибка: ProjectHandler не найден\n";
}

echo "\n=== Проверка завершена ===\n";