<?php

echo "Checking and fixing encoding issues...\n\n";

// Проверка 1: Кодировка файлов
echo "=== Check 1: File Encoding ===\n";
$filesToCheck = [
    __DIR__ . '/CustomCommands/StartCommand.php',
    __DIR__ . '/CustomCommands/Services/MessageService.php',
    __DIR__ . '/CustomCommands/Services/AdminService.php',
    __DIR__ . '/CustomCommands/Services/DatabaseService.php'
];

foreach ($filesToCheck as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $encoding = mb_detect_encoding($content, ['UTF-8', 'Windows-1251', 'ISO-8859-1'], true);
        echo "File: " . basename($file) . " - Encoding: " . ($encoding ?: 'Unknown') . "\n";
        
        // Если кодировка не UTF-8, конвертируем
        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            if (file_put_contents($file, $content)) {
                echo "  ✓ Converted to UTF-8\n";
            } else {
                echo "  ✗ Failed to convert to UTF-8\n";
            }
        }
    }
}

echo "\n";

// Проверка 2: Добавление BOM если нужно
echo "=== Check 2: BOM (Byte Order Mark) ===\n";
foreach ($filesToCheck as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $hasBOM = substr($content, 0, 3) === "\xEF\xBB\xBF";
        echo "File: " . basename($file) . " - Has BOM: " . ($hasBOM ? 'Yes' : 'No') . "\n";
        
        // Удаляем BOM если он есть
        if ($hasBOM) {
            $content = substr($content, 3);
            if (file_put_contents($file, $content)) {
                echo "  ✓ BOM removed\n";
            } else {
                echo "  ✗ Failed to remove BOM\n";
            }
        }
    }
}

echo "\n";

// Проверка 3: Кодировка в базе данных
echo "=== Check 3: Database Encoding ===\n";
try {
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
    
    $databaseService = new DatabaseService();
    $connection = $databaseService->getConnection();
    
    // Проверяем кодировку базы данных
    $result = $connection->query("SHOW VARIABLES LIKE 'character_set_database'");
    if ($result && $row = $result->fetch_assoc()) {
        echo "Database character set: " . $row['Value'] . "\n";
    }
    
    $result = $connection->query("SHOW VARIABLES LIKE 'collation_database'");
    if ($result && $row = $result->fetch_assoc()) {
        echo "Database collation: " . $row['Value'] . "\n";
    }
    
    // Проверяем кодировку таблицы ozna_data
    $result = $connection->query("SHOW FULL COLUMNS FROM ozna_data WHERE Field = 'data'");
    if ($result && $row = $result->fetch_assoc()) {
        echo "ozna_data.data column type: " . $row['Type'] . "\n";
        echo "ozna_data.data column collation: " . ($row['Collation'] ?: 'default') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error checking database encoding: " . $e->getMessage() . "\n";
}

echo "\n";

// Проверка 4: Кодировка в JSON
echo "=== Check 4: JSON Encoding ===\n";
$testArray = [
    'text' => 'Тестовое сообщение с русскими буквами',
    'buttons' => [
        ['text' => 'Кнопка 1'],
        ['text' => 'Кнопка 2']
    ]
];

$json = json_encode($testArray, JSON_UNESCAPED_UNICODE);
if ($json === false) {
    echo "✗ JSON encoding failed\n";
    echo "JSON error: " . json_last_error_msg() . "\n";
} else {
    echo "✓ JSON encoding successful\n";
    echo "Sample JSON: " . substr($json, 0, 100) . "...\n";
}

echo "\n";

// Проверка 5: Создание тестового скрипта для проверки кодировки в боте
echo "=== Check 5: Creating Encoding Test Script ===\n";
$encodingTestContent = '<?php

// Тест кодировки в боте
echo "Testing encoding in bot environment...\n\n";

// Тест 1: Русский текст
$russianText = "Привет, мир! Тест русских букв: абвгдеёжзийклмнопрстуфхцчшщъыьэюя";
echo "1. Russian text: " . $russianText . "\n";

// Тест 2: Кодировка текста
$encoding = mb_detect_encoding($russianText, [\'UTF-8\', \'Windows-1251\', \'ISO-8859-1\'], true);
echo "2. Detected encoding: " . ($encoding ?: \'Unknown\') . "\n";

// Тест 3: JSON с русским текстом
$data = [
    \'message\' => $russianText,
    \'buttons\' => [
        [\'text\' => \'Кнопка 1\'],
        [\'text\' => \'Кнопка 2\']
    ]
];

$json = json_encode($data, JSON_UNESCAPED_UNICODE);
if ($json === false) {
    echo "3. JSON encoding failed: " . json_last_error_msg() . "\n";
} else {
    echo "3. JSON encoding successful\n";
    echo "   Sample: " . substr($json, 0, 100) . "...\n";
}

// Тест 4: Декодирование JSON
if ($json) {
    $decoded = json_decode($json, true);
    if ($decoded === null) {
        echo "4. JSON decoding failed: " . json_last_error_msg() . "\n";
    } else {
        echo "4. JSON decoding successful\n";
        echo "   Decoded message: " . $decoded[\'message\'] . "\n";
    }
}

echo "\nEncoding test completed.\n";
';

$encodingTestPath = __DIR__ . '/encoding_test.php';
if (file_put_contents($encodingTestPath, $encodingTestContent)) {
    echo "✓ encoding_test.php created successfully\n";
    echo "  Run with: php encoding_test.php\n";
} else {
    echo "✗ Failed to create encoding_test.php\n";
}

echo "\n";

echo "Encoding check and fix completed!\n";
echo "All files should now be in proper UTF-8 encoding without BOM.\n";
echo "Run 'php encoding_test.php' to verify encoding in bot environment.\n";