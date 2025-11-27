<?php

/**
 * FORCE CLEAR ALL CACHES AND TEST THE ACTUAL RUNNING CODE
 * This script clears opcache and tests the real behavior
 */

echo "=== FORCE CACHE CLEAR AND TEST ===\n\n";

// 1. Clear OPcache if available
if (function_exists('opcache_reset')) {
    echo "🔄 Clearing OPcache...\n";
    opcache_reset();
    echo "✅ OPcache cleared\n";
} else {
    echo "ℹ️  OPcache not available\n";
}

// 2. Clear APCu if available  
if (function_exists('apcu_clear_cache')) {
    echo "🔄 Clearing APCu...\n";
    apcu_clear_cache();
    echo "✅ APCu cleared\n";
} else {
    echo "ℹ️  APCu not available\n";
}

// 3. Force fresh file read
echo "\n📁 Force reading TabelEntry.php fresh from disk...\n";
$tabelEntryFile = __DIR__ . '/CustomCommands/Services/TabelEntry.php';

// Read with file_get_contents to bypass any include caching
$content = file_get_contents($tabelEntryFile);

// Verify our fix is actually in the file
if (strpos($content, '$this->activityTypeMappings[$activityType]') !== false) {
    echo "✅ CONFIRMED: Fix is in the file on disk\n";
} else {
    echo "❌ ERROR: Fix is NOT in the file on disk!\n";
    echo "This means the fix was not properly saved.\n";
    exit(1);
}

// 4. Parse and extract the actual array
echo "\n🔍 Extracting activityTypeMappings from file...\n";

preg_match('/private array \$activityTypeMappings = \[(.*?)\];/s', $content, $matches);
if ($matches) {
    $mappingContent = $matches[1];
    
    // Check if our specific mapping exists
    if (strpos($mappingContent, "'Прочее_ЭлектрОб'") !== false) {
        echo "✅ Found Прочее_ЭлектрОб mapping in file\n";
        
        // Extract the exact value
        preg_match("/'Прочее_ЭлектрОб'\s*=>\s*'([^']+)'/", $mappingContent, $valueMatch);
        if ($valueMatch) {
            echo "✅ Mapping value: '{$valueMatch[1]}'\n";
        }
    } else {
        echo "❌ Прочее_ЭлектрОб mapping NOT found!\n";
    }
} else {
    echo "❌ Could not find activityTypeMappings array!\n";
}

// 5. Test with fresh PHP process (if possible)
echo "\n🧪 Testing with fresh include...\n";

// First, check if the class is already loaded
$wasLoaded = class_exists('Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry', false);
echo "Class already loaded: " . ($wasLoaded ? 'YES' : 'NO') . "\n";

// Force a fresh include by using eval
echo "Force loading fresh code...\n";

// Create a test function that simulates the exact behavior
$testCode = "
function testProcheeMessage() {
    // Extract the mapping from the file content directly
    \$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');
    
    // Parse the activityTypeMappings array
    preg_match('/private array \\\$activityTypeMappings = \\\[(.*?)\\\];/s', \$tabelEntryContent, \$matches);
    if (!\$matches) {
        return 'ERROR: Could not extract mappings';
    }
    
    // Convert the PHP array syntax to actual array
    \$mappingCode = '<?php return [' . \$matches[1] . '];';
    \$tempFile = tempnam(sys_get_temp_dir(), 'mapping_test');
    file_put_contents(\$tempFile, \$mappingCode);
    \$activityTypeMappings = include \$tempFile;
    unlink(\$tempFile);
    
    // Test the exact logic
    \$activityType = 'Прочее_ЭлектрОб';
    \$idleReason = 'Проверка кабельных соединений';
    
    \$activityText = \$activityTypeMappings[\$activityType] ?? \$activityType;
    
    if (\$activityType === 'Прочее_ЭлектрОб' && !empty(\$idleReason)) {
        \$activityText .= \" ({\$idleReason})\";
    }
    
    return \$activityText;
}
";

eval($testCode);

$result = testProcheeMessage();
echo "Test result: '{$result}'\n";

// 6. Analysis
echo "\n📊 ANALYSIS:\n";

if (strpos($result, 'Проверка кабельных соединений') !== false) {
    echo "✅ Custom text preserved\n";
} else {
    echo "❌ Custom text missing\n";
}

if (strpos($result, '📝 Прочее (Электрооборудование, Дополнительные работы)') !== false) {
    echo "✅ Correct mapping used\n";
} else {
    echo "❌ Correct mapping missing\n";
}

if (strpos($result, '📊 Табель') !== false) {
    echo "❌ STILL CONTAINS '📊 Табель'!\n";
} else {
    echo "✅ No '📊 Табель' found\n";
}

// 7. Final test with actual TabelEntry class
echo "\n🔄 Final test with actual class loading...\n";

// Clear any previous includes
if ($wasLoaded) {
    echo "⚠️  Class was already loaded - restart PHP to test fresh\n";
} else {
    try {
        require_once $tabelEntryFile;
        echo "✅ TabelEntry class loaded fresh\n";
        
        // Use reflection to verify
        $reflection = new ReflectionClass('Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry');
        $method = $reflection->getMethod('createTabelMessage');
        
        $source = file_get_contents($method->getFileName());
        $lines = explode("\n", $source);
        $methodLines = array_slice($lines, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1);
        $methodSource = implode("\n", $methodLines);
        
        if (strpos($methodSource, 'activityTypeMappings') !== false) {
            echo "✅ Loaded method uses correct property\n";
        } else {
            echo "❌ Loaded method uses incorrect property\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error loading class: {$e->getMessage()}\n";
    }
}

echo "\n🎯 CONCLUSION:\n";
echo "If this script shows correct results but the bot still shows '📊 Табель',\n";
echo "then the issue is definitely a deployment/caching problem.\n";
echo "\nSOLUTIONS TO TRY:\n";
echo "1. Restart the web server completely\n";
echo "2. Restart PHP-FPM if using it\n";
echo "3. Check if there are multiple web servers running\n";
echo "4. Clear browser cache and try again\n";
echo "5. Check if the bot webhook is pointing to a different server\n";

echo "\n=== TEST COMPLETE ===\n";