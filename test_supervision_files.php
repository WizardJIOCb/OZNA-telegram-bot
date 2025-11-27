<?php
// Simple supervision menu diagnostics without API calls
header('Content-Type: text/plain; charset=utf-8');

echo "=== ДИАГНОСТИКА МЕНЮ 'ШЕФ-МОНТАЖНЫЕ РАБОТЫ' ===\n";
echo "Время: " . date('Y-m-d H:i:s') . "\n\n";

// Test 1: Check if MessageService exists and has the right method
echo "Тест 1: Проверка MessageService\n";
echo "================================\n";
$messageServiceFile = __DIR__ . '/CustomCommands/Services/MessageService.php';
if (file_exists($messageServiceFile)) {
    echo "✅ MessageService.php найден\n";
    
    $content = file_get_contents($messageServiceFile);
    
    if (strpos($content, 'createSupervisionWorksKeyboard') !== false) {
        echo "✅ Метод createSupervisionWorksKeyboard найден\n";
        
        // Extract the keyboard definition
        if (preg_match('/function createSupervisionWorksKeyboard.*?return \[(.*?)\];/s', $content, $matches)) {
            echo "✅ Определение клавиатуры найдено\n";
            
            // Check for our new callback
            if (strpos($content, 'cat_supervision_prep') !== false) {
                echo "✅ Новый callback cat_supervision_prep найден\n";
            } else {
                echo "❌ Новый callback cat_supervision_prep НЕ НАЙДЕН\n";
            }
            
            // Check for old callback
            if (strpos($content, 'cat_ШМР_ПодгРаб') !== false) {
                echo "✅ Старый callback cat_ШМР_ПодгРаб найден\n";
            } else {
                echo "❌ Старый callback cat_ШМР_ПодгРаб НЕ НАЙДЕН\n";
            }
        } else {
            echo "❌ Не удалось извлечь определение клавиатуры\n";
        }
    } else {
        echo "❌ Метод createSupervisionWorksKeyboard НЕ НАЙДЕН\n";
    }
} else {
    echo "❌ MessageService.php НЕ НАЙДЕН\n";
}

echo "\n";

// Test 2: Check TabelHandler canHandle method
echo "Тест 2: Проверка TabelHandler\n";
echo "=============================\n";
$tabelHandlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
if (file_exists($tabelHandlerFile)) {
    echo "✅ TabelHandler.php найден\n";
    
    $content = file_get_contents($tabelHandlerFile);
    
    // Check for canHandle method
    if (strpos($content, 'function canHandle') !== false) {
        echo "✅ Метод canHandle найден\n";
        
        // Check for supervision callbacks support
        if (strpos($content, "str_starts_with(\$callbackData, 'cat_ШМР_')") !== false) {
            echo "✅ Поддержка cat_ШМР_ найдена\n";
        } else {
            echo "❌ Поддержка cat_ШМР_ НЕ НАЙДЕНА\n";
        }
        
        if (strpos($content, "str_starts_with(\$callbackData, 'cat_supervision_')") !== false) {
            echo "✅ Поддержка cat_supervision_ найдена\n";
        } else {
            echo "❌ Поддержка cat_supervision_ НЕ НАЙДЕНА\n";
        }
    } else {
        echo "❌ Метод canHandle НЕ НАЙДЕН\n";
    }
    
    // Check for extractCategoryFromCallback method
    if (strpos($content, 'cat_supervision_prep') !== false) {
        echo "✅ Поддержка cat_supervision_prep найдена\n";
    } else {
        echo "❌ Поддержка cat_supervision_prep НЕ НАЙДЕНА\n";
    }
} else {
    echo "❌ TabelHandler.php НЕ НАЙДЕН\n";
}

echo "\n";

// Test 3: Check CallbackqueryCommand early exit
echo "Тест 3: Проверка CallbackqueryCommand\n";
echo "=====================================\n";
$callbackCommandFile = __DIR__ . '/CustomCommands/CallbackqueryCommand.php';
if (file_exists($callbackCommandFile)) {
    echo "✅ CallbackqueryCommand.php найден\n";
    
    $content = file_get_contents($callbackCommandFile);
    
    // Check for our corruption fix
    if (strpos($content, "strpos(\$callback_data, 'cat_РЁРњР') !== 0") !== false) {
        echo "✅ Фикс для corrupted ШМР найден\n";
    } else {
        echo "❌ Фикс для corrupted ШМР НЕ НАЙДЕН\n";
    }
    
    // Check for supervision support
    if (strpos($content, "strpos(\$callback_data, 'cat_supervision_') !== 0") !== false) {
        echo "✅ Поддержка cat_supervision_ найдена\n";
    } else {
        echo "❌ Поддержка cat_supervision_ НЕ НАЙДЕНА\n";
    }
} else {
    echo "❌ CallbackqueryCommand.php НЕ НАЙДЕН\n";
}

echo "\n";

// Test 4: Check recent log activity
echo "Тест 4: Проверка активности логов\n";
echo "=================================\n";
$logFile = __DIR__ . '/telegram_bot_error.log';
if (file_exists($logFile)) {
    echo "✅ Лог файл найден\n";
    echo "Размер: " . number_format(filesize($logFile)) . " байт\n";
    echo "Последнее изменение: " . date('Y-m-d H:i:s', filemtime($logFile)) . "\n";
    
    // Get last few lines
    try {
        $command = 'tail -n 5 "' . $logFile . '"';
        $lastLines = shell_exec($command);
        if ($lastLines) {
            echo "Последние 5 строк:\n";
            echo "---\n";
            echo $lastLines;
            echo "---\n";
        } else {
            echo "❌ Не удалось прочитать последние строки\n";
        }
    } catch (Exception $e) {
        echo "❌ Ошибка чтения лога: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Лог файл НЕ НАЙДЕН\n";
}

echo "\n=== ИТОГИ ===\n";
echo "1. Если все тесты ✅ - проблема в том, что сервер не обновляет файлы\n";
echo "2. Если есть ❌ - нужно исправить указанные проблемы\n";
echo "3. Далее нужно загрузить файлы на сервер и попробовать кнопки\n";
?>