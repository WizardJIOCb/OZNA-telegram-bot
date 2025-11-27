<?php
/**
 * Real-time monitoring script for electrical equipment button
 * This script will monitor the logs to see what happens when the button is clicked
 */

echo "=== МОНИТОРИНГ КНОПКИ ЭЛЕКТРООБОРУДОВАНИЯ ===\n\n";

$logFile = __DIR__ . '/telegram_bot_error.log';

if (!file_exists($logFile)) {
    echo "❌ Файл логов не найден: {$logFile}\n";
    echo "Создаю пустой файл логов...\n";
    touch($logFile);
}

echo "📊 Мониторинг файла: {$logFile}\n";
echo "🔍 Ожидаю нажатие кнопки 'Электрооборудование'...\n";
echo "⏹️  Нажмите Ctrl+C для остановки\n\n";

// Get current file size
$lastSize = filesize($logFile);
$startTime = time();

echo "Время начала мониторинга: " . date('H:i:s') . "\n\n";

while (true) {
    clearstatcache();
    $currentSize = filesize($logFile);
    
    if ($currentSize > $lastSize) {
        // New content added to log file
        $handle = fopen($logFile, 'r');
        fseek($handle, $lastSize);
        
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $timestamp = date('H:i:s');
            
            // Check for electrical equipment related logs
            if (strpos($line, 'ТабЭлОб') !== false || 
                strpos($line, 'Электрооборудование') !== false ||
                strpos($line, 'handleElectricalEquipmentMenu') !== false ||
                strpos($line, 'createElectricalEquipmentKeyboard') !== false) {
                echo "🔴 [{$timestamp}] ЭЛЕКТРООБОРУДОВАНИЕ: {$line}\n";
            }
            // Check for TabelHandler logs
            elseif (strpos($line, 'TabelHandler') !== false) {
                echo "🟡 [{$timestamp}] TABEL: {$line}\n";
            }
            // Check for CallbackHandlerFactory logs
            elseif (strpos($line, 'CallbackHandlerFactory') !== false) {
                echo "🟢 [{$timestamp}] CALLBACK: {$line}\n";
            }
            // Check for any errors
            elseif (strpos($line, 'Error') !== false || strpos($line, 'Fatal') !== false) {
                echo "❌ [{$timestamp}] ERROR: {$line}\n";
            }
            // Show other important logs
            else {
                echo "⚪ [{$timestamp}] {$line}\n";
            }
        }
        
        fclose($handle);
        $lastSize = $currentSize;
    }
    
    // Show periodic status
    if ((time() - $startTime) % 30 == 0) {
        echo "\n💭 [" . date('H:i:s') . "] Мониторинг активен. Ожидаю нажатие кнопки...\n\n";
    }
    
    usleep(500000); // Wait 0.5 seconds
}
?>