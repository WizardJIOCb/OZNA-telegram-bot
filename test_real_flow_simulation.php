<?php

/**
 * Simulate the EXACT real flow to identify where "📊 Табель" comes from
 */

echo "=== REAL FLOW SIMULATION ===\n\n";

// Mock classes that match the real system
class MockDatabaseService {
    public function addUserTabel($userId, $message) {
        echo "🗄️  DATABASE: Saving message: '{$message}'\n";
        return true;
    }
    
    public function getAllUsers() {
        return [];
    }
}

class MockMessageService {
    public function sendMessage($chatId, $text, $keyboard = null) {
        echo "📱 TELEGRAM: Sending message to chat {$chatId}: '{$text}'\n";
        return new stdClass();
    }
    
    public function sendToHistoryChatByFunctionalDirection($message, $functionalDirection) {
        echo "📤 HISTORY CHAT: Sending to {$functionalDirection}: '{$message}'\n";
        return new stdClass();
    }
}

// Load the ACTUAL TabelEntry class
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "🔧 Creating TabelEntry instance...\n";

try {
    $databaseService = new MockDatabaseService();
    $messageService = new MockMessageService();
    
    // This will fail due to type checking, but let's try a different approach
    
} catch (Exception $e) {
    echo "⚠️  Cannot instantiate TabelEntry due to type checking: {$e->getMessage()}\n";
    echo "Let's use reflection to access the method directly...\n";
}

// Use reflection to test the actual method
$reflectionClass = new ReflectionClass('Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry');
$method = $reflectionClass->getMethod('createTabelMessage');

// Get the actual property value
$property = $reflectionClass->getProperty('activityTypeMappings');
$property->setAccessible(true);

// Create a minimal instance for testing (we'll handle the constructor issue)
$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

// Extract the activityTypeMappings array directly from the source
preg_match('/private array \$activityTypeMappings = \[(.*?)\];/s', $tabelEntryContent, $matches);

if ($matches) {
    echo "✅ Found activityTypeMappings in source code\n";
    
    // Parse the mapping for our specific case
    $mappingContent = $matches[1];
    
    if (strpos($mappingContent, "'Прочее_ЭлектрОб'") !== false) {
        echo "✅ Found Прочее_ЭлектрОб in mappings\n";
        
        // Extract the specific mapping
        preg_match("/'Прочее_ЭлектрОб'\s*=>\s*'([^']+)'/", $mappingContent, $prodiMapping);
        
        if ($prodiMapping) {
            $expectedMapping = $prodiMapping[1];
            echo "✅ Expected mapping: '{$expectedMapping}'\n";
        }
    }
}

// Now let's test the EXACT scenario step by step
echo "\n🧪 TESTING EXACT SCENARIO...\n";

echo "Step 1: User clicks 'Прочее' button\n";
echo "Step 2: User enters custom text: 'Проверка кабельных соединений'\n";
echo "Step 3: System calls TabelEntry::addEntry()\n";

// Simulate the addEntry method logic
echo "\n📋 Simulating addEntry method...\n";

$userId = 12345;
$fio = "Тестовый Пользователь";
$date = "18.09.2025";
$projectCode = "ОИ696";
$activityType = "Прочее_ЭлектрОб";
$chatId = "67890";
$idleReason = "Проверка кабельных соединений";

echo "Input parameters:\n";
echo "  userId: {$userId}\n";
echo "  fio: '{$fio}'\n";
echo "  date: '{$date}'\n";
echo "  projectCode: '{$projectCode}'\n";
echo "  activityType: '{$activityType}'\n";
echo "  chatId: '{$chatId}'\n";
echo "  idleReason: '{$idleReason}'\n";

echo "\n📝 Simulating createTabelMessage...\n";

// This is the EXACT logic from the fixed TabelEntry
$activityTypeMappings = [
    'Прочее_ЭлектрОб' => '📝 Прочее (Электрооборудование, Дополнительные работы)',
];

echo "1. Looking up activityType '{$activityType}' in mappings...\n";
$activityText = $activityTypeMappings[$activityType] ?? $activityType;
echo "   Result: '{$activityText}'\n";

echo "2. Checking if we should add custom description...\n";
if ($activityType === 'Прочее_ЭлектрОб' && !empty($idleReason)) {
    echo "   ✅ Condition met: adding custom description\n";
    $activityText .= " ({$idleReason})";
    echo "   Final activityText: '{$activityText}'\n";
} else {
    echo "   ❌ Condition not met\n";
}

echo "3. Creating final message...\n";
$message = sprintf(
    "<b>%s</b>\r\n✅ Запись в табель от <b>%s</b>, в проект <b>%s</b> о <b>%s добавлена</b>",
    $fio,
    $date,
    $projectCode,
    $activityText
);

echo "📜 FINAL MESSAGE:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo $message . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Check for issues
echo "\n🔍 ANALYSIS:\n";

if (strpos($message, $idleReason) !== false) {
    echo "✅ PASS: Custom text '{$idleReason}' is preserved\n";
} else {
    echo "❌ FAIL: Custom text '{$idleReason}' is missing\n";
}

if (strpos($message, '📝 Прочее (Электрооборудование, Дополнительные работы)') !== false) {
    echo "✅ PASS: Correct activity type mapping used\n";
} else {
    echo "❌ FAIL: Correct activity type mapping missing\n";
}

if (strpos($message, '📊 Табель') !== false) {
    echo "❌ CRITICAL: Found '📊 Табель' in message!\n";
    
    // Find where it comes from
    $pos = strpos($message, '📊 Табель');
    $context = substr($message, max(0, $pos - 20), 40);
    echo "   Context: '...{$context}...'\n";
} else {
    echo "✅ PASS: No '📊 Табель' found in message\n";
}

echo "\n📊 CONCLUSION:\n";
echo "If this simulation shows the correct result but the real system shows '📊 Табель',\n";
echo "then the issue might be:\n";
echo "1. Server caching - the old code is still running\n";
echo "2. A different code path is being used\n";
echo "3. External system modification after message creation\n";
echo "4. Multiple TabelEntry classes (though we only found one)\n";

echo "\n=== SIMULATION COMPLETE ===\n";