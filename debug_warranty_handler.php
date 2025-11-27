<?php
// Debug individual warranty equipment callback handling

echo "=== DEBUGGING INDIVIDUAL WARRANTY EQUIPMENT CALLBACK ===\n";

$electricalCallback = 'category_Табель_РабГарантии_ЭлектрОб';
echo "Testing callback: '{$electricalCallback}'\n\n";

// Check the handleWarrantyEquipmentCallback method
$handlerContent = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');

// Find the handleWarrantyEquipmentCallback method
$methodStart = strpos($handlerContent, 'private function handleWarrantyEquipmentCallback');
if ($methodStart !== false) {
    $methodEnd = strpos($handlerContent, 'return $this->messageService->sendMessage($chatId, \'Неизвестная команда\');', $methodStart);
    if ($methodEnd === false) {
        // Try to find the end of the method
        $braceCount = 0;
        $pos = $methodStart;
        while ($pos < strlen($handlerContent)) {
            if ($handlerContent[$pos] === '{') $braceCount++;
            if ($handlerContent[$pos] === '}') $braceCount--;
            if ($braceCount === 0 && $pos > $methodStart + 100) break;
            $pos++;
        }
        $methodEnd = $pos;
    }
    
    $method = substr($handlerContent, $methodStart, $methodEnd - $methodStart);
    
    echo "ANALYSIS OF handleWarrantyEquipmentCallback:\n";
    
    // Check if it handles the main warranty menu
    if (strpos($method, "category === 'cat_ТабГарантии'") !== false) {
        echo "✅ Handles main warranty menu (cat_ТабГарантии)\n";
    } else {
        echo "❌ Does NOT handle main warranty menu\n";
    }
    
    // Check if it handles individual equipment callbacks
    if (strpos($method, "str_starts_with(\$category, 'category_Табель_РабГарантии_')") !== false) {
        echo "✅ Has code to handle individual equipment callbacks\n";
        
        // Check if it extracts equipment type correctly
        if (strpos($method, "str_replace('category_Табель_РабГарантии_', '', \$category)") !== false) {
            echo "✅ Extracts equipment type correctly\n";
        } else {
            echo "❌ Does NOT extract equipment type correctly\n";
        }
        
        // Check if it creates confirmation
        if (strpos($method, 'getConfirmationMessage') !== false) {
            echo "✅ Creates confirmation message\n";
        } else {
            echo "❌ Does NOT create confirmation message\n";
        }
        
    } else {
        echo "❌ Does NOT handle individual equipment callbacks\n";
        echo "This is the problem! The method only handles the main menu.\n";
    }
    
} else {
    echo "❌ handleWarrantyEquipmentCallback method not found\n";
}

// Check if the routing is calling the right method
echo "\nROUTING ANALYSIS:\n";
$routeStart = strpos($handlerContent, 'str_starts_with($category, \'category_Табель_РабГарантии_\')');
if ($routeStart !== false) {
    echo "✅ Routing exists for individual warranty equipment\n";
    
    // Find what method it calls
    $lineStart = strrpos($handlerContent, "\n", $routeStart - 100);
    $lineEnd = strpos($handlerContent, "\n", $routeStart + 200);
    $routingLine = substr($handlerContent, $lineStart, $lineEnd - $lineStart);
    
    if (strpos($routingLine, 'handleWarrantyEquipmentCallback') !== false) {
        echo "✅ Calls handleWarrantyEquipmentCallback\n";
    } else {
        echo "❌ Does NOT call handleWarrantyEquipmentCallback\n";
        echo "Routing line: " . trim($routingLine) . "\n";
    }
} else {
    echo "❌ No routing for individual warranty equipment callbacks\n";
}

echo "\n=== SOLUTION NEEDED ===\n";
echo "If the handleWarrantyEquipmentCallback method does NOT handle individual\n";
echo "equipment callbacks, that's why clicking 'Электрооборудование' doesn't work.\n";
echo "The method needs to be updated to handle both:\n";
echo "1. 'cat_ТабГарантии' (main menu)\n";
echo "2. 'category_Табель_РабГарантии_XXX' (individual equipment)\n";
?>