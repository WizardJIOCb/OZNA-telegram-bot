<?php
/**
 * Rename vendor GenericmessageCommand to prevent it from loading
 * This allows our custom version to be used instead
 */

$vendorFile = __DIR__ . '/vendor/longman/telegram-bot/src/Commands/SystemCommands/GenericmessageCommand.php';
$backupFile = __DIR__ . '/vendor/longman/telegram-bot/src/Commands/SystemCommands/GenericmessageCommand.php.backup';

echo "=== Disabling Vendor GenericmessageCommand ===\n\n";

if (file_exists($vendorFile)) {
    echo "✅ Found vendor file: {$vendorFile}\n";
    
    if (file_exists($backupFile)) {
        echo "ℹ️  Backup already exists: {$backupFile}\n";
        echo "   Vendor version is already disabled\n";
    } else {
        echo "📝 Renaming vendor file to .backup...\n";
        
        if (rename($vendorFile, $backupFile)) {
            echo "✅ SUCCESS: Vendor GenericmessageCommand disabled!\n";
            echo "   Original file backed up to: {$backupFile}\n";
            echo "\n";
            echo "🎯 Now our custom version will be loaded instead!\n";
        } else {
            echo "❌ ERROR: Failed to rename file\n";
            echo "   You may need to do this manually with admin rights\n";
        }
    }
} else {
    echo "ℹ️  Vendor file not found (already renamed or deleted)\n";
    
    if (file_exists($backupFile)) {
        echo "✅ Backup file exists, vendor version is disabled\n";
    }
}

echo "\n=== Instructions ===\n";
echo "After running this script:\n";
echo "1. Upload changed files to server via FTP/SFTP\n";
echo "2. Test bot: Проекты → Добавить проект → Type 'ТЕСТ5'\n";
echo "3. Check logs for 'CUSTOM GenericmessageCommand EXECUTE START'\n";
echo "\nTo restore vendor version (if needed):\n";
echo "   Rename {$backupFile} back to {$vendorFile}\n";
echo "   Or run: composer install --force\n";
