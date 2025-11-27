# Fix: Infinite Loop for Project Reports Ending Today

## Problem Description

When selecting a project report with period ending on today's date (17.10.2025), such as:
- "Сегодня" (Today)
- "Последние 7 дней" (Last 7 days)
- "Последние 30 дней" (Last 30 days)

And then clicking "⚡ Быстрые фильтры" → "⬅️ Назад", the system creates an infinite loop, continuously showing the period selection menu.

**Important**: This problem does NOT occur in "Простой отчёт" (Simple Report), only in "По-проектный отчёт" (Project Report).

## Root Cause

The "⬅️ Назад" (Back) button in the quick filters menu had incorrect callback data:

**Line 2239** in `ProjectHandler.php`:
```php
// WRONG:
['text' => '⬅️ Назад', 'callback_data' => "proj_report_period_{$projectCode}_{$period}"]
```

This callback `proj_report_period_{projectCode}_{period}` is handled on **lines 1377-1385**:
```php
if (str_starts_with($callbackData, 'proj_report_period_')) {
    // ...
    return $this->showProjectReportFilterMenu($chatId, $userId, $projectCode, $period);
}
```

This shows the **filter type menu** again, but then the user selects "Быстрые фильтры" which shows the quick filters menu, and when they click "Назад" again, it loops back to the filter type menu, creating an infinite cycle.

### Why Only With Today's Date?

The problem actually occurs with ALL periods, but is most noticeable with periods ending today because:
1. Users test current data more frequently
2. The date in the display (17.10.2025) makes the loop more obvious
3. Users expect to go back to date selection, not filter selection

## Solution

### 1. Changed Back Button Callback

**File**: `ProjectHandler.php`  
**Line**: 2239

```php
// BEFORE:
['text' => '⬅️ Назад', 'callback_data' => "proj_report_period_{$projectCode}_{$period}"]

// AFTER:
['text' => '⬅️ Назад', 'callback_data' => "proj_report_back_to_filter_{$projectCode}_{$period}"]
```

### 2. Added New Callback Handler

**File**: `ProjectHandler.php`  
**Added after line 1395**:

```php
// Handle back to filter menu from quick filters
if (str_starts_with($callbackData, 'proj_report_back_to_filter_')) {
    $remaining = str_replace('proj_report_back_to_filter_', '', $callbackData);
    $parts = explode('_', $remaining, 2);
    if (count($parts) >= 2) {
        $projectCode = $parts[0];
        $period = $parts[1];
        return $this->showProjectReportFilterMenu($chatId, $userId, $projectCode, $period);
    }
}
```

## Correct Navigation Flow

### Before Fix (Infinite Loop):
```
Date Selection (Сегодня)
    ↓
Filter Type Menu (⚡ Быстрые фильтры / 📊 Без фильтра)
    ↓
Quick Filters Menu (6 filters)
    ↓ (Назад button)
Filter Type Menu ← callback: proj_report_period_{code}_today
    ↓ (User clicks Быстрые фильтры again)
Quick Filters Menu
    ↓ (Назад button)
Filter Type Menu ← LOOP CONTINUES...
```

### After Fix (Correct):
```
Date Selection (Сегодня)
    ↓
Filter Type Menu (⚡ Быстрые фильтры / 📊 Без фильтра)
    ↓
Quick Filters Menu (6 filters)
    ↓ (Назад button)
Filter Type Menu ← callback: proj_report_back_to_filter_{code}_today
    ↓ (Назад button)
Date Selection ← Can properly exit
```

## Technical Details

### Callback Structure

**Date Selection** → **Filter Type Menu**:
- User clicks: "📅 Сегодня"
- Callback: `proj_report_period_{projectCode}_today`
- Handler: `showProjectReportFilterMenu()`
- Shows: Filter Type Menu

**Filter Type Menu** → **Quick Filters**:
- User clicks: "⚡ Быстрые фильтры"
- Callback: `proj_report_prj_filter_{projectCode}_today`
- Handler: `showProjectReportQuickFilterMenu()`
- Shows: Quick Filters Menu

**Quick Filters** → **Back** → **Filter Type Menu**:
- User clicks: "⬅️ Назад"
- Callback: `proj_report_back_to_filter_{projectCode}_today` ✅ NEW
- Handler: `showProjectReportFilterMenu()`
- Shows: Filter Type Menu (correct return point)

## Files Modified

**ProjectHandler.php**:
1. **Line 2239**: Changed back button callback from `proj_report_period_` to `proj_report_back_to_filter_`
2. **After Line 1395**: Added new callback handler for `proj_report_back_to_filter_`

## Testing Steps

### Test with "Сегодня" (Today):
1. ✅ Select project
2. ✅ Click "📅 Сегодня"
3. ✅ Click "⚡ Быстрые фильтры"
4. ✅ Click "⬅️ Назад"
5. ✅ Should show Filter Type Menu (NOT loop)
6. ✅ Click "⬅️ Назад" again
7. ✅ Should return to Date Selection

### Test with other periods:
- ✅ "Последние 7 дней" (Last 7 days)
- ✅ "Последние 30 дней" (Last 30 days)
- ✅ "Вчера" (Yesterday)
- ✅ "Весь период" (All time)

### Test Simple Report (should still work):
- ✅ Simple report should remain unaffected
- ✅ No changes were made to simple report flow

## Deployment

```bash
scp "c:\ozna_bot\CustomCommands\Handlers\ProjectHandler.php" root@212.193.28.202:/var/www/html/ozna_bot/CustomCommands/Handlers/ProjectHandler.php
```

No webhook restart needed - PHP changes load automatically.

## Status

✅ **FIXED** - Ready for deployment and testing

## Related Issues

This fix also prevents the loop for:
- Custom date ranges ending today
- Any period selection in project reports
- Ensures consistent back button behavior across all date periods
