<?php
/**
 * Verify server file content for name_1c fix
 * Uses SSH2 extension or fallback to phpseclib
 */

$host = '82.146.42.213';
$username = 'root';
$password = '7qQ#28051961';
$remotePath = '/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/Handlers/ProjectHandler.php';

echo "=== ПРОВЕРКА КОДА НА СЕРВЕРЕ ===\n";
echo "Сервер: {$host}\n";
echo "Файл: {$remotePath}\n\n";

// Try SSH2 extension first
if (function_exists('ssh2_connect')) {
    echo "Использую SSH2 extension...\n";
    
    $connection = ssh2_connect($host, 22);
    if (!$connection) {
        die("Ошибка: не удалось подключиться к серверу\n");
    }
    
    if (!ssh2_auth_password($connection, $username, $password)) {
        die("Ошибка: аутентификация не удалась\n");
    }
    
    $sftp = ssh2_sftp($connection);
    $stream = fopen("ssh2.sftp://{$sftp}{$remotePath}", 'r');
    
    if (!$stream) {
        die("Ошибка: не удалось открыть файл\n");
    }
    
    $content = stream_get_contents($stream);
    fclose($stream);
    
} else {
    // Fallback: try to use phpseclib if available
    $phpseclibPath = __DIR__ . '/vendor/autoload.php';
    if (file_exists($phpseclibPath)) {
        require_once $phpseclibPath;
        
        if (class_exists('phpseclib3\Net\SFTP')) {
            echo "Использую phpseclib3...\n";
            $sftp = new \phpseclib3\Net\SFTP($host);
            if (!$sftp->login($username, $password)) {
                die("Ошибка: аутентификация не удалась\n");
            }
            $content = $sftp->get($remotePath);
        } elseif (class_exists('phpseclib\Net\SFTP')) {
            echo "Использую phpseclib...\n";
            $sftp = new \phpseclib\Net\SFTP($host);
            if (!$sftp->login($username, $password)) {
                die("Ошибка: аутентификация не удалась\n");
            }
            $content = $sftp->get($remotePath);
        } else {
            die("Ошибка: SSH2 extension и phpseclib не доступны\n");
        }
    } else {
        die("Ошибка: SSH2 extension не установлен и vendor/autoload.php не найден\n");
    }
}

if (empty($content)) {
    die("Ошибка: файл пуст или не удалось прочитать\n");
}

echo "✓ Файл успешно прочитан (" . strlen($content) . " байт)\n\n";

// Find the relevant lines (around 8899)
$lines = explode("\n", $content);
$totalLines = count($lines);

echo "Всего строк в файле: {$totalLines}\n\n";

// Search for the nomenclatureName line
echo "=== ПОИСК СТРОКИ С name_1c / nomenclature_1c ===\n\n";

$found = false;
foreach ($lines as $lineNum => $line) {
    $actualLineNum = $lineNum + 1;
    
    // Look for nomenclatureName assignment
    if (strpos($line, 'nomenclatureName') !== false && strpos($line, '=') !== false) {
        echo "Строка {$actualLineNum}:\n";
        echo trim($line) . "\n\n";
        $found = true;
        
        // Check which column name is used
        if (strpos($line, "['name_1c']") !== false) {
            echo "✅ ИСПРАВЛЕНО: Используется правильное имя колонки 'name_1c'\n";
        } elseif (strpos($line, "['nomenclature_1c']") !== false) {
            echo "❌ НЕ ИСПРАВЛЕНО: Используется старое имя колонки 'nomenclature_1c'\n";
            echo "\n>>> ТРЕБУЕТСЯ СИНХРОНИЗАЦИЯ ФАЙЛА НА СЕРВЕР! <<<\n";
        }
    }
}

if (!$found) {
    echo "Строка с nomenclatureName не найдена!\n";
    
    // Search for Наименование 1С
    echo "\nПоиск 'Наименование 1С':\n";
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, 'Наименование 1С') !== false || strpos($line, 'name_1c') !== false) {
            echo "Строка " . ($lineNum + 1) . ": " . trim($line) . "\n";
        }
    }
}

echo "\n=== ПРОВЕРКА ЗАВЕРШЕНА ===\n";
