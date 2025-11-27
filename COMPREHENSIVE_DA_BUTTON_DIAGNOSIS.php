<?php
/**
 * COMPREHENSIVE DIAGNOSIS: Why "Да" button is still not working
 */

echo "=== COMPREHENSIVE DIAGNOSIS FOR NON-WORKING 'ДА' BUTTON ===\n\n";

// Load current TabelHandler to verify all fixes are in place
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

echo "1. VERIFYING ALL CALLBACK RECOGNITION FIXES...\n";
echo "=============================================\n";

// Check 1: canHandle method
$hasCanHandle = strpos($tabelHandlerContent, "\$callbackData === 'новое_прочее_yes'") !== false;
echo "✓ canHandle includes 'новое_прочее_yes': " . ($hasCanHandle ? "YES" : "NO") . "\n";

// Check 2: isConfirmationCallback method  
$hasConfirmation = strpos($tabelHandlerContent, "if (\$category === 'новое_прочее_yes')") !== false;
echo "✓ isConfirmationCallback recognizes 'новое_прочее_yes': " . ($hasConfirmation ? "YES" : "NO") . "\n";

// Check 3: routing in routeTabelCallback
$hasRouting = strpos($tabelHandlerContent, "\$category === 'новое_прочее_yes'") !== false;
echo "✓ routeTabelCallback includes 'новое_прочее_yes': " . ($hasRouting ? "YES" : "NO") . "\n";

// Check 4: handleNewProcheeConfirmation method exists
$hasMethod = strpos($tabelHandlerContent, 'function handleNewProcheeConfirmation') !== false;
echo "✓ handleNewProcheeConfirmation method exists: " . ($hasMethod ? "YES" : "NO") . "\n\n";

echo "2. POTENTIAL ISSUES TO INVESTIGATE...\n";
echo "=====================================\n";

if (!$hasCanHandle || !$hasConfirmation || !$hasRouting || !$hasMethod) {
    echo "❌ CODE ISSUES FOUND:\n";
    if (!$hasCanHandle) echo "   - canHandle method missing 'новое_прочее_yes' support\n";
    if (!$hasConfirmation) echo "   - isConfirmationCallback missing 'новое_прочее_yes' support\n";
    if (!$hasRouting) echo "   - routeTabelCallback missing 'новое_прочее_yes' routing\n";
    if (!$hasMethod) echo "   - handleNewProcheeConfirmation method missing\n";
    echo "\n";
} else {
    echo "✅ All code fixes are in place. Issue may be:\n\n";
    
    echo "A. SERVER RESTART REQUIRED:\n";
    echo "   - PHP code changes require web server restart\n";
    echo "   - Old code may still be cached in memory\n";
    echo "   - Try restarting Apache/Nginx or PHP-FPM\n\n";
    
    echo "B. CONVERSATION STATE ISSUES:\n";
    echo "   - User's conversation may be in wrong state\n";
    echo "   - Missing required conversation data\n";
    echo "   - Try starting fresh conversation\n\n";
    
    echo "C. DATABASE CONNECTION ISSUES:\n";
    echo "   - getUserData() failing\n";
    echo "   - TabelEntry->addEntry() failing\n";
    echo "   - Check error logs for exceptions\n\n";
    
    echo "D. USER DATA VALIDATION ISSUES:\n";
    echo "   - FIO not set (required)\n";
    echo "   - Functional direction not set (required)\n";
    echo "   - Check with /profile command\n\n";
    
    echo "E. CALLBACK ENCODING ISSUES:\n";
    echo "   - UTF-8 encoding problems\n";
    echo "   - Cyrillic character corruption\n";
    echo "   - Hidden characters in callback data\n\n";
}

echo "3. DEBUGGING STEPS TO TRY...\n";
echo "============================\n";
echo "1. Check server error logs for PHP errors\n";
echo "2. Restart web server to reload PHP code\n";
echo "3. Test with fresh conversation (clear /start)\n";
echo "4. Verify user profile (/profile command)\n";
echo "5. Check if other buttons work (test different flows)\n";
echo "6. Monitor logs during button click for exact error\n\n";

echo "4. QUICK DIAGNOSTIC COMMANDS...\n";
echo "===============================\n";
echo "• Check server logs: tail -f /var/log/apache2/error.log\n";
echo "• Restart Apache: sudo service apache2 restart\n";
echo "• Test user profile: Send /profile to bot\n";
echo "• Fresh conversation: Send /start to bot\n\n";

echo "✅ DIAGNOSIS COMPLETE - READY FOR TROUBLESHOOTING\n";
?>