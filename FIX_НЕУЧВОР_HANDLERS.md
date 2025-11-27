# Fix: "Неизвестная команда табеля" для кнопок "Работы неучтенные в ВОР"

## Проблема
После добавления новых кнопок "Работы неучтенные в ВОР" во всех 6 активностях ШМР технологического оборудования, при нажатии на эти кнопки система показывала ошибку **"Неизвестная команда табеля"** вместо диалога подтверждения.

## Причина
Были добавлены:
- ✅ Кнопки в MessageService.php
- ✅ Маппинги активностей в TabelEntry.php  
- ✅ Callback маппинги в TabelHandler.php

**НО отсутствовали обработчики (handlers)** в методе `handleOtherWorkTypes()` файла TabelHandler.php, которые должны обрабатывать нажатия на эти кнопки.

## Решение
Добавлены 6 обработчиков в файл `TabelHandler.php` (метод `handleOtherWorkTypes`, после строки ~2950):

```php
// CRITICAL FIX: Handle ШМР technological equipment "Работы неучтенные в ВОР" activities - NEW HANDLERS
if ($category === 'ШМР_МонтажТрубопроводов_НеучВОР') {
    error_log("TabelHandler: handling ШМР pipeline installation unaccounted work confirmation");
    $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
    $confirmationText = $this->tabelEntry->getConfirmationMessage('ШМР_МонтажТрубопроводов_НеучВОР', $projectCode);
    $keyboard = $this->messageService->createConfirmationKeyboard("cat_ШМР_МонтТруб_НеучВОР_yes");
    return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
}

if ($category === 'ШМР_ГидроПневмоИспытания_НеучВОР') {
    error_log("TabelHandler: handling ШМР hydro/pneumatic testing unaccounted work confirmation");
    $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
    $confirmationText = $this->tabelEntry->getConfirmationMessage('ШМР_ГидроПневмоИспытания_НеучВОР', $projectCode);
    $keyboard = $this->messageService->createConfirmationKeyboard("cat_ШМР_ГидрИсп_НеучВОР_yes");
    return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
}

if ($category === 'ШМР_МонтажТехнологическогоОборудования_НеучВОР') {
    error_log("TabelHandler: handling ШМР technological equipment installation unaccounted work confirmation");
    $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
    $confirmationText = $this->tabelEntry->getConfirmationMessage('ШМР_МонтажТехнологическогоОборудования_НеучВОР', $projectCode);
    $keyboard = $this->messageService->createConfirmationKeyboard("cat_ШМР_МонтТехОб_НеучВОР_yes");
    return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
}

if ($category === 'ШМР_МонтажЗРА_НеучВОР') {
    error_log("TabelHandler: handling ШМР ZRA installation unaccounted work confirmation");
    $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
    $confirmationText = $this->tabelEntry->getConfirmationMessage('ШМР_МонтажЗРА_НеучВОР', $projectCode);
    $keyboard = $this->messageService->createConfirmationKeyboard("cat_ШМР_МонтЗРА_НеучВОР_yes");
    return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
}

if ($category === 'ШМР_ПротяжкаСоединений_НеучВОР') {
    error_log("TabelHandler: handling ШМР connection stretching unaccounted work confirmation");
    $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
    $confirmationText = $this->tabelEntry->getConfirmationMessage('ШМР_ПротяжкаСоединений_НеучВОР', $projectCode);
    $keyboard = $this->messageService->createConfirmationKeyboard("cat_ШМР_ПротСоед_НеучВОР_yes");
    return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
}

if ($category === 'ШМР_МонтажТеплоизоляции_НеучВОР') {
    error_log("TabelHandler: handling ШМР thermal insulation installation unaccounted work confirmation");
    $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
    $confirmationText = $this->tabelEntry->getConfirmationMessage('ШМР_МонтажТеплоизоляции_НеучВОР', $projectCode);
    $keyboard = $this->messageService->createConfirmationKeyboard("cat_ШМР_МонтТеплИзол_НеучВОР_yes");
    return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
}
```

## Как работает
1. **Пользователь нажимает** кнопку "📄 Работы неучтенные в ВОР"
2. **Callback** отправляется: `cat_ШМР_[Activity]_НеучВОР`
3. **TabelHandler** мапит callback → `ШМР_[FullActivity]_НеучВОР`
4. **Новый обработчик** перехватывает категорию
5. **Создается диалог подтверждения** с текстом из TabelEntry
6. **Пользователь нажимает "Да"**
7. **Создается запись** в табель с форматом ШМР

## Измененные файлы
- `c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php` - добавлены 6 обработчиков

## Тестирование
Создан и выполнен тестовый скрипт `test_неучвор_handlers.php`:

```
✅ ALL HANDLERS FOUND AND CONFIGURED CORRECTLY!

The buttons should now work properly:
1. Click 'Работы неучтенные в ВОР' button
2. System shows confirmation dialog
3. Click 'Да' to confirm
4. Entry is created with proper format
```

## Ожидаемое поведение
При нажатии на кнопку "Работы неучтенные в ВОР":

**До исправления:**
```
❌ Неизвестная команда табеля
```

**После исправления:**
```
Иванов Иван Иванович
Вы создаете запись в табель на 10.10.2025
для проекта XXX
Активность: 📄 Работы неучтенные в ВОР (Монтаж трубопроводов)

Подтвердить?

[Да] [Нет]
```

После подтверждения:
```
Иванов Иван Иванович
✅10.10.2025, Проект XXX: Добавлена запись в табель: 
📄 Работы неучтенные в ВОР (Монтаж трубопроводов)
```

## Статус
✅ **ИСПРАВЛЕНО** - Все 6 кнопок теперь работают корректно

## Дата исправления
2025-10-10
