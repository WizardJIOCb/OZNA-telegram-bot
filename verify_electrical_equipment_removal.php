<?php
/**
 * Verification script to confirm complete removal of "Электрооборудование" from warranty equipment
 */

echo "🔍 VERIFYING ELECTRICAL EQUIPMENT REMOVAL FROM WARRANTY WORKS\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// Check files
$files = [
    'CustomCommands/Services/MessageService.php',
    'CustomCommands/Services/TabelEntry.php', 
    'CustomCommands/Handlers/TabelHandler.php'
];

$foundReferences = false;

foreach ($files as $file) {
    $fullPath = __DIR__ . '/' . $file;
    
    if (!file_exists($fullPath)) {
        echo "❌ File not found: {$file}\n";
        continue;
    }
    
    $content = file_get_contents($fullPath);
    
    // Check for РабГарантии_ЭлектрОб references
    if (strpos($content, 'РабГарантии_ЭлектрОб') !== false) {
        echo "❌ Found РабГарантии_ЭлектрОб reference in: {$file}\n";
        $foundReferences = true;
    } else {
        echo "✅ No РабГарантии_ЭлектрОб references in: {$file}\n";
    }
    
    // Check warranty keyboard specifically
    if ($file === 'CustomCommands/Services/MessageService.php') {
        if (strpos($content, 'Электрооборудование\', \'callback_data\' => \'category_Табель_РабГарантии_') !== false) {
            echo "❌ Found electrical equipment button in warranty keyboard\n";
            $foundReferences = true;
        } else {
            echo "✅ No electrical equipment button in warranty keyboard\n";
        }
        
        // Check for callback mapping
        if (strpos($content, 'category_Табель_РабГарантии_ЭлектрОб') !== false) {
            echo "❌ Found electrical equipment callback in warranty keyboard\n";
            $foundReferences = true;
        } else {
            echo "✅ No electrical equipment callback in warranty keyboard\n";
        }
    }
    
    // Check TabelHandler for callback mappings
    if ($file === 'CustomCommands/Handlers/TabelHandler.php') {
        // Check for callback mapping
        if (strpos($content, 'category_Табель_РабГарантии_ЭлектрОб') !== false) {
            echo "❌ Found electrical equipment callback mapping in TabelHandler\n";
            $foundReferences = true;
        } else {
            echo "✅ No electrical equipment callback mapping in TabelHandler\n";
        }
        
        // Check for confirmation callback mapping
        if (strpos($content, 'cat_Гарантии_ЭлектрОб_yes') !== false) {
            echo "❌ Found electrical equipment confirmation callback in TabelHandler\n";
            $foundReferences = true;
        } else {
            echo "✅ No electrical equipment confirmation callback in TabelHandler\n";
        }
        
        // Check warranty equipment map - be more precise
        $warrantyMapPattern = '/warrantyEquipmentMap\s*=\s*\[.*?["\']ЭлектрОб["\'].*?=>/s';
        if (preg_match($warrantyMapPattern, $content)) {
            echo "❌ Found electrical equipment in warranty equipment map\n";
            $foundReferences = true;
        } else {
            echo "✅ No electrical equipment in warranty equipment map\n";
        }
    }
    
    // Check TabelEntry for activity type mappings
    if ($file === 'CustomCommands/Services/TabelEntry.php') {
        // Check activity type mappings
        if (strpos($content, 'РабГарантии_ЭлектрОб\' => \'') !== false) {
            echo "❌ Found electrical equipment in activity type mappings\n";
            $foundReferences = true;
        } else {
            echo "✅ No electrical equipment in activity type mappings\n";
        }
        
        // Check warranty equipment activities array
        if (preg_match('/warrantyEquipmentActivities.*?РабГарантии_ЭлектрОб/s', $content)) {
            echo "❌ Found electrical equipment in warranty activities array\n";
            $foundReferences = true;
        } else {
            echo "✅ No electrical equipment in warranty activities array\n";
        }
    }
}

echo "\n" . str_repeat("=", 70) . "\n";

if (!$foundReferences) {
    echo "🎉 SUCCESS: Electrical equipment has been completely removed from warranty works!\n";
    echo "✅ All production files are clean\n";
    echo "✅ Warranty keyboard no longer contains electrical equipment button\n";
    echo "✅ All callback mappings have been removed\n";
    echo "✅ Activity type mappings have been cleaned up\n";
} else {
    echo "❌ FAILURE: Some references to electrical equipment still remain\n";
    echo "Please review the output above for specific issues\n";
}

echo "\nRemoval request completed: 'Удали из Субактивностей Работа по гарантии, активность \"Электрооборудование\"'\n";