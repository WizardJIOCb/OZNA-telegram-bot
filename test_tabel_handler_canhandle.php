<?php

echo "=== TESTING TABEL HANDLER canHandle ===\n\n";

// Test the exact callbacks against TabelHandler's canHandle logic
$test_callbacks = [
    'cat_ТабПНРаботы' => 'ПНР Department',
    'category_Табель_ШМР' => 'ШМР Department (main)',
    'category_Табель_Пусконаладка' => 'Commissioning Department',
    'category_Табель_МобБриг' => 'Mobile Brigade',
];

echo "🔍 TESTING TABEL HANDLER LOGIC\n";
echo "==============================\n";

foreach ($test_callbacks as $callback => $description) {
    echo "\nTesting: '{$callback}' ({$description})\n";
    
    // Simulate TabelHandler's canHandle logic
    $canHandle = (str_starts_with($callback, 'category_Табель') || 
                  str_starts_with($callback, 'cat_ТабПНРаботы') ||
                  str_starts_with($callback, 'cat_Таб') ||
                  str_starts_with($callback, 'cat_Гарантии_') ||
                  str_starts_with($callback, 'cat_ШМР_') ||
                  str_starts_with($callback, 'ШМР_') ||
                  str_starts_with($callback, 'cat_supervision_') ||
                  str_starts_with($callback, 'cat_РЭРњР _') ||
                  $callback === 'новое_прочее_yes' ||
                  $callback === 'cat_ТабЭлОб_Проч_yes' ||
                  $callback === 'cat_ТабВентОтопл' ||
                  $callback === 'cat_ТабОтоплВент' ||
                  str_starts_with($callback, 'cat_ТабВентОтопл_') ||
                  str_starts_with($callback, 'cat_ТабОтоплВент_') ||
                  $callback === 'cat_ТабГарантии');
    
    if ($canHandle) {
        echo "✅ TabelHandler will handle this callback\n";
        
        // Check which pattern matched
        if (str_starts_with($callback, 'category_Табель')) {
            echo "   Matches: 'category_Табель' pattern\n";
        } elseif (str_starts_with($callback, 'cat_ТабПНРаботы')) {
            echo "   Matches: 'cat_ТабПНРаботы' pattern\n";
        } elseif (str_starts_with($callback, 'cat_Таб')) {
            echo "   Matches: 'cat_Таб' pattern\n";
        } elseif (str_starts_with($callback, 'cat_ШМР_')) {
            echo "   Matches: 'cat_ШМР_' pattern\n";
        }
    } else {
        echo "❌ TabelHandler will NOT handle this callback\n";
        echo "   ⚠️  Need to add pattern to canHandle method!\n";
    }
}

echo "\n🔧 CHECKING FOR MISSING PATTERNS\n";
echo "=================================\n";

// Check if we need to add any patterns
$missing_patterns = [];

if (!str_starts_with('cat_ТабПНРаботы', 'cat_ТабПНРаботы') && 
    !str_starts_with('cat_ТабПНРаботы', 'cat_Таб')) {
    // This should actually match cat_Таб pattern
}

// cat_ТабПНРаботы should match cat_Таб pattern
echo "Testing 'cat_ТабПНРаботы' against 'cat_Таб' pattern:\n";
if (str_starts_with('cat_ТабПНРаботы', 'cat_Таб')) {
    echo "✅ 'cat_ТабПНРаботы' matches 'cat_Таб' pattern\n";
} else {
    echo "❌ 'cat_ТабПНРаботы' does NOT match 'cat_Таб' pattern\n";
}

echo "\n📋 CONCLUSION\n";
echo "=============\n";
echo "If TabelHandler canHandle returns true for all callbacks,\n";
echo "but buttons still don't work, the issue is likely in:\n";
echo "1. Handler priority order\n";
echo "2. Handler execution logic\n";
echo "3. Missing route handling in routeTabelCallback method\n";

?>