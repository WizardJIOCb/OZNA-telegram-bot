# Bot Diagnostics and Fix Scripts

This directory contains a comprehensive set of diagnostic and fix scripts for the Telegram bot. These scripts help identify and resolve common issues with the bot, particularly with the /start command and menu display.

## Scripts Overview

### 1. Quick Tests
- `quick_menu_test.php` - Quick test of menu creation functionality
- `run_all_tests.bat` - Batch script to run all tests

### 2. Database Related
- `test_database_connection.php` - Test database connectivity and table structure
- `check_user_access_level.php` - Check and display user access levels
- `check_table_structure.php` - Check database table structure

### 3. Menu and Command Related
- `test_menu_display.php` - Test menu display functionality
- `test_start_command.php` - Test StartCommand functionality
- `test_start_command_isolated.php` - Isolated test of StartCommand logic
- `test_message_service.php` - Test MessageService functionality

### 4. Comprehensive Diagnostics
- `comprehensive_test.php` - Comprehensive test of all bot components
- `full_bot_diagnostic.php` - Full bot diagnostic with detailed checks
- `diagnose_start_button.php` - Specific diagnosis of /start button issues

### 5. Fixes and Repairs
- `fix_user_access_levels.php` - Fix and set user access levels
- `check_encoding_fix.php` - Check and fix encoding issues
- `encoding_test.php` - Test encoding in bot environment

### 6. Webhook Related
- `check_and_fix_webhook.php` - Check and fix webhook configuration
- `check_webhook_simple.php` - Simple webhook status check
- `set_webhook.php` - Set webhook URL

### 7. Logging and Debugging
- `check_bot_logs.php` - Check bot logs for errors
- `debug_user_access.php` - Debug user access levels

## How to Use

### Running Individual Scripts
```bash
# Run a specific test
php quick_menu_test.php

# Check database connection
php test_database_connection.php

# Check user access levels
php check_user_access_level.php
```

### Running All Tests
```bash
# On Windows
run_all_tests.bat

# On Linux/Mac
./run_all_tests.sh
```

## Common Issues and Solutions

### 1. /start Command Not Working
1. Run `diagnose_start_button.php` to identify the issue
2. Check if `StartCommand.php` exists in `CustomCommands/`
3. Verify the `execute()` method is properly implemented
4. Check database connectivity with `test_database_connection.php`

### 2. Menu Not Displaying
1. Run `quick_menu_test.php` to test menu creation
2. Check encoding issues with `check_encoding_fix.php`
3. Verify `MessageService.php` has proper `sendMessageWithReplyKeyboard` method

### 3. User Access Level Issues
1. Run `check_user_access_level.php` to check current levels
2. Use `fix_user_access_levels.php` to set proper levels
3. Verify database structure with `check_table_structure.php`

### 4. Webhook Problems
1. Check current webhook status with `check_webhook_simple.php`
2. Set webhook with `set_webhook.php` if needed
3. Verify server accessibility and SSL certificate

## Troubleshooting Steps

### Step 1: Basic Checks
1. Run `quick_menu_test.php` - verifies basic menu functionality
2. Run `test_database_connection.php` - verifies database connectivity
3. Run `check_bot_logs.php` - checks for error messages

### Step 2: Detailed Diagnosis
1. Run `full_bot_diagnostic.php` - comprehensive check of all components
2. Run `diagnose_start_button.php` - specific /start command diagnosis
3. Run `comprehensive_test.php` - end-to-end functionality test

### Step 3: Apply Fixes
1. If encoding issues: Run `check_encoding_fix.php`
2. If access level issues: Run `fix_user_access_levels.php`
3. If webhook issues: Run `check_and_fix_webhook.php`

### Step 4: Verification
1. Run `test_start_command_isolated.php` - verify StartCommand logic
2. Run `test_menu_display.php` - verify menu display
3. Test the bot manually by sending /start command

## Additional Notes

- All scripts should be run from the bot's root directory
- Make sure PHP is properly installed and configured
- Database credentials should be correctly set in `config.php`
- Webhook URL should be accessible from the internet
- SSL certificate is required for webhook functionality

## Support

If you continue to experience issues after running these diagnostics:

1. Check the bot logs for specific error messages
2. Verify all file permissions are correct
3. Ensure the server meets all requirements
4. Contact the bot developer for further assistance