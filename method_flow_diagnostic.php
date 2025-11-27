<?php
/**
 * Diagnostic for method execution flow and positioning
 */

echo "=== ДИАГНОСТИКА ПОТОКА ВЫПОЛНЕНИЯ МЕТОДОВ ===\n\n";

$handlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

echo "1. АНАЛИЗ ПОЗИЦИЙ КЛЮЧЕВЫХ МЕТОДОВ:\n";

// Find key method positions
$methods = [
    'canHandle' => 'public function canHandle',
    'handle' => 'public function handle',
    'isActivityExecution' => 'private function isActivityExecution',
    'handleActivityExecution' => 'private function handleActivityExecution'
];

$positions = [];
foreach ($methods as $name => $signature) {
    $pos = strpos($handlerContent, $signature);
    if ($pos !== false) {
        $lineNum = substr_count($handlerContent, "\n", 0, $pos) + 1;
        $positions[$name] = ['pos' => $pos, 'line' => $lineNum];
        echo "   {$name}: строка {$lineNum}\n";
    } else {
        echo "   {$name}: ❌ НЕ НАЙДЕН\n";
    }
}

echo "\n2. ПРОВЕРКА ПОРЯДКА ВЫЗОВОВ В handle():\n";

// Extract handle method content
$handleStart = $positions['handle']['pos'];
$handleEnd = strpos($handlerContent, 'private function', $handleStart + 1);
if ($handleEnd === false) {
    $handleEnd = strpos($handlerContent, 'public function', $handleStart + 1);
}

if ($handleEnd !== false) {
    $handleContent = substr($handlerContent, $handleStart, $handleEnd - $handleStart);
    
    // Check for isActivityExecution call
    if (strpos($handleContent, 'isActivityExecution') !== false) {
        echo "   ✅ handle() вызывает isActivityExecution\n";
        
        // Check position of isActivityExecution call relative to handleActivityExecution call
        $isActPos = strpos($handleContent, 'isActivityExecution');
        $handleActPos = strpos($handleContent, 'handleActivityExecution');
        
        if ($isActPos !== false && $handleActPos !== false) {
            if ($isActPos < $handleActPos) {
                echo "   ✅ isActivityExecution вызывается ПЕРЕД handleActivityExecution\n";
            } else {
                echo "   ⚠️ handleActivityExecution вызывается ПЕРЕД isActivityExecution\n";
            }
        }
    } else {
        echo "   ❌ handle() НЕ вызывает isActivityExecution\n";
    }
    
    if (strpos($handleContent, 'handleActivityExecution') !== false) {
        echo "   ✅ handle() вызывает handleActivityExecution\n";
    } else {
        echo "   ❌ handle() НЕ вызывает handleActivityExecution\n";
    }
} else {
    echo "   ❌ Не удалось извлечь содержимое метода handle()\n";
}

echo "\n3. АНАЛИЗ isActivityExecution МЕТОДА:\n";

// Extract isActivityExecution method
$isActStart = $positions['isActivityExecution']['pos'];
$isActEnd = strpos($handlerContent, 'private function', $isActStart + 1);
if ($isActEnd === false) {
    $isActEnd = strpos($handlerContent, 'public function', $isActStart + 1);
}

if ($isActEnd !== false) {
    $isActContent = substr($handlerContent, $isActStart, $isActEnd - $isActStart);
    
    // Check for our supervision pattern
    if (strpos($isActContent, 'ШМ_УчастиеВЗПИ|ШМ_РазрПрограммы|ШМ_РазрГрафика|ШМ_ОргДопоставки|ШМ_УчастиеВОПИ|ШМ_ИспобязМастера') !== false) {
        echo "   ✅ isActivityExecution содержит паттерн для ШМР активностей\n";
        
        // Check position in the method
        $supervisionPos = strpos($isActContent, 'ШМ_УчастиеВЗПИ');
        $methodLength = strlen($isActContent);
        $relativePos = ($supervisionPos / $methodLength) * 100;
        
        echo "   📍 Паттерн ШМР находится на " . number_format($relativePos, 1) . "% метода\n";
    } else {
        echo "   ❌ isActivityExecution НЕ содержит паттерн для ШМР активностей\n";
    }
} else {
    echo "   ❌ Не удалось извлечь содержимое isActivityExecution\n";
}

echo "\n4. АНАЛИЗ handleActivityExecution МЕТОДА:\n";

// Extract handleActivityExecution method  
$handleActStart = $positions['handleActivityExecution']['pos'];
$handleActEnd = strpos($handlerContent, 'private function', $handleActStart + 1);
if ($handleActEnd === false) {
    $handleActEnd = strpos($handlerContent, 'public function', $handleActStart + 1);
}

if ($handleActEnd !== false) {
    $handleActContent = substr($handlerContent, $handleActStart, $handleActEnd - $handleActStart);
    
    // Check for our supervision pattern
    if (strpos($handleActContent, 'Handle supervision project activities FIRST') !== false) {
        echo "   ✅ handleActivityExecution содержит исправление для ШМР\n";
        
        // Check position of our fix
        $fixPos = strpos($handleActContent, 'Handle supervision project activities FIRST');
        $methodLength = strlen($handleActContent);
        $relativePos = ($fixPos / $methodLength) * 100;
        
        echo "   📍 Исправление ШМР находится на " . number_format($relativePos, 1) . "% метода\n";
        
        // Check if it's before other equipment handlers
        $electricalPos = strpos($handleActContent, 'cat_ТабЭлОб');
        if ($electricalPos !== false && $fixPos < $electricalPos) {
            echo "   ✅ Обработка ШМР идет ПЕРЕД обработкой электрооборудования\n";
        } else {
            echo "   ⚠️ Обработка ШМР может идти ПОСЛЕ других обработчиков\n";
        }
    } else {
        echo "   ❌ handleActivityExecution НЕ содержит исправление для ШМР\n";
    }
} else {
    echo "   ❌ Не удалось извлечь содержимое handleActivityExecution\n";
}

echo "\n5. ПРОВЕРКА ЛОГИРОВАНИЯ:\n";

// Check if our logging is present
if (strpos($handlerContent, 'handleActivityExecution - supervision project activity') !== false) {
    echo "   ✅ Логирование для ШМР активностей найдено\n";
} else {
    echo "   ❌ Логирование для ШМР активностей отсутствует\n";
}

echo "\n6. ИТОГОВАЯ ОЦЕНКА ПОТОКА:\n";

$flowIssues = [];

// Check if all key methods exist
foreach ($positions as $method => $data) {
    if ($data['pos'] === false) {
        $flowIssues[] = "Метод {$method} не найден";
    }
}

// Check supervision pattern in both methods
if (strpos($handlerContent, 'ШМ_УчастиеВЗПИ|ШМ_РазрПрограммы') === false) {
    $flowIssues[] = "Паттерн ШМР отсутствует в isActivityExecution";
}

if (strpos($handlerContent, 'Handle supervision project activities FIRST') === false) {
    $flowIssues[] = "Исправление ШМР отсутствует в handleActivityExecution";
}

if (empty($flowIssues)) {
    echo "🎉 ПОТОК ВЫПОЛНЕНИЯ КОРРЕКТЕН!\n";
    echo "   1. canHandle() → проверяет формат callback\n";
    echo "   2. handle() → определяет тип обработки\n";
    echo "   3. isActivityExecution() → проверяет, является ли это выполнением активности\n";
    echo "   4. handleActivityExecution() → обрабатывает выполнение (с нашим исправлением)\n";
    echo "\nВсе компоненты на месте для корректной работы ШМР активностей.\n";
} else {
    echo "⚠️ ОБНАРУЖЕНЫ ПРОБЛЕМЫ В ПОТОКЕ:\n";
    foreach ($flowIssues as $issue) {
        echo "   - {$issue}\n";
    }
}

echo "\n=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";
?>