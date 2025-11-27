# Test Simple Report Flow

## How to Test

1. **Upload the file**:
   ```bash
   scp "c:\ozna_bot\CustomCommands\Handlers\ProjectHandler.php" root@212.193.28.202:/var/www/html/ozna_bot/CustomCommands/Handlers/ProjectHandler.php
   ```

2. **Open bot** and go to: `Проекты` → `Отчёт по проекту` → `Простой отчёт`

3. **Enter start date**: e.g., `01.01.2025`

4. **Enter end date**: e.g., `13.01.2025`

5. **Check logs** on server:
   ```bash
   ssh root@212.193.28.202 "tail -100 /var/www/html/ozna_bot/php_error.log | grep DEBUG"
   ```

## Expected Log Output

If working correctly, you should see:

```
DEBUG_SIMPLE_REPORT_DATE: handleSimpleReportDateInput called with input: 01.01.2025
DEBUG_SIMPLE_REPORT_DATE: awaiting_simple_report_start_date = true
DEBUG_SIMPLE_REPORT_DATE: awaiting_simple_report_end_date = not set
DEBUG_SIMPLE_REPORT_DATE: Showing end date menu

DEBUG_SIMPLE_REPORT_DATE: handleSimpleReportDateInput called with input: 13.01.2025
DEBUG_SIMPLE_REPORT_DATE: awaiting_simple_report_start_date = false
DEBUG_SIMPLE_REPORT_DATE: awaiting_simple_report_end_date = true
DEBUG_SIMPLE_REPORT_DATE: Processing END date
DEBUG_SIMPLE_REPORT_DATE: Showing filter menu with dates: 01.01.2025 to 13.01.2025

DEBUG_FILTER_MENU: showSimpleReportFilterMenu called with dates: 01.01.2025 to 13.01.2025
DEBUG_FILTER_MENU: Preparing to set conversation notes
DEBUG_FILTER_MENU: Conversation updated successfully
DEBUG_FILTER_MENU: Preparing keyboard with 4 rows
DEBUG_FILTER_MENU: Sending message to chat: [CHAT_ID]
DEBUG_FILTER_MENU: Message sent, result: OK
```

## Possible Issues

### If you see "Message sent, result: FAILED"
- Telegram rejected the message (unlikely - our callbacks are under 64 bytes)

### If you don't see "DEBUG_FILTER_MENU" logs at all
- The `showSimpleReportFilterMenu` is not being called
- Check if `awaiting_simple_report_end_date` flag is being set correctly

### If you see "No conditions matched, returning null"
- Conversation notes are not being saved/loaded correctly
- Conversation state issue

## Callback Data Lengths (for reference)

All under 64-byte limit:
- `proj_report_filter_by_fn` = 25 bytes ✅
- `proj_report_filter_quick` = 24 bytes ✅  
- `proj_report_simple_fn_all` = 26 bytes ✅
- `category_Проекты_ОтчётПоПроекту` = 47 bytes ✅

## Quick Fix if Needed

If the issue persists, share the logs and I'll provide a targeted fix!
