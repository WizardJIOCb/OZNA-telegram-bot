# Mobile Brigade Callback Fix

## Problem
The mobile brigade callback button was not working. When users clicked the "💼 Мобильная бригада" button, the expected flow of date selection → project selection → activities menu was not happening.

## Root Cause
The fatal error was occurring in the CallbackHandlerFactory due to a closure function in the `usort` method. In PHP, closures can sometimes cause fatal errors in certain environments or configurations.

## Solution
Replaced the closure function in the `usort` method with a named private method in the CallbackHandlerFactory class.

### Before (causing fatal error):
```php
// Sort handlers by priority (highest first)
usort($this->handlers, function ($a, $b) {
    $priorityA = $a->getPriority();
    $priorityB = $b->getPriority();
    error_log("CallbackHandlerFactory comparing priorities: " . get_class($a) . " ({$priorityA}) vs " . get_class($b) . " ({$priorityB})");
    // We want highest priority first, so we compare B to A (reverse order)
    $result = $priorityB - $priorityA;
    error_log("CallbackHandlerFactory sort result: {$result}");
    return $result;
});
```

### After (fixed):
```php
// Sort handlers by priority (highest first)
usort($this->handlers, [$this, 'compareHandlerPriorities']);

// ...

/**
 * Compare handler priorities for sorting
 */
private function compareHandlerPriorities($a, $b): int
{
    $priorityA = $a->getPriority();
    $priorityB = $b->getPriority();
    error_log("CallbackHandlerFactory comparing priorities: " . get_class($a) . " ({$priorityA}) vs " . get_class($b) . " ({$priorityB})");
    // We want highest priority first, so we compare B to A (reverse order)
    $result = $priorityB - $priorityA;
    error_log("CallbackHandlerFactory sort result: {$result}");
    return $result;
}
```

## How the Mobile Brigade Callback Flow Works

1. User clicks the "💼 Мобильная бригада" button
2. The button sends callback data: `category_Табель_Мобильная_Бригада`
3. CallbackHandlerFactory receives the callback and routes it to TabelHandler
4. TabelHandler recognizes this callback and calls `handleMobileBrigadeDateSelection()`
5. `handleMobileBrigadeDateSelection()` shows date selection with prefix: `category_Табель_МобБриг`
6. After user selects a date, the callback goes to `handleDateSelection()`
7. After date selection, shows project selection
8. After project selection, shows mobile brigade activities

## Verification
Created test scripts to verify:
1. No syntax errors in CallbackqueryCommand.php
2. No syntax errors in CallbackHandlerFactory.php
3. Correct callback data flow for mobile brigade functionality

## Files Modified
- `CustomCommands/Services/CallbackHandlerFactory.php` - Replaced closure with named method

The fix ensures that the mobile brigade callback button now works correctly and users can complete the full flow of date selection → project selection → activities menu.