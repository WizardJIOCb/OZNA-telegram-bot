<?php
/**
 * Verify that the current code has our fixes
 */

echo "=== VERIFYING CURRENT CODE ===\n\n";

// Check TabelHandler for the back button routing
$tabelHandlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
if (file_exists($tabelHandlerFile)) {
    $content = file_get_contents($tabelHandlerFile);
    
    echo "1. Checking TabelHandler routing for 'Табель_ШМ_Back':\n";
    echo "================================================\n";
    
    if (strpos($content, "if (\$category === 'Табель_ШМ_Back')") !== false) {
        echo "✅ FOUND - Back button routing exists\n";
    } else {
        echo "❌ NOT FOUND - Back button routing missing\n";
    }
    
    if (strpos($content, "return \$this->handleBackToSupervision(\$chatId, \$conversation);") !== false) {
        echo "✅ FOUND - handleBackToSupervision call exists\n";
    } else {
        echo "❌ NOT FOUND - handleBackToSupervision call missing\n";
    }
    
    echo "\n2. Checking shortFormats mapping for 'category_Табель_ШМ_Простой':\n";
    echo "=============================================================\n";
    
    if (strpos($content, "'category_Табель_ШМ_Простой' => 'Табель_ШМ_Простой'") !== false) {
        echo "✅ FOUND - 'category_Табель_ШМ_Простой' mapping exists\n";
    } else {
        echo "❌ NOT FOUND - 'category_Табель_ШМ_Простой' mapping missing\n";
    }
    
} else {
    echo "❌ TabelHandler file not found\n";
}

echo "\n3. Checking MessageService keyboards:\n";
echo "==================================\n";

$messageServiceFile = __DIR__ . '/CustomCommands/Services/MessageService.php';
if (file_exists($messageServiceFile)) {
    $content = file_get_contents($messageServiceFile);
    
    // Check supervision installation works keyboard
    if (strpos($content, "['text' => '⬅️ Назад', 'callback_data' => 'Табель_ШМ_Back']") !== false) {
        echo "✅ FOUND - Supervision installation works keyboard back button\n";
    } else {
        echo "❌ NOT FOUND - Supervision installation works keyboard back button\n";
    }
    
    // Check supervision project activity keyboard
    $pattern = "/createSupervisionProjectActivityKeyboard\(.*?'callback_data' => 'Табель_ШМ_Back'/s";
    if (preg_match($pattern, $content)) {
        echo "✅ FOUND - Supervision project activity keyboard back button\n";
    } else {
        echo "❌ NOT FOUND - Supervision project activity keyboard back button\n";
    }
    
    // Check warranty equipment keyboard
    if (strpos($content, "\$backButtonCallback = \$context === 'supervision' \n            ? 'Табель_ШМ_Back'") !== false) {
        echo "✅ FOUND - Warranty equipment keyboard back button logic\n";
    } else {
        echo "❌ NOT FOUND - Warranty equipment keyboard back button logic\n";
    }
    
    // Check warranty works keyboard
    if (strpos($content, "['text' => '⬅️ Назад', 'callback_data' => 'Табель_ШМ_Back']") !== false) {
        echo "✅ FOUND - Warranty works keyboard back button\n";
    } else {
        echo "❌ NOT FOUND - Warranty works keyboard back button\n";
    }
    
} else {
    echo "❌ MessageService file not found\n";
}

echo "\nVerification completed.\n";
?>