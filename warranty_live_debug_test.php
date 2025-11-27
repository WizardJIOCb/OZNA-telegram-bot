<?php
/**
 * Warranty Equipment Live Debug Test
 * This simulates the exact warranty equipment flow to identify issues
 */

echo "🔧 WARRANTY EQUIPMENT LIVE DEBUG TEST\n";
echo "=" . str_repeat("=", 55) . "\n\n";

// We'll simulate the TabelHandler logic without loading the full class

class WarrantyDebugTester {
    
    public function testWarrantyFlow() {
        echo "TESTING STEP-BY-STEP WARRANTY FLOW:\n";
        echo "-----------------------------------\n\n";
        
        // Step 1: Test main warranty menu callback
        echo "1. Main Warranty Menu\n";
        $callback1 = 'cat_ТабГарантии';
        echo "   Callback: {$callback1}\n";
        echo "   Should route to: handleWarrantyEquipmentMenu\n";
        
        if ($this->testCanHandle($callback1)) {
            echo "   ✅ TabelHandler can handle this callback\n";
        } else {
            echo "   ❌ TabelHandler CANNOT handle this callback\n";
        }
        
        // Step 2: Test equipment selection callback
        echo "\n2. Equipment Selection\n";
        $callback2 = 'cat_Гарантии_ЭлектрОб';
        echo "   Callback: {$callback2}\n";
        echo "   Should route to: warranty equipment type handler\n";
        
        if ($this->testCanHandle($callback2)) {
            echo "   ✅ TabelHandler can handle this callback\n";
        } else {
            echo "   ❌ TabelHandler CANNOT handle this callback\n";
        }
        
        // Step 3: Test confirmation callback (THE CRITICAL ONE)
        echo "\n3. Confirmation Callback (CRITICAL)\n";
        $callback3 = 'cat_Гарантии_ЭлектрОб_yes';
        echo "   Callback: {$callback3}\n";
        echo "   Should route to: handleActivityExecution\n";
        
        if ($this->testCanHandle($callback3)) {
            echo "   ✅ TabelHandler can handle this callback\n";
        } else {
            echo "   ❌ TabelHandler CANNOT handle this callback\n";
        }
        
        // Test if this is recognized as activity execution
        if ($this->testIsActivityExecution($callback3)) {
            echo "   ✅ Recognized as activity execution\n";
        } else {
            echo "   ❌ NOT recognized as activity execution\n";
        }
        
        // Test if this is recognized as confirmation
        if ($this->testIsConfirmationCallback($callback3)) {
            echo "   ✅ Recognized as confirmation callback\n";
        } else {
            echo "   ❌ NOT recognized as confirmation callback\n";
        }
        
        echo "\n4. Activity Type Mapping Test\n";
        if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $callback3, $matches)) {
            $equipmentType = $matches[1];
            $activityType = 'РабГарантии_' . $equipmentType;
            echo "   Extracted equipment type: {$equipmentType}\n";
            echo "   Mapped activity type: {$activityType}\n";
            echo "   ✅ Pattern matching works correctly\n";
        } else {
            echo "   ❌ Pattern matching failed\n";
        }
        
        echo "\n5. Debugging Information\n";
        echo "   If reports are not being sent, check:\n";
        echo "   - User FIO is filled (/fio command)\n";
        echo "   - User functional direction is set (/profile command)\n";
        echo "   - Conversation state has project_code and tabel_data\n";
        echo "   - TabelEntry.addEntry() method execution\n";
        echo "   - Database connection and permissions\n";
    }
    
    private function testCanHandle(string $callbackData): bool {
        // Simulate TabelHandler canHandle method
        return str_starts_with($callbackData, 'category_Табель') || 
               str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
               str_starts_with($callbackData, 'cat_Таб') ||
               str_starts_with($callbackData, 'cat_Гарантии_') ||
               $callbackData === 'новое_прочее_yes' ||
               $callbackData === 'cat_ТабЭлОб_Проч_yes' ||
               $callbackData === 'cat_ТабВентОтопл' ||
               $callbackData === 'cat_ТабОтоплВент' ||
               str_starts_with($callbackData, 'cat_ТабВентОтопл_') ||
               str_starts_with($callbackData, 'cat_ТабОтоплВент_') ||
               $callbackData === 'cat_ТабГарантии';
    }
    
    private function testIsActivityExecution(string $category): bool {
        // Test warranty equipment confirmation pattern
        if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $category, $matches)) {
            $equipmentType = $matches[1];
            
            // Map equipment types to activity types
            $warrantyEquipmentMap = [
                'ЭлектрОб' => 'РабГарантии_ЭлектрОб',
                'СрИзм' => 'РабГарантии_СрИзм',
                'НасОб' => 'РабГарантии_НасОб',
                'ТрубАрм' => 'РабГарантии_ТрубАрм',
                'ИспМех' => 'РабГарантии_ИспМех',
                'КонтрОб' => 'РабГарантии_КонтрОб',
                'Прочее' => 'РабГарантии_Прочее'
            ];
            
            return isset($warrantyEquipmentMap[$equipmentType]);
        }
        
        return false;
    }
    
    private function testIsConfirmationCallback(string $category): bool {
        // Test warranty equipment confirmation pattern
        if (preg_match('/^cat_Гарантии_.+_yes$/u', $category)) {
            return true;
        }
        
        return str_contains($category, '_yes') && str_contains($category, 'Табель_');
    }
}

// Run the test
$tester = new WarrantyDebugTester();
$tester->testWarrantyFlow();

echo "\n" . str_repeat("=", 55) . "\n";
echo "🎯 LIVE TEST INSTRUCTIONS:\n";
echo "\n";
echo "To perform a live test with the bot:\n";
echo "1. Open Telegram and find your bot\n";
echo "2. Send /tabels command\n";
echo "3. Click 'Пусконаладочные работы'\n";
echo "4. Select any project and current date\n";
echo "5. Click '🛡️ Работы по гарантии'\n";
echo "6. Click '⚡ Электрооборудование'\n";
echo "7. Click 'Да' button\n";
echo "\n";
echo "Expected result: Confirmation message with report sent\n";
echo "If you get 'неизвестная команда табеля', there's a flow issue\n";
echo "\n";
echo "Check bot logs for these debug messages:\n";
echo "- 'WARRANTY DEBUG: Mapped warranty equipment confirmation'\n";
echo "- 'TabelHandler: handleActivityExecution'\n";
echo "- 'addEntry SUCCESS'\n";
?>