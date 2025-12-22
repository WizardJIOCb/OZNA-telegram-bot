# Руководство по редактированию сообщений вместо отправки новых

## Проблема
По умолчанию бот отправляет новое сообщение при каждом нажатии кнопки, что создаёт много "мусора" в чате.

## Решение
Используй редактирование существующих сообщений (`editMessage`) вместо отправки новых.

---

## Методы в MessageService

### 1. `editMessage` — Редактирование сообщения
```php
public function editMessage(
    string $chatId, 
    int $messageId, 
    string $text, 
    ?array $keyboard = null, 
    string $parseMode = 'HTML'
): ServerResponse
```

**Использование:**
```php
$messageId = $callbackQuery->getMessage()->getMessageId();
return $this->messageService->editMessage($chatId, $messageId, $text, $keyboard);
```

### 2. `sendOrEditMessage` — Универсальный метод
```php
public function sendOrEditMessage(
    string $chatId, 
    string $text, 
    ?array $keyboard = null, 
    ?int $messageId = null, 
    string $parseMode = 'HTML'
): ServerResponse
```

**Использование:**
```php
// Если messageId передан — редактирует, иначе — отправляет новое
$messageId = $callbackQuery->getMessage()->getMessageId();
return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
```

---

## Как обновить хендлер

### ДО (старый способ):
```php
public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
{
    $chatId = $callbackQuery->getMessage()->getChat()->getId();
    
    $text = "Выберите опцию:";
    $keyboard = [
        [['text' => 'Опция 1', 'callback_data' => 'opt_1']],
        [['text' => 'Опция 2', 'callback_data' => 'opt_2']]
    ];
    
    // Отправляет НОВОЕ сообщение каждый раз
    return $this->messageService->sendMessage($chatId, $text, $keyboard);
}
```

### ПОСЛЕ (новый способ):
```php
public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
{
    $chatId = $callbackQuery->getMessage()->getChat()->getId();
    $messageId = $callbackQuery->getMessage()->getMessageId(); // ⭐ Добавили эту строку
    
    $text = "Выберите опцию:";
    $keyboard = [
        [['text' => 'Опция 1', 'callback_data' => 'opt_1']],
        [['text' => 'Опция 2', 'callback_data' => 'opt_2']]
    ];
    
    // Редактирует существующее сообщение вместо отправки нового
    return $this->messageService->editMessage($chatId, $messageId, $text, $keyboard);
}
```

---

## Примеры из проекта

### MainMenuHandler (уже обновлён ✅)
```php
private function showMainMenu(string $chatId, int $userId, int $messageId): ServerResponse
{
    $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
    
    if ($userAccessLevel == 0) {
        $keyboard = [
            [['text' => '👤 Профиль', 'callback_data' => 'category_Профиль']],
            [['text' => '❓ Помощь', 'callback_data' => 'category_Помощь']],
            [['text' => '🔐 Запросить доступ', 'callback_data' => 'category_Админка_ЗапросДоступ']]
        ];
        
        $text = "<b>🏠 Главное меню</b>\\n\\n(для вызова этого меню в любой момент отправьте /start)";
        
        // ⭐ Редактирует сообщение вместо отправки нового
        return $this->messageService->editMessage($chatId, $messageId, $text, $keyboard);
    } else {
        // ... полное меню ...
        return $this->messageService->editMessage($chatId, $messageId, $text, $keyboard);
    }
}
```

### Пример для TabelHandler
```php
private function showCommissioningMenu(string $chatId, int $messageId): ServerResponse
{
    $keyboard = [
        [['text' => '⚡ Электрооборудование', 'callback_data' => 'cat_ТабЭлОб']],
        [['text' => '🤖 Автоматизация', 'callback_data' => 'cat_ТабАвтом']],
        [['text' => '🔥 Пожарная сигнализация', 'callback_data' => 'cat_ТабПожСигн']],
        [['text' => '⬅️ Назад', 'callback_data' => 'category_Табель']]
    ];
    
    $text = "<b>⚙️ Пусконаладочные работы</b>\\n\\nВыберите тип оборудования:";
    
    // ⭐ Редактирует сообщение
    return $this->messageService->editMessage($chatId, $messageId, $text, $keyboard);
}
```

---

## Преимущества

✅ **Чистота чата** — Нет кучи старых сообщений с кнопками  
✅ **Лучший UX** — Выглядит профессиональнее и аккуратнее  
✅ **Меньше спама** — Telegram не ругается на слишком частые сообщения  
✅ **Быстрее** — Редактирование работает быстрее отправки нового сообщения  

---

## Когда НЕ использовать editMessage

❌ **Команда /start** — Первое сообщение нужно отправить, а не редактировать  
❌ **Отправка файлов/документов** — Нельзя редактировать тип сообщения  
❌ **Сообщения с подтверждением действия** — Лучше оставить историю для проверки  

---

## Быстрый чеклист для обновления хендлера

1. ✅ Добавь получение messageId:
   ```php
   $messageId = $callbackQuery->getMessage()->getMessageId();
   ```

2. ✅ Замени `sendMessage` на `editMessage`:
   ```php
   // Было:
   return $this->messageService->sendMessage($chatId, $text, $keyboard);
   
   // Стало:
   return $this->messageService->editMessage($chatId, $messageId, $text, $keyboard);
   ```

3. ✅ Или используй универсальный метод:
   ```php
   return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
   ```

---

## Статус обновления хендлеров

- ✅ **MainMenuHandler** — Обновлён (использует editMessage)
- ⏳ **TabelHandler** — Требует обновления (6872 строки)
- ⏳ **AdminHandler** — Требует обновления
- ⏳ **ProfileHandler** — Требует обновления
- ⏳ **ProjectHandler** — Требует обновления
- ⏳ **SupervisorHandler** — Требует обновления

---

## Дальнейшие шаги

Рекомендуется постепенно обновлять хендлеры:
1. Сначала самые часто используемые (Табель, Профиль)
2. Затем админ-панель и управление
3. В конце редко используемые

**Важно:** Тестируй после каждого обновления!
