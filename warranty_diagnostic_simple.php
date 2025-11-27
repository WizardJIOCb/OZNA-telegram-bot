<?php
/**
 * Simple Warranty Functionality Diagnostic
 * Analyzes warranty functionality without loading full classes
 */

class SimpleWarrantyDiagnostic
{
    private string $tabelHandlerPath;
    private string $messageServicePath;
    
    public function __construct()
    {
        $this->tabelHandlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
        $this->messageServicePath = __DIR__ . '/CustomCommands/Services/MessageService.php';
    }
    
    public function runDeepTest()
    {
        echo "🔍 SIMPLE WARRANTY DIAGNOSTIC TEST\n";
        echo "=" . str_repeat("=", 50) . "\n\n";
        
        // Test 1: Callback Length Analysis
        $this->testCallbackLengths();
        
        // Test 2: Source Code Analysis
        $this->analyzeSourceCode();
        
        // Test 3: Category Mapping Analysis
        $this->analyzeCategoryMapping();
        
        // Test 4: Handler Logic Analysis
        $this->analyzeHandlerLogic();
        
        // Test 5: Compare with Working Functionality
        $this->compareWithWorkingFunctionality();
        
        // Test 6: Critical Issues Detection
        $this->detectCriticalIssues();
        
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
    
    private function analyzeSourceCode()
    {
        echo "📝 TEST 2: SOURCE CODE ANALYSIS\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        if (!file_exists($this->tabelHandlerPath)) {
            echo "❌ TabelHandler.php not found\n";
            return;
        }
        
        $source = file_get_contents($this->tabelHandlerPath);
        
        // Check for warranty-related methods
        echo "Warranty-related methods:\n";
        if (strpos($source, 'handleWarrantyEquipmentMenu') !== false) {
            echo "  ✅ handleWarrantyEquipmentMenu found\n";
        } else {
            echo "  ❌ handleWarrantyEquipmentMenu NOT found\n";
        }
        
        // Check for warranty callbacks in canHandle
        echo "canHandle method analysis:\n";
        if (strpos($source, 'cat_ТабГарантии') !== false) {
            echo "  ✅ cat_ТабГарантии found in canHandle\n";
        } else {
            echo "  ❌ cat_ТабГарантии NOT found in canHandle\n";
        }
        
        if (strpos($source, 'cat_Гарантии_') !== false) {
            echo "  ✅ cat_Гарантии_ pattern found in canHandle\n";
        } else {
            echo "  ❌ cat_Гарантии_ pattern NOT found in canHandle\n";
        }
        
        echo "\n";
    }
    
    private function analyzeCategoryMapping()
    {
        echo "🗺️ TEST 3: CATEGORY MAPPING ANALYSIS\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $source = file_get_contents($this->tabelHandlerPath);
        
        // Check for extractCategoryFromCallback mapping
        echo "Category mapping analysis:\n";
        if (strpos($source, "'cat_ТабГарантии' => 'Табель_РаботыПоГарантии'") !== false) {
            echo "  ✅ Warranty callback mapping found\n";
            echo "      cat_ТабГарантии → Табель_РаботыПоГарантии\n";
        } else {
            echo "  ❌ Warranty callback mapping NOT found\n";
        }
        
        // Check for handler condition matching
        echo "Handler condition analysis:\n";
        if (strpos($source, "category === 'cat_ТабГарантии'") !== false) {
            echo "  ✅ Direct warranty callback check found\n";
        } else {
            echo "  ❌ Direct warranty callback check NOT found\n";
        }
        
        if (strpos($source, "category === 'Табель_РаботыПоГарантии'") !== false) {
            echo "  ✅ Mapped warranty category check found\n";
        } else {
            echo "  ❌ Mapped warranty category check NOT found\n";
        }
        
        echo "\n";
    }
    
    private function analyzeHandlerLogic()
    {
        echo "⚙️ TEST 4: HANDLER LOGIC ANALYSIS\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $source = file_get_contents($this->tabelHandlerPath);
        
        // Check for handleOtherWorkTypes warranty handling
        echo "handleOtherWorkTypes analysis:\n";
        if (strpos($source, 'WARRANTY equipment menu handling') !== false) {
            echo "  ✅ Warranty equipment menu handler found\n";
        } else {
            echo "  ❌ Warranty equipment menu handler NOT found\n";
        }
        
        // Check for warranty handling priority
        if (strpos($source, 'HIGHEST PRIORITY') !== false && strpos($source, 'cat_ТабГарантии') !== false) {
            echo "  ✅ Warranty handler marked as HIGHEST PRIORITY\n";
        } else {
            echo "  ❌ Warranty handler NOT marked as HIGHEST PRIORITY\n";
        }
        
        // Check for multiple condition checks
        $warrantyConditions = substr_count($source, 'cat_ТабГарантии');
        echo "  ℹ️ 'cat_ТабГарантии' appears {$warrantyConditions} times in handler\n";
        
        echo "\n";
    }
    
    private function compareWithWorkingFunctionality()
    {
        echo "🔄 TEST 5: COMPARE WITH WORKING FUNCTIONALITY\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $source = file_get_contents($this->tabelHandlerPath);
        
        // Compare warranty with RK elimination patterns
        $patterns = [
            'Warranty' => [
                'callback' => 'cat_ТабГарантии',
                'handler_pattern' => 'WARRANTY equipment menu handling',
                'method' => 'handleWarrantyEquipmentMenu'
            ],
            'RK Elimination' => [
                'callback' => 'cat_ТабПНРаботы_РК_Устранение_замечаний',
                'handler_pattern' => 'RK Comments Elimination',
                'method' => 'handleRKCommentsEliminationMenu'
            ]
        ];
        
        foreach ($patterns as $type => $config) {
            echo "{$type} Analysis:\n";
            echo "  Callback: {$config['callback']}\n";
            echo "  Length: " . strlen($config['callback']) . " bytes\n";
            
            // Check if callback exists in canHandle
            if (strpos($source, $config['callback']) !== false) {
                echo "  ✅ Callback found in source\n";
            } else {
                echo "  ❌ Callback NOT found in source\n";
            }
            
            // Check if handler pattern exists
            if (strpos($source, $config['handler_pattern']) !== false) {
                echo "  ✅ Handler pattern found\n";
            } else {
                echo "  ❌ Handler pattern NOT found\n";
            }
            
            // Check if method exists
            if (strpos($source, $config['method']) !== false) {
                echo "  ✅ Handler method found\n";
            } else {
                echo "  ❌ Handler method NOT found\n";
            }
            
            echo "\n";
        }
    }
    
    private function detectCriticalIssues()
    {
        echo "🚨 TEST 6: CRITICAL ISSUES DETECTION\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $source = file_get_contents($this->tabelHandlerPath);
        $issues = [];
        
        // Issue 1: Category mapping mismatch
        $hasMapping = strpos($source, "'cat_ТабГарантии' => 'Табель_РаботыПоГарантии'") !== false;
        $hasDirectCheck = strpos($source, "category === 'cat_ТабГарантии'") !== false;
        $hasMappedCheck = strpos($source, "category === 'Табель_РаботыПоГарантии'") !== false;
        
        if ($hasMapping && !$hasDirectCheck && !$hasMappedCheck) {
            $issues[] = "❌ CRITICAL: Category mapping exists but no handler checks for either form";
        } elseif ($hasMapping && ($hasDirectCheck || $hasMappedCheck)) {
            echo "✅ Category mapping and handler checks are aligned\n";
        }
        
        // Issue 2: Missing canHandle recognition
        $canHandleMethod = $this->extractCanHandleMethod($source);
        if (!strpos($canHandleMethod, 'cat_ТабГарантии')) {
            $issues[] = "❌ CRITICAL: canHandle method doesn't recognize warranty callback";
            error_log("DEBUG: canHandle method content: " . substr($canHandleMethod, 0, 200) . "...");
        } else {
            echo "✅ canHandle method recognizes warranty callback\n";
        }
        
        // Issue 3: Missing handler method
        if (!strpos($source, 'handleWarrantyEquipmentMenu')) {
            $issues[] = "❌ CRITICAL: handleWarrantyEquipmentMenu method missing";
        } else {
            echo "✅ handleWarrantyEquipmentMenu method exists\n";
        }
        
        // Issue 4: Check MessageService warranty keyboard
        if (file_exists($this->messageServicePath)) {
            $messageSource = file_get_contents($this->messageServicePath);
            if (!strpos($messageSource, 'createWarrantyEquipmentKeyboard')) {
                $issues[] = "❌ CRITICAL: createWarrantyEquipmentKeyboard method missing in MessageService";
            } else {
                echo "✅ createWarrantyEquipmentKeyboard method exists in MessageService\n";
            }
            
            if (!strpos($messageSource, 'cat_ТабГарантии')) {
                $issues[] = "❌ CRITICAL: Warranty button not found in commissioning keyboard";
            } else {
                echo "✅ Warranty button found in commissioning keyboard\n";
            }
        }
        
        // Report all issues
        if (empty($issues)) {
            echo "\n🎉 NO CRITICAL ISSUES DETECTED!\n";
            echo "Warranty functionality should work correctly.\n";
        } else {
            echo "\n🚨 CRITICAL ISSUES FOUND:\n";
            foreach ($issues as $issue) {
                echo $issue . "\n";
            }
        }
        
        echo "\n";
    }
    
    private function extractCanHandleMethod(string $source): string
    {
        // Extract canHandle method content
        $start = strpos($source, 'public function canHandle(');
        if ($start === false) return '';
        
        // Find the end of the method by counting braces
        $braceCount = 0;
        $inMethod = false;
        $methodContent = '';
        
        for ($i = $start; $i < strlen($source); $i++) {
            $char = $source[$i];
            $methodContent .= $char;
            
            if ($char === '{') {
                $braceCount++;
                $inMethod = true;
            } elseif ($char === '}') {
                $braceCount--;
                if ($inMethod && $braceCount === 0) {
                    break;
                }
            }
        }
        
        return $methodContent;
    }
}

// Run the diagnostic
$diagnostic = new SimpleWarrantyDiagnostic();
$diagnostic->runDeepTest();