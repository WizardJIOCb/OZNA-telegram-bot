<?php

// Verify project management integration
echo "Verifying project management integration...\n";

// Check 1: Handler file exists and has correct structure
echo "\n1. Checking if handler file exists and is properly structured...\n";
$handlerFile = __DIR__ . '/CustomCommands/Handlers/ProjectManagementHandler.php';
if (file_exists($handlerFile)) {
    $content = file_get_contents($handlerFile);
    if (strpos($content, 'УправлениеПроектом_Персонал') !== false) {
        echo "✅ ProjectManagementHandler.php exists and includes personnel integration\n";
    } else {
        echo "❌ ProjectManagementHandler.php exists but doesn't include personnel integration\n";
    }
} else {
    echo "❌ ProjectManagementHandler.php not found\n";
    exit(1);
}

// Check 2: CallbackqueryCommand includes the handler
echo "\n2. Checking if CallbackqueryCommand registers the handler...\n";
$callbackCommandFile = __DIR__ . '/CustomCommands/CallbackqueryCommand.php';
if (file_exists($callbackCommandFile)) {
    $content = file_get_contents($callbackCommandFile);
    if (strpos($content, 'ProjectManagementHandler') !== false) {
        echo "✅ CallbackqueryCommand registers ProjectManagementHandler\n";
    } else {
        echo "❌ CallbackqueryCommand does not register ProjectManagementHandler\n";
    }
} else {
    echo "❌ CallbackqueryCommand.php not found\n";
}

// Check 3: MainMenuHandler includes the project management button for super admins
echo "\n3. Checking if MainMenuHandler includes project management button...\n";
$mainMenuHandlerFile = __DIR__ . '/CustomCommands/Handlers/MainMenuHandler.php';
if (file_exists($mainMenuHandlerFile)) {
    $content = file_get_contents($mainMenuHandlerFile);
    if (strpos($content, 'Управление проектом') !== false) {
        echo "✅ MainMenuHandler includes project management button\n";
    } else {
        echo "❌ MainMenuHandler does not include project management button\n";
    }
} else {
    echo "❌ MainMenuHandler.php not found\n";
}

// Check 4: MessageService includes project management button for super admins
echo "\n4. Checking if MessageService includes project management button...\n";
$messageServiceFile = __DIR__ . '/CustomCommands/Services/MessageService.php';
if (file_exists($messageServiceFile)) {
    $content = file_get_contents($messageServiceFile);
    if (strpos($content, 'Управление проектом') !== false) {
        echo "✅ MessageService includes project management button\n";
    } else {
        echo "❌ MessageService does not include project management button\n";
    }
} else {
    echo "❌ MessageService.php not found\n";
}

echo "\nVerification completed!\n";