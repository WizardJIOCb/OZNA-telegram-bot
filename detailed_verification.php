<?php
/**
 * Detailed verification of the current code
 */

echo "=== DETAILED CODE VERIFICATION ===\n\n";

// Check TabelHandler for the back button routing
$tabelHandlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
if (file_exists($tabelHandlerFile)) {
    $content = file_get_contents($tabelHandlerFile);
    
    echo "1. TabelHandler back button routing:\n";
    echo "==================================\n";
    
    // Look for the specific routing code
    $lines = explode("\n", $content);
    $foundRouting = false;
    $foundMethodCall = false;
    
    foreach ($lines as $line) {
        if (strpos($line, "if (\$category === 'Табель_ШМ_Back')") !== false) {
            echo "Line: " . trim($line) . "\n";
            $foundRouting = true;
        }
        
        if (strpos($line, "return \$this->handleBackToSupervision(\$chatId, \$conversation);") !== false) {
            echo "Line: " . trim($line) . "\n";
            $foundMethodCall = true;
        }
    }
    
    if ($foundRouting && $foundMethodCall) {
        echo "✅ COMPLETE - Back button routing is correctly implemented\n";
    } else {
        echo "❌ INCOMPLETE - Back button routing has issues\n";
    }
    
    echo "\n2. ShortFormats mapping:\n";
    echo "=====================\n";
    
    if (strpos($content, "'category_Табель_ШМ_Простой' => 'Табель_ШМ_Простой'") !== false) {
        echo "✅ FOUND - 'category_Табель_ШМ_Простой' mapping exists\n";
    } else {
        echo "❌ NOT FOUND - 'category_Табель_ШМ_Простой' mapping missing\n";
    }
    
} else {
    echo "❌ TabelHandler file not found\n";
}

echo "\n3. MessageService keyboards:\n";
echo "=========================\n";

$messageServiceFile = __DIR__ . '/CustomCommands/Services/MessageService.php';
if (file_exists($messageServiceFile)) {
    $content = file_get_contents($messageServiceFile);
    
    // Check warranty equipment keyboard back button logic
    echo "Warranty equipment keyboard back button logic:\n";
    $pattern = "/createWarrantyEquipmentKeyboard\(.*?\\\$backButtonCallback = \\\$context === 'supervision'/s";
    if (preg_match($pattern, $content)) {
        echo "✅ FOUND - Warranty equipment keyboard back button logic\n";
    } else {
        // Let's look for the specific lines
        $lines = explode("\n", $content);
        $inWarrantyMethod = false;
        $foundLogic = false;
        
        foreach ($lines as $i => $line) {
            if (strpos($line, 'public function createWarrantyEquipmentKeyboard') !== false) {
                $inWarrantyMethod = true;
                echo "Found createWarrantyEquipmentKeyboard method at line " . ($i + 1) . "\n";
            }
            
            if ($inWarrantyMethod) {
                if (strpos($line, 'supervision') !== false && strpos($line, 'Табель_ШМ_Back') !== false) {
                    echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
                    $foundLogic = true;
                }
                
                // End of method
                if (strpos($line, '}') === 0 && trim($line) === '}') {
                    $inWarrantyMethod = false;
                }
            }
        }
        
        if ($foundLogic) {
            echo "✅ FOUND - Warranty equipment keyboard back button logic\n";
        } else {
            echo "❌ NOT FOUND - Warranty equipment keyboard back button logic\n";
        }
    }
    
} else {
    echo "❌ MessageService file not found\n";
}

echo "\nVerification completed.\n";
?>