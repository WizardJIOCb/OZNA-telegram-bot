<?php
/**
 * Debug script to identify why ШМР activity lookup is failing
 * This will help us understand what's happening in the real runtime environment
 */

// Include the TabelEntry service to test the actual lookup
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

// Create mock services for testing
class MockDatabaseService extends DatabaseService {
    public function __construct() {}
    public function hasRecentTabelEntry($userId, $isSuperAdmin = false) { return null; }
}

class MockMessageService extends MessageService {
    public function __construct() {}
}

// Create TabelEntry instance
$mockDbService = new MockDatabaseService();
$mockMsgService = new MockMessageService();
$tabelEntry = new TabelEntry($mockDbService, $mockMsgService);

echo "=== ШМР Activity Type Lookup Debug ===\n\n";

// Test cases that should work
$testCases = [
    'ШМР_ВхКонтр_РабПоГр',
    'ШМР_ЗавОбор_РабПоГр', 
    'ШМР_РасстБлок_РабПоГр',
    'ШМР_ВходнойКонтроль_РабПоГр',  // Full form
    'Автоматизация_ЭлектрОб',        // Working automation example
];

// Test the createTabelMessage method with debug logging
foreach ($testCases as $activityType) {
    echo "Testing activity type: '{$activityType}'\n";
    
    // Call createTabelMessage and capture the result
    $result = $tabelEntry->createTabelMessage(
        'Test User',
        '04.10.2025', 
        'XXX',
        $activityType
    );
    
    echo "Result: {$result}\n";
    echo "---\n\n";
}

echo "=== Check if activityTypeMappings property is accessible ===\n";

// Use reflection to check the mappings directly
$reflection = new ReflectionClass($tabelEntry);
$mappingsProperty = $reflection->getProperty('activityTypeMappings');
$mappingsProperty->setAccessible(true);
$mappings = $mappingsProperty->getValue($tabelEntry);

echo "Total mappings count: " . count($mappings) . "\n\n";

echo "ШМР related mappings found:\n";
foreach ($mappings as $key => $value) {
    if (strpos($key, 'ШМР') !== false) {
        echo "  '{$key}' => '{$value}'\n";
    }
}

echo "\n=== Test direct lookup ===\n";
foreach ($testCases as $activityType) {
    $found = isset($mappings[$activityType]);
    $value = $mappings[$activityType] ?? 'NOT FOUND';
    echo "'{$activityType}' => Found: " . ($found ? 'YES' : 'NO') . " | Value: '{$value}'\n";
}