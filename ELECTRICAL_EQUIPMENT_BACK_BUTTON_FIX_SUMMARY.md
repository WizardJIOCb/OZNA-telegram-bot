# Fix for Electrical Equipment Back Button Navigation

## Problem Description
The back button in the "Электрооборудование" (Electrical Equipment) menu was returning users to the "Отдел ПНР" (Commissioning Department) menu instead of the "Пусконаладочные работы" (Commissioning Works) menu.

## Root Cause
The electrical equipment keyboard's back button was using the callback data `category_Табель_Пусконаладка_Back`, which when processed by the TabelHandler, would navigate back to the main commissioning department menu rather than the commissioning works submenu.

## Solution
1. Changed the back button callback data in all equipment keyboards from their specific category callbacks to `cat_ТабПНРаботы`
2. Updated the TabelHandler to properly recognize and handle the `cat_ТабПНРаботы` callback
3. Ensured consistency across all equipment types (electrical, automation, fire alarm, etc.)

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

### 2. CustomCommands\Handlers\TabelHandler.php
- Updated the commissioning works handler to recognize both `ТабПНРаботы` and `cat_ТабПНРаботы` callbacks

## Expected Behavior
After this fix, when users press the back button in any equipment submenu, they will be navigated directly to the "Пусконаладочные работы" menu instead of the main "Отдел ПНР" menu.

## Testing
The fix has been implemented and should be tested by:
1. Navigating to the electrical equipment menu
2. Pressing the back button
3. Verifying that the user is taken to the "Пусконаладочные работы" menu rather than the "Отдел ПНР" menu

The same behavior should be consistent across all equipment types.