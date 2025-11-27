# Fix: Infinite Loop in Custom Date Project Report

## Problem Description

When using **"Выбранный период" (Custom Period)** in project reports:

1. User selects project → БКНС120
2. Clicks "📆 Выбранный период"
3. Enters start date → 01.10.2025
4. Enters end date → 17.10.2025
5. Filter menu appears
6. Clicks "⚡ Быстрые фильтры"
7. Quick filters menu appears
8. Clicks "⬅️ Назад"
9. **PROBLEM**: System requests date input again instead of returning to filter menu!

This creates an infinite loop where the user cannot proceed without re-entering dates every time.

## Root Cause

**Line 2434** in `ProjectHandler.php` - the "Назад" button in custom quick filters menu:

```php
// WRONG:
['text' => '⬅️ Назад', 'callback_data' => "proj_report_custom_{$projectCode}"]
```

This callback `proj_report_custom_{projectCode}` is handled on **line 1435**:

```php
if (str_starts_with($callbackData, 'proj_report_custom_')) {
    $projectCode = str_replace('proj_report_custom_', '', $callbackData);
    return $this->showProjectReportStartDateMenu($chatId, $userId, $conversation, $projectCode);
    // ↑ This RESTARTS date input flow!
}
```

### Navigation Flow (Before Fix):
```
Custom Date Input
    ↓
Enter Start Date: 01.10.2025
    ↓
Enter End Date: 17.10.2025
    ↓
Filter Type Menu (⚡ Быстрые фильтры / 📊 Без фильтра)
    ↓
Quick Filters Menu (6 filters)
    ↓ (Назад button)
Custom Date Input ← WRONG! Restarts entire flow
    ↓
Enter Start Date: 01.10.2025 (again!)
    ↓
... LOOP CONTINUES
```

## Solution

### 1. Changed Back Button Callback

**File**: `ProjectHandler.php`  
**Line**: 2434

```php
// BEFORE:
['text' => '⬅️ Назад', 'callback_data' => "proj_report_custom_{$projectCode}"]

// AFTER:
['text' => '⬅️ Назад', 'callback_data' => "proj_report_custom_back_to_filter_{$projectCode}"]
```

### 2. Added New Callback Handler

**File**: `ProjectHandler.php`  
**Added after line 1442**:

```php
// Handle back to custom filter menu from quick filters
if (str_starts_with($callbackData, 'proj_report_custom_back_to_filter_')) {
    $projectCode = str_replace('proj_report_custom_back_to_filter_', '', $callbackData);
    $startDate = $conversation->notes['project_report_start_date'] ?? null;
    $endDate = $conversation->notes['project_report_end_date'] ?? null;
    
    if ($startDate && $endDate) {
        return $this->showProjectReportCustomFilterMenu($chatId, $userId, $conversation, $projectCode, $startDate, $endDate);
    } else {
        // If dates are lost, restart the flow
        return $this->showProjectReportStartDateMenu($chatId, $userId, $conversation, $projectCode);
    }
}
```

## Correct Navigation Flow (After Fix)

```
Custom Date Input
    ↓
Enter Start Date: 01.10.2025
    ↓
Enter End Date: 17.10.2025
    ↓
Filter Type Menu (⚡ Быстрые фильтры / 📊 Без фильтра)
    ↓
Quick Filters Menu (6 filters)
    ↓ (Назад button)
Filter Type Menu ← CORRECT! Returns to filter selection
    ↓ (Назад button)
Date Selection Menu ← Can exit properly
```

## Technical Details

### Callback Structure for Custom Dates

**Date Input Start**:
- Callback: `proj_report_custom_{projectCode}`
- Handler: `showProjectReportStartDateMenu()`
- Action: Prompts for start date

**Filter Type Menu**:
- From: After entering both dates
- Handler: `showProjectReportCustomFilterMenu()`
- Shows: ⚡ Быстрые фильтры / 📊 Без фильтра

**Quick Filters Menu**:
- Callback: `proj_report_custom_filter_{projectCode}`
- Handler: `showProjectReportCustomQuickFilterMenu()`
- Shows: 6 quick filters

**Back from Quick Filters**:
- Callback: `proj_report_custom_back_to_filter_{projectCode}` ✅ NEW
- Handler: Returns to Filter Type Menu (not date input!)
- Preserves: Both dates in conversation notes

## Conversation State Management

The fix uses conversation notes to preserve user-entered dates:

```php
$startDate = $conversation->notes['project_report_start_date'];
$endDate = $conversation->notes['project_report_end_date'];
```

This ensures that when the user clicks "Назад", the system:
1. Retrieves previously entered dates
2. Shows filter menu with those dates
3. Does NOT request date input again

### Fallback Mechanism

If conversation notes are lost (shouldn't happen, but safety check):
```php
if ($startDate && $endDate) {
    return $this->showProjectReportCustomFilterMenu(...);
} else {
    // Safety: restart date input if dates lost
    return $this->showProjectReportStartDateMenu(...);
}
```

## Files Modified

**ProjectHandler.php**:
1. **Line 2434**: Changed back button callback from `proj_report_custom_` to `proj_report_custom_back_to_filter_`
2. **After Line 1442**: Added new callback handler `proj_report_custom_back_to_filter_`

## Testing Steps

### Test Custom Date Flow:
1. ✅ Select project (e.g., БКНС120)
2. ✅ Click "📆 Выбранный период"
3. ✅ Enter start date: 01.10.2025
4. ✅ Enter end date: 17.10.2025
5. ✅ Filter Type Menu should appear
6. ✅ Click "⚡ Быстрые фильтры"
7. ✅ Quick Filters Menu should appear
8. ✅ Click "⬅️ Назад"
9. ✅ Should return to Filter Type Menu (NOT date input!)
10. ✅ Dates should be preserved (01.10.2025 - 17.10.2025)
11. ✅ Click "⬅️ Назад" again
12. ✅ Should return to Date Selection Menu

### Test Predefined Periods (should still work):
- ✅ "Сегодня" → should work correctly
- ✅ "Вчера" → should work correctly
- ✅ "Последние 7 дней" → should work correctly
- ✅ "Последние 30 дней" → should work correctly
- ✅ "Весь период" → should work correctly

## Related Fixes

This fix complements the earlier fix for predefined periods (`proj_report_back_to_filter_`) and ensures consistent navigation behavior across:
- ✅ Predefined periods (today, yesterday, week, month, all)
- ✅ Custom date ranges (user-entered dates)

## Deployment

```bash
scp "c:\ozna_bot\CustomCommands\Handlers\ProjectHandler.php" root@212.193.28.202:/var/www/html/ozna_bot/CustomCommands/Handlers/ProjectHandler.php
```

No webhook restart needed - PHP changes load automatically.

## Status

✅ **FIXED** - Ready for deployment and testing

## Summary

The fix ensures that:
- ✅ "Назад" button in custom quick filters returns to filter menu
- ✅ User-entered dates are preserved in conversation
- ✅ No infinite loop when using custom date ranges
- ✅ Consistent navigation behavior across all report types
- ✅ Fallback mechanism if conversation data is lost
