<?php

// Test script to verify supervision works button functionality
echo "=== Testing Supervision Works Button Functionality ===\n\n";

// Test categories that should work for the problematic buttons
$testCategories = [
    'cat_ШМР_МонтТруб_РабПоГр',     // "Работы по графику" button callback
    'cat_ШМР_МонтТруб_ПодтВех',     // "Подтвердить достижение вехи" button callback
    'cat_ШМР_МонтТруб_РабПоГр_yes', // Confirmation for schedule works
    'cat_ШМР_МонтТруб_ПодтВех_yes'  // Confirmation for milestone achievement
];

// Read the actual TabelHandler.php file
$handlerFile = 'c:\Users\user\Documents\projects\CustomCommands\Handlers\TabelHandler.php';
$content = file_get_contents($handlerFile);

echo "Testing button callback recognition:\n";
echo "=====================================\n\n";

foreach ($testCategories as $category) {
    echo "Testing category: '$category'\n";
    
    // Check if callback exists in extractCategoryFromCallback mapping
    if (strpos($content, "'$category' =>") !== false) {
        echo "  ✅ Found in extractCategoryFromCallback mapping\n";
    } else {
        echo "  ❌ NOT found in extractCategoryFromCallback mapping\n";
    }
    
    // Check if callback exists in isActivityConfirmation supervision callbacks
    if (strpos($content, "'$category',") !== false && strpos($content, 'supervisionWorksCallbacks') !== false) {
        echo "  ✅ Found in isActivityConfirmation supervision callbacks\n";
    } else {
        echo "  ❌ NOT found in isActivityConfirmation supervision callbacks\n";
    }
    
    // Check if callback exists in isActivityExecution
    if (strpos($content, "'$category'") !== false) {
        echo "  ✅ Found somewhere in isActivityExecution\n";
    } else {
        echo "  ❌ NOT found in isActivityExecution\n";
    }
    
    echo "\n";
}

echo "\n=== Checking MessageService button definitions ===\n";

// Check MessageService for button definitions
$messageServiceFile = 'c:\Users\user\Documents\projects\CustomCommands\Services\MessageService.php';
if (file_exists($messageServiceFile)) {
    $messageContent = file_get_contents($messageServiceFile);
    
    echo "Looking for button definitions:\n";
    
    if (strpos($messageContent, 'cat_ШМР_МонтТруб_РабПоГр') !== false) {
        echo "  ✅ 'Работы по графику' button found in MessageService\n";
    } else {
        echo "  ❌ 'Работы по графику' button NOT found in MessageService\n";
    }
    
    if (strpos($messageContent, 'cat_ШМР_МонтТруб_ПодтВех') !== false) {
        echo "  ✅ 'Подтвердить достижение вехи' button found in MessageService\n";
    } else {
        echo "  ❌ 'Подтвердить достижение вехи' button NOT found in MessageService\n";
    }
} else {
    echo "  ❌ MessageService.php file not found\n";
}

echo "\nTest completed.\n";
?>