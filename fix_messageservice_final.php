<?php
/**
 * ФИНАЛЬНОЕ ИСПРАВЛЕНИЕ MessageService.php
 * Удаляет ВСЕ дубликаты функций
 */

$file = __DIR__ . '/CustomCommands/Services/MessageService.php';
$backup = $file . '.backup_final_' . date('YmdHis');

copy($file, $backup);
echo "✅ Backup: $backup\n\n";

$content = file_get_contents($file);
$lines = explode("\n", $content);

echo "Всего строк: " . count($lines) . "\n\n";

// Удалить строки с дубликатами (1-indexed -> 0-indexed)
// Нумерация: человеческая (1-based) -> PHP (0-based) = -1

$linesToRemove = [
    // Дубликаты ensureUtf8Encoding, convertToUtf8, initializeBotIfNeeded (133-176)
    133, 134, 135, 136, 137, 138, 139, 140, 141, 142, 143, 144, 145, 146,  // ensureUtf8Encoding old
    147, 148, 149, 150, 151, 152, 153, 154, 155, 156, 157, 158, 159, 160,  // convertToUtf8
    161, 162, 163, 164, 165, 166, 167, 168, 169, 170, 171, 172, 173, 174, 175, 176,  // initializeBotIfNeeded duplicate
    
    // Дубликаты sendMessageWithReplyKeyboard (195-256)
    195, 196, 197, 198, 199, 200,
    201, 202, 203, 204, 205, 206, 207, 208, 209, 210,
    211, 212, 213, 214, 215, 216, 217, 218, 219, 220,
    221, 222, 223, 224, 225, 226, 227, 228, 229, 230,
    231, 232, 233, 234, 235, 236, 237, 238, 239, 240,
    241, 242, 243, 244, 245, 246, 247, 248, 249, 250,
    251, 252, 253, 254, 255, 256
];

// Convert to 0-based and mark for removal
$removeMap = [];
foreach ($linesToRemove as $lineNum) {
    $removeMap[$lineNum - 1] = true;  // Convert to 0-based
}

// Build new content
$newLines = [];
foreach ($lines as $idx => $line) {
    if (!isset($removeMap[$idx])) {
        $newLines[] = $line;
    }
}

$newContent = implode("\n", $newLines);
file_put_contents($file, $newContent);

echo "✅ Удалено строк: " . count($linesToRemove) . "\n\n";

// Test syntax
exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $code);

if ($code === 0) {
    echo "✅ ✅ ✅ СИНТАКСИС ПРАВИЛЬНЫЙ! ✅ ✅ ✅\n\n";
    echo "═══════════════════════════════════════════\n";
    echo "ФАЙЛ ИСПРАВЛЕН!\n";
    echo "Загрузите на сервер:\n";
    echo "  $file\n";
    echo "═══════════════════════════════════════════\n";
} else {
    echo "❌ ОШИБКА:\n";
    echo implode("\n", $output) . "\n\n";
    echo "Восстанавливаю...\n";
    copy($backup, $file);
}
