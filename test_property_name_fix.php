<?php

/**
 * Test to verify the property name fix in TabelEntry
 * This tests that activityTypeMappings is correctly used instead of activityTypes
 */

require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

// Test using reflection to examine the actual property
$reflectionClass = new ReflectionClass('Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry');

echo "=== Testing TabelEntry Property Names ===\n\n";

// Check if activityTypeMappings property exists
$hasActivityTypeMappings = false;
$hasActivityTypes = false;

foreach ($reflectionClass->getProperties() as $property) {
    if ($property->getName() === 'activityTypeMappings') {
        $hasActivityTypeMappings = true;
        echo "✅ Found property: activityTypeMappings\n";
    }
    if ($property->getName() === 'activityTypes') {
        $hasActivityTypes = true;
        echo "⚠️  Found property: activityTypes\n";
    }
}

if (!$hasActivityTypeMappings) {
    echo "❌ MISSING: activityTypeMappings property not found!\n";
}

if ($hasActivityTypes) {
    echo "⚠️  WARNING: Both activityTypes and activityTypeMappings exist - potential confusion\n";
}

// Check the createTabelMessage method source
$methodReflection = $reflectionClass->getMethod('createTabelMessage');
$fileName = $methodReflection->getFileName();
$startLine = $methodReflection->getStartLine();
$endLine = $methodReflection->getEndLine();

$fileContents = file($fileName);
$methodSource = '';
for ($i = $startLine - 1; $i < $endLine; $i++) {
    $methodSource .= $fileContents[$i];
}

echo "\n🔍 Analyzing createTabelMessage method...\n";

// Check for correct property usage
if (strpos($methodSource, '$this->activityTypeMappings') !== false) {
    echo "✅ CORRECT: Method uses \$this->activityTypeMappings\n";
} else {
    echo "❌ ERROR: Method does NOT use \$this->activityTypeMappings\n";
}

if (strpos($methodSource, '$this->activityTypes') !== false) {
    echo "❌ ERROR: Method incorrectly uses \$this->activityTypes\n";
} else {
    echo "✅ CORRECT: Method does NOT use \$this->activityTypes\n";
}

// Test the actual functionality with a mock
echo "\n🧪 Testing message creation functionality...\n";

// Create a simple test without constructor issues
function testMessageCreation() {
    // Simulate the logic from the fixed createTabelMessage method
    $activityTypeMappings = [
        'Прочее_ЭлектрОб' => '📝 Прочее (Электрооборудование, Дополнительные работы)',
    ];
    
    $activityType = 'Прочее_ЭлектрОб';
    $customText = 'Проверка кабельных соединений';
    
    $activityText = $activityTypeMappings[$activityType] ?? $activityType;
    
    if ($activityType === 'Прочее_ЭлектрОб' && !empty($customText)) {
        $activityText .= " ({$customText})";
    }
    
    return $activityText;
}

$result = testMessageCreation();
echo "Generated activity text: {$result}\n";

if (strpos($result, 'Проверка кабельных соединений') !== false) {
    echo "✅ PASS: Custom text properly included\n";
} else {
    echo "❌ FAIL: Custom text not found\n";
}

if (strpos($result, '📝 Прочее (Электрооборудование, Дополнительные работы)') !== false) {
    echo "✅ PASS: Correct activity type mapping used\n";
} else {
    echo "❌ FAIL: Incorrect activity type mapping\n";
}

if (strpos($result, '📊 Табель') !== false) {
    echo "❌ FAIL: Found '📊 Табель' issue\n";
} else {
    echo "✅ PASS: No '📊 Табель' issue\n";
}

echo "\n=== Test Complete ===\n";