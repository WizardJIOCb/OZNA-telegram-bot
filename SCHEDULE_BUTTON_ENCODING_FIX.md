# Fix for "Работы по графику" Button Encoding Issue

## Problem
The "Работы по графику" button in the electrical equipment menu was showing "Неизвестная команда табеля" instead of generating a report directly. This was happening because the CallbackHandlerFactory was incorrectly transforming specific electrical equipment activity callbacks into the general electrical equipment menu callback.

## Root Cause
The CallbackHandlerFactory had an early check that forced all electrical equipment callbacks to be transformed to `category_Табель_Электрооборудование`, which meant specific activity callbacks like `cat_ТабЭлОб_РабПоГр` were being lost and treated as unknown commands.

## Solution
Modified the CallbackHandlerFactory to be more specific about which electrical equipment callbacks it transforms:

1. Updated the early check to exclude specific electrical equipment activity callbacks:
   - `РаботыПоГрафику` / `РабПоГр`
   - `ФормИД`
   - `ИзмСхПд`
   - `МонтКбЛн`
   - `РаскКбЛн`
   - `ДопРаб`
   - `ПодтВех`
   - `Проч`

2. Updated the fixKnownCyrillicPatterns method to avoid fixing specific electrical equipment activity patterns that could interfere with the callback processing.

## Verification
Created a test script that confirms:
- The callback `cat_ТабЭлОб_РабПоГр` is properly recognized by TabelHandler
- The category is correctly extracted as `Табель_РаботыПоГрафику_Электрооборудование`
- This category should be handled by handleOtherWorkTypes to directly execute the activity without confirmation

## Result
The "Работы по графику" button should now work correctly and generate a report directly without showing "Неизвестная команда табеля".