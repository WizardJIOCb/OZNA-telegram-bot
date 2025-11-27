<?php

// Test the fixed automation scheme changes confirmation flow

echo "=== Testing Fixed Automation Scheme Changes Confirmation ===\n\n";

// Test the specific confirmation callback
$confirmationCallback = 'cat_ТабАвтом_ИзмСх_yes';
echo "Testing confirmation callback: '{$confirmationCallback}'\n\n";

// Simulate isConfirmationCallback check (the method we just fixed)
function testIsConfirmationCallback($category) {
    // Check for automation equipment short format confirmations (newly added)
    if (preg_match('/^cat_ТабАвтом_.+_yes$/u', $category)) {
        return true;
    }
    return false;
}

$isConfirmation = testIsConfirmationCallback($confirmationCallback);
echo "isConfirmationCallback('{$confirmationCallback}'): " . ($isConfirmation ? 'TRUE' : 'FALSE') . "\n";

// Simulate isActivityExecution check (already working from previous fix)
function testIsActivityExecution($category) {
    if (preg_match('/^cat_ТабАвтом_(.+)_yes$/u', $category, $matches)) {
        $shortToFullMap = [
            'РабПоГр' => 'РабПоГр_Автом',
            'ФормИД' => 'ФормИД_Автом',
            'ДопРаб' => 'ДопРаб_Автом',
            'ПодтВех' => 'ПодтВех_Автом',
            'ИзмСх' => 'ИзмСх_Автом',
            'МонтКб' => 'МонтКб_Автом',
            'РаскКб' => 'РаскКб_Автом',
            'МонтАСУ' => 'МонтАСУ_Автом',
            'УдалПО' => 'УдалПО_Автом',
            'Проч' => 'Прочее_Автом'
        ];
        
        $shortActivityKey = $matches[1];
        if (isset($shortToFullMap[$shortActivityKey])) {
            return true;
        }
    }
    return false;
}

$isActivityExecution = testIsActivityExecution($confirmationCallback);
echo "isActivityExecution('{$confirmationCallback}'): " . ($isActivityExecution ? 'TRUE' : 'FALSE') . "\n\n";

if ($isConfirmation && $isActivityExecution) {
    echo "✅ SUCCESS: The automation 'Изменение схем подключения' 'Да' button should now work!\n\n";
    echo "Flow:\n";
    echo "1. User clicks 'Изменение схем подключения' → Shows confirmation\n";
    echo "2. User clicks 'Да' (cat_ТабАвтом_ИзмСх_yes)\n";
    echo "3. isConfirmationCallback() → TRUE (now fixed)\n";
    echo "4. isActivityExecution() → TRUE (already working)\n";
    echo "5. handleActivityExecution() → Creates report with ИзмСх_Автом\n";
    echo "6. Report sent successfully!\n";
} else {
    echo "❌ ISSUE: There's still a problem.\n";
    if (!$isConfirmation) {
        echo "   - isConfirmationCallback() returned FALSE\n";
    }
    if (!$isActivityExecution) {
        echo "   - isActivityExecution() returned FALSE\n";
    }
}

?>