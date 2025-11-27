# Mobile Brigade Submenu - Callback Length Fix

## Problem
The mobile brigade submenu buttons were not working because the callback_data exceeded Telegram's **64-byte limit**.

## Root Cause
Original callbacks were too long:
- ❌ `category_Табель_Электромонтажные_Досборка` = **71 bytes** (EXCEEDS LIMIT!)
- ❌ `category_Табель_Электромонтажные_РеализацияТР` = **79 bytes** (EXCEEDS LIMIT!)
- ❌ `category_Табель_Электромонтажные_УстранениеЗамечаний` = **93 bytes** (EXCEEDS LIMIT!)
- ❌ `category_Табель_Электромонтажные_РабПоГарантии` = **81 bytes** (EXCEEDS LIMIT!)

When callbacks exceed 64 bytes, Telegram **silently ignores them**, which is why clicking the buttons did nothing!

## Solution
Shortened all mobile brigade callbacks using compact format:

### Main Activity Buttons
- ✅ `cat_МобБриг_Электр` = **31 bytes** (Электромонтажные)
- ✅ `cat_МобБриг_Слес` = **27 bytes** (Слесарные)
- ✅ `cat_МобБриг_Свар` = **27 bytes** (Сварочные)
- ✅ `cat_МобБриг_Тепло` = **29 bytes** (Теплоизоляция)
- ✅ `cat_МобБриг_Маляр` = **29 bytes** (Малярные)

### Submenu Buttons (example for Electrical)
- ✅ `cat_МобБриг_Электр_Досб` = **40 bytes** (Досборка)
- ✅ `cat_МобБриг_Электр_РеалТР` = **44 bytes** (Реализация ТР)
- ✅ `cat_МобБриг_Электр_УстрЗам` = **46 bytes** (Устранение замечаний РК)
- ✅ `cat_МобБриг_Электр_РабГар` = **44 bytes** (Работы по гарантии)
- ✅ `cat_МобБриг_Назад` = **29 bytes** (Назад button)

### Confirmation Callbacks
- ✅ `cat_МобБриг_Электр_Досб_yes` = **44 bytes**
- ✅ `cat_МобБриг_Электр_РеалТР_yes` = **48 bytes**
- ✅ `cat_МобБриг_Электр_УстрЗам_yes` = **50 bytes**
- ✅ `cat_МобБриг_Электр_РабГар_yes` = **48 bytes**

**All callbacks now safely under the 64-byte limit!** ✓

## Changes Made

### 1. MessageService.php
Updated all 5 main activity keyboards and 5 submenu keyboards:
- `createMobileBrigadeKeyboard()` - Changed from `category_Табель_*` to `cat_МобБриг_*`
- `createElectricalWorksSubmenuKeyboard()` - Shortened all 4 sub-activities
- `createLocksmithWorksSubmenuKeyboard()` - Shortened all 4 sub-activities  
- `createWeldingWorksSubmenuKeyboard()` - Shortened all 4 sub-activities
- `createThermalInsulationWorksSubmenuKeyboard()` - Shortened all 4 sub-activities
- `createPaintingWorksSubmenuKeyboard()` - Shortened all 4 sub-activities

### 2. TabelHandler.php - extractCategoryFromCallback()
Added 50+ mapping entries to translate short callbacks to internal category names:

```php
// MOBILE BRIGADE MAPPINGS - CRITICAL FIX FOR 64-BYTE LIMIT
'cat_МобБриг_Электр' => 'Табель_Электромонтажные',
'cat_МобБриг_Электр_Досб' => 'Табель_Электромонтажные_Досборка',
'cat_МобБриг_Электр_Досб_yes' => 'Табель_Электромонтажные_Досборка_yes',
// ... and 47 more mappings for all 5 activity types
```

### 3. TabelHandler.php - handleOtherWorkTypes()
Updated the mobile brigade keyboard handler to show activities keyboard when user clicks "Назад":

```php
if ($category === 'Табель_МобильнаяБригада' || ...) {
    // Show mobile brigade keyboard (from submenu back button)
    $keyboard = $this->messageService->createMobileBrigadeKeyboard();
    return $this->messageService->sendMessage($chatId, $text, $keyboard);
}
```

## Flow Verification
1. User clicks "⚡ Электромонтажные работы" → sends `cat_МобБриг_Электр` (31 bytes ✓)
2. `extractCategoryFromCallback` maps to `Табель_Электромонтажные`
3. Handler shows submenu with 4 options
4. User clicks "🔧 Досборка" → sends `cat_МобБриг_Электр_Досб` (40 bytes ✓)
5. `extractCategoryFromCallback` maps to `Табель_Электромонтажные_Досборка`
6. Handler shows confirmation dialog
7. User confirms → sends `cat_МобБриг_Электр_Досб_yes` (44 bytes ✓)
8. `extractCategoryFromCallback` maps to `Табель_Электромонтажные_Досборка_yes`
9. `isActivityExecution` returns TRUE
10. Report created successfully! ✓

## Testing
All tests pass - all callbacks are under 64-byte limit:
```
✓✓✓ ALL CALLBACKS PASS! All under 64-byte limit! ✓✓✓
```

## Benefits
1. **Buttons now work** - Telegram accepts all callbacks
2. **Scalable** - Room to add more activity types if needed
3. **Consistent** - Follows same `cat_` pattern as other equipment types
4. **Maintainable** - Clear mapping in `extractCategoryFromCallback`

## Related Files
- `CustomCommands/Services/MessageService.php` (Lines 475-1460)
- `CustomCommands/Handlers/TabelHandler.php` (Lines 1015-1214, 4200-4270)

## Lesson Learned
**Always check callback_data length against Telegram's 64-byte limit!** Long Russian text in callbacks can quickly exceed this limit, causing silent failures.
