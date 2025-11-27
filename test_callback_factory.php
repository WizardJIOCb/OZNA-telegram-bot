<?php
// Test to see what's happening in CallbackHandlerFactory

echo "=== CALLBACK FACTORY DEBUG TEST ===\n\n";

// Check if CallbackHandlerFactory logs are working
echo "Looking for CallbackHandlerFactory logs in the server logs...\n\n";

echo "Expected log pattern when you click RK button:\n";
echo "============================================\n";
echo "CallbackHandlerFactory: handleCallback called with callbackData: 'cat_ТабУстрЗамРК'\n";
echo "CallbackHandlerFactory: handleCallback callbackData length: 28\n";
echo "CallbackHandlerFactory: handleCallback callbackData hex: 6361745f...\n";
echo "CallbackHandlerFactory: registered handlers:\n";
echo "  Handler #0: MainMenuHandler with priority: 50\n";
echo "  Handler #1: TabelHandler with priority: 70\n";
echo "  Handler #2: AdminHandler with priority: 25\n";
echo "  etc...\n";
echo "CallbackHandlerFactory: checking handler #0: MainMenuHandler\n";
echo "MainMenuHandler: canHandle called with callbackData: 'cat_ТабУстрЗамРК'\n";
echo "MainMenuHandler: canHandle result: false\n";
echo "CallbackHandlerFactory: checking handler #1: TabelHandler\n";
echo "=== TABEL HANDLER CANHANDLE DEBUG ===\n";
echo "RAW CALLBACK: 'cat_ТабУстрЗамРК'\n";
echo "TabelHandler: RK CALLBACK DETECTED in canHandle!\n";
echo "TabelHandler: canHandle result: true\n";
echo "CallbackHandlerFactory: found handler: TabelHandler\n\n";

echo "If you DON'T see these logs, the problem is:\n";
echo "1. CallbackHandlerFactory is not being used\n";
echo "2. Another system is intercepting the callback\n";
echo "3. The bot webhook/polling is not working\n\n";

echo "If you DO see these logs but still get 'Unknown command':\n";
echo "1. The issue is in TabelHandler's routing logic\n";
echo "2. Check for encoding issues\n";
echo "3. Check for missing handlers in the routing chain\n\n";

echo "INSTRUCTIONS:\n";
echo "=============\n";
echo "1. Click the RK button: '🔧 Устранения замечаний РК'\n";
echo "2. Check your server logs immediately\n";
echo "3. Look for the log patterns shown above\n";
echo "4. Report back what you see (or don't see)\n\n";

echo "If no logs appear at all, the issue is NOT in TabelHandler\n";
echo "If logs appear but show 'Unknown command', the issue IS in TabelHandler\n";
?>