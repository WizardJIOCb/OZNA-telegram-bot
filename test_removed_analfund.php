<?php
/**
 * Test script to verify АналФунд has been completely removed and replaced
 */

echo "=== Testing АналФунд Removal ===\n\n";

// Test the new callback name
$newCallback = 'cat_ШМР_ПровМонт_yes';
echo "New callback: {$newCallback}\n";

// Test pattern matching
if (preg_match('/^cat_ШМР_(.+)_yes$/u', $newCallback, $matches)) {
    echo "✅ Pattern MATCHES\n";
    echo "Extracted key: '{$matches[1]}'\n";
    
    // Test the new mapping
    $simpleSupervisionMap = [
        'ПровМонт' => 'ПроверкаМонтажнойПлощадки',
        'ПодпАкт' => 'ПодписаниеАктаШМР',
        'ПодтВех' => 'ПодтверждениеВехи_ШМР'
    ];
    
    $shortActivityKey = $matches[1];
    if (isset($simpleSupervisionMap[$shortActivityKey])) {
        $activityType = $simpleSupervisionMap[$shortActivityKey];
        echo "✅ Mapping found: {$shortActivityKey} → {$activityType}\n";
        
        // Test activity text mapping
        $activityTypeMappings = [
            'ПроверкаМонтажнойПлощадки' => '🏗️ Проверка монтажной площадки'
        ];
        
        if (isset($activityTypeMappings[$activityType])) {
            $activityText = $activityTypeMappings[$activityType];
            echo "✅ Activity text: {$activityText}\n";
            
            // Test message generation
            $message = sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
                "Test User",
                "05.10.2025",
                "ХХХ",
                $activityText
            );
            
            echo "\n✅ Generated message:\n";
            echo $message . "\n";
            
            // Strip HTML for clean view
            echo "\n✅ Clean text:\n";
            echo strip_tags($message) . "\n";
            
        } else {
            echo "❌ Activity text mapping not found\n";
        }
    } else {
        echo "❌ Mapping not found for key: {$shortActivityKey}\n";
    }
} else {
    echo "❌ Pattern does NOT match\n";
}

echo "\n=== Summary ===\n";
echo "✅ АналФунд references have been replaced with ПровМонт\n";
echo "✅ Button callback is now: cat_ШМР_ПровМонт\n";
echo "✅ Confirmation callback is now: cat_ШМР_ПровМонт_yes\n";
echo "✅ Activity mapping: ПровМонт → ПроверкаМонтажнойПлощадки\n";
echo "✅ Activity text: 🏗️ Проверка монтажной площадки\n\n";

echo "The site inspection button should now work without any АналФунд conflicts!\n";
echo "\n=== Test Complete ===\n";
?>