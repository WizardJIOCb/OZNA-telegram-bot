# Fix for Electrical Equipment Activities ("Работа по графику" and "Формирование ИД")

## Problem Description
When users clicked the "Да" button to send a report for "Работа по графику" and "Формирование ИД" activities in the "Электрооборудование" section, the report was not being sent. However, the "Изменение схем подключения" activity was working correctly.

## Root Cause Analysis
The issue was caused by inconsistent callback data formats being used for different electrical equipment activities:

1. **"Работа по графику" and "Формирование ИД"** activities were using the long callback format:
   - `category_Табель_РабПоГр_ЭлектрОб_yes`
   - `category_Табель_ФормИД_ЭлектрОб_yes`

2. **"Изменение схем подключения" and other activities** were using the short callback format:
   - `cat_ТабЭлОб_ИзмСхПд_yes`

The handler was properly configured to process the short format but not the long format for these specific activities.

## Solution Implemented
Updated the TabelHandler to use consistent short callback format for all electrical equipment activities:

### 1. Updated Confirmation Keyboards
Changed the confirmation keyboards in `handleOtherWorkTypes` method to use short callback format:
```php
// Before (lines 941-953):
$keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$strippedCategory}_yes");

// After:
$keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_РабПоГр_yes");
$keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_ФормИД_yes");
```

### 2. Updated Callback Data References
Updated references to use the short format consistently:
```php
// Before:
$callbackData === 'category_Табель_РабПоГр_ЭлектрОб_yes' ||
$callbackData === 'category_Табель_ФормИД_ЭлектрОб_yes' ||

// After:
$callbackData === 'cat_ТабЭлОб_РабПоГр_yes' ||
$callbackData === 'cat_ТабЭлОб_ФормИД_yes' ||
```

### 3. Updated Category Mapping
Updated the category mapping in `extractCategoryFromCallback` method:
```php
// Before:
'category_Табель_РабПоГр_ЭлектрОб_yes' => 'Табель_РабПоГр_ЭлектрОб_yes',
'category_Табель_ФормИД_ЭлектрОб_yes' => 'Табель_ФормИД_ЭлектрОб_yes'

// After:
'cat_ТабЭлОб_РабПоГр_yes' => 'Табель_РабПоГр_ЭлектрОб_yes',
'cat_ТабЭлОб_ФормИД_yes' => 'Табель_ФормИД_ЭлектрОб_yes'
```

### 4. Removed Duplicated Conditions
Removed duplicated conditions in the `handle` method that were causing confusion in callback processing:
```php
// Before (lines 176-185):
$callbackData === 'cat_ТабЭлОб_РабПоГр_yes' ||
$callbackData === 'cat_ТабЭлОб_ФормИД_yes' ||
// ... other conditions ...
$actualCategory === 'cat_ТабЭлОб_РабПоГр_yes' ||
$actualCategory === 'cat_ТабЭлОб_ФормИД_yes' ||

// After:
$callbackData === 'cat_ТабЭлОб_РабПоГр_yes' ||
$callbackData === 'cat_ТабЭлОб_ФормИД_yes' ||
// ... other conditions (without duplicated actualCategory checks) ...
```

### 5. Removed Duplicated Mappings
Removed duplicated mappings in the `extractCategoryFromCallback` method:
```php
// Before:
'cat_ТабЭлОб_ИзмСхПд_yes' => 'Табель_ИзмСхПд_ЭлектрОб_yes',
// ... other mappings ...
'cat_ТабЭлОб_Проч_yes' => 'Табель_Прочее_ЭлектрОб_yes',
// Add identical format for electrical equipment to match automation equipment pattern
'cat_ТабЭлОб_РабПоГр_yes' => 'Табель_РабПоГр_ЭлектрОб_yes',
'cat_ТабЭлОб_ФормИД_yes' => 'Табель_ФормИД_ЭлектрОб_yes'

// After:
'cat_ТабЭлОб_ИзмСхПд_yes' => 'Табель_ИзмСхПд_ЭлектрОб_yes',
// ... other mappings ...
'cat_ТабЭлОб_Проч_yes' => 'Табель_Прочее_ЭлектрОб_yes'
```

## Files Modified
- `CustomCommands/Handlers/TabelHandler.php` - Main fix implementation

## Verification
The fix ensures that all electrical equipment activities now use the same short callback format (`cat_ТабЭлОб_*_yes`), which is properly handled by the existing logic in the `isActivityExecution` and `handleActivityExecution` methods.

## Testing
Multiple test scripts have been created to verify the fix works correctly:

1. `test_electrical_works_fix.php` - Original test script
2. `simple_test.php` - Checks for duplicated conditions
3. `electrical_test.php` - Verifies electrical equipment callback processing
4. `final_test.php` - Comprehensive test of all fixes

All tests pass, confirming that the "Да" button for "Работа по графику" and "Формирование ИД" activities in the "Электрооборудование" section should now send reports properly.