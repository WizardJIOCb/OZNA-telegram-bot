<?php
/**
 * Deep Comparison Test: Warranty vs Working Equipment
 * Compare warranty equipment with fully working pump equipment
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "🔍 DEEP COMPARISON TEST: WARRANTY vs WORKING EQUIPMENT\n";
echo "=" . str_repeat("=", 60) . "\n\n";

class WarrantyDeepTest
{
    private $handlerFile;
    private $messageServiceFile;
    
    public function __construct()
    {
        $this->handlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
        $this->messageServiceFile = __DIR__ . '/CustomCommands/Services/MessageService.php';
    }
    
    public function runDeepTest()
    {
        echo "🎯 COMPARING: WARRANTY EQUIPMENT vs PUMP EQUIPMENT\n";
        echo "-" . str_repeat("-", 50) . "\n\n";
        
        // Test 1: Check callback generation in MessageService
        $this->testCallbackGeneration();
        
        // Test 2: Check callback mapping
        $this->testCallbackMapping();
        
        // Test 3: Check handler recognition
        $this->testHandlerRecognition();
        
        // Test 4: Check activity execution flow
        $this->testActivityExecutionFlow();
        
        // Test 5: Deep handler comparison
        $this->deepHandlerComparison();
        
        echo "\n🎯 CONCLUSION AND RECOMMENDATIONS\n";
    }
    
    private function testCallbackGeneration()
    {
        echo "📋 TEST 1: CALLBACK GENERATION IN MESSAGESERVICE\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        if (!file_exists($this->messageServiceFile)) {
            echo "❌ MessageService.php not found\n";
            return;
        }
        
        $source = file_get_contents($this->messageServiceFile);
        
        echo "Warranty Equipment Keyboard:\n";
        $warrantyCallbacks = [
            'category_Табель_РабГарантии_ЭлектрОб',
            'category_Табель_РабГарантии_НасОб'
        ];
        
        foreach ($warrantyCallbacks as $callback) {
            if (strpos($source, $callback) !== false) {
                echo "  ✅ Found: {$callback}\n";
            } else {
                echo "  ❌ Missing: {$callback}\n";
            }
        }
        
        // Check if createWarrantyEquipmentKeyboard exists
        if (strpos($source, 'createWarrantyEquipmentKeyboard') !== false) {
            echo "  ✅ createWarrantyEquipmentKeyboard method exists\n";
        } else {
            echo "  ❌ createWarrantyEquipmentKeyboard method missing\n";
        }
        
        echo "\n";
    }
    
    private function testCallbackMapping()
    {
        echo "🗺️ TEST 2: CALLBACK MAPPING IN EXTRACTCATEGORYFROMCALLBACK\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        if (!file_exists($this->handlerFile)) {
            echo "❌ TabelHandler.php not found\n";
            return;
        }
        
        $source = file_get_contents($this->handlerFile);
        
        $mappingsToCheck = [
            'category_Табель_РабГарантии_ЭлектрОб' => 'Табель_РабГарантии_ЭлектрОб',
            'category_Табель_РабГарантии_НасОб' => 'Табель_РабГарантии_НасОб'
        ];
        
        echo "Checking warranty callback mappings:\n";
        foreach ($mappingsToCheck as $callback => $expected) {
            if (strpos($source, "'{$callback}' => '{$expected}'") !== false) {
                echo "  ✅ Mapped: {$callback} → {$expected}\n";
            } else {
                echo "  ❌ Missing: {$callback} → {$expected}\n";
            }
        }
        
        echo "\n";
    }
    
    private function testHandlerRecognition()
    {
        echo "🎯 TEST 3: HANDLER RECOGNITION IN HANDLEOTHERWORKTYPES\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $source = file_get_contents($this->handlerFile);
        
        $handlersToCheck = [
            'Табель_РабГарантии_ЭлектрОб' => 'warranty electrical equipment',
            'Табель_РабГарантии_НасОб' => 'warranty pump equipment'
        ];
        
        echo "Checking warranty equipment handlers:\n";
        foreach ($handlersToCheck as $category => $description) {
            if (strpos($source, "category === '{$category}'") !== false) {
                echo "  ✅ Handler found: {$category} ({$description})\n";
            } else {
                echo "  ❌ Handler missing: {$category} ({$description})\n";
            }
        }
        
        echo "\n";
    }
    
    private function testActivityExecutionFlow()
    {
        echo "⚙️ TEST 4: ACTIVITY EXECUTION FLOW\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $source = file_get_contents($this->handlerFile);
        
        // Check isActivityExecution method
        echo "Checking isActivityExecution method:\n";
        if (strpos($source, "cat_Гарантии_(.+)_yes") !== false) {
            echo "  ✅ Warranty confirmation pattern found\n";
        } else {
            echo "  ❌ Warranty confirmation pattern missing\n";
        }
        
        // Check handleActivityExecution method
        echo "Checking handleActivityExecution method:\n";
        if (strpos($source, "cat_Гарантии_(.+)_yes") !== false) {
            echo "  ✅ Warranty processing pattern found\n";
        } else {
            echo "  ❌ Warranty processing pattern missing\n";
        }
        
        // Check activity types array
        echo "Checking activityTypes array:\n";
        $warrantyActivityTypes = [
            'РабГарантии_ЭлектрОб',
            'РабГарантии_НасОб'
        ];
        
        foreach ($warrantyActivityTypes as $activityType) {
            if (strpos($source, "'{$activityType}'") !== false) {
                echo "  ✅ Activity type found: {$activityType}\n";
            } else {
                echo "  ❌ Activity type missing: {$activityType}\n";
            }
        }
        
        echo "\n";
    }
    
    private function deepHandlerComparison()
    {
        echo "🔬 TEST 5: DEEP HANDLER COMPARISON\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $source = file_get_contents($this->handlerFile);
        
        echo "COMPARING: Warranty Electrical vs Warranty Pump Equipment\n\n";
        
        // Extract warranty electrical handler
        $electricalPattern = '/if \(\$category === \'Табель_РабГарантии_ЭлектрОб\'\) \{.*?return.*?;\s*\}/s';
        $pumpPattern = '/if \(\$category === \'Табель_РабГарантии_НасОб\'\) \{.*?return.*?;\s*\}/s';
        
        echo "Electrical Equipment Handler:\n";
        if (preg_match($electricalPattern, $source, $electricalMatch)) {
            $electricalHandler = $electricalMatch[0];
            echo "  ✅ Found electrical handler\n";
            echo "  Length: " . strlen($electricalHandler) . " characters\n";
            
            // Check key components
            if (strpos($electricalHandler, 'project_code') !== false) {
                echo "  ✅ Contains project_code handling\n";
            } else {
                echo "  ❌ Missing project_code handling\n";
            }
            
            if (strpos($electricalHandler, 'cat_Гарантии_ЭлектрОб_yes') !== false) {
                echo "  ✅ Contains correct confirmation callback\n";
            } else {
                echo "  ❌ Missing correct confirmation callback\n";
            }
        } else {
            echo "  ❌ Electrical handler not found\n";
        }
        
        echo "\nPump Equipment Handler:\n";
        if (preg_match($pumpPattern, $source, $pumpMatch)) {
            $pumpHandler = $pumpMatch[0];
            echo "  ✅ Found pump handler\n";
            echo "  Length: " . strlen($pumpHandler) . " characters\n";
            
            // Check key components
            if (strpos($pumpHandler, 'project_code') !== false) {
                echo "  ✅ Contains project_code handling\n";
            } else {
                echo "  ❌ Missing project_code handling\n";
            }
            
            if (strpos($pumpHandler, 'cat_Гарантии_НасОб_yes') !== false) {
                echo "  ✅ Contains correct confirmation callback\n";
            } else {
                echo "  ❌ Missing correct confirmation callback\n";
            }
        } else {
            echo "  ❌ Pump handler not found\n";
        }
        
        // Compare the two handlers
        if (isset($electricalHandler) && isset($pumpHandler)) {
            echo "\nHANDLER COMPARISON:\n";
            $electricalLines = explode("\n", $electricalHandler);
            $pumpLines = explode("\n", $pumpHandler);
            
            echo "  Electrical handler lines: " . count($electricalLines) . "\n";
            echo "  Pump handler lines: " . count($pumpLines) . "\n";
            
            if (count($electricalLines) === count($pumpLines)) {
                echo "  ✅ Both handlers have same structure\n";
            } else {
                echo "  ⚠️ Handlers have different structure\n";
            }
        }
        
        echo "\n";
    }
}

// Run the deep test
$test = new WarrantyDeepTest();
$test->runDeepTest();

echo "🎯 NEXT STEPS:\n";
echo "1. Check if all warranty equipment handlers are identical in structure\n";
echo "2. Verify callback generation matches expected patterns\n";
echo "3. Ensure activity execution flow is complete\n";
echo "4. Test with actual button clicks if all checks pass\n";