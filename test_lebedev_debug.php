<?php
/**
 * Test parsing for Lebedev's strange entry
 */

require_once __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';

// Simulate the problematic message
$testMessages = [
    // Try different formats that might produce this result
    "<b>Лебедев Андрей Валериевич</b>\r\n✅09.10.2025, Проект <b>БКНС120</b>: Добавлена запись в табель: <b>Монтаж оборудования (Электрооборудование, Дополнительные работы)</b>",
    "<b>Лебедев Андрей Валериевич</b>\r\n✅09.10.2025, Проект <b>БКНС120</b>: Добавлена запись в табель: <b>⚙️ Монтаж оборудования (Электрооборудование, Дополнительные работы)</b>",
    "<b>Лебедев Андрей Валериевич</b>\r\n✅09.10.2025, Проект <b>БКНС120</b>: Добавлена запись в табель: <b>Прочее (Электрооборудование, Дополнительные работы)</b>",
    "<b>Лебедев Андрей Валериевич</b>\r\n✅09.10.2025, Проект <b>БКНС120</b>: Добавлена запись в табель: <b>📝 Прочее (Электрооборудование, Дополнительные работы)</b>",
];

echo "=== Testing Lebedev's Entry Debug ===\n\n";

$handler = new ReflectionClass('CustomCommands\Handlers\ProjectHandler');
$parseMethod = $handler->getMethod('parseReportEntry');
$parseMethod->setAccessible(true);
$parseDescMethod = $handler->getMethod('parseDescription');
$parseDescMethod->setAccessible(true);

$instance = $handler->newInstanceWithoutConstructor();

foreach ($testMessages as $index => $message) {
    echo "Test " . ($index + 1) . ":\n";
    echo "Message: " . substr($message, 0, 200) . "...\n\n";
    
    // First test parseDescription to see what main/subs we get
    if (preg_match('/: <b>(.*?)<\/b>/', $message, $matches)) {
        $description = $matches[1];
        echo "Description extracted: '{$description}'\n";
        
        $parsed = $parseDescMethod->invoke($instance, $description);
        echo "Main: '{$parsed['main']}'\n";
        echo "Subs: " . json_encode($parsed['subs'], JSON_UNESCAPED_UNICODE) . "\n\n";
    }
    
    // Now test full parsing
    $result = $parseMethod->invoke($instance, $message, 'Пусконаладка');
    
    echo "Parsed result:\n";
    echo "  Project: " . $result['project'] . "\n";
    echo "  Date: " . $result['date'] . "\n";
    echo "  Process: " . $result['process'] . "\n";
    echo "  Основная (Level 1): '" . $result['osnovnaya'] . "'\n";
    echo "  Тип1 (Level 2): '" . $result['tip1'] . "'\n";
    echo "  Тип2 (Level 3): '" . $result['tip2'] . "'\n";
    echo "  Тип3 (Level 4): '" . $result['tip3'] . "'\n";
    echo "  Тип4 (Level 5): '" . $result['tip4'] . "'\n";
    echo "\n";
    echo "CSV representation:\n";
    echo "  {$result['project']} | {$result['date']} | {$result['process']} | {$result['osnovnaya']} | {$result['tip1']} | {$result['tip2']} | {$result['tip3']} | {$result['tip4']}\n";
    echo "\n" . str_repeat("-", 80) . "\n\n";
}

echo "\n=== Analysis ===\n";
echo "Expected result:\n";
echo "  Level 1: Пусконаладочные работы\n";
echo "  Level 2: Электрооборудование\n";
echo "  Level 3: Дополнительные работы\n";
echo "  Level 4: Монтаж оборудования (or Прочее)\n";
echo "  Level 5: (empty or custom text)\n";
