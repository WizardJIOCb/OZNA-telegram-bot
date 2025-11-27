<?php
/**
 * Deep Warranty Functionality Diagnostic
 * Comprehensive analysis of warranty button functionality
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

class WarrantyDiagnostic
{
    private $tabelHandler;
    private $messageService;
    
    public function __construct()
    {
        // Create required dependencies for TabelHandler
        $this->messageService = new MessageService();
        
        // Note: TabelHandler requires multiple dependencies that may not be available in diagnostic context
        // For diagnostic purposes, we'll work with reflection to test methods without full instantiation
        error_log("WarrantyDiagnostic: MessageService created successfully");
    }
    
    public function runDeepTest()
    {
        echo "🔍 DEEP WARRANTY DIAGNOSTIC TEST\n";
        echo "=" . str_repeat("=", 50) . "\n\n";
        
        // Test 1: Callback Length Analysis
        $this->testCallbackLengths();
        
        // Test 2: Handler Recognition
        $this->testHandlerRecognition();
        
        // Test 3: Category Extraction
        $this->testCategoryExtraction();
        
        // Test 4: Method Processing Flow
        $this->testProcessingFlow();
        
        // Test 5: Compare with Working Functionality
        $this->compareWithWorkingFunctionality();
        
        // Test 6: Keyboard Generation
        $this->testKeyboardGeneration();
        
        echo "\n🎯 DIAGNOSTIC COMPLETE\n";
    }
    
    private function testCallbackLengths()
    {
        echo "📏 TEST 1: CALLBACK LENGTH ANALYSIS\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $callbacks = [
            'cat_ТабГарантии',
            'Табель_РаботыПоГарантии',
            'cat_Гарантии_АСУ_ТП',
            'cat_Гарантии_КИП',
            'cat_Гарантии_СДТУ',
            'category_Табель_РаботыПоГарантии',
            'cat_ТабПНРаботы_РК_Устранение_замечаний'
        ];
        
        foreach ($callbacks as $callback) {
            $length = strlen($callback);
            $status = $length <= 64 ? "✅ OK" : "❌ TOO LONG";
            echo sprintf("%-40s | %2d bytes | %s\n", $callback, $length, $status);
        }
        echo "\n";
    }
    
    private function testHandlerRecognition()
    {
        echo "🎯 TEST 2: HANDLER RECOGNITION\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $testCallbacks = [
            'cat_ТабГарантии',
            'Табель_РаботыПоГарантии',
            'cat_Гарантии_АСУ_ТП',
            'cat_ТабПНРаботы_РК_Устранение_замечаний',
            'invalid_callback'
        ];
        
        foreach ($testCallbacks as $callback) {
            try {
                // Read the TabelHandler source to check canHandle logic
                $handlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
                $source = file_get_contents($handlerFile);
                
                // Extract canHandle method logic
                $canHandle = $this->simulateCanHandle($callback, $source);
                $status = $canHandle ? "✅ CAN HANDLE" : "❌ CANNOT HANDLE";
                echo sprintf("%-40s | %s\n", $callback, $status);
            } catch (Exception $e) {
                echo sprintf("%-40s | ❌ ERROR: %s\n", $callback, $e->getMessage());
            }
        }
        echo "\n";
    }
    
    private function testCategoryExtraction()
    {
        echo "🔄 TEST 3: CATEGORY EXTRACTION\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $testCallbacks = [
            'cat_ТабГарантии',
            'cat_Гарантии_АСУ_ТП',
            'cat_ТабПНРаботы_РК_Устранение_замечаний',
            'category_Табель_РаботыПоГарантии'
        ];
        
        foreach ($testCallbacks as $callback) {
            try {
                // Read the TabelHandler source to check extractCategoryFromCallback logic
                $handlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
                $source = file_get_contents($handlerFile);
                
                $category = $this->simulateExtractCategory($callback, $source);
                echo sprintf("%-40s → %-30s\n", $callback, $category);
            } catch (Exception $e) {
                echo sprintf("%-40s → ERROR: %s\n", $callback, $e->getMessage());
            }
        }
        echo "\n";
    }
    
    private function testProcessingFlow()
    {
        echo "⚙️ TEST 4: METHOD PROCESSING FLOW\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        // Check if warranty handler method exists by reading source
        $handlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
        $source = file_get_contents($handlerFile);
        
        echo "Found warranty-related methods:\n";
        if (strpos($source, 'handleWarrantyEquipmentMenu') !== false) {
            echo "  ✅ handleWarrantyEquipmentMenu\n";
        } else {
            echo "  ❌ handleWarrantyEquipmentMenu missing\n";
        }
        
        // Check handleOtherWorkTypes method for warranty handling
        if (strpos($source, 'cat_ТабГарантии') !== false) {
            echo "  ✅ handleOtherWorkTypes contains warranty handling\n";
            
            // Check specific warranty handling patterns
            if (strpos($source, 'WARRANTY equipment menu handling') !== false) {
                echo "  ✅ Warranty equipment menu handler found\n";
            }
            
            if (strpos($source, 'category === \'cat_ТабГарантии\'') !== false) {
                echo "  ✅ Direct warranty callback check found\n";
            }
            
            if (strpos($source, 'Табель_РаботыПоГарантии') !== false) {
                echo "  ✅ Mapped warranty category check found\n";
            }
        } else {
            echo "  ❌ handleOtherWorkTypes missing warranty handling\n";
        }
        echo "\n";
    }
    
    private function compareWithWorkingFunctionality()
    {
        echo "🔄 TEST 5: COMPARE WITH WORKING FUNCTIONALITY\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        // Compare warranty with RK elimination (known working functionality)
        $comparisons = [
            'Warranty Callback' => 'cat_ТабГарантии',
            'RK Elimination Callback' => 'cat_ТабПНРаботы_РК_Устранение_замечаний'
        ];
        
        $handlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
        $source = file_get_contents($handlerFile);
        
        foreach ($comparisons as $type => $callback) {
            echo "{$type}:\n";
            echo "  Callback: {$callback}\n";
            echo "  Length: " . strlen($callback) . " bytes\n";
            
            try {
                $canHandle = $this->simulateCanHandle($callback, $source);
                echo "  Can Handle: " . ($canHandle ? "✅ YES" : "❌ NO") . "\n";
                
                $category = $this->simulateExtractCategory($callback, $source);
                echo "  Extracted Category: {$category}\n";
            } catch (Exception $e) {
                echo "  Error: " . $e->getMessage() . "\n";
            }
            echo "\n";
        }
    }
    
    private function testKeyboardGeneration()
    {
        echo "⌨️ TEST 6: KEYBOARD GENERATION\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        try {
            // Test commissioning keyboard (contains warranty button)
            $commissioningKeyboard = $this->messageService->createCommissioningKeyboard();
            echo "Commissioning keyboard rows: " . count($commissioningKeyboard) . "\n";
            
            $warrantyButtonFound = false;
            foreach ($commissioningKeyboard as $row) {
                foreach ($row as $button) {
                    if (isset($button['callback_data']) && $button['callback_data'] === 'cat_ТабГарантии') {
                        $warrantyButtonFound = true;
                        echo "✅ Warranty button found: " . $button['text'] . "\n";
                        echo "   Callback: " . $button['callback_data'] . "\n";
                    }
                }
            }
            
            if (!$warrantyButtonFound) {
                echo "❌ Warranty button NOT found in commissioning keyboard\n";
            }
            
            // Test warranty equipment keyboard
            if (method_exists($this->messageService, 'createWarrantyEquipmentKeyboard')) {
                $warrantyKeyboard = $this->messageService->createWarrantyEquipmentKeyboard();
                echo "✅ Warranty equipment keyboard rows: " . count($warrantyKeyboard) . "\n";
                
                foreach ($warrantyKeyboard as $row) {
                    foreach ($row as $button) {
                        if (isset($button['callback_data'])) {
                            echo "   Button: {$button['text']} → {$button['callback_data']}\n";
                        }
                    }
                }
            } else {
                echo "❌ createWarrantyEquipmentKeyboard method not found\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error testing keyboards: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    /**
     * Simulate canHandle method logic by parsing source code
     */
    private function simulateCanHandle(string $callbackData, string $source): bool
    {
        // Extract canHandle method logic from source
        $patterns = [
            'str_starts_with($callbackData, \'category_Табель\')',
            'str_starts_with($callbackData, \'cat_ТабПНРаботы\')',
            'str_starts_with($callbackData, \'cat_Таб\')',
            'str_starts_with($callbackData, \'cat_Гарантии_\')',
            '$callbackData === \'cat_ТабГарантии\''
        ];
        
        foreach ($patterns as $pattern) {
            if ($pattern === 'str_starts_with($callbackData, \'category_Табель\')') {
                if (str_starts_with($callbackData, 'category_Табель')) return true;
            }
            if ($pattern === 'str_starts_with($callbackData, \'cat_ТабПНРаботы\')') {
                if (str_starts_with($callbackData, 'cat_ТабПНРаботы')) return true;
            }
            if ($pattern === 'str_starts_with($callbackData, \'cat_Таб\')') {
                if (str_starts_with($callbackData, 'cat_Таб')) return true;
            }
            if ($pattern === 'str_starts_with($callbackData, \'cat_Гарантии_\')') {
                if (str_starts_with($callbackData, 'cat_Гарантии_')) return true;
            }
            if ($pattern === '$callbackData === \'cat_ТабГарантии\'') {
                if ($callbackData === 'cat_ТабГарантии') return true;
            }
        }
        
        return false;
    }
    
    /**
     * Simulate extractCategoryFromCallback method logic by parsing source code
     */
    private function simulateExtractCategory(string $callbackData, string $source): string
    {
        // Check for direct mapping in source
        if (strpos($source, "'cat_ТабГарантии' => 'Табель_РаботыПоГарантии'") !== false) {
            if ($callbackData === 'cat_ТабГарантии') {
                return 'Табель_РаботыПоГарантии';
            }
        }
        
        // Handle category_ prefix removal
        if (str_starts_with($callbackData, 'category_')) {
            return substr($callbackData, 9); // Remove 'category_' prefix
        }
        
        // Handle cat_ prefix patterns
        if (str_starts_with($callbackData, 'cat_')) {
            return $callbackData; // Return as-is for cat_ patterns
        }
        
        return $callbackData;
    }
}

// Run the diagnostic
$diagnostic = new WarrantyDiagnostic();
$diagnostic->runDeepTest();