<?php

// Test the TabelEntry idle reason integration

require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

echo "=== TESTING TABELENTRY IDLE REASON INTEGRATION ===\n\n";

// Mock minimal dependencies
class MockDatabaseService {
    public function __construct() {}
}

class MockMessageService {
    public function __construct() {}
}

// Test the TabelEntry idle confirmation
$dbService = new MockDatabaseService();
$msgService = new MockMessageService();
$tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($dbService, $msgService);

// Test scenarios
$testScenarios = [
    [
        'activityType' => 'Простой',
        'projectCode' => 'ОИ834',
        'activityName' => 'Простой',
        'idleReason' => '',
        'description' => 'Empty idle reason (current behavior)'
    ],
    [
        'activityType' => 'Простой',
        'projectCode' => 'ОИ834',
        'activityName' => 'Простой',
        'idleReason' => 'Простой',
        'description' => 'Basic idle reason (current result)'
    ],
    [
        'activityType' => 'Простой',
        'projectCode' => 'ОИ834',
        'activityName' => 'Простой',
        'idleReason' => 'Ожидание допоставки (Дефицит по спецификации)',
        'description' => 'Enhanced idle reason (desired result)'
    ],
    [
        'activityType' => 'Простой',
        'projectCode' => 'ОИ834',
        'activityName' => 'Простой',
        'idleReason' => 'Дефицит по спецификации',
        'description' => 'Simple subcategory only (fallback)'
    ]
];

foreach ($testScenarios as $i => $scenario) {
    echo ($i + 1) . ". Testing: {$scenario['description']}\n";
    echo "   Activity: {$scenario['activityType']}\n";
    echo "   Project: {$scenario['projectCode']}\n";
    echo "   Idle reason: '{$scenario['idleReason']}'\n";
    
    try {
        $result = $tabelEntry->getConfirmationMessage(
            $scenario['activityType'], 
            $scenario['projectCode'], 
            $scenario['activityName'], 
            $scenario['idleReason']
        );
        
        echo "   RESULT:\n";
        echo "   {$result}\n";
        
        // Check if idle reason is included
        if (!empty($scenario['idleReason']) && strpos($result, $scenario['idleReason']) !== false) {
            echo "   ✅ Idle reason properly included\n";
        } elseif (empty($scenario['idleReason'])) {
            echo "   ✅ No idle reason expected\n";
        } else {
            echo "   ❌ Idle reason missing or incorrect\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== TEST COMPLETE ===\n";
echo "The TabelEntry modifications should now support idle reason display for commissioning works.\n";
?>