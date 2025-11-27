# Critical Typo Fix: Project Report Filter Menu

## Problem
After selecting a date range in the "По-проектный отчёт" (Project Report), the filter selection menu was not appearing.

## Root Cause
**Line 2152** in `ProjectHandler.php` had a typo in the keyboard array:

```php
// WRONG (typo):
['техт' => '⚡ Быстрые фильтры', 'callback_data' => "proj_report_prj_filter_{$projectCode}_{$period}"]

// CORRECT:
['text' => '⚡ Быстрые фильтры', 'callback_data' => "proj_report_prj_filter_{$projectCode}_{$period}"]
```

The word `техт` is meaningless in Russian (and not a valid keyboard parameter). It should be `text`.

## Impact
Telegram's Bot API silently rejected the entire keyboard message because of the invalid parameter name, causing:
- No filter menu displayed to users
- Flow stopped after date selection
- Users couldn't generate filtered reports

## Fix Applied
Changed `техт` → `text` in the `showProjectReportFilterMenu()` method.

## Files Modified
- `c:\ozna_bot\CustomCommands\Handlers\ProjectHandler.php` (Line 2146)

## Testing Steps
1. Upload fixed file to server
2. Go to: Проекты → Отчёт по проекту → По-проектный отчёт
3. Select any project
4. Select any date range (Сегодня, Вчера, etc.)
5. **Expected**: Filter menu appears with:
   - ⚡ Быстрые фильтры
   - 📊 Без фильтра (все данные)
   - ⬅️ Назад

## Deployment
```bash
scp "c:\ozna_bot\CustomCommands\Handlers\ProjectHandler.php" root@212.193.28.202:/var/www/html/ozna_bot/CustomCommands/Handlers/ProjectHandler.php
```

No webhook restart needed - file changes are loaded automatically.

## Status
✅ **FIXED** - Ready for deployment and testing
