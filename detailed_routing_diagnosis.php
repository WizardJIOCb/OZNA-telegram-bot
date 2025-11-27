<?php

echo "=== ДЕТАЛЬНАЯ ДИАГНОСТИКА ПОРЯДКА В routeTabelCallback ===\n";

$handlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$content = file_get_contents($handlerFile);

// Find the routeTabelCallback method
$methodStart = strpos($content, 'private function routeTabelCallback');
$methodEnd = strpos($content, 'private function extractCategoryFromCallback', $methodStart);

if ($methodStart !== false && $methodEnd !== false) {
    $method = substr($content, $methodStart, $methodEnd - $methodStart);
    
    // Find key positions in the method
    $positions = [];
    
    // Key checks and handlers
    $keyItems = [
        'isActivityExecution check' => 'if ($this->isActivityExecution($category))',
        'handleOtherWorkTypes call' => 'return $this->handleOtherWorkTypes',
        'handleMainTabel' => 'handleMainTabel',
        'handleDateSelection' => 'handleDateSelection',
        'handleProjectSelection' => 'handleProjectSelection',
        'handleIdleMenu' => 'handleIdleMenu'
    ];
    
    foreach ($keyItems as $name => $pattern) {
        $pos = strpos($method, $pattern);
        if ($pos !== false) {
            $positions[] = [
                'name' => $name,
                'position' => $pos,
                'found' => true
            ];
        } else {
            $positions[] = [
                'name' => $name,
                'position' => -1,
                'found' => false
            ];
        }
    }
    
    // Sort by position
    usort($positions, function($a, $b) {
        if (!$a['found']) return 1;
        if (!$b['found']) return -1;
        return $a['position'] <=> $b['position'];
    });
    
    echo "Порядок выполнения в routeTabelCallback:\n";
    foreach ($positions as $i => $item) {
        $status = $item['found'] ? '✅' : '❌';
        $position = $item['found'] ? "позиция {$item['position']}" : 'НЕ НАЙДЕН';
        echo sprintf("%d. %s %s (%s)\n", $i + 1, $status, $item['name'], $position);
    }
    
    // Check the relative positions
    $isActivityPos = false;
    $handleOtherPos = false;
    
    foreach ($positions as $item) {
        if ($item['name'] === 'isActivityExecution check' && $item['found']) {
            $isActivityPos = $item['position'];
        }
        if ($item['name'] === 'handleOtherWorkTypes call' && $item['found']) {
            $handleOtherPos = $item['position'];
        }
    }
    
    echo "\n=== КРИТИЧЕСКИЙ АНАЛИЗ ===\n";
    if ($isActivityPos !== false && $handleOtherPos !== false) {
        if ($isActivityPos < $handleOtherPos) {
            echo "✅ ПРАВИЛЬНО: isActivityExecution проверяется ДО handleOtherWorkTypes\n";
            echo "   isActivityExecution: позиция $isActivityPos\n";
            echo "   handleOtherWorkTypes: позиция $handleOtherPos\n";
            echo "   Разница: " . ($handleOtherPos - $isActivityPos) . " символов\n";
        } else {
            echo "❌ НЕПРАВИЛЬНО: isActivityExecution проверяется ПОСЛЕ handleOtherWorkTypes\n";
            echo "   isActivityExecution: позиция $isActivityPos\n";
            echo "   handleOtherWorkTypes: позиция $handleOtherPos\n";
        }
    } else {
        echo "❌ НЕ УДАЛОСЬ определить позиции\n";
        echo "   isActivityExecution найден: " . ($isActivityPos !== false ? 'ДА' : 'НЕТ') . "\n";
        echo "   handleOtherWorkTypes найден: " . ($handleOtherPos !== false ? 'ДА' : 'НЕТ') . "\n";
    }
    
    // Check what comes immediately before handleOtherWorkTypes
    if ($handleOtherPos !== false) {
        $beforeHandleOther = substr($method, max(0, $handleOtherPos - 200), 200);
        echo "\n=== КОД ПЕРЕД handleOtherWorkTypes ===\n";
        $lines = explode("\n", $beforeHandleOther);
        foreach (array_slice($lines, -5) as $line) {
            echo "  " . trim($line) . "\n";
        }
    }
    
} else {
    echo "❌ НЕ УДАЛОСЬ найти метод routeTabelCallback\n";
}

?>