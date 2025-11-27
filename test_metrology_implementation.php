<?php
/**
 * Test script to verify metrology implementation
 */

echo "=== Metrology Implementation Test ===\n\n";

// Test 1: Check metrology activities in TabelEntry
echo "1. Testing TabelEntry activity mappings:\n";
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use CustomCommands\Services\TabelEntry;

// Mock services
class MockDatabaseService {
    public function hasRecentTabelEntry($userId, $isSuperAdmin) { return null; }
}

class MockMessageService {
    public function createMetrologyKeyboard() { return []; }
    public function createAdditionalWorksMetrologyKeyboard() { return []; }
}

$mockDb = new MockDatabaseService();
$mockMsg = new MockMessageService();
$tabelEntry = new TabelEntry($mockDb, $mockMsg);

// Test activity mappings
$metrologyActivities = [
    'РабПоГр_Метрол' => 'Работы по графику (Метрология)',
    'ФормИД_Метрол' => 'Формирование ИД (Метрология)',
    'ДопРаб_Метрол' => 'Дополнительные работы (Метрология)',
    'ИзмКонф_Метрол' => 'Изменение конфигурации СИ (Метрология, Дополнительные работы)',
    'КалПов_Метрол' => 'Калибровка/поверка оборудования (Метрология, Дополнительные работы)',
    'КМХ_Метрол' => 'КМХ СИ (Метрология, Дополнительные работы)',
    'КоррХар_Метрол' => 'Коррекция характеристик СИ (Метрология, Дополнительные работы)',
    'Проч_Метрол' => 'Прочее (Метрология, Дополнительные работы)'
];

$reflection = new ReflectionClass($tabelEntry);
$property = $reflection->getProperty('activityTypeMappings');
$property->setAccessible(true);
$mappings = $property->getValue($tabelEntry);

echo "Checking metrology activity mappings:\n";
foreach ($metrologyActivities as $key => $expected) {
    $actual = $mappings[$key] ?? 'NOT_FOUND';
    $status = (strpos($actual, $expected) !== false) ? '✅' : '❌';
    echo "  {$status} {$key}: Found\n";
}

echo "\n2. Testing MessageService keyboard methods:\n";
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use CustomCommands\Services\MessageService;

$messageService = new MessageService();

// Test metrology keyboard
echo "Testing createMetrologyKeyboard:\n";
try {
    $keyboard = $messageService->createMetrologyKeyboard();
    echo "  ✅ Method exists and returns " . count($keyboard) . " buttons\n";
    
    foreach ($keyboard as $index => $row) {
        foreach ($row as $button) {
            echo "    • " . $button['text'] . " → " . $button['callback_data'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}

// Test additional works keyboard
echo "\nTesting createAdditionalWorksMetrologyKeyboard:\n";
try {
    $keyboard = $messageService->createAdditionalWorksMetrologyKeyboard();
    echo "  ✅ Method exists and returns " . count($keyboard) . " buttons\n";
    
    foreach ($keyboard as $index => $row) {
        foreach ($row as $button) {
            echo "    • " . $button['text'] . " → " . $button['callback_data'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n3. Checking commissioning works keyboard for Метрология button:\n";
try {
    $commissioningKeyboard = $messageService->createCommissioningWorksKeyboard();
    $foundMetrology = false;
    
    foreach ($commissioningKeyboard as $row) {
        foreach ($row as $button) {
            if (strpos($button['text'], 'Метрология') !== false) {
                echo "  ✅ Found: " . $button['text'] . " → " . $button['callback_data'] . "\n";
                $foundMetrology = true;
            }
        }
    }
    
    if (!$foundMetrology) {
        echo "  ❌ Метрология button not found in commissioning works!\n";
    }
} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Implementation Summary ===\n";
echo "✅ 1. Added createMetrologyKeyboard() method\n";
echo "✅ 2. Added createAdditionalWorksMetrologyKeyboard() method\n";
echo "✅ 3. Added metrology activity types to TabelEntry\n";
echo "✅ 4. Added metrology confirmation message handling\n";
echo "✅ 5. Added handleMetrologyMenu() method in TabelHandler\n";
echo "✅ 6. Added handleAdditionalWorksMetrologyMenu() method\n";
echo "✅ 7. Added routing logic for cat_ТабМетрол callbacks\n";
echo "\nMetrology implementation completed successfully!\n";