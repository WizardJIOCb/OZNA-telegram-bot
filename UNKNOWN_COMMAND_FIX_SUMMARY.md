# Fix for "Неизвестная команда табеля" Issue

## Issue Description
When users clicked certain buttons in the Telegram bot, they would see the error message "Неизвестная команда табеля" (Unknown table command). This was happening because the callback handling logic in the [TabelHandler.php](file:///c:/ozna_bot/CustomCommands/Handlers/TabelHandler.php) file was not properly recognizing and processing all callback categories.

## Root Cause
The issue was in the [handleOtherWorkTypes](file:///c:/ozna_bot/CustomCommands/Handlers/TabelHandler.php#L638-L970) method in [CustomCommands/Handlers/TabelHandler.php](file:///c:/ozna_bot/CustomCommands/Handlers/TabelHandler.php). The method had several problems:

1. Duplicate and overlapping conditions for handling electrical equipment callbacks
2. Incomplete condition checking for short callback formats (like `cat_ТабЭлОб_РабПоГр`)
3. Missing proper handling for back navigation buttons

## Solution Implemented

### 1. Consolidated Electrical Equipment Menu Handling
Modified the electrical equipment menu handling condition to be more comprehensive and eliminate duplicates:

```php
// Handle electrical equipment menu with new short callback format
// Consolidated all electrical equipment menu checks into one comprehensive condition
if ($category === 'Табель_Электрооборудование' || 
    $category === 'cat_ТабЭлОб' ||
    $category === 'cat_ТабЭлОб_Наз' ||
    $category === 'Табель_ПусконаладочныеРаботы' ||
    (strpos($category, 'Табель_Электрооборудование') !== false) ||
    (strpos($category, 'cat_ТабЭлОб') === 0)) {
    error_log("TabelHandler: handleOtherWorkTypes: handling electrical equipment menu");
    // Handle back button cases
    if ($category === 'Табель_ПусконаладочныеРаботы') {
        return $this->handleCommissioningWorksMenu($chatId);
    }
    if ($category === 'Табель_ЭлектрОб') {
        return $this->handleElectricalEquipmentMenu($chatId);
    }
    return $this->handleElectricalEquipmentMenu($chatId);
}
```

### 2. Ensured Proper Handling of "Работы по графику"
Made sure the "Работы по графику" button directly executes the activity without confirmation:

```php
// Handle works by schedule for electrical equipment
if ($category === 'Табель_РаботыПоГрафику_Электрооборудование' || 
    $category === 'cat_ТабЭлОб_РабПоГр') {
    error_log("TabelHandler: handleOtherWorkTypes: handling works by schedule for electrical equipment");
    // For "Работы по графику", we should directly execute without confirmation as per previous fix
    // Create a mock callback for direct execution
    $strippedCategory = 'РаботыПоГрафику_Электрооборудование';
    $conversation->notes['current_category'] = $category;
    $conversation->update();
    
    // Create a mock callback query for direct execution
    $mockCallbackQuery = new CallbackQuery([
        'data' => "category_Табель_{$strippedCategory}_yes"
    ]);
    
    return $this->handleActivityExecution($mockCallbackQuery, "Табель_{$strippedCategory}_yes", $conversation);
}
```

## Testing
Created and ran test scripts to verify:
1. All electrical equipment callbacks are properly recognized
2. The "Работы по графику" button works correctly
3. Back navigation buttons work properly
4. No more "Неизвестная команда табеля" messages for valid callbacks

## Result
After implementing these changes, users should no longer see the "Неизвестная команда табеля" error when clicking buttons in the Telegram bot. All electrical equipment menu buttons, including "Работы по графику", should work correctly.