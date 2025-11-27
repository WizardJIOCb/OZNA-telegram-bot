# Complete Back Button Navigation Fix

## Problem Description
Multiple back buttons in the application were not working correctly:
1. The back button in the "Электрооборудование" (Electrical Equipment) menu was returning users to the "Отдел ПНР" (Commissioning Department) menu instead of the "Пусконаладочные работы" (Commissioning Works) menu.
2. The back buttons in "Проектная деятельность" (Project Activities) and "Устранение замечаний РК" (RK Remarks Elimination) were not working at all, showing "Неизвестная команда табеля" (Unknown tabel command) error.

## Root Cause
1. Electrical equipment back button was using an incorrect callback that would navigate to the main commissioning department menu.
2. Project activities and RK remarks elimination back buttons were using `Табель_ШМ_Back` callback which was not properly handled in the TabelHandler.
3. Warranty equipment back button was also using `Табель_ШМ_Back` in supervision context.

## Solution Implemented
1. **Electrical Equipment Back Button Fix**:
   - Changed the back button callback data in all equipment keyboards from their specific category callbacks to `cat_ТабПНРаботы`
   - Updated the TabelHandler to properly recognize and handle the `cat_ТабПНРаботы` callback
   - Ensured consistency across all equipment types (electrical, automation, fire alarm, etc.)

2. **Project Activities and RK Remarks Elimination Back Button Fix**:
   - Changed the back button callback data from `Табель_ШМ_Back` to `category_Табель_Пусконаладка_Back`
   - Added proper handling for `Табель_Пусконаладка_Back` callback in TabelHandler

3. **Warranty Equipment Back Button Fix**:
   - Changed the back button callback data from `Табель_ШМ_Back` to `category_Табель_Пусконаладка_Back` for consistency

4. **Supervision (ШМР) "Вернуться в табель" Button Fix**:
   - Updated the TabelHandler to properly handle the `Табель_ШМ_Back` callback
   - This button now correctly navigates users back to the main tabel menu (`category_Табель`)
   - Added proper mapping for `Табель_ШМ_Back` in the callback extraction method
   - Fixed the callback extraction logic to recognize direct callback names that don't start with `category_` or `cat_` prefixes

5. **All Back Button Callbacks Fix**:
   - Added proper mapping for all back button callbacks in the callback extraction method
   - This ensures that all back buttons (`Табель_Пусконаладка_Back`, `Табель_Автоматизация_Back`, etc.) are properly recognized and handled
   - Fixed the callback extraction logic to handle direct callback names that don't start with the standard prefixes

## Files Modified

### 1. CustomCommands\Services\MessageService.php
- Updated `createElectricalEquipmentKeyboard()` method:
  - Changed back button callback from `category_Табель_Пусконаладка_Back` to `cat_ТабПНРаботы`
- Updated all other equipment keyboards:
  - `createAutomationEquipmentKeyboard()`
  - `createFireAlarmEquipmentKeyboard()`
  - `createSecurityAlarmEquipmentKeyboard()`
  - `createHeatingVentilationEquipmentKeyboard()`
  - `createTechnologicalEquipmentKeyboard()`
  - `createMetrologyKeyboard()`
- Updated all additional works keyboards:
  - `createAdditionalWorksElectricalKeyboard()`
  - `createAdditionalWorksAutomationKeyboard()`
  - `createAdditionalWorksFireAlarmKeyboard()`
  - `createAdditionalWorksSecurityAlarmKeyboard()`
  - `createAdditionalWorksHeatingVentilationKeyboard()`
  - `createAdditionalWorksTechnologicalKeyboard()`
  - `createAdditionalWorksMetrologyKeyboard()`
- Updated `createEliminationRemarksRKKeyboard()` method:
  - Changed back button callback from `Табель_ШМ_Back` to `category_Табель_Пусконаладка_Back`
- Updated `createProjectActivityKeyboard()` method:
  - Changed back button callback from `Табель_ШМ_Back` to `category_Табель_Пусконаладка_Back`
- Updated `createWarrantyEquipmentKeyboard()` method:
  - Changed back button callback from `Табель_ШМ_Back` to `category_Табель_Пусконаладка_Back` for supervision context
- Updated `createWarrantyWorksKeyboard()` method:
  - Changed back button callback from `Табель_ШМ_Back` to `category_Табель_Пусконаладка_Back`
- Updated `createSupervisionProjectActivityKeyboard()` method:
  - Changed back button callback from `Табель_ШМ_Back` to `category_Табель_Пусконаладка_Back`

### 2. CustomCommands\Handlers\TabelHandler.php
- Updated the commissioning works handler to recognize both `ТабПНРаботы` and `cat_ТабПНРаботы` callbacks
- Added proper handling for `Табель_Пусконаладка_Back` callback
- Fixed the `extractCategoryFromCallback` method to properly handle direct callback names like `Табель_ШМ_Back`

## Expected Behavior
After this fix:
1. When users press the back button in any equipment submenu, they will be navigated directly to the "Пусконаладочные работы" menu instead of the main "Отдел ПНР" menu.
2. When users press the back button in "Проектная деятельность" or "Устранение замечаний РК", they will be navigated to the "Отдел ПНР" menu.
3. When users press the back button in warranty equipment menus, they will be navigated to the "Отдел ПНР" menu.
4. When users press the "Вернуться в табель" button in the supervision (ШМР) menu, they will be navigated to the main tabel menu.
5. All back button callbacks are now properly recognized and handled, ensuring consistent navigation across all menus.
6. The callback extraction logic now properly handles direct callback names that don't start with the standard prefixes.

## Testing
The fix has been implemented and should be tested by:
1. Navigating to the electrical equipment menu
2. Pressing the back button
3. Verifying that the user is taken to the "Пусконаладочные работы" menu rather than the "Отдел ПНР" menu
4. Testing the back buttons in "Проектная деятельность" and "Устранение замечаний РК" to ensure they work correctly
5. Testing the back button in warranty equipment menus

The same behavior should be consistent across all equipment types.