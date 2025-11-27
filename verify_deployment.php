<?php
// Simple test to verify if our changes are deployed

echo "=== DEPLOYMENT VERIFICATION ===\n";

// Check if our emergency debug is in TabelHandler
$tabelHandlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
if (file_exists($tabelHandlerPath)) {
    $content = file_get_contents($tabelHandlerPath);
    
    if (strpos($content, '*** EMERGENCY: PNR BUTTON DETECTED IN TABEL HANDLER! ***') !== false) {
        echo "✅ Emergency debug found in TabelHandler\n";
    } else {
        echo "❌ Emergency debug NOT found in TabelHandler\n";
    }
    
    if (strpos($content, 'category_Табель_ПНР_ТЕСТ') !== false) {
        echo "✅ Test callback found in TabelHandler\n";
    } else {
        echo "❌ Test callback NOT found in TabelHandler\n";
    }
} else {
    echo "❌ TabelHandler file not found\n";
}

// Check if our emergency bypass is in CallbackHandlerFactory
$factoryPath = __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';
if (file_exists($factoryPath)) {
    $content = file_get_contents($factoryPath);
    
    if (strpos($content, '*** EMERGENCY: PNR BUTTON INTERCEPTED IN FACTORY! FORCING TABEL HANDLER! ***') !== false) {
        echo "✅ Emergency bypass found in CallbackHandlerFactory\n";
    } else {
        echo "❌ Emergency bypass NOT found in CallbackHandlerFactory\n";
    }
} else {
    echo "❌ CallbackHandlerFactory file not found\n";
}

// Check if test callback is in MessageService
$messageServicePath = __DIR__ . '/CustomCommands/Services/MessageService.php';
if (file_exists($messageServicePath)) {
    $content = file_get_contents($messageServicePath);
    
    if (strpos($content, 'category_Табель_ПНР_ТЕСТ') !== false) {
        echo "✅ Test callback found in MessageService\n";
    } else {
        echo "❌ Test callback NOT found in MessageService\n";
    }
} else {
    echo "❌ MessageService file not found\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "If any checks failed:\n";
echo "1. Restart your web server (Apache/Nginx)\n";
echo "2. Clear OPcache: opcache_reset()\n";
echo "3. Check file permissions\n";
echo "4. Verify you're editing the correct server files\n";
?>