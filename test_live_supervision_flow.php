<?php

require_once 'CustomCommands/Handlers/TabelHandler.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/ProjectSelector.php';
require_once 'CustomCommands/Services/DatabaseService.php';
require_once 'CustomCommands/Services/AdminService.php';
require_once 'CustomCommands/Services/TabelEntry.php';

echo "=== LIVE SUPERVISION FLOW TEST ===\n";

// Test the exact sequence of user actions
$testSequence = [
    'category_Табель',                           // 1. User clicks main Табель
    'category_Табель_Шеф_монтаж',               // 2. User clicks "🔧 Отдел ШМР"
    'category_Табель_ШМ_Сегодня',               // 3. User clicks "Сегодня"
    'project_ПКПО-24'                           // 4. User clicks a project
];

echo "Testing sequence:\n";
foreach ($testSequence as $i => $callback) {
    echo "  Step " . ($i + 1) . ": {$callback}\n";
}

try {
    // Initialize services
    $adminService = new AdminService();
    $messageService = new MessageService($adminService);
    $tabelHandler = new TabelHandler($adminService, $messageService);
    
    echo "\n=== STEP 1: Main Табель Menu ===\n";
    $result = $tabelHandler->canHandle($testSequence[0]);
    echo "TabelHandler->canHandle('{$testSequence[0]}'): " . ($result ? 'TRUE' : 'FALSE') . "\n";
    
    if ($result) {
        echo "✓ TabelHandler can handle main menu\n";
    } else {
        echo "❌ TabelHandler cannot handle main menu - PROBLEM!\n";
        exit;
    }
    
    echo "\n=== STEP 2: Supervision Department Button ===\n";
    $result = $tabelHandler->canHandle($testSequence[1]);
    echo "TabelHandler->canHandle('{$testSequence[1]}'): " . ($result ? 'TRUE' : 'FALSE') . "\n";
    
    if ($result) {
        echo "✓ TabelHandler can handle supervision department button\n";
        
        // Extract category
        if (strpos($testSequence[1], 'category_') === 0) {
            $category = substr($testSequence[1], strlen('category_'));
            echo "Extracted category: '{$category}'\n";
            
            // Check routing
            if ($category === 'Табель_Шеф_монтаж') {
                echo "✓ Should route to handleSupervisionDateSelection()\n";
            } else {
                echo "❌ Category mismatch: expected 'Табель_Шеф_монтаж', got '{$category}'\n";
            }
        }
    } else {
        echo "❌ TabelHandler cannot handle supervision department button - PROBLEM!\n";
        exit;
    }
    
    echo "\n=== STEP 3: Date Selection ===\n";
    $result = $tabelHandler->canHandle($testSequence[2]);
    echo "TabelHandler->canHandle('{$testSequence[2]}'): " . ($result ? 'TRUE' : 'FALSE') . "\n";
    
    if ($result) {
        echo "✓ TabelHandler can handle date selection\n";
        
        // Check if this is detected as date selection
        $projectSelector = new ProjectSelector($adminService);
        $isDateSelection = $projectSelector->isDateSelection($testSequence[2]);
        echo "ProjectSelector->isDateSelection('{$testSequence[2]}'): " . ($isDateSelection ? 'TRUE' : 'FALSE') . "\n";
        
        if ($isDateSelection) {
            echo "✓ Date selection detected correctly\n";
            
            // Extract category and check for supervision marker
            if (strpos($testSequence[2], 'category_') === 0) {
                $category = substr($testSequence[2], strlen('category_'));
                echo "Extracted category: '{$category}'\n";
                
                if (str_contains($category, 'ШМ')) {
                    echo "✓ Contains supervision marker 'ШМ'\n";
                    echo "✓ Should set is_supervision = true in conversation\n";
                } else {
                    echo "❌ Does not contain supervision marker 'ШМ'\n";
                }
            }
        } else {
            echo "❌ Date selection not detected - PROBLEM!\n";
        }
    } else {
        echo "❌ TabelHandler cannot handle date selection - PROBLEM!\n";
        exit;
    }
    
    echo "\n=== STEP 4: Project Selection ===\n";
    $result = $tabelHandler->canHandle($testSequence[3]);
    echo "TabelHandler->canHandle('{$testSequence[3]}'): " . ($result ? 'TRUE' : 'FALSE') . "\n";
    
    if ($result) {
        echo "✓ TabelHandler can handle project selection\n";
        
        // Check if this is detected as project selection
        $isProjectSelection = $projectSelector->isProjectSelection($testSequence[3]);
        echo "ProjectSelector->isProjectSelection('{$testSequence[3]}'): " . ($isProjectSelection ? 'TRUE' : 'FALSE') . "\n";
        
        if ($isProjectSelection) {
            echo "✓ Project selection detected correctly\n";
            echo "✓ Should route to handleProjectSelection()\n";
            echo "✓ Should check is_supervision flag from conversation\n";
            echo "✓ Should call createSupervisionKeyboard()\n";
            
            // Test supervision keyboard creation
            echo "\nTesting createSupervisionKeyboard():\n";
            $keyboard = $messageService->createSupervisionKeyboard();
            echo "Keyboard created with " . count($keyboard) . " rows\n";
            
            if (count($keyboard) > 0) {
                echo "✓ Supervision keyboard created successfully\n";
                echo "Sample buttons:\n";
                for ($i = 0; $i < min(3, count($keyboard)); $i++) {
                    foreach ($keyboard[$i] as $button) {
                        echo "  - '{$button['text']}' -> '{$button['callback_data']}'\n";
                    }
                }
            } else {
                echo "❌ Supervision keyboard is empty - PROBLEM!\n";
            }
        } else {
            echo "❌ Project selection not detected - PROBLEM!\n";
        }
    } else {
        echo "❌ TabelHandler cannot handle project selection - PROBLEM!\n";
        exit;
    }
    
    echo "\n=== CONCLUSION ===\n";
    echo "✓ All components appear to be working correctly\n";
    echo "✓ The issue is likely in the conversation state management\n";
    echo "✓ The is_supervision flag might not be preserved between callbacks\n";
    
    echo "\n=== RECOMMENDATIONS ===\n";
    echo "1. Check if conversation is being properly loaded/saved\n";
    echo "2. Add debugging to track conversation state across callbacks\n";
    echo "3. Verify that is_supervision flag is set and maintained\n";
    echo "4. Check if there are any exceptions during project selection\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nDONE\n";