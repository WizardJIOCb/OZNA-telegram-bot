# ШМР Equipment Fix - Complete Solution

## Problem Description

When clicking ШМР equipment buttons (Электрооборудование, Метрология, etc.), the system was showing an immediate confirmation dialog:

```
ШМР_Метрология, отправить запись в табель в проект ХХХ?
```

Instead of showing a submenu with "Прочее" and "Назад" buttons.

---

## Root Cause Analysis

### Investigation Steps

1. **Traced callback flow**:
   - User clicks "📊 Метрология" button
   - Callback: `cat_ШМР_Метрол`
   - `extractCategoryFromCallback()` converts to: `Табель_ШМР_Метрология`
   - Should route to submenu handler at line ~3512 in `TabelHandler.php`

2. **Found the problem**:
   - The 7 ШМР equipment menu activities were **incorrectly included** in the `$activityTypes` array:
     ```php
     // Lines 129-135 in TabelHandler.php (WRONG!)
     'ШМР_ТехнологическоеОборудование',
     'ШМР_Электрооборудование',
     'ШМР_ОтоплениеИВентиляция',
     'ШМР_ПожарнаяСигнализация',
     'ШМР_ОхраннаяСигнализация',
     'ШМР_Автоматизация',
     'ШМР_Метрология',
     ```

3. **Why this caused the issue**:
   - These activities are **menu items** that should show submenus, NOT terminal activities
   - The fallback check at line ~4795 catches anything in `$activityTypes` array:
     ```php
     if (in_array($strippedCategory, $this->activityTypes) && !$shouldSkip) {
         // Show confirmation dialog immediately
     }
     ```
   - This prevented the flow from reaching the submenu handlers earlier in the method

---

## The Complete Fix

### 1. Remove Equipment Menu Activities from `activityTypes` Array

**File**: `CustomCommands/Handlers/TabelHandler.php`  
**Lines**: 129-135

**REMOVED**:
```php
// NEW 7 ШМР intra-block installation sub-activities (unique for ШМР, not duplicating ПНР)
'ШМР_ТехнологическоеОборудование',
'ШМР_Электрооборудование',
'ШМР_ОтоплениеИВентиляция',
'ШМР_ПожарнаяСигнализация',
'ШМР_ОхраннаяСигнализация',
'ШМР_Автоматизация',
'ШМР_Метрология',
```

**KEPT** (these need to stay because they trigger custom input prompts):
```php
// NOTE: ШМР equipment menu activities (ШМР_Электрооборудование, etc.) are NOT in activityTypes
// because they show submenus, not direct confirmations. They are handled by specific menu handlers.
// ONLY the "Прочее" variants are in activityTypes because they trigger custom input prompts.
// ШМР equipment "Прочее" activity types - these trigger custom input, then confirmation
'ШМР_Прочее_Электрооборудование',
'ШМР_Прочее_ОтоплениеИВентиляция',
'ШМР_Прочее_ПожарнаяСигнализация',
'ШМР_Прочее_ОхраннаяСигнализация',
'ШМР_Прочее_Автоматизация',
'ШМР_Прочее_Метрология'
```

### 2. Simplify Exclusion Logic

**File**: `CustomCommands/Handlers/TabelHandler.php`  
**Lines**: ~4775-4782

**BEFORE** (unnecessarily complex):
```php
$shouldSkip = str_contains($strippedCategory, 'Прочее') || 
              in_array($strippedCategory, [
                  'ШМР_ТехнологическоеОборудование',
                  'ШМР_Электрооборудование',
                  'ШМР_ОтоплениеИВентиляция',
                  'ШМР_ПожарнаяСигнализация',
                  'ШМР_ОхраннаяСигнализация',
                  'ШМР_Автоматизация',
                  'ШМР_Метрология'
              ]);
```

**AFTER** (simplified):
```php
// CRITICAL: Check for activity type AFTER specific handlers to prevent premature confirmation
// SKIP activities that should show submenus or require special handling:
// 1. "Прочее" activities - they should be handled by specific custom input handlers above
// NOTE: ШМР equipment menu activities are no longer in activityTypes array (removed to prevent this issue)
$shouldSkip = str_contains($strippedCategory, 'Прочее');
```

**Why this works**: Since equipment menu activities are no longer in `activityTypes`, we don't need the `in_array` check anymore.

---

## How It Works Now

### Flow for Equipment Buttons (e.g., "📊 Метрология")

1. **User clicks "📊 Метрология"**
   - Callback data: `cat_ШМР_Метрол`

2. **Callback processing**
   - `extractCategoryFromCallback()` converts to: `Табель_ШМР_Метрология`

3. **Handler routing**
   - ✅ NOT in `$activityTypes` array (we removed it!)
   - ✅ Routes to submenu handler at line ~3512:
     ```php
     if ($category === 'Табель_ШМР_Метрология') {
         $keyboard = $this->messageService->createSMRMetrologyKeyboard();
         return $this->messageService->sendMessage($chatId, ...);
     }
     ```

4. **Keyboard display**
   - Shows 2-button submenu:
     ```
     📊 Метрология
     ├── 📝 Прочее
     └── ⬅️ Назад
     ```

### Flow for "Прочее" Buttons

1. **User clicks "📝 Прочее" (from equipment submenu)**
   - Callback data: `cat_ШМР_Метрол_Проч`
   - Converts to: `Табель_ШМР_Прочее_Метрология`

2. **Custom input prompt**
   - Routes to handler at line ~3587:
     ```php
     if ($category === 'Табель_ШМР_Прочее_Метрология') {
         $conversation->notes['awaiting_custom_prochee_input'] = true;
         return $this->messageService->sendMessage($chatId, "Опишите выполняемые работы:");
     }
     ```

3. **User types custom text**
   - Text stored in conversation notes

4. **Confirmation dialog**
   - Shows confirmation with custom text:
     ```
     ШМР_Прочее_Метрология: [custom text], отправить запись в табель в проект XXX?
     ```

---

## Files Modified

### 1. `CustomCommands/Handlers/TabelHandler.php`

**Change 1**: Lines 129-135  
- **Action**: Removed 7 equipment menu activities from `$activityTypes` array
- **Kept**: 6 "Прочее" variants (they need custom input prompts)

**Change 2**: Lines ~4775-4782  
- **Action**: Simplified `$shouldSkip` logic
- **Removed**: Unnecessary `in_array` check for equipment activities

### 2. `CustomCommands/Services/MessageService.php`

**Lines**: 1658-1760  
**Already correct**: All 6 equipment keyboard methods have 2-button structure:
- `createSMRElectricalEquipmentKeyboard()` - ✅ 2 buttons
- `createSMRHeatingVentilationKeyboard()` - ✅ 2 buttons
- `createSMRFireAlarmKeyboard()` - ✅ 2 buttons
- `createSMRSecurityAlarmKeyboard()` - ✅ 2 buttons
- `createSMRAutomationKeyboard()` - ✅ 2 buttons
- `createSMRMetrologyKeyboard()` - ✅ 2 buttons

---

## Expected Behavior

### Equipment Buttons (6 types)
- ⚡ Электрооборудование → Shows submenu (Прочее + Назад)
- 🌡️ Отопление и вентиляция → Shows submenu (Прочее + Назад)
- 🔥 Пожарная сигнализация → Shows submenu (Прочее + Назад)
- 🛡️ Охранная сигнализация → Shows submenu (Прочее + Назад)
- 🤖 Автоматизация → Shows submenu (Прочее + Назад)
- 📊 Метрология → Shows submenu (Прочее + Назад)

### "Прочее" Buttons
- Click "Прочее" → Prompts for custom text input
- Enter text → Shows confirmation dialog with custom text
- Click "Да" → Creates timesheet entry

### "Назад" Buttons
- Click "Назад" → Returns to "Внутриблочный монтаж" menu

---

## Testing

Run the verification script:
```bash
php test_smr_equipment_final_fix.php
```

**Expected output**: All 5 tests should pass ✅

### Test Results
- ✅ TEST 1: All 7 equipment menu activities removed from activityTypes
- ✅ TEST 2: All 'Прочее' variants present in activityTypes
- ✅ TEST 3: Exclusion logic properly simplified
- ✅ TEST 4: All submenu handlers present
- ✅ TEST 5: All keyboards have correct 2-button structure

---

## Why This Fix Is Correct

### Activity Type Classification

**Menu Activities** (should NOT be in `activityTypes`):
- Show submenus when clicked
- Example: `ШМР_Электрооборудование` → shows submenu
- Handled by specific menu handlers (lines 3474-3517)

**Terminal Activities** (should be in `activityTypes`):
- Can be directly confirmed and create timesheet entries
- Example: `ШМР_ВходнойКонтроль_РабПоГр` → direct confirmation
- Handled by fallback check (line 4790)

**"Прочее" Activities** (should be in `activityTypes`):
- Require custom text input before confirmation
- Example: `ШМР_Прочее_Метрология` → prompt for text → confirmation
- Handled by specific "Прочее" handlers (lines 3552-3587)

### The Key Insight

The 7 ШМР equipment activities (`ШМР_Электрооборудование`, etc.) are **menu activities**, NOT terminal activities. By removing them from `activityTypes`, we ensure they route to their submenu handlers instead of being caught by the fallback confirmation check.

---

## Architecture Notes

### Callback Flow

```
User Action
  ↓
Callback Data (cat_ШМР_Метрол)
  ↓
extractCategoryFromCallback()
  ↓
Full Category (Табель_ШМР_Метрология)
  ↓
Specific Handler Check (line 3512) ← Should go HERE
  ↓
[SKIPPED: Fallback Check] ← Should NOT go here (removed from activityTypes)
  ↓
Show Submenu
```

### Before the Fix

```
Callback → Category → IN activityTypes array → Fallback check → IMMEDIATE CONFIRMATION ❌
```

### After the Fix

```
Callback → Category → NOT in activityTypes array → Specific handler → SUBMENU ✅
```

---

## Summary

**Problem**: Equipment buttons showed immediate confirmation dialogs  
**Root Cause**: Equipment menu activities incorrectly in `activityTypes` array  
**Solution**: Remove menu activities from array, keep only "Прочее" variants  
**Result**: Equipment buttons now properly show submenus  

**Status**: ✅ READY FOR PRODUCTION TESTING
