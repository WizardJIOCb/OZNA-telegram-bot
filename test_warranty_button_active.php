<?php
/**
 * Simple test to check warranty button functionality
 */

echo "=== TESTING WARRANTY BUTTON ACTIVITY ===\n\n";

$handlerFile = 'CustomCommands/Handlers/TabelHandler.php';
$messageServiceFile = 'CustomCommands/Services/MessageService.php';

// Test 1: Check if warranty button exists in MessageService
echo "1. CHECKING WARRANTY BUTTON IN KEYBOARD:\n";
echo "=======================================\n";

if (file_exists($messageServiceFile)) {
    $content = file_get_contents($messageServiceFile);
    
    // Check for warranty button
    if (strpos($content, 'Работы по гарантии') !== false && strpos($content, 'cat_ТабГарантии') !== false) {
        echo "✅ Warranty button found in MessageService\n";
        
        // Extract the button line
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            if (strpos($line, 'Работы по гарантии') !== false) {
                echo "   Button definition (line " . ($i + 1) . "): " . trim($line) . "\n";
                break;
            }
        }
    } else {
        echo "❌ Warranty button NOT found in MessageService\n";
    }
} else {
    echo "❌ MessageService file not found\n";
}

// Test 2: Check if TabelHandler can handle the callback
echo "\n2. CHECKING TABELHANDLER CANHANDLE METHOD:\n";
echo "==========================================\n";

if (file_exists($handlerFile)) {
    $content = file_get_contents($handlerFile);
    
    // Check canHandle method for warranty callback
    if (strpos($content, 'cat_ТабГарантии') !== false) {
        echo "✅ TabelHandler canHandle recognizes cat_ТабГарантии\n";
        
        // Find the canHandle method
        $pattern = '/public function canHandle.*?return.*?;/s';
        if (preg_match($pattern, $content, $matches)) {
            $canHandleMethod = $matches[0];
            if (strpos($canHandleMethod, 'cat_ТабГарантии') !== false) {
                echo "✅ cat_ТабГарантии is in canHandle method\n";
            } else {
                echo "❌ cat_ТабГарантии NOT in canHandle method\n";
            }
        }
    } else {
        echo "❌ TabelHandler does NOT recognize cat_ТабГарантии\n";
    }
} else {
    echo "❌ TabelHandler file not found\n";
}

// Test 3: Check warranty equipment menu handler
echo "\n3. CHECKING WARRANTY EQUIPMENT MENU HANDLER:\n";
echo "============================================\n";

if (file_exists($handlerFile)) {
    $content = file_get_contents($handlerFile);
    
    // Check for warranty menu handler
    if (strpos($content, 'handleWarrantyEquipmentMenu') !== false) {
        echo "✅ handleWarrantyEquipmentMenu method exists\n";
        
        // Check if it's properly routed
        if (strpos($content, 'cat_ТабГарантии') !== false && strpos($content, 'handleWarrantyEquipmentMenu') !== false) {
            echo "✅ cat_ТабГарантии is routed to handleWarrantyEquipmentMenu\n";
        } else {
            echo "❌ cat_ТабГарантии is NOT properly routed to handleWarrantyEquipmentMenu\n";
        }
    } else {
        echo "❌ handleWarrantyEquipmentMenu method does NOT exist\n";
    }
}

// Test 4: Check callback length
echo "\n4. CHECKING CALLBACK LENGTH:\n";
echo "============================\n";

$warrantyCallback = 'cat_ТабГарантии';
$callbackLength = strlen($warrantyCallback);
echo "Warranty button callback: '{$warrantyCallback}'\n";
echo "Callback length: {$callbackLength} bytes\n";
echo "Within Telegram limit (64 bytes): " . ($callbackLength <= 64 ? "✅ YES" : "❌ NO") . "\n";

echo "\n5. POTENTIAL ISSUES:\n";
echo "===================\n";
echo "If the warranty button appears inactive, possible causes:\n";
echo "1. Server needs restart to load new code changes\n";
echo "2. User conversation state is corrupted\n";
echo "3. User access level restrictions\n";
echo "4. Missing project code or date in conversation\n";
echo "5. FIO or functional direction not set\n";
echo "6. Recent duplicate entry restrictions\n";

echo "\n6. RECOMMENDED TESTS:\n";
echo "=====================\n";
echo "1. Try clicking the warranty button and check bot logs\n";
echo "2. Use /start to reset conversation state\n";
echo "3. Ensure user has proper access level (not Guest)\n";
echo "4. Set FIO with /fio command if needed\n";
echo "5. Set functional direction with /profile if needed\n";

echo "\n=== TEST COMPLETE ===\n";
?>