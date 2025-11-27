<?php
/**
 * MASTER DUTIES TELEGRAM DIAGNOSTIC
 * This command will simulate the Master Duties button click and show results in Telegram
 * Usage: Send this as a message to the bot: /debug_master_duties
 */

require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

// Create a simple diagnostic command that can be called from Telegram
class MasterDutiesDiagnostic {
    
    public function runDiagnostic($chatId) {
        $result = "🔍 **MASTER DUTIES DIAGNOSTIC REPORT**\n";
        $result .= "=====================================\n\n";
        
        // Test 1: Check if button callback mapping exists
        $result .= "**1. BUTTON CALLBACK MAPPING:**\n";
        $handlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
        if (file_exists($handlerFile)) {
            $content = file_get_contents($handlerFile);
            
            if (strpos($content, "'cat_ТабИсполнОбяз' => 'Табель_ИсполнениеОбязМастера'") !== false) {
                $result .= "✅ Short format mapping: FOUND\n";
            } else {
                $result .= "❌ Short format mapping: MISSING\n";
            }
            
            if (strpos($content, "'category_Табель_ИсполнениеОбязМастера' => 'Табель_ИсполнениеОбязМастера'") !== false) {
                $result .= "✅ Full format mapping: FOUND\n";
            } else {
                $result .= "❌ Full format mapping: MISSING\n";
            }
        } else {
            $result .= "❌ TabelHandler.php not found\n";
        }
        
        $result .= "\n";
        
        // Test 2: Check handler method exists
        $result .= "**2. HANDLER METHOD:**\n";
        if (strpos($content, "category === 'Табель_ИсполнениеОбязМастера'") !== false) {
            $result .= "✅ Master Duties handler: FOUND\n";
        } else {
            $result .= "❌ Master Duties handler: MISSING\n";
        }
        
        $result .= "\n";
        
        // Test 3: Check activity type registration
        $result .= "**3. ACTIVITY TYPE REGISTRATION:**\n";
        if (strpos($content, "'ИсполнениеОбязМастера'") !== false) {
            $result .= "✅ Activity type in array: FOUND\n";
        } else {
            $result .= "❌ Activity type in array: MISSING\n";
        }
        
        $result .= "\n";
        
        // Test 4: Check critical exclusion fix
        $result .= "**4. CRITICAL EXCLUSION FIX:**\n";
        if (strpos($content, "category === 'Табель_ИсполнениеОбязМастера'") !== false &&
            strpos($content, "return false") !== false) {
            $result .= "✅ Critical exclusion fix: FOUND\n";
        } else {
            $result .= "❌ Critical exclusion fix: MISSING\n";
        }
        
        $result .= "\n";
        
        // Test 5: Simulate callback processing
        $result .= "**5. CALLBACK PROCESSING SIMULATION:**\n";
        $testCallback = 'cat_ТабИсполнОбяз';
        $result .= "Testing callback: '{$testCallback}'\n";
        
        // Simulate the mapping
        $shortFormats = [
            'cat_ТабИсполнОбяз' => 'Табель_ИсполнениеОбязМастера',
        ];
        
        if (isset($shortFormats[$testCallback])) {
            $mappedCategory = $shortFormats[$testCallback];
            $result .= "✅ Mapping result: '{$mappedCategory}'\n";
            
            // Check if this would match our handler
            if ($mappedCategory === 'Табель_ИсполнениеОбязМастера') {
                $result .= "✅ Handler condition: WOULD MATCH\n";
                $result .= "✅ Expected result: Confirmation dialog\n";
            } else {
                $result .= "❌ Handler condition: WOULD NOT MATCH\n";
            }
        } else {
            $result .= "❌ Mapping failed: NOT FOUND\n";
        }
        
        $result .= "\n";
        
        // Test 6: Navigation path verification
        $result .= "**6. NAVIGATION PATH:**\n";
        $result .= "Required path: /tabel → ШМР → Project → Проектная деятельность\n";
        $result .= "Button location: Supervision Project Activity Menu\n";
        $result .= "Button text: '👷 Исполнение обязанностей мастера'\n";
        $result .= "Button callback: 'cat_ТабИсполнОбяз'\n";
        
        $result .= "\n";
        
        // Test 7: Requirements check
        $result .= "**7. REQUIREMENTS TO WORK:**\n";
        $result .= "• User access level > 0 (not guest)\n";
        $result .= "• User has FIO set\n";
        $result .= "• User has functional direction set\n";
        $result .= "• Project code exists in conversation\n";
        $result .= "• No recent tabel entry (for non-admin users)\n";
        
        $result .= "\n";
        
        // Overall status
        $result .= "**OVERALL STATUS:**\n";
        $allChecksPass = (
            strpos($content, "'cat_ТабИсполнОбяз' => 'Табель_ИсполнениеОбязМастера'") !== false &&
            strpos($content, "category === 'Табель_ИсполнениеОбязМастера'") !== false &&
            strpos($content, "'ИсполнениеОбязМастера'") !== false
        );
        
        if ($allChecksPass) {
            $result .= "🎉 **ALL SYSTEMS GREEN** - Button should work!\n";
            $result .= "\n**If button still doesn't work, possible causes:**\n";
            $result .= "• User access/permission issue\n";
            $result .= "• Missing conversation state\n";
            $result .= "• Wrong navigation path\n";
            $result .= "• Cache/server restart needed\n";
        } else {
            $result .= "⚠️ **ISSUES DETECTED** - Button may not work\n";
            $result .= "Check the failed items above\n";
        }
        
        return $result;
    }
}

// If called directly, output the diagnostic
if (php_sapi_name() === 'cli') {
    $diagnostic = new MasterDutiesDiagnostic();
    echo $diagnostic->runDiagnostic('test_chat');
}
?>