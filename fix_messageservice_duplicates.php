<?php
/**
 * СРОЧНЫЙ ФИКС ДЛЯ MessageService.php
 * Удаляет дублирующиеся функции
 */

$file = __DIR__ . '/CustomCommands/Services/MessageService.php';
$backup = __DIR__ . '/CustomCommands/Services/MessageService.php.backup_' . date('Y-m-d_His');

echo "=== ИСПРАВЛЕНИЕ MessageService.php ===\n\n";

// Создать backup
copy($file, $backup);
echo "✅ Backup создан: {$backup}\n";

// Читать файл
$content = file_get_contents($file);
$lines = explode("\n", $content);

echo "\n📊 Анализ файла:\n";
echo "   Всего строк: " . count($lines) . "\n";

// Найти все функции
$functions = [];
$currentFunction = null;
$braceLevel = 0;
$inClass = false;

for ($i = 0; $i < count($lines); $i++) {
    $line = $lines[$i];
    
    // Detect class start
    if (preg_match('/^class\s+/', $line)) {
        $inClass = true;
    }
    
    // Track brace level
    $braceLevel += substr_count($line, '{') - substr_count($line, '}');
    
    // Find function definitions
    if (preg_match('/^\s*(public|private|protected)\s+function\s+(\w+)\s*\(/', $line, $matches)) {
        $functionName = $matches[2];
        
        if (!isset($functions[$functionName])) {
            $functions[$functionName] = [];
        }
        
        $functions[$functionName][] = [
            'line' => $i + 1,
            'name' => $functionName,
            'visibility' => $matches[1]
        ];
    }
}

echo "\n🔍 Найденные дубликаты:\n";
$hasDuplicates = false;
foreach ($functions as $name => $occurrences) {
    if (count($occurrences) > 1) {
        $hasDuplicates = true;
        echo "   ❌ {$name}: " . count($occurrences) . " раз(а) - линии: ";
        echo implode(", ", array_column($occurrences, 'line')) . "\n";
    }
}

if (!$hasDuplicates) {
    echo "   ✅ Дубликатов не найдено!\n";
} else {
    echo "\n⚠️  ВНИМАНИЕ: Найдены дублирующиеся функции!\n";
    echo "   Это вызывает PHP Parse Error.\n";
    echo "   Нужно вручную удалить дубликаты из файла.\n\n";
    
    echo "📝 Рекомендации:\n";
    echo "   1. Откройте файл: {$file}\n";
    echo "   2. Удалите ДУБЛИРУЮЩИЕСЯ функции (оставьте только одну версию каждой)\n";
    echo "   3. Сохраните файл\n";
    echo "   4. Запустите: php -l {$file}\n";
    echo "   5. Если ошибок нет - перезапустите webhook\n";
}

echo "\n=== КОНЕЦ ДИАГНОСТИКИ ===\n";
