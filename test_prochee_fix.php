<?php
/**
 * Test PNR "Прочее" entries with and without custom comments
 */

echo "=== Testing PNR 'Прочее' Entries ===\n\n";

// Simulate parseDescription function
function parseDescription(string $desc): array {
    $desc = trim($desc);
    
    // Remove emojis
    $desc = preg_replace('/[\x{1F600}-\x{1FFFF}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2702}-\x{27B0}\x{24C2}-\x{1F251}]+/u', '', $desc);
    $desc = trim($desc);
    
    // Find first parenthesis
    $firstParen = strpos($desc, '(');
    if ($firstParen !== false) {
        $mainPart = trim(substr($desc, 0, $firstParen));
        $subsString = trim(substr($desc, $firstParen));
    } else {
        $mainPart = $desc;
        $subsString = '';
    }
    
    $subs = extractSubs($subsString);
    
    return ['main' => $mainPart, 'subs' => $subs];
}

function extractSubs(string $s): array {
    $subs = [];
    $s = trim($s);
    
    if (empty($s)) {
        return $subs;
    }
    
    $i = 0;
    $len = strlen($s);
    
    while ($i < $len) {
        if ($s[$i] === '(') {
            $start = $i;
            $count = 1;
            $i++;
            
            while ($i < $len && $count > 0) {
                if ($s[$i] === '(') {
                    $count++;
                } elseif ($s[$i] === ')') {
                    $count--;
                }
                $i++;
            }
            
            if ($count === 0) {
                $sub = trim(substr($s, $start + 1, $i - $start - 2));
                $subs[] = $sub;
                while ($i < $len && $s[$i] === ' ') {
                    $i++;
                }
                continue;
            }
        } else {
            $i++;
        }
    }
    
    return $subs;
}

// Test cases
$testCases = [
    [
        'name' => 'Прочее WITH custom comment',
        'description' => '📝 Прочее (Электрооборудование, Дополнительные работы) (Настройка реле защиты)',
        'expected' => [
            'Level 1' => 'Пусконаладочные работы',
            'Level 2' => 'Электрооборудование',
            'Level 3' => 'Дополнительные работы',
            'Level 4' => 'Прочее',
            'Level 5' => 'Настройка реле защиты'
        ]
    ],
    [
        'name' => 'Прочее WITHOUT custom comment (Lebedev case)',
        'description' => '📝 Прочее (Электрооборудование, Дополнительные работы)',
        'expected' => [
            'Level 1' => 'Пусконаладочные работы',
            'Level 2' => 'Электрооборудование',
            'Level 3' => 'Дополнительные работы',
            'Level 4' => 'Прочее',
            'Level 5' => '(empty)'
        ]
    ],
    [
        'name' => 'Монтаж оборудования',
        'description' => '⚙️ Монтаж оборудования (Электрооборудование, Дополнительные работы)',
        'expected' => [
            'Level 1' => 'Пусконаладочные работы',
            'Level 2' => 'Электрооборудование',
            'Level 3' => 'Дополнительные работы',
            'Level 4' => 'Монтаж оборудования',
            'Level 5' => '(empty)'
        ]
    ],
    [
        'name' => 'Изменение схемы подключения',
        'description' => '🔀 Изменение схемы подключения (Электрооборудование, Дополнительные работы)',
        'expected' => [
            'Level 1' => 'Пусконаладочные работы',
            'Level 2' => 'Электрооборудование',
            'Level 3' => 'Дополнительные работы',
            'Level 4' => 'Изменение схемы подключения',
            'Level 5' => '(empty)'
        ]
    ]
];

foreach ($testCases as $index => $test) {
    echo "Test " . ($index + 1) . ": {$test['name']}\n";
    echo str_repeat("-", 80) . "\n";
    echo "Input: {$test['description']}\n\n";
    
    $parsed = parseDescription($test['description']);
    echo "Parsed:\n";
    echo "  Main: '{$parsed['main']}'\n";
    echo "  Subs: " . json_encode($parsed['subs'], JSON_UNESCAPED_UNICODE) . "\n";
    echo "  Subs count: " . count($parsed['subs']) . "\n\n";
    
    // Simulate logic
    $main = $parsed['main'];
    $subs = $parsed['subs'];
    
    // Check condition
    if (strpos($main, 'Прочее') !== false && isset($subs[0])) {
        echo "✅ Condition matches: strpos(\$main, 'Прочее') !== false && isset(\$subs[0])\n";
        
        $firstSub = $subs[0];
        if (strpos($firstSub, ',') !== false) {
            $parts = explode(',', $firstSub, 2);
            $tip1 = trim($parts[0]);
            $tip2 = trim($parts[1]);
        } else {
            $tip1 = $firstSub;
            $tip2 = 'Дополнительные работы';
        }
        
        $tip3 = 'Прочее';
        $tip4 = $subs[1] ?? '';
        
        echo "\nResult:\n";
        echo "  Level 1 (osnovnaya): Пусконаладочные работы\n";
        echo "  Level 2 (tip1): {$tip1}\n";
        echo "  Level 3 (tip2): {$tip2}\n";
        echo "  Level 4 (tip3): {$tip3}\n";
        echo "  Level 5 (tip4): " . ($tip4 ?: '(empty)') . "\n";
        
        // Verify against expected
        $expectedL2 = $test['expected']['Level 2'];
        $expectedL3 = $test['expected']['Level 3'];
        $expectedL4 = $test['expected']['Level 4'];
        $expectedL5 = $test['expected']['Level 5'];
        
        $matches = ($tip1 === $expectedL2 && $tip2 === $expectedL3 && $tip3 === $expectedL4 && 
                    (($tip4 ?: '(empty)') === $expectedL5));
        
        echo "\n" . ($matches ? "✅ PASS" : "❌ FAIL") . "\n";
    } else {
        echo "❌ Condition does NOT match - would fall to default handler\n";
        echo "❌ FAIL - Wrong handler!\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n\n";
}

echo "Summary:\n";
echo "- All 'Прочее' entries should now be handled correctly\n";
echo "- With or without custom comment (Level 5)\n";
echo "- Equipment type properly extracted to Level 2\n";
echo "- Category properly extracted to Level 3\n";
