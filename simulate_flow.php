<?php

// Simulate the exact flow of the mobile brigade button click
echo "=== Simulating Mobile Brigade Button Flow ===\n\n";

// Step 1: User clicks the mobile brigade button
$initialCallback = 'category_Табель_Мобильная_Бригада_Дата';
echo "1. Initial callback: {$initialCallback}\n";

// Simulate handler selection process
echo "\n--- Handler Selection Process ---\n";

// AdminHandler check (priority 60)
$adminCanHandle = strpos($initialCallback, 'category_Админка') === 0 || 
                  strpos($initialCallback, 'category_Адм_') === 0 ||
                  strpos($initialCallback, 'adm_req_') === 0;
echo "AdminHandler canHandle: " . ($adminCanHandle ? 'true' : 'false') . "\n";

// AccessLevelHandler check (priority 25)
$accessLevelCanHandle = strpos($initialCallback, 'category_УровеньДоступа') === 0 ||
                        $initialCallback === 'acc_lvl_users' ||
                        $initialCallback === 'acc_lvl_manage_users' ||
                        strpos($initialCallback, 'acc_lvl_user_details_') === 0 ||
                        strpos($initialCallback, 'acc_lvl_set_level_') === 0;
echo "AccessLevelHandler canHandle: " . ($accessLevelCanHandle ? 'true' : 'false') . "\n";

// SupervisorHandler check (priority 25)
$supervisorCanHandle = strpos($initialCallback, 'category_Супервайзер') === 0;
echo "SupervisorHandler canHandle: " . ($supervisorCanHandle ? 'true' : 'false') . "\n";

// TabelHandler check (priority 5)
$tabelCanHandle = strpos($initialCallback, 'category_Табель') === 0;
echo "TabelHandler canHandle: " . ($tabelCanHandle ? 'true' : 'false') . "\n";

echo "\n--- Expected Handler Selection ---\n";
echo "Expected: TabelHandler should handle this callback\n";

// Simulate what TabelHandler.routeTabelCallback should do
echo "\n--- TabelHandler Processing ---\n";
$category = substr($initialCallback, strlen('category_'));
echo "Extracted category: {$category}\n";

// Check if it matches the mobile brigade condition
$mobileBrigadeMatch = $category === 'Табель_Мобильная_Бригада_Дата';
echo "Category matches mobile brigade condition: " . ($mobileBrigadeMatch ? 'true' : 'false') . "\n";

if ($mobileBrigadeMatch) {
    echo "Should call handleDateSelectionMenu()\n";
    echo "Which should show date selection keyboard with prefix: category_Табель_Мобильная_Бригада\n";
    
    // Simulate what the date selection keyboard would look like
    $todayCallback = 'category_Табель_Мобильная_Бригада_ДатаСегодня';
    $yesterdayCallback = 'category_Табель_Мобильная_Бригада_ДатаВчера';
    echo "Date selection buttons:\n";
    echo "  Today: {$todayCallback}\n";
    echo "  Yesterday: {$yesterdayCallback}\n";
    
    // Simulate user selecting today
    $dateCallback = $todayCallback;
    echo "\nUser selected: {$dateCallback}\n";
    
    $dateCategory = substr($dateCallback, strlen('category_'));
    echo "Extracted date category: {$dateCategory}\n";
    
    // Check if ProjectSelector recognizes this as a date selection
    $isDateSelection = strpos($dateCategory, 'ДатаСегодня') !== false || strpos($dateCategory, 'ДатаВчера') !== false;
    echo "ProjectSelector recognizes as date selection: " . ($isDateSelection ? 'true' : 'false') . "\n";
    
    if ($isDateSelection) {
        echo "Should call handleDateSelection()\n";
        echo "Which should show project selection keyboard\n";
        
        // Simulate what happens when user selects a project
        $projectCallback = 'category_Табель_ШифрПроекта_ОИ834';
        echo "\nUser selected project: {$projectCallback}\n";
        
        $projectCategory = substr($projectCallback, strlen('category_'));
        echo "Extracted project category: {$projectCategory}\n";
        
        // Check if ProjectSelector recognizes this as a project selection
        $isProjectSelection = strpos($projectCategory, 'ШифрПроекта_') !== false;
        echo "ProjectSelector recognizes as project selection: " . ($isProjectSelection ? 'true' : 'false') . "\n";
        
        if ($isProjectSelection) {
            echo "Should call handleProjectSelection()\n";
            echo "Which should show mobile brigade activities keyboard\n";
        }
    }
}

echo "\n=== Simulation Complete ===\n";
echo "\nIf the button is not working, the issue might be:\n";
echo "1. The callback data is not exactly 'category_Табель_Мобильная_Бригада_Дата'\n";
echo "2. There's an encoding issue with the Cyrillic characters\n";
echo "3. There's an error in one of the handler methods\n";
echo "4. The conversation state is not being managed correctly\n";
echo "5. There's an issue with the MessageService methods\n";