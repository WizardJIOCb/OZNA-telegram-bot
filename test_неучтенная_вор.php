<?php
/**
 * Test script for "Работа неучтенная в ВОР" button functionality
 */

echo "=== Testing 'Работа неучтенная в ВОР' Functionality ===\n\n";

$files = [
    'TabelEntry.php' => __DIR__ . '/CustomCommands/Services/TabelEntry.php',
    'MessageService.php' => __DIR__ . '/CustomCommands/Services/MessageService.php',
    'TabelHandler.php' => __DIR__ . '/CustomCommands/Handlers/TabelHandler.php'
];

$activities = [
    'ШМР_МонтажТрубопроводов_НеучВОР' => 'Монтаж трубопроводов',
    'ШМР_ГидроПневмоИспытания_НеучВОР' => 'Гидро-пневмо испытания',
    'ШМР_МонтажТехнологическогоОборудования_НеучВОР' => 'Монтаж технологического оборудования',
    'ШМР_МонтажЗРА_НеучВОР' => 'Монтаж ЗРА',
    'ШМР_ПротяжкаСоединений_НеучВОР' => 'Протяжка соединений',
    'ШМР_МонтажТеплоизоляции_НеучВОР' => 'Монтаж теплоизоляции'
];

$allPass = true;

echo "Test 1: Check Activity Mappings\n";
echo str_repeat("-", 60) . "\n";
$content = file_get_contents($files['TabelEntry.php']);
foreach ($activities as $code => $name) {
    $expected = "📄 Работа неучтенная в ВОР ($name)";
    if (strpos($content, "'$code' => '$expected'") !== false) {
        echo "✅ $name\n";
    } else {
        echo "❌ $name - NOT FOUND\n";
        $allPass = false;
    }
}

echo "\nTest 2: Check Supervision Array\n";
echo str_repeat("-", 60) . "\n";
foreach ($activities as $code => $name) {
    if (strpos($content, "'$code'") !== false) {
        echo "✅ $name\n";
    } else {
        echo "❌ $name - NOT IN ARRAY\n";
        $allPass = false;
    }
}

echo "\nTest 3: Check Keyboards\n";
echo str_repeat("-", 60) . "\n";
$msgContent = file_get_contents($files['MessageService.php']);
$count = substr_count($msgContent, 'Работа неучтенная в ВОР');
echo "Found $count occurrences (expected: 6)\n";
if ($count >= 6) {
    echo "✅ All keyboards updated\n";
} else {
    echo "❌ Missing keyboards\n";
    $allPass = false;
}

echo "\nTest 4: Check Handlers\n";
echo str_repeat("-", 60) . "\n";
$handlerContent = file_get_contents($files['TabelHandler.php']);
$handlerCount = substr_count($handlerContent, '_НеучВОР');
echo "Found $handlerCount НеучВОР handlers (expected: >= 12)\n";
if ($handlerCount >= 12) {
    echo "✅ All handlers added\n";
} else {
    echo "❌ Missing handlers\n";
    $allPass = false;
}

echo "\n" . str_repeat("=", 60) . "\n";
if ($allPass) {
    echo "✅ ALL TESTS PASSED!\n";
} else {
    echo "❌ SOME TESTS FAILED\n";
}
echo str_repeat("=", 60) . "\n";

exit($allPass ? 0 : 1);
