# Equipment Menu Back Button Navigation Fixes

## Problem
The back button in equipment menus was not working correctly. When users navigated from the commissioning works menu to an equipment menu and then pressed the back button, they were incorrectly returned to the main commissioning menu (Отдел ПНР) instead of the commissioning works list.

## Root Cause
The issue was in the `handleBackToCommissioning` method which relies on:
1. The `current_category` field in conversation notes to determine the correct navigation path
2. The back button callback data in equipment keyboards to properly identify the source menu

## Solution Implemented

### 1. Fixed Conversation State Management
Added `current_category` assignment in all equipment menu handler methods in `TabelHandler.php`:
- `handleElectricalEquipmentMenu` - Set `current_category` to 'Табель_Электрооборудование'
- `handleAutomationEquipmentMenu` - Set `current_category` to 'Табель_Автоматизация'
- `handleFireAlarmEquipmentMenu` - Set `current_category` to 'Табель_ПожарнаяСигнализация'
- `handleSecurityAlarmEquipmentMenu` - Set `current_category` to 'Табель_ОхраннаяСигнализация'
- `handleHeatingVentilationEquipmentMenu` - Set `current_category` to 'Табель_ОтоплениеИВентиляция'
- `handleTechnologicalEquipmentMenu` - Set `current_category` to 'Табель_ТехнологическоеОборудование'
- `handleMetrologyEquipmentMenu` - Set `current_category` to 'Табель_Метрология'

### 2. Fixed Back Button Callback Data
Updated back button callback data in all equipment keyboards in `MessageService.php`:

#### Electrical Equipment Menu
**File:** `CustomCommands/Services/MessageService.php`
**Method:** `createElectricalEquipmentKeyboard()`
**Change:**
```php
// Before
['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']

// After
['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Электрооборудование']
```

#### Automation Equipment Menu
**File:** `CustomCommands/Services/MessageService.php`
**Method:** `createAutomationEquipmentKeyboard()`
**Change:**
```php
// Before
['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']

// After
['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабАвтом']
```

#### Fire Alarm Equipment Menu
**File:** `CustomCommands/Services/MessageService.php`
**Method:** `createFireAlarmEquipmentKeyboard()`
**Change:**
```php
// Before
['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']

// After
['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабПожСигн']
```

#### Security Alarm Equipment Menu
**File:** `CustomCommands/Services/MessageService.php`
**Method:** `createSecurityAlarmEquipmentKeyboard()`
**Change:**
```php
// Before
['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']

// After
['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабОхрСигн']
```

#### Heating and Ventilation Equipment Menu
**File:** `CustomCommands/Services/MessageService.php`
**Method:** `createHeatingVentilationEquipmentKeyboard()`
**Change:**
```php
// Before
['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']

// After
['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабВентОтопл']
```

#### Technological Equipment Menu
**File:** `CustomCommands/Services/MessageService.php`
**Method:** `createTechnologicalEquipmentKeyboard()`
**Change:**
```php
// Before
['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']

// After
['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабТехОб']
```

#### Metrology Equipment Menu
**File:** `CustomCommands/Services/MessageService.php`
**Method:** `createMetrologyKeyboard()`
**Change:**
```php
// Before
['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']

// After
['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабМетрол']
```

## How It Works
When a user navigates to an equipment menu:
1. The equipment menu handler sets `current_category` in conversation notes
2. The back button uses a callback that identifies the specific equipment menu
3. When the user presses the back button, `handleBackToCommissioning` checks both:
   - The `current_category` field to determine if we're coming from an equipment menu
   - The callback data to route to the correct back handler
4. Since both conditions are met, the method correctly navigates back to the commissioning works list instead of the main commissioning menu

## Files Modified
- `c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php` - Added `current_category` assignments in 7 equipment menu handler methods
- `c:\ozna_bot\CustomCommands\Services\MessageService.php` - Updated back button callback data in 7 equipment keyboards

## Testing
After implementing these changes, the back button in all equipment menus should correctly navigate back to the commissioning works list, not the main commissioning menu.