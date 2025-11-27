# Idle Stage Information Fix - Complete Report

## Problem Description

**User Report:**
When testing idle activities in the Commissioning Department (Отдел ПНР), the process/stage information was missing from the final report:

**Current (Wrong):**
```
😨 ⚠️ 11.10.2025, Проект ХХХ: ИНЦИДЕНТ - Добавлена запись в табель: ⚠️ Простой (Транспорт (Отсутствие свободных мест))
```

**Expected (Correct):**
```
😨 ⚠️ 11.10.2025, Проект ХХХ: ИНЦИДЕНТ - Добавлена запись в табель: ⚠️ Простой (Пусконаладка) (Транспорт (Отсутствие свободных мест))
```

The stage information "(Пусконаладка)" was missing from the report.

## Root Cause Analysis

### Issue Location
File: `CustomCommands/Services/TabelEntry.php`
Method: `createTabelMessage()` at lines 908-918

### What Was Wrong

The `getConfirmationMessage()` method (used for confirmation prompts) **correctly** included stage information:
```php
// In getConfirmationMessage() - CORRECT
if ($activityType === 'МобБриг_Простой') {
    $stageInfo = 'Монтажные работы';
} elseif (str_contains($activityType, 'ШМ_')) {
    $stageInfo = 'Шеф-монтаж';
} else {
    $stageInfo = 'Пусконаладочные работы';
}
```

However, the `createTabelMessage()` method (used for final report generation) **did NOT** include this stage information:
```php
// In createTabelMessage() - WRONG (before fix)
$idleActivities = ['Простой', 'ШМ_Простой'];

if (in_array($activityType, $idleActivities)) {
    return sprintf(
        "<b>%s</b>\r\n😨 ⚠️ %s, Проект <b>%s</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⚠️ %s</b>",
        $fio,
        $date,
        $projectCode,
        $activityText  // ← This only contained "Простой (reason)" without stage
    );
}
```

## Solution Implemented

### Changes Made
File: `CustomCommands/Services/TabelEntry.php`
Lines: 905-945

### Fix Details

Added stage detection logic to `createTabelMessage()` method to match the behavior of `getConfirmationMessage()`:

```php
// Idle activities ("Простой") - use new commissioning format with stage information
$idleActivities = ['Простой', 'ШМ_Простой', 'МобБриг_Простой'];

if (in_array($activityType, $idleActivities)) {
    // Determine stage information based on activity type
    if ($activityType === 'МобБриг_Простой') {
        $stageInfo = 'Монтажные работы';
        error_log("✅ TABELENTRY: createTabelMessage - Set stageInfo = 'Монтажные работы' for МобБриг_Простой");
    } elseif ($activityType === 'ШМ_Простой') {
        $stageInfo = 'Шеф-монтаж';
        error_log("✅ TABELENTRY: createTabelMessage - Set stageInfo = 'Шеф-монтаж' for ШМ_Простой");
    } else {
        $stageInfo = 'Пусконаладка';
        error_log("✅ TABELENTRY: createTabelMessage - Set stageInfo = 'Пусконаладка' for Простой");
    }
    
    // Build activity text with stage: "Простой (Пусконаладка) (причина)"
    // If activityText already has the reason appended (from line 445-447), prepend the stage
    if (strpos($activityText, 'Простой') === 0) {
        // Replace "Простой (reason)" with "Простой (Stage) (reason)"
        if (preg_match('/^Простой \((.+)\)$/', $activityText, $matches)) {
            $reason = $matches[1];
            $activityText = "Простой ({$stageInfo}) ({$reason})";
            error_log("✅ TABELENTRY: createTabelMessage - Enhanced idle text with stage: '{$activityText}'");
        } else {
            // No reason found, just add stage
            $activityText = "Простой ({$stageInfo})";
            error_log("✅ TABELENTRY: createTabelMessage - Added stage to idle text: '{$activityText}'");
        }
    }
    
    return sprintf(
        "<b>%s</b>\r\n😨 ⚠️ %s, Проект <b>%s</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⚠️ %s</b>",
        $fio,
        $date,
        $projectCode,
        $activityText  // ← Now contains "Простой (Stage) (reason)"
    );
}
```

### Key Improvements

1. **Stage Detection**: Added logic to determine the correct stage based on `activityType`:
   - `МобБриг_Простой` → "Монтажные работы" (Installation works)
   - `ШМ_Простой` → "Шеф-монтаж" (Supervision works)
   - `Простой` → "Пусконаладка" (Commissioning works)

2. **Text Enhancement**: Used regex to parse the existing `$activityText` and insert the stage information:
   - Input: "Простой (Транспорт (Отсутствие свободных мест))"
   - Output: "Простой (Пусконаладка) (Транспорт (Отсутствие свободных мест))"

3. **Logging**: Added comprehensive error logging for debugging and verification.

4. **Added МобБриг_Простой**: Included Mobile Brigade idle in the activities array to ensure consistent handling.

## Testing Results

All tests passed successfully:

### Test 1: Commissioning Department (ПНР)
- **Input**: `activityType = 'Простой'`, `reason = 'Транспорт (Отсутствие свободных мест)'`
- **Output**: `Простой (Пусконаладка) (Транспорт (Отсутствие свободных мест))`
- **Status**: ✅ PASSED

### Test 2: Supervision Department (Шеф-монтаж)
- **Input**: `activityType = 'ШМ_Простой'`, `reason = 'Погодные условия (Буран/снегопад)'`
- **Output**: `Простой (Шеф-монтаж) (Погодные условия (Буран/снегопад))`
- **Status**: ✅ PASSED

### Test 3: Mobile Brigade (Монтажные работы)
- **Input**: `activityType = 'МобБриг_Простой'`, `reason = 'Готовность Заказчика (Отсутствие энергоресурсов)'`
- **Output**: `Простой (Монтажные работы) (Готовность Заказчика (Отсутствие энергоресурсов))`
- **Status**: ✅ PASSED

### Test 4: User's Specific Example
- **Current (Wrong)**: `😨 ⚠️ 11.10.2025, Проект ХХХ: ИНЦИДЕНТ - Добавлена запись в табель: ⚠️ Простой (Транспорт (Отсутствие свободных мест))`
- **Expected (Correct)**: `😨 ⚠️ 11.10.2025, Проект ХХХ: ИНЦИДЕНТ - Добавлена запись в табель: ⚠️ Простой (Пусконаладка) (Транспорт (Отсутствие свободных мест))`
- **Actual After Fix**: `😨 ⚠️ 11.10.2025, Проект ХХХ: ИНЦИДЕНТ - Добавлена запись в табель: ⚠️ Простой (Пусконаладка) (Транспорт (Отсутствие свободных мест))`
- **Status**: ✅ PASSED

## Impact Analysis

### Affected Departments
1. **Отдел ПНР (Commissioning Department)**: Now shows "Пусконаладка" in idle reports
2. **Шеф-монтаж (Supervision Department)**: Now shows "Шеф-монтаж" in idle reports
3. **Мобильная бригада (Mobile Brigade)**: Now shows "Монтажные работы" in idle reports

### What Users Will See
Before this fix, all idle reports were missing the stage/process information. After this fix:

- **Commissioning workers** will see: `Простой (Пусконаладка) (reason)`
- **Supervision workers** will see: `Простой (Шеф-монтаж) (reason)`
- **Mobile brigade workers** will see: `Простой (Монтажные работы) (reason)`

This makes it immediately clear which work stage/process the idle time belongs to.

## Files Modified

1. **`CustomCommands/Services/TabelEntry.php`**
   - Method: `createTabelMessage()`
   - Lines: 905-945
   - Changes: Added stage detection and text enhancement for idle activities

## Related Documentation

- **Memory**: Successfully fixed Mobile Brigade idle/downtime system (memory ID: 84e3abf7-f548-456c-a950-b3eb538a1ab3)
- **Similar Logic**: `getConfirmationMessage()` method at lines 1750-1777 (reference implementation)

## Verification Steps

To verify the fix is working in production:

1. Navigate to **Отдел ПНР** (Commissioning Department)
2. Select a date and project
3. Click **⏸️ Простой** (Idle)
4. Select any idle reason (e.g., "Транспорт" → "Отсутствие свободных мест")
5. Confirm the entry
6. Check the report message - it should now include **(Пусконаладка)** between "Простой" and the reason

**Expected format:**
```
😨 ⚠️ 11.10.2025, Проект ХХХ: ИНЦИДЕНТ - Добавлена запись в табель: ⚠️ Простой (Пусконаладка) (Транспорт (Отсутствие свободных мест))
```

## Conclusion

The fix successfully resolves the missing stage information in idle reports. All three department types (Commissioning, Supervision, Mobile Brigade) now correctly display their stage information in the final report message, making reports more informative and consistent with the confirmation messages shown earlier in the workflow.

**Status**: ✅ **COMPLETE AND TESTED**
