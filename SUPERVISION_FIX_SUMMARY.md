# Fix for "Отдел ШМР" Button Showing Mobile Brigade Activities

## Issue Description
When users clicked the "🔧 Отдел ШМР" button in the Telegram bot, they were seeing the activities of the "💼 Мобильная бригада" instead of the correct supervision activities. This was causing confusion as users expected to see ШМР-specific activities but were presented with mobile brigade options.

## Root Cause
The issue was in two places in the [TabelHandler.php](file:///c:/ozna_bot/CustomCommands/Handlers/TabelHandler.php) file:

1. In the [handleProjectSelection](file:///c:/ozna_bot/CustomCommands/Handlers/TabelHandler.php#L415-L454) method (line 504), when handling the supervision flow (`$isSupervision`), the code was incorrectly using the [createMobileBrigadeKeyboard()](file:///c:/ozna_bot/CustomCommands/Services/MessageService.php#L362-L407) method instead of a proper supervision keyboard method.

2. In the [handleBackToSupervision](file:///c:/ozna_bot/CustomCommands/Handlers/TabelHandler.php#L977-L987) method (line 980), the same issue occurred where the mobile brigade keyboard was being used instead of a supervision keyboard.

Additionally, there was no dedicated `createSupervisionKeyboard()` method in the [MessageService.php](file:///c:/ozna_bot/CustomCommands/Services/MessageService.php) file.

## Solution Implemented

### 1. Added `createSupervisionKeyboard()` Method
Added a new method in [CustomCommands/Services/MessageService.php](file:///c:/ozna_bot/CustomCommands/Services/MessageService.php):

```php
/**
 * Create inline keyboard for supervision (Шеф-монтаж) activities
 */
public function createSupervisionKeyboard(): array
{
    return [
        [
            ['text' => '📋 Подготовка к ШМР', 'callback_data' => 'category_Табель_Подготовка']
        ],
        [
            ['text' => '📋 Инструктаж', 'callback_data' => 'category_Табель_Инструктаж']
        ],
        [
            ['text' => '🔧 ШМР', 'callback_data' => 'category_Табель_ШМР']
        ],
        [
            ['text' => '🔧 Устранение замечаний РК', 'callback_data' => 'category_Табель_УЗ_РК']
        ],
        [
            ['text' => '🛡️ Работы по гарантии', 'callback_data' => 'category_Табель_ШМ_РабГарантии']
        ],
        [
            ['text' => '⏸️ Простой', 'callback_data' => 'category_Табель_ШМ_Простой']
        ],
        [
            ['text' => '⬅️ Вернуться в табель', 'callback_data' => 'category_Табель']
        ]
    ];
}
```

### 2. Updated TabelHandler to Use Correct Keyboard
Modified two methods in [CustomCommands/Handlers/TabelHandler.php](file:///c:/ozna_bot/CustomCommands/Handlers/TabelHandler.php):

1. In the [handleProjectSelection](file:///c:/ozna_bot/CustomCommands/Handlers/TabelHandler.php#L415-L454) method:
```php
} elseif ($isSupervision) {
    $keyboard = $this->messageService->createSupervisionKeyboard();
    return $this->messageService->sendMessage(
        $chatId, 
        "<b>🔧 Табель - Шеф-монтаж для проекта {$projectCode}</b>",
        $keyboard
    );
```

2. In the [handleBackToSupervision](file:///c:/ozna_bot/CustomCommands/Handlers/TabelHandler.php#L977-L987) method:
```php
/**
 * Handle back to supervision menu
 */
private function handleBackToSupervision(string $chatId, Conversation $conversation): ServerResponse
{
    $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
    $keyboard = $this->messageService->createSupervisionKeyboard();
    return $this->messageService->sendMessage(
        $chatId, 
        "<b>🔧 Табель - Шеф-монтаж для проекта {$projectCode}</b>",
        $keyboard
    );
}
```

## Testing
Created and ran test scripts to verify:
1. The new `createSupervisionKeyboard()` method works correctly
2. The TabelHandler properly recognizes the supervision callback
3. The supervision keyboard has appropriate ШМР-specific activities

## Activities Comparison

### Mobile Brigade Activities:
- 🚗 Подготовка к служебной поездке
- 🚗 Дорога
- 📋 Инструктаж
- ⚡ Электромонтажные работы
- 🔧 Слесарные работы
- 🔥 Сварочные работы
- 🧱 Теплоизоляция
- 📂 Сдача отчетных документов
- 💰 Сдача авансового отчета
- ⏸️ Простой
- 🛡️ Гарантия

### Supervision (ШМР) Activities:
- 📋 Подготовка к ШМР
- 📋 Инструктаж
- 🔧 ШМР
- 🔧 Устранение замечаний РК
- 🛡️ Работы по гарантии
- ⏸️ Простой

## Result
After implementing these changes, when users click the "🔧 Отдел ШМР" button, they will now see the appropriate ШМР-specific activities instead of the mobile brigade activities. This resolves the confusion and provides the correct workflow for supervision personnel.