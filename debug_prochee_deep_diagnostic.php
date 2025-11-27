<?php

/**
 * DEEP DIAGNOSTIC for Prochee custom text issue
 * This traces the EXACT flow to find where "📊 Табель" is coming from
 */

echo "=== DEEP PROCHEE DIAGNOSTIC ===\n\n";

// Step 1: Check TabelEntry property and method
echo "🔍 STEP 1: Checking TabelEntry class...\n";

require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

$reflectionClass = new ReflectionClass('Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry');

// Check properties
echo "Properties in TabelEntry:\n";
foreach ($reflectionClass->getProperties() as $property) {
    echo "  - {$property->getName()}\n";
}

// Check the actual method source
$methodReflection = $reflectionClass->getMethod('createTabelMessage');
$fileName = $methodReflection->getFileName();
$startLine = $methodReflection->getStartLine();
$endLine = $methodReflection->getEndLine();

$fileContents = file($fileName);
$methodSource = '';
for ($i = $startLine - 1; $i < $endLine; $i++) {
    $methodSource .= $fileContents[$i];
}

echo "\n📋 createTabelMessage method source:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$lines = explode("\n", $methodSource);
foreach ($lines as $lineNum => $line) {
    $actualLineNum = $startLine + $lineNum;
    echo sprintf("%3d: %s\n", $actualLineNum, $line);
}
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Step 2: Check if the property name is correct
echo "\n🔍 STEP 2: Checking property usage in method...\n";

if (strpos($methodSource, '$this->activityTypeMappings') !== false) {
    echo "✅ CORRECT: Method uses \$this->activityTypeMappings\n";
} else {
    echo "❌ ERROR: Method does NOT use \$this->activityTypeMappings\n";
}

if (strpos($methodSource, '$this->activityTypes[') !== false) {
    echo "❌ ERROR: Method incorrectly uses \$this->activityTypes\n";
} else {
    echo "✅ CORRECT: Method does NOT use \$this->activityTypes\n";
}

// Step 3: Test the actual logic step by step
echo "\n🔍 STEP 3: Testing logic step by step...\n";

// Simulate exactly what should happen
function simulateExactLogic($activityType, $idleReason) {
    echo "Input: activityType='{$activityType}', idleReason='{$idleReason}'\n";
    
    // This is the EXACT mapping from TabelEntry.php
    $activityTypeMappings = [
        'Прочее_ЭлектрОб' => '📝 Прочее (Электрооборудование, Дополнительные работы)',
    ];
    
    echo "Step 1: Looking up activity type in mappings...\n";
    $activityText = $activityTypeMappings[$activityType] ?? $activityType;
    echo "  Result: activityText = '{$activityText}'\n";
    
    echo "Step 2: Checking if we should add custom description...\n";
    if ($activityType === 'Прочее_ЭлектрОб' && !empty($idleReason)) {
        echo "  Condition met: activityType is Prochee AND idleReason is not empty\n";
        $activityText .= " ({$idleReason})";
        echo "  Final activityText: '{$activityText}'\n";
    } else {
        echo "  Condition NOT met:\n";
        echo "    - activityType === 'Прочее_ЭлектрОб': " . ($activityType === 'Прочее_ЭлектрОб' ? 'true' : 'false') . "\n";
        echo "    - !empty(idleReason): " . (!empty($idleReason) ? 'true' : 'false') . "\n";
    }
    
    return $activityText;
}

echo "\nTest Case 1: Prochee with custom text\n";
$result1 = simulateExactLogic('Прочее_ЭлектрОб', 'Проверка кабельных соединений');
echo "Final result: '{$result1}'\n";

echo "\nTest Case 2: Prochee without custom text\n";
$result2 = simulateExactLogic('Прочее_ЭлектрОб', '');
echo "Final result: '{$result2}'\n";

// Step 4: Check if there are any external systems that might be modifying the message
echo "\n🔍 STEP 4: Checking for external message modification...\n";

// Check if sendToHistoryChatByFunctionalDirection does anything to the message
echo "Checking MessageService::sendToHistoryChatByFunctionalDirection...\n";

$messageServiceFile = __DIR__ . '/CustomCommands/Services/MessageService.php';
if (file_exists($messageServiceFile)) {
    $messageServiceContent = file_get_contents($messageServiceFile);
    
    // Look for the sendToHistoryChatByFunctionalDirection method
    if (strpos($messageServiceContent, 'sendToHistoryChatByFunctionalDirection') !== false) {
        echo "Found sendToHistoryChatByFunctionalDirection method\n";
        
        // Check if it modifies the message
        if (strpos($messageServiceContent, '📊 Табель') !== false) {
            echo "⚠️  WARNING: MessageService contains '📊 Табель' - could be source of replacement!\n";
            
            // Find the specific line
            $lines = explode("\n", $messageServiceContent);
            foreach ($lines as $lineNum => $line) {
                if (strpos($line, '📊 Табель') !== false) {
                    echo "  Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
                }
            }
        }
    }
}

// Step 5: Check database service
echo "\nChecking DatabaseService::addUserTabel...\n";

$databaseServiceFile = __DIR__ . '/CustomCommands/Services/DatabaseService.php';
if (file_exists($databaseServiceFile)) {
    $databaseServiceContent = file_get_contents($databaseServiceFile);
    
    if (strpos($databaseServiceContent, 'addUserTabel') !== false) {
        echo "Found addUserTabel method\n";
        
        // Check if it modifies the message
        if (strpos($databaseServiceContent, '📊 Табель') !== false) {
            echo "⚠️  WARNING: DatabaseService contains '📊 Табель'!\n";
        } else {
            echo "✅ DatabaseService does NOT contain '📊 Табель'\n";
        }
    }
}

// Step 6: Check for any global text replacement functions
echo "\n🔍 STEP 5: Checking for global text replacement functions...\n";

$suspiciousFunctions = [
    'fixEmojiEncoding',
    'ensureUtf8Encoding',
    'fixKnownCyrillicPatterns',
    'str_replace',
    'preg_replace'
];

foreach ($suspiciousFunctions as $func) {
    echo "Checking for '{$func}' usage...\n";
    
    // Search in TabelEntry.php
    $tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');
    if (strpos($tabelEntryContent, $func) !== false) {
        echo "  Found '{$func}' in TabelEntry.php\n";
    }
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
echo "If the issue persists, it might be:\n";
echo "1. A caching issue - the old code is still running\n";
echo "2. Multiple TabelEntry classes (check for duplicates)\n";
echo "3. External system modifying the message after it's created\n";
echo "4. The fix wasn't applied to the correct file\n";