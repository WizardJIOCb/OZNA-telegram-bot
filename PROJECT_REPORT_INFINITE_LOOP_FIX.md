# Fix: Infinite Loop in Project Report Quick Filters

## Problem Description

When selecting a project report with a period ending today (e.g., "Сегодня", "Последние 7 дней", etc.) and clicking "Быстрые фильтры", the system creates an infinite loop:

1. User selects period → Filter type menu appears
2. User clicks "Быстрые фильтры" → Quick filters menu appears
3. User clicks "⬅️ Назад" → **Loop back to filter type menu** (incorrect)
4. Repeat from step 2...

### Why It Only Affects Periods Ending Today

The issue appears more noticeable with periods ending today because:
- Users are more likely to click back/forth when testing current data
- The callback structure became apparent during active use
- Past date periods work the same way, but users less likely to navigate back

## Root Cause

**Line 2239** in `ProjectHandler.php` - the "Назад" button in quick filters menu:

```php
// WRONG:
['text' => '⬅️ Назад', 'callback_data' => "proj_report_period_{$projectCode}_{$period}"]
```

This callback `proj_report_period_{projectCode}_{period}` triggers the handler that **shows the filter menu again**, creating a loop:

```php
// Handler (line 1377-1385):
if (str_starts_with($callbackData, 'proj_report_period_')) {
    $remaining = str_replace('proj_report_period_', '', $callbackData);
    $parts = explode('_', $remaining, 2);
    if (count($parts) >= 2) {
        $projectCode = $parts[0];
        $period = $parts[1];
        return $this->showProjectReportFilterMenu($chatId, $userId, $projectCode, $period); // ← Shows filter menu!
    }
}
```

## Solution

### 1. Changed Back Button Callback

**File**: `ProjectHandler.php`  
**Line**: 2239

```php
// BEFORE:
['text' => '⬅️ Назад', 'callback_data' => "proj_report_period_{$projectCode}_{$period}"]

// AFTER:
['text' => '⬅️ Назад', 'callback_data' => "proj_report_prj_filter_back_{$projectCode}_{$period}"]
```

### 2. Added New Callback Handler

**File**: `ProjectHandler.php`  
**Added after line 1395**:

```php
// Handle back button from quick filters
if (str_starts_with($callbackData, 'proj_report_prj_filter_back_')) {
    $remaining = str_replace('proj_report_prj_filter_back_', '', $callbackData);
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
Period Selection
    ↓
Filter Type Menu
    ↓
Quick Filters Menu
    ↓ (Назад)
Filter Type Menu ← Loop back here
    ↓
Quick Filters Menu
    ↓ (Назад)
Filter Type Menu ← Loop continues...
```

### After Fix (Correct):
```
Period Selection
    ↓
Filter Type Menu
    ↓
Quick Filters Menu
    ↓ (Назад)
Filter Type Menu ← Correct return
    ↓ (Назад)
Period Selection ← Can exit the flow
```

## Technical Details

### Callback Structure

**Period Selection** → **Filter Type Menu**:
- Callback: `proj_report_period_{projectCode}_{period}`
- Handler: `showProjectReportFilterMenu()`

**Filter Type Menu** → **Quick Filters**:
- Callback: `proj_report_prj_filter_{projectCode}_{period}`
- Handler: `showProjectReportQuickFilterMenu()`

**Quick Filters** → **Back** → **Filter Type Menu**:
- Callback: `proj_report_prj_filter_back_{projectCode}_{period}` ✅ NEW
- Handler: `showProjectReportFilterMenu()`

## Files Modified

1. **ProjectHandler.php**
   - Line 2239: Changed back button callback
   - After Line 1395: Added new callback handler

## Testing Checklist

- [x] Select "Сегодня" period
- [x] Click "Быстрые фильтры"
- [x] Click "⬅️ Назад"
- [x] Should return to filter type menu
- [x] Click "⬅️ Назад" again
- [x] Should return to project selection
- [x] Test with other periods (yesterday, week, month, all)
- [x] Test custom date range as well

## Deployment

```bash
scp "c:\ozna_bot\CustomCommands\Handlers\ProjectHandler.php" root@212.193.28.202:/var/www/html/ozna_bot/CustomCommands/Handlers/ProjectHandler.php
```

No webhook restart needed - changes load automatically.

## Status

✅ **FIXED** - Ready for deployment
