<?php
// Test individual warranty equipment callback

echo "=== Testing Individual Warranty Equipment Callback ===\n";

$electricalEquipmentCallback = 'category_Табель_РабГарантии_ЭлектрОб';
echo "Testing callback: '$electricalEquipmentCallback'\n";

// Check if it starts with the warranty equipment pattern
if (str_starts_with($electricalEquipmentCallback, 'category_Табель_РабГарантии_')) {
    echo "✅ Callback matches warranty equipment pattern\n";
    
    // Extract equipment type
    $equipmentType = str_replace('category_Табель_РабГарантии_', '', $electricalEquipmentCallback);
    echo "✅ Extracted equipment type: '$equipmentType'\n";
    
    // Check activity type
    $activityType = 'РабГарантии_' . $equipmentType;
    echo "✅ Activity type would be: '$activityType'\n";
    
    // Check if activity type exists in TabelEntry.php
    $tabelEntryContent = file_get_contents('c:\ozna_bot\CustomCommands\Services\TabelEntry.php');
    if (strpos($tabelEntryContent, "'$activityType'") !== false) {
        echo "✅ Activity type found in TabelEntry mappings\n";
    } else {
        echo "❌ Activity type NOT found in TabelEntry mappings\n";
    }
    
} else {
    echo "❌ Callback does NOT match warranty equipment pattern\n";
}

echo "\n=== Checking TabelHandler canHandle ===\n";
$handlerContent = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');

// Check if the callback would be handled by category_Табель pattern
if (str_starts_with($electricalEquipmentCallback, 'category_Табель')) {
    echo "✅ Callback starts with 'category_Табель' - should be handled\n";
} else {
    echo "❌ Callback does NOT start with 'category_Табель'\n";
}

echo "\n=== Complete ===\n";
?>