# Commissioning Works Debugging Guide

This guide explains how to debug commissioning works functionality and where to find execution results.

## Where to Find Debugging Information

### 1. Log Files
All debugging information is written to log files in the main project directory:

- **telegram_bot_error.log** - Main error log file containing all debug information
- **webhook_input.log** - Contains raw webhook requests from Telegram
- **debug_callback_processing.log** - Detailed callback processing information
- **debug_electrical_callback.log** - Electrical equipment callback debugging

### 2. How to View Logs

#### On Windows (PowerShell):
```powershell
# View the last 50 lines of the main error log
Get-Content -Path "telegram_bot_error.log" -Tail 50

# Continuously monitor the log file for new entries
Get-Content -Path "telegram_bot_error.log" -Wait

# Search for specific terms in logs
Select-String -Path "telegram_bot_error.log" -Pattern "commissioning"
```

#### On Windows (Command Prompt):
```cmd
# View the last 50 lines of the main error log
type telegram_bot_error.log | more

# Search for specific terms in logs
findstr "commissioning" telegram_bot_error.log
```

## Debugging Commissioning Works Issues

### 1. Check User Access Level
Commissioning works functionality requires at least level 1 access. Users with "Guest" level (0) will be blocked.

### 2. Verify Callback Processing
The flow for commissioning works:
1. User clicks "⚙️ Отдел ПНР" button
2. Callback data: `category_Табель_Пусконаладка`
3. TabelHandler processes this callback
4. Shows date selection menu
5. User selects date
6. Shows project selection
7. User selects project
8. Shows commissioning activities menu

### 3. Common Issues and Solutions

#### Issue: Date selection menu not appearing
- Check if the callback is being processed correctly in TabelHandler
- Look for "handleCommissioningDateSelection" in logs
- Verify that the prefix `category_Табель_ПНЛ` is working correctly

#### Issue: Project selection not appearing
- Check if date was correctly selected
- Look for "handleDateSelection" in logs
- Verify project list is loaded correctly

#### Issue: Commissioning activities menu not appearing
- Check if project was correctly selected
- Look for "handleCommissioningKeyboard" in logs

## Testing Commissioning Works

### 1. Manual Test Script
Run the debug script to trace the complete commissioning flow:
```bash
php debug_commissioning_flow.php
```

### 2. Direct Testing
1. Start a conversation with the bot
2. Click "⚙️ Отдел ПНР" button
3. Check logs immediately for processing information

## Log Analysis

### Key Log Entries to Look For:
- "TabelHandler canHandle result"
- "MATCHED: Category is 'Табель_Пусконаладка'"
- "handleCommissioningDateSelection"
- "Creating date selection keyboard"
- "handleProjectSelection"
- "handleCommissioningKeyboard"

### Error Indicators:
- "Telegram Bot Error"
- "Telegram Bot Fatal Error"
- "response status: FAILED"
- "PDOException"
- "Database connection failed"

## Troubleshooting Steps

### 1. Check File Permissions
Ensure all PHP files have read permissions and log files have write permissions.

### 2. Verify Database Connection
Check if the database connection is working:
- Look for database connection errors in logs
- Verify MySQL credentials in hook.php

### 3. Check Telegram API Response
- Look for Telegram API errors in logs
- Check if messages are being sent successfully

### 4. Encoding Issues
- Ensure all files are saved in UTF-8 encoding
- Check for Cyrillic text handling issues

## Additional Debugging Tools

### 1. Run Specific Debug Scripts
```bash
# Debug the complete commissioning flow
php debug_commissioning_flow.php

# Debug callback processing
php debug_callback_processing.php

# Debug electrical equipment (related to commissioning)
php debug_electrical_callback.php
```

### 2. Check Webhook Status
```bash
# Check if webhook is properly set
php check_webhook.php
```

## Contact for Further Assistance

If issues persist after following this guide:
1. Collect the relevant log entries
2. Note the exact steps that lead to the issue
3. Provide the timestamp of when the issue occurred
4. Include any error messages from the logs

The development team can then analyze the specific issue and provide a targeted solution.