# Personnel Management - Database Mapping Fix

## Problem Identified

The Personnel module was not showing employee counts because of a mismatch between:
- **UI department names**: "Отдел ШМР", "Отдел ПНР"
- **Database field values**: "Шеф-монтаж", "Пусконаладка"

## Root Cause

In `ozna_data` table, the `functional_direction` field stores:
- ✅ `"Мобильная бригада"` (correct)
- ✅ `"Шеф-монтаж"` (NOT "Отдел ШМР")  
- ✅ `"Пусконаладка"` (NOT "Отдел ПНР")

The PersonnelHandler was incorrectly mapping:
- ❌ `'shmr' => 'Отдел ШМР'` (wrong - no users have this value)
- ❌ `'pnr' => 'Отдел ПНР'` (wrong - no users have this value)

## Fix Applied

### 1. Updated PersonnelHandler.php (Line 145)
```php
// BEFORE (wrong):
$fnMapping = [
    'mobile' => 'Мобильная бригада',
    'shmr' => 'Отдел ШМР',      // ❌ Wrong!
    'pnr' => 'Отдел ПНР'        // ❌ Wrong!
];

// AFTER (correct):
$fnMapping = [
    'mobile' => 'Мобильная бригада',
    'shmr' => 'Шеф-монтаж',     // ✅ Correct!
    'pnr' => 'Пусконаладка'     // ✅ Correct!
];
```

### 2. Updated mapFnToDatabase() method (Line 337)
Already correct - no changes needed.

### 3. Added Comprehensive Logging

Added logging to both:
- **PersonnelHandler::showStatusMenu()** - tracks callback routing and employee count retrieval
- **PersonnelService::getUsersByFunctionalDirection()** - tracks database queries and user filtering

## Testing Instructions

1. **Clear PHP error logs** (or note current timestamp)

2. **Test each department**:
   - Click "Персонал" button
   - Click "Мобильная бригада" → Should show employee counts (not 0)
   - Click "Отдел ШМР" → Should show employee counts (not 0)
   - Click "Отдел ПНР" → Should show employee counts (not 0)

3. **Test status lists**:
   - Click "На объекте" → Should show list of employees (if any)
   - Click "Свободная вахта" → Should show list of employees (if any)

4. **Check logs** for:
   ```
   PersonnelHandler::showStatusMenu - Mapped fn 'shmr' to database value: 'Шеф-монтаж'
   PersonnelService::getUsersByFunctionalDirection called with: 'Шеф-монтаж'
   PersonnelService::getUsersByFunctionalDirection - Found X users for FN: 'Шеф-монтаж'
   ```

## Expected Behavior

✅ **Мобильная бригада**: Shows actual count of employees with `functional_direction = "Мобильная бригада"`
✅ **Отдел ШМР**: Shows actual count of employees with `functional_direction = "Шеф-монтаж"`
✅ **Отдел ПНР**: Shows actual count of employees with `functional_direction = "Пусконаладка"`

## Database Reference

Values stored in `ozna_data.data` JSON:
```json
{
  "fio": "Иванов Иван Иванович",
  "functional_direction": "Мобильная бригада",  // or "Шеф-монтаж" or "Пусконаладка"
  "access_level": 1
}
```

Set via FunctionalDirectionHandler when user selects their department in profile.
