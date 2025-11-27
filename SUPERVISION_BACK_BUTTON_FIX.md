# Fix for Supervision "Back" Button Navigation

## Issue Description
The "Назад" (Back) button in the "Шеф-монтажные работы" (Supervision Installation Works) menu was navigating users to the main tabel menu instead of the "Отдел ШМР" (Supervision Department) menu.

## Root Cause
The [Табель_ШМ_Back](file:///c:/ozna_bot/CustomCommands/Handlers/TabelHandler.php#L135-L138) callback was being routed to [handleMainTabel](file:///c:/ozna_bot/CustomCommands/Handlers/TabelHandler.php#L739-L746) instead of the appropriate supervision menu handler.

## Solution Implemented
Updated the routing logic in [TabelHandler.php](file:///c:/ozna_bot/CustomCommands/Handlers/TabelHandler.php) to correctly route [Табель_ШМ_Back](file:///c:/ozna_bot/CustomCommands/Handlers/TabelHandler.php#L135-L138) to the supervision menu:

```php
// Back to supervision menu (Отдел ШМР)
if ($category === 'Табель_ШМ_Back') {
    error_log("TabelHandler: routing to handleSupervisionInstallationWorksMenu");
    // We need to create a new conversation for this menu
    $newConversation = new Conversation($callbackQuery->getFrom()->getId(), $chatId);
    return $this->handleSupervisionInstallationWorksMenu($chatId, $newConversation);
}
```

## Files Modified
1. [c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php](file:///c:/ozna_bot/CustomCommands/Handlers/TabelHandler.php) - Updated routing logic for [Табель_ШМ_Back](file:///c:/ozna_bot/CustomCommands/Handlers/TabelHandler.php#L135-L138)

## Context
The [Табель_ШМ_Back](file:///c:/ozna_bot/CustomCommands/Handlers/TabelHandler.php#L135-L138) callback is used in two places:
1. In [createSupervisionKeyboard](file:///c:/ozna_bot/CustomCommands\Services\MessageService.php#L488-L509)() method - "⬅️ Вернуться в табель" button
2. In [createSupervisionInstallationWorksKeyboard](file:///c:/ozna_bot/CustomCommands\Services\MessageService.php#L854-L870)() method - "⬅️ Назад" button

Both of these buttons should navigate users back to the supervision menu ("Отдел ШМР"), which is implemented as the [handleSupervisionInstallationWorksMenu](file:///c:/ozna_bot/CustomCommands/Handlers/TabelHandler.php#L3736-L3753) method.

## Expected Behavior
When users click the "Назад" button in the "Шеф-монтажные работы" menu:
1. They will be navigated to the "Отдел ШМР" menu
2. No more incorrect navigation to the main tabel menu
3. No more "Неизвестная команда табеля" error

## Verification
The fix has been implemented and should be tested by:
1. Navigating to the "Шеф-монтажные работы" menu
2. Pressing the "Назад" button
3. Verifying that the user is taken to the "Отдел ШМР" menu rather than the main tabel menu