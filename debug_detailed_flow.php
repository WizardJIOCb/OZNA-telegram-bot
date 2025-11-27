<?php

// Detailed debug script to trace the exact flow of the mobile brigade button
echo "=== Detailed Debug of Mobile Brigade Button Flow ===\n\n";

// Simulate the initial button click
$initialCallback = 'category_Табель_Мобильная_Бригада_Дата';
echo "1. Initial callback: {$initialCallback}\n";

// Simulate what happens in CallbackHandlerFactory
$category = substr($initialCallback, strlen('category_'));
echo "2. Extracted category: {$category}\n";

// Simulate what happens in TabelHandler::canHandle
$canHandle = strpos($initialCallback, 'category_Табель') === 0;
echo "3. TabelHandler::canHandle result: " . ($canHandle ? 'true' : 'false') . "\n";

if ($canHandle) {
    echo "4. TabelHandler will handle this callback\n";
    
    // Simulate what happens in TabelHandler::handle
    // Skip access level check for now (assume user has access)
    // Skip FIO check (isMainTabelMenu should return true for this category)
    $isMainTabelMenu = in_array($category, ['Табель', 'Табель_Мобильная_Бригада_Дата']);
    echo "5. isMainTabelMenu result: " . ($isMainTabelMenu ? 'true' : 'false') . "\n";
    
    // Skip confirmation callback check
    $isConfirmationCallback = strpos($category, '_yes') !== false && strpos($category, 'Табель_') !== false;
    echo "6. isConfirmationCallback result: " . ($isConfirmationCallback ? 'true' : 'false') . "\n";
    
    echo "7. Calling routeTabelCallback\n";
    
    // Simulate what happens in routeTabelCallback
    if ($category === 'Табель_Мобильная_Бригада_Дата') {
        echo "8. Matched mobile brigade condition in routeTabelCallback\n";
        echo "9. Calling handleDateSelectionMenu\n";
        
        // Simulate what happens in handleDateSelectionMenu
        $prefix = 'category_Табель_Мобильная_Бригада';
        echo "10. Creating date selection keyboard with prefix: {$prefix}\n";
        
        // Simulate what createDateSelectionKeyboard creates
        $todayCallback = $prefix . '_ДатаСегодня';
        $yesterdayCallback = $prefix . '_ДатаВчера';
        echo "11. Date selection buttons created:\n";
        echo "    Today: {$todayCallback}\n";
        echo "    Yesterday: {$yesterdayCallback}\n";
        
        // Simulate user clicking "Today"
        $dateCallback = $todayCallback;
        echo "\n12. User selected date: {$dateCallback}\n";
        
        $dateCategory = substr($dateCallback, strlen('category_'));
        echo "13. Extracted date category: {$dateCategory}\n";
        
        // Simulate what happens in handleDateSelection
        echo "14. Calling handleDateSelection\n";
        
        // Extract date
        if (strpos($dateCategory, 'ДатаСегодня') !== false) {
            $date = date("d.m.Y");
            echo "15. Extracted date: {$date} (today)\n";
        } else if (strpos($dateCategory, 'ДатаВчера') !== false) {
            $date = date("d.m.Y", time() - 86400);
            echo "15. Extracted date: {$date} (yesterday)\n";
        } else {
            $date = null;
            echo "15. Failed to extract date\n";
        }
        
        if ($date) {
            echo "16. Date extracted successfully: {$date}\n";
            
            // Check conditions in handleDateSelection
            echo "17. Checking conditions in handleDateSelection:\n";
            
            $isCurrentActivity = strpos($dateCategory, 'Текущая') !== false;
            echo "    isCurrentActivity: " . ($isCurrentActivity ? 'true' : 'false') . "\n";
            
            $isCommissioning = strpos($dateCategory, 'Пусконаладка') !== false || strpos($dateCategory, 'ПНЛ') !== false;
            echo "    isCommissioning: " . ($isCommissioning ? 'true' : 'false') . "\n";
            
            $isSupervision = strpos($dateCategory, 'Шеф-монтаж') !== false || strpos($dateCategory, 'ШМ') !== false;
            echo "    isSupervision: " . ($isSupervision ? 'true' : 'false') . "\n";
            
            $isMobileBrigade = strpos($dateCategory, 'Мобильная_Бригада') !== false;
            echo "    isMobileBrigade: " . ($isMobileBrigade ? 'true' : 'false') . "\n";
            
            if ($isCurrentActivity) {
                echo "18. Handling current activity flow\n";
            } else if ($isCommissioning) {
                echo "18. Handling commissioning flow\n";
            } else if ($isSupervision) {
                echo "18. Handling supervision flow\n";
            } else if ($isMobileBrigade) {
                echo "18. Handling mobile brigade flow\n";
                echo "19. Setting conversation flags:\n";
                echo "    is_current_activity = false\n";
                echo "    is_commissioning = false\n";
                echo "    is_supervision = false\n";
                echo "20. Showing project selection keyboard\n";
                
                // Simulate user selecting a project
                $projectCallback = 'category_Табель_ШифрПроекта_ОИ834';
                echo "\n21. User selected project: {$projectCallback}\n";
                
                $projectCategory = substr($projectCallback, strlen('category_'));
                echo "22. Extracted project category: {$projectCategory}\n";
                
                // Simulate what happens in handleProjectSelection
                echo "23. Calling handleProjectSelection\n";
                
                // Extract project code
                if (strpos($projectCategory, 'ШифрПроекта_') !== false) {
                    $parts = explode('_', $projectCategory);
                    $projectCode = end($parts);
                    echo "24. Extracted project code: {$projectCode}\n";
                } else {
                    $projectCode = null;
                    echo "24. Failed to extract project code\n";
                }
                
                if ($projectCode) {
                    echo "25. Project code extracted successfully: {$projectCode}\n";
                    
                    // Simulate conversation flags (from previous step)
                    $conversationFlags = [
                        'is_current_activity' => false,
                        'is_commissioning' => false,
                        'is_supervision' => false
                    ];
                    
                    echo "26. Checking conversation flags:\n";
                    echo "    is_current_activity: " . ($conversationFlags['is_current_activity'] ? 'true' : 'false') . "\n";
                    echo "    is_commissioning: " . ($conversationFlags['is_commissioning'] ? 'true' : 'false') . "\n";
                    echo "    is_supervision: " . ($conversationFlags['is_supervision'] ? 'true' : 'false') . "\n";
                    
                    if ($conversationFlags['is_current_activity']) {
                        echo "27. Showing current activity keyboard\n";
                    } else if ($conversationFlags['is_commissioning']) {
                        echo "27. Showing commissioning keyboard\n";
                    } else if ($conversationFlags['is_supervision']) {
                        echo "27. Showing supervision keyboard\n";
                    } else {
                        echo "27. Showing mobile brigade keyboard (else clause)\n";
                        echo "28. SUCCESS: Mobile brigade activities keyboard should be shown!\n";
                    }
                } else {
                    echo "25. Failed to extract project code\n";
                }
            } else {
                echo "18. Handling other flow\n";
            }
        } else {
            echo "16. Failed to extract date\n";
        }
    } else {
        echo "8. No match in routeTabelCallback\n";
    }
} else {
    echo "4. TabelHandler cannot handle this callback\n";
}

echo "\n=== Debug Complete ===\n";
echo "\nBased on this simulation, the mobile brigade button should work correctly.\n";
echo "If it's not working, the issue might be:\n";
echo "1. An error in one of the handler methods that's not being caught\n";
echo "2. An issue with the conversation state not being properly maintained\n";
echo "3. An issue with the MessageService methods\n";
echo "4. An encoding issue with the Cyrillic characters\n";
echo "5. The callback data is not exactly what we expect\n";