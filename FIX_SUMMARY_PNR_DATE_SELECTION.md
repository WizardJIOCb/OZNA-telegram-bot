# Fix Summary: Отдел ПНР Date Selection Issue

## Problem Description
When users clicked the "⚙️ Отдел ПНР" button, the date selection buttons were not appearing. This was preventing users from selecting a date to proceed with their work logging.

## Root Cause
The issue was in the `handleCommissioningDateSelection()` method in `TabelHandler.php`. It was using `category_Табель_ПНЛ` as the prefix for date selection buttons, which created a conflict with the "Пусконаладочные работы" button that also uses `Табель_ПНЛ` as its category.

When a user clicked the "⚙️ Отдел ПНР" button (`category_Табель_Пусконаладка`), it correctly routed to `handleCommissioningDateSelection()`, which created a date selection keyboard with the prefix `category_Табель_ПНЛ`. 

However, when the user then clicked a date button (e.g., "Сегодня"), the callback would be `category_Табель_ПНЛ_Сегодня`. Later in the code, this callback got processed and might be incorrectly routed because the system saw `Табель_ПНЛ` and thought it was related to the "Пусконаладочные работы" button rather than a date selection for the "Отдел ПНР".

## Solution
We changed the prefixes used for date selection to be unique and not conflict with existing categories:

1. In `handleCommissioningDateSelection()`, changed the prefix from `category_Табель_ПНЛ` to `category_Табель_Пусконаладка`
2. In `handleSupervisionDateSelection()`, changed the prefix from `category_Табель_ШМ` to `category_Табель_Шеф_монтаж`

## Changes Made

### File: `CustomCommands/Handlers/TabelHandler.php`

```php
// Before (lines 1748-1751):
private function handleCommissioningDateSelection(string $chatId): ServerResponse
{
    // Используем короткий префикс для избежания ограничения в 64 байта
    $keyboard = $this->messageService->createDateSelectionKeyboard('category_Табель_ПНЛ');
    return $this->messageService->sendMessage($chatId, '⚙️ <b>Выберите дату для Пусконаладки</b>', $keyboard);
}

// After:
private function handleCommissioningDateSelection(string $chatId): ServerResponse
{
    // Используем уникальный префикс для избежания конфликта с Пусконаладочные работы
    $keyboard = $this->messageService->createDateSelectionKeyboard('category_Табель_Пусконаладка');
    return $this->messageService->sendMessage($chatId, '⚙️ <b>Выберите дату для Пусконаладки</b>', $keyboard);
}
```

```php
// Before (lines 1757-1760):
private function handleSupervisionDateSelection(string $chatId): ServerResponse
{
    // Используем короткий префикс для избежания ограничения в 64 байта
    $keyboard = $this->messageService->createDateSelectionKeyboard('category_Табель_ШМ');
    return $this->messageService->sendMessage($chatId, '🔧 <b>Выберите дату для Шеф-монтажа</b>', $keyboard);
}

// After:
private function handleSupervisionDateSelection(string $chatId): ServerResponse
{
    // Используем уникальный префикс для избежания конфликта с другими категориями
    $keyboard = $this->messageService->createDateSelectionKeyboard('category_Табель_Шеф_монтаж');
    return $this->messageService->sendMessage($chatId, '🔧 <b>Выберите дату для Шеф-монтажа</b>', $keyboard);
}
```

## Verification
We created and ran a test script that confirmed:
1. The "⚙️ Отдел ПНР" button correctly routes to the date selection handler
2. Date selection buttons are created with unique prefixes
3. Date selection callbacks are correctly identified as date selections
4. There are no conflicts with the "Пусконаладочные работы" button

## Expected Result
After this fix, when users click the "⚙️ Отдел ПНР" button, they should now see the date selection buttons (Сегодня, Вчера, Позавчера, Другая дата) and be able to proceed with their work logging.