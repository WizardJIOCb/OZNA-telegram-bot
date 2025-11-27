# Исправление: Добавлен счетчик заявок на кнопку "Заявки на доступ"

## Дата исправления
2025-10-19

## Описание проблемы
В админ-панели бота кнопка "👥 Заявки на доступ" не отображала количество pending заявок, хотя они были в системе. Это затрудняло администраторам понимание того, есть ли новые заявки на рассмотрение.

## Решение
Добавлен счетчик pending заявок на кнопку "Заявки на доступ" аналогично тому, как это реализовано для кнопки "Проекты на согласование".

## Внесенные изменения

### Файл: `CustomCommands/Handlers/AdminHandler.php`

**Метод**: `showAdminMainMenu()` (строки ~265-295)

**Было**:
```php
$keyboard[] = [
    ['text' => '👥 Заявки на доступ', 'callback_data' => 'category_Админка_Заявки']
];
```

**Стало**:
```php
// Добавляем кнопку заявок на доступ с счетчиком
try {
    $databaseService = $this->adminService->getDatabaseService();
    $accessRequestsCount = count($databaseService->getPendingAccessRequests());
    
    $accessButtonText = '👥 Заявки на доступ';
    if ($accessRequestsCount > 0) {
        $accessButtonText .= " ({$accessRequestsCount})";
    }
    
    $keyboard[] = [
        ['text' => $accessButtonText, 'callback_data' => 'category_Админка_Заявки']
    ];
} catch (\Exception $e) {
    error_log("AdminHandler::showAdminMainMenu - Error adding access requests button: " . $e->getMessage());
    // Fallback без счетчика если ошибка
    $keyboard[] = [
        ['text' => '👥 Заявки на доступ', 'callback_data' => 'category_Админка_Заявки']
    ];
}
```

## Как работает

1. При открытии админ-панели код запрашивает количество pending заявок из базы данных
2. Метод `DatabaseService::getPendingAccessRequests()` возвращает массив заявок со статусом "pending"
3. Подсчитывается количество заявок через `count()`
4. Если заявок больше 0, к тексту кнопки добавляется счетчик в круглых скобках
5. В случае ошибки при получении данных используется fallback без счетчика (для стабильности)

## Примеры отображения

- **Нет заявок**: `👥 Заявки на доступ`
- **1 заявка**: `👥 Заявки на доступ (1)`
- **4 заявки**: `👥 Заявки на доступ (4)`
- **10 заявок**: `👥 Заявки на доступ (10)`

## Преимущества

✅ Администраторы сразу видят количество ожидающих заявок  
✅ Не нужно открывать раздел чтобы узнать есть ли новые заявки  
✅ Согласованность UI - аналогичный подход используется для "Проекты на согласование"  
✅ Стабильность - в случае ошибки кнопка отображается без счетчика  

## Тестирование

Созданы тестовые скрипты:
1. `test_access_requests_counter.php` - проверка работы счетчика
2. `check_access_requests_table.php` - проверка содержимого таблицы заявок

### Запуск тестов:
```bash
php test_access_requests_counter.php
php check_access_requests_table.php
```

## Технические детали

- **Используемый метод**: `DatabaseService::getPendingAccessRequests()`
- **SQL запрос**: `SELECT * FROM access_requests WHERE status = 'pending' ORDER BY timestamp DESC`
- **Обработка ошибок**: Try-catch блок с fallback на кнопку без счетчика
- **Производительность**: Минимальное влияние, т.к. запрос выполняется только при открытии админ-панели

## Связанные файлы

- `CustomCommands/Handlers/AdminHandler.php` - основная логика
- `CustomCommands/Services/DatabaseService.php` - метод `getPendingAccessRequests()`
- `test_access_requests_counter.php` - тестовый скрипт
- `check_access_requests_table.php` - скрипт проверки БД

## Результат

✅ **Проблема решена**  
Кнопка "Заявки на доступ" теперь отображает количество pending заявок в круглых скобках, как и просил пользователь.
