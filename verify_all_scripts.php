<?php

echo "Verifying all created diagnostic scripts...\n";
echo "==========================================\n\n";

// Список всех созданных скриптов
$scripts = [
    'quick_menu_test.php' => 'Quick menu test',
    'test_database_connection.php' => 'Database connection test',
    'check_user_access_level.php' => 'User access level check',
    'test_menu_display.php' => 'Menu display test',
    'test_start_command_isolated.php' => 'Start command isolated test',
    'comprehensive_test.php' => 'Comprehensive test',
    'check_bot_logs.php' => 'Bot logs check',
    'diagnose_start_button.php' => 'Start button diagnosis',
    'fix_user_access_levels.php' => 'User access levels fix',
    'check_and_fix_webhook.php' => 'Webhook check and fix',
    'full_bot_diagnostic.php' => 'Full bot diagnostic',
    'check_encoding_fix.php' => 'Encoding check and fix',
    'encoding_test.php' => 'Encoding test',
    'check_webhook_simple.php' => 'Simple webhook check',
    'set_webhook.php' => 'Set webhook',
    'debug_user_access.php' => 'Debug user access',
    'check_table_structure.php' => 'Check table structure',
    'test_start_command.php' => 'Start command test',
    'test_message_service.php' => 'Message service test',
    'test_commands.php' => 'Commands test'
];

$foundScripts = 0;
$missingScripts = 0;

foreach ($scripts as $filename => $description) {
    $filepath = __DIR__ . '/' . $filename;
    if (file_exists($filepath)) {
        $foundScripts++;
        echo "✓ {$filename} - Found\n";
        
        // Проверяем, что файл не пустой
        $content = file_get_contents($filepath);
        if (empty(trim($content))) {
            echo "  ⚠ File is empty\n";
        } else {
            $lines = count(explode("\n", $content));
            echo "  ✓ File has {$lines} lines\n";
        }
    } else {
        $missingScripts++;
        echo "✗ {$filename} - Missing\n";
    }
}

echo "\n";
echo "Summary:\n";
echo "========\n";
echo "Found scripts: {$foundScripts}\n";
echo "Missing scripts: {$missingScripts}\n";
echo "Total scripts: " . count($scripts) . "\n";

if ($missingScripts > 0) {
    echo "\n⚠ Some scripts are missing. Please recreate them.\n";
} else {
    echo "\n✓ All scripts are present.\n";
}

echo "\nBatch files:\n";
echo "============\n";
$batchFiles = [
    'run_all_tests.bat' => 'Run all tests',
    'run_all_diagnostics.bat' => 'Run all diagnostics'
];

foreach ($batchFiles as $filename => $description) {
    $filepath = __DIR__ . '/' . $filename;
    if (file_exists($filepath)) {
        echo "✓ {$filename} - Found\n";
    } else {
        echo "✗ {$filename} - Missing\n";
    }
}

echo "\nDocumentation:\n";
echo "==============\n";
$docs = [
    'BOT_DIAGNOSTICS_README.md' => 'Diagnostics README'
];

foreach ($docs as $filename => $description) {
    $filepath = __DIR__ . '/' . $filename;
    if (file_exists($filepath)) {
        echo "✓ {$filename} - Found\n";
    } else {
        echo "✗ {$filename} - Missing\n";
    }
}

echo "\nVerification completed.\n";