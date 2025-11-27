<?php
// Test script to verify date selection flow fix

echo "Testing date selection flow fix...\n";

// Simulate the handleDateSelection method
function handleDateSelection($category) {
    echo "handleDateSelection called with category: " . $category . "\n";
    
    // Extract date from category (simplified for test)
    $date = null;
    if (preg_match('/(\d{2}\.\d{2}\.\d{4})/', $category, $matches)) {
        $date = $matches[1];
    } else {
        // Try to extract date from date selection patterns
        $datePatterns = [
            '/Табель_Мобильная_Бригада_(\d{2}\.\d{2}\.\d{4})/' => '$1',
            '/Табель_Пусконаладка_(\d{2}\.\d{2}\.\d{4})/' => '$1',
            '/Табель_Шеф_монтаж_(\d{2}\.\d{2}\.\d{4})/' => '$1'
        ];
        
        foreach ($datePatterns as $pattern => $replacement) {
            if (preg_match($pattern, $category, $matches)) {
                $date = $matches[1];
                break;
            }
        }
    }
    
    if ($date) {
        echo "Extracted date: " . $date . "\n";
        
        // Check what type of flow this is
        if (strpos($category, 'Текущая') !== false) {
            echo "Current activity flow - should show current activity keyboard\n";
            return "show_current_activity_keyboard";
        } elseif (strpos($category, 'Пусконаладка') !== false || strpos($category, 'ПНЛ') !== false) {
            if (strpos($category, 'cat_ТабПНРаботы') !== false) {
                echo "Short format commissioning works - go directly to confirmation\n";
                return "show_confirmation";
            } else {
                echo "Commissioning flow - should show project selection keyboard\n";
                return "show_project_selection";
            }
        } elseif (strpos($category, 'Шеф-монтаж') !== false || strpos($category, 'ШМ') !== false) {
            echo "Supervision flow - should show project selection keyboard\n";
            return "show_project_selection";
        } elseif (strpos($category, 'Мобильная_Бригада') !== false || strpos($category, 'МобБриг') !== false) {
            echo "Mobile brigade flow - should show project selection keyboard\n";
            return "show_project_selection";
        } else {
            echo "Default mobile brigade flow - should show project selection keyboard\n";
            return "show_project_selection";
        }
    } else {
        echo "Could not extract date from category\n";
        return "error";
    }
}

// Test cases
$testCases = [
    "Табель_Мобильная_Бригада_28.09.2025",
    "Табель_Пусконаладка_28.09.2025",
    "Табель_Шеф_монтаж_28.09.2025",
    "Табель_Текущая_28.09.2025"
];

foreach ($testCases as $testCase) {
    echo "\n--- Testing: " . $testCase . " ---\n";
    $result = handleDateSelection($testCase);
    echo "Result: " . $result . "\n";
}

echo "\nTest completed.\n";
?>