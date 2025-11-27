# Menu Button Fix Deployment Guide

This guide explains how to deploy and test the menu button fix for the OznaWork Telegram bot.

## Overview

The menu buttons in the main menu ('📊 Табель', '❓ Помощь', '👤 Профиль', '⚙️ Админка') were not working due to command naming conflicts and autoloading issues. This fix resolves those problems.

## Files Included

1. `CustomCommands/TextmenuCommand.php` - Handles menu button selections
2. `CustomCommands/GenericmessageCommand.php` - Processes general text messages and routes menu selections
3. `MENU_BUTTON_FIX_SUMMARY.md` - Detailed summary of the fix
4. `deploy_menu_fix.bat` - Windows batch script for deployment
5. `verify_menu_fix.php` - Server-side verification script

## Deployment Steps

### 1. Backup Current Files
Before deploying, backup the current files on the server:
```bash
# On server
cp /var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/TextmenuCommand.php /var/www/api.ladyzenko.pro/tg/ozna/backups/TextmenuCommand_backup_$(date +%Y%m%d_%H%M%S).php
cp /var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/GenericmessageCommand.php /var/www/api.ladyzenko.pro/tg/ozna/backups/GenericmessageCommand_backup_$(date +%Y%m%d_%H%M%S).php
```

### 2. Upload Fixed Files
Upload the fixed files to the server:
```bash
# From local machine
scp CustomCommands/TextmenuCommand.php root@api.ladyzenko.pro:/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/TextmenuCommand.php
scp CustomCommands/GenericmessageCommand.php root@api.ladyzenko.pro:/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/GenericmessageCommand.php
```

### 3. Regenerate Autoloader
Regenerate the composer autoloader on the server:
```bash
# On server
cd /var/www/api.ladyzenko.pro/tg/ozna
composer dump-autoload
```

### 4. Restart Webhook
Restart the Telegram bot webhook:
```bash
# On server
cd /var/www/api.ladyzenko.pro/tg/ozna
php set.php
```

## Testing the Fix

### 1. Verify Class Loading
Run the verification script on the server:
```bash
# On server
cd /var/www/api.ladyzenko.pro/tg/ozna
php verify_menu_fix.php
```

### 2. Test Menu Buttons
1. Send `/start` command to the bot to display the main menu
2. Press each menu button:
   - '📊 Табель' - Should display work type selection
   - '❓ Помощь' - Should display help information
   - '👤 Профиль' - Should display profile options
   - '⚙️ Админка' - Should display admin panel (if user has appropriate access)

### 3. Check Logs
Monitor the log files for proper routing:
```bash
# On server
tail -f /var/www/api.ladyzenko.pro/tg/ozna/telegram_bot_error.log
tail -f /var/www/api.ladyzenko.pro/tg/ozna/debug_generic.log
```

Look for messages like:
- "Redirecting Reply Keyboard message to TextmenuCommand"
- "TextmenuCommand handling text: '📊 Табель'"

## Troubleshooting

### Issue: Menu buttons still not working
1. Check that both TextmenuCommand.php and GenericmessageCommand.php were uploaded correctly
2. Verify that the autoloader was regenerated
3. Check the error logs for any remaining issues

### Issue: Autoloading errors
1. Ensure composer.json has the correct PSR-4 mappings
2. Verify that custom_autoload.php is included in hook.php
3. Run `composer dump-autoload` again

### Issue: Command naming conflicts
1. Verify that TextmenuCommand has name 'textmenu' and GenericmessageCommand has name 'genericmessage'
2. Check that there are no duplicate command files

## Rollback Procedure

If issues occur, rollback to the previous version:
```bash
# On server
cp /var/www/api.ladyzenko.pro/tg/ozna/backups/TextmenuCommand_backup_YYYYMMDD_HHMMSS.php /var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/TextmenuCommand.php
cp /var/www/api.ladyzenko.pro/tg/ozna/backups/GenericmessageCommand_backup_YYYYMMDD_HHMMSS.php /var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/GenericmessageCommand.php
cd /var/www/api.ladyzenko.pro/tg/ozna
composer dump-autoload
php set.php
```

Replace `YYYYMMDD_HHMMSS` with the actual timestamp of your backup files.