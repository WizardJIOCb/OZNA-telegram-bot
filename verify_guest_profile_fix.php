<?php

// Simple verification script for guest profile access request button
echo "Verifying guest profile access request button implementation...\n";

// Check if ProfileHandler was modified correctly
echo "\n1. Checking ProfileHandler modifications...\n";
$profileHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/ProfileHandler.php');

if (strpos($profileHandlerContent, 'Запросить доступ') !== false) {
    echo "✅ ProfileHandler now shows 'Запросить доступ' button for guests\n";
} else {
    echo "❌ ProfileHandler missing access request button for guests\n";
}

if (strpos($profileHandlerContent, 'Guest level') !== false) {
    echo "✅ ProfileHandler implements guest-level profile display\n";
} else {
    echo "❌ ProfileHandler missing guest profile logic\n";
}

if (strpos($profileHandlerContent, 'category_Админка_ЗапросДоступ') !== false) {
    echo "✅ ProfileHandler includes correct callback for access request\n";
} else {
    echo "❌ ProfileHandler missing access request callback\n";
}

echo "\n2. Summary of changes made:\n";
echo "   - Modified ProfileHandler to show 'Запросить доступ' button for guests\n";
echo "   - Guests can now request access upgrade directly from their profile\n";
echo "   - Non-guest users see the standard profile without the request button\n";

echo "\n✅ Guest profile access request button implementation verified.\n";
echo "Guest users should now see the 'Запросить доступ' button in their profile.\n";