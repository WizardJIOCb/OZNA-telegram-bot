<?php
/**
 * Server-side diagnostic script to check ProjectHandler.php code
 * Access via: https://api.ladyzenko.pro/tg/ozna/diag_name_1c.php
 */

header('Content-Type: text/plain; charset=utf-8');

$filePath = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';

echo "=== ДИАГНОСТИКА name_1c НА СЕРВЕРЕ ===\n\n";

if (!file_exists($filePath)) {
    echo "ОШИБКА: Файл не найден: {$filePath}\n";
    exit;
}

$content = file_get_contents($filePath);
$lines = explode("\n", $content);

echo "Файл: {$filePath}\n";
echo "Размер: " . filesize($filePath) . " байт\n";
echo "Строк: " . count($lines) . "\n\n";

echo "=== СТРОКИ 8895-8905 ===\n\n";
for ($i = 8894; $i < 8905 && $i < count($lines); $i++) {
    echo ($i + 1) . ": " . $lines[$i] . "\n";
}

echo "\n=== ПРОВЕРКА ===\n\n";

// Search for the nomenclatureName line
$found = false;
foreach ($lines as $lineNum => $line) {
    if (strpos($line, 'nomenclatureName') !== false && strpos($line, '=') !== false && strpos($line, 'cardData') !== false) {
        echo "Найдена строка " . ($lineNum + 1) . ":\n";
        echo trim($line) . "\n\n";
        $found = true;
        
        if (strpos($line, "['name_1c']") !== false) {
            echo "✅ ИСПРАВЛЕНО: Используется 'name_1c'\n";
        } elseif (strpos($line, "['nomenclature_1c']") !== false) {
            echo "❌ НЕ ИСПРАВЛЕНО: Используется 'nomenclature_1c'\n";
            echo ">>> ТРЕБУЕТСЯ СИНХРОНИЗАЦИЯ ФАЙЛА <<<\n";
        }
        break;
    }
}

if (!$found) {
    echo "Строка с nomenclatureName не найдена в файле.\n";
}

echo "\n=== КОНЕЦ ДИАГНОСТИКИ ===\n";
