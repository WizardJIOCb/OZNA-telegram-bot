# Fix Summary: "Работы по графику" Button Order Issue

## Problem
The "Работы по графику" button in the electrical equipment category was not showing confirmation buttons and was not creating timesheet entries correctly. The issue was that the callback `cat_ТабЭлОб_РабПоГр` was being processed by the activity execution handler instead of showing confirmation buttons.

## Root Cause
The issue was in the order of operations in the `TabelHandler::handle()` method. The callback mapping was happening correctly, but the order of checks was incorrect:

1. Callback `cat_ТабЭлОб_РабПоГр` was correctly mapped to `Табель_РаботыПоГрафику_Электрооборудование`
2. The system then tried to create a confirmation category by appending `_yes`: `Табель_РаботыПоГрафику_Электрооборудование_yes`
3. This confirmation category was recognized as an activity execution callback
4. The activity execution check was happening BEFORE the "Работы по графику" check
5. As a result, the callback was processed by the activity execution handler instead of showing confirmation buttons

## Solution Implemented
Moved the "Работы по графику" check to happen BEFORE the activity execution check in the `TabelHandler::handle()` method:

### Before (Problematic Order):
```php
// Activity execution - Check with mapped category for short callback formats
// First check if this is already an activity execution callback
if ($this->isActivityExecution($category)) {
    error_log("TabelHandler: handle - handling activity execution with category: " . $category);
    return $this->handleActivityExecution($callbackQuery, $category, $conversation);
}

// If not, try to map it
$mappedCategory = $this->extractCategoryFromCallback($category);
if ($mappedCategory && $mappedCategory !== $category) {
    $confirmationCategory = $mappedCategory . '_yes';
    if ($this->isActivityExecution($confirmationCategory)) {
        error_log("TabelHandler: handle - handling activity execution with mapped category: " . $confirmationCategory);
        return $this->handleActivityExecution($callbackQuery, $confirmationCategory, $conversation);
    }
}

// Handle works by schedule for electrical equipment - SHOW CONFIRMATION
if ($actualCategory === 'Табель_РаботыПоГрафику_Электрооборудование' || 
    $actualCategory === 'cat_ТабЭлОб_РабПоГр') {
    error_log("TabelHandler: handle - handling works by schedule for electrical equipment - SHOW CONFIRMATION");
    $strippedCategory = 'РаботыПоГрафику_Электрооборудование';
    $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
    $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
    $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$strippedCategory}_yes");
    return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
}
```

### After (Fixed Order):
```php
// Extract the actual category from the callback data
$actualCategory = $this->extractCategoryFromCallback($callbackData);
error_log("TabelHandler: extracted actual category: '{$actualCategory}'");

// Handle works by schedule for electrical equipment - SHOW CONFIRMATION
// This check must come BEFORE the activity execution check to prevent conflicts
if ($actualCategory === 'Табель_РаботыПоГрафику_Электрооборудование' || 
    $actualCategory === 'cat_ТабЭлОб_РабПоГр') {
    error_log("TabelHandler: handle - handling works by schedule for electrical equipment - SHOW CONFIRMATION");
    $strippedCategory = 'РаботыПоГрафику_Электрооборудование';
    $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
    $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
    $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$strippedCategory}_yes");
    return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
}

// Handle main tabel menu
if ($actualCategory === 'Табель') {
    return $this->handleMainMenu($chatId, $userId);
}

// ... other checks ...

// Activity execution - Check with mapped category for short callback formats
// First check if this is already an activity execution callback
if ($this->isActivityExecution($category)) {
    error_log("TabelHandler: handle - handling activity execution with category: " . $category);
    return $this->handleActivityExecution($callbackQuery, $category, $conversation);
}

// If not, try to map it
$mappedCategory = $this->extractCategoryFromCallback($category);
if ($mappedCategory && $mappedCategory !== $category) {
    $confirmationCategory = $mappedCategory . '_yes';
    if ($this->isActivityExecution($confirmationCategory)) {
        error_log("TabelHandler: handle - handling activity execution with mapped category: " . $confirmationCategory);
        return $this->handleActivityExecution($callbackQuery, $confirmationCategory, $conversation);
    }
}
```

## Expected Behavior After Fix
1. User clicks "Работы по графику" button in electrical equipment menu
2. Button sends callback data `cat_ТабЭлОб_РабПоГр`
3. System maps this to `Табель_РаботыПоГрафику_Электрооборудование`
4. System shows confirmation message: "Работы по графику (Электрооборудование), отправить запись в табель в проект <PROJECT>?"
5. User sees confirmation buttons: [✅ Да] [❌ Нет]
6. If user clicks "Да", the timesheet entry is created
7. If user clicks "Нет", they return to the tabel menu

## Verification
- ✅ Syntax check passed - no errors in modified code
- ✅ Correct order of operations maintained
- ✅ "Работы по графику" check happens before activity execution check
- ✅ Proper error logging maintained
- ✅ Confirmation message properly formatted
- ✅ Keyboard with confirmation buttons created correctly

The "Работы по графику" button in electrical equipment category should now work correctly, showing confirmation buttons before creating timesheet entries.