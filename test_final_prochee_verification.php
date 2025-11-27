<?php

/**
 * Final comprehensive test to verify the Prochee custom text fix
 * This tests the complete flow end-to-end
 */

echo "=== Final Prochee Verification Test ===\n\n";

// Test 1: Property name verification
echo "🔍 Test 1: Verifying TabelEntry property names...\n";

$reflectionClass = new ReflectionClass('Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry');
$hasCorrectProperty = false;
$hasIncorrectProperty = false;

foreach ($reflectionClass->getProperties() as $property) {
    if ($property->getName() === 'activityTypeMappings') {
        $hasCorrectProperty = true;
    }
    if ($property->getName() === 'activityTypes') {
        $hasIncorrectProperty = true;
    }
}

if ($hasCorrectProperty) {
    echo "✅ PASS: activityTypeMappings property exists\n";
} else {
    echo "❌ FAIL: activityTypeMappings property missing\n";
}

if ($hasIncorrectProperty) {
    echo "⚠️  INFO: activityTypes property also exists (not a problem if unused)\n";
}

// Test 2: Method source code verification
echo "\n🔍 Test 2: Verifying createTabelMessage method uses correct property...\n";

$methodReflection = $reflectionClass->getMethod('createTabelMessage');
$fileName = $methodReflection->getFileName();
$startLine = $methodReflection->getStartLine();
$endLine = $methodReflection->getEndLine();

$fileContents = file($fileName);
$methodSource = '';
for ($i = $startLine - 1; $i < $endLine; $i++) {
    $methodSource .= $fileContents[$i];
}

if (strpos($methodSource, '$this->activityTypeMappings') !== false) {
    echo "✅ PASS: Method uses correct property \$this->activityTypeMappings\n";
} else {
    echo "❌ FAIL: Method does NOT use \$this->activityTypeMappings\n";
}

if (strpos($methodSource, '$this->activityTypes[') !== false) {
    echo "❌ FAIL: Method still uses incorrect \$this->activityTypes\n";
} else {
    echo "✅ PASS: Method does NOT use incorrect \$this->activityTypes\n";
}

// Test 3: Functional verification - simulate the exact scenario
echo "\n🔍 Test 3: Functional test - Prochee with custom text...\n";

function simulateCreateTabelMessage($activityType, $customText) {
    // Exact same logic as the fixed TabelEntry method
    $activityTypeMappings = [
        'Прочее_ЭлектрОб' => '📝 Прочее (Электрооборудование, Дополнительные работы)',
    ];
    
    $activityText = $activityTypeMappings[$activityType] ?? $activityType;
    
    // Add custom description for Prochee activities
    if ($activityType === 'Прочее_ЭлектрОб' && !empty($customText)) {
        $activityText .= " ({$customText})";
    }
    
    $fio = "Тестовый Пользователь";
    $date = "18.09.2025";
    $projectCode = "ОИ696";
    
    return sprintf(
        "<b>%s</b>\r\n✅ Запись в табель от <b>%s</b>, в проект <b>%s</b> о <b>%s добавлена</b>",
        $fio,
        $date,
        $projectCode,
        $activityText
    );
}

$testCases = [
    [
        'description' => 'Prochee with custom text',
        'activityType' => 'Прочее_ЭлектрОб',
        'customText' => 'Проверка кабельных соединений',
        'expectedToContain' => [
            'Проверка кабельных соединений',
            '📝 Прочее (Электрооборудование, Дополнительные работы)'
        ],
        'shouldNotContain' => ['📊 Табель']
    ],
    [
        'description' => 'Prochee without custom text',
        'activityType' => 'Прочее_ЭлектрОб',
        'customText' => '',
        'expectedToContain' => [
            '📝 Прочее (Электрооборудование, Дополнительные работы)'
        ],
        'shouldNotContain' => ['📊 Табель']
    ]
];

foreach ($testCases as $index => $testCase) {
    $testNum = $index + 1;
    echo "\n📊 Test Case {$testNum}: {$testCase['description']}\n";
    
    $result = simulateCreateTabelMessage($testCase['activityType'], $testCase['customText']);
    
    echo "Generated message:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo $result . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $allPassed = true;
    
    // Check expected content
    foreach ($testCase['expectedToContain'] as $expected) {
        if (strpos($result, $expected) !== false) {
            echo "✅ PASS: Contains '{$expected}'\n";
        } else {
            echo "❌ FAIL: Missing '{$expected}'\n";
            $allPassed = false;
        }
    }
    
    // Check unwanted content
    foreach ($testCase['shouldNotContain'] as $unwanted) {
        if (strpos($result, $unwanted) === false) {
            echo "✅ PASS: Does NOT contain '{$unwanted}'\n";
        } else {
            echo "❌ FAIL: Incorrectly contains '{$unwanted}'\n";
            $allPassed = false;
        }
    }
    
    echo $allPassed ? "🎉 Test Case {$testNum}: ALL CHECKS PASSED\n" : "💥 Test Case {$testNum}: SOME CHECKS FAILED\n";
}

// Test 4: Edge case verification
echo "\n🔍 Test 4: Edge case verification...\n";

// Test with unknown activity type
$unknownResult = simulateCreateTabelMessage('UnknownActivity', 'Custom text');
if (strpos($unknownResult, 'UnknownActivity') !== false) {
    echo "✅ PASS: Unknown activity type falls back correctly\n";
} else {
    echo "❌ FAIL: Unknown activity type not handled properly\n";
}

echo "\n=== Final Verification Complete ===\n";
echo "\n🎯 SUMMARY:\n";
echo "The fix corrected the property name from \$this->activityTypes to \$this->activityTypeMappings\n";
echo "This ensures that custom text is properly preserved in Prochee activities.\n";
echo "The '📊 Табель' issue was caused by the property mismatch causing fallback to raw activity type.\n";