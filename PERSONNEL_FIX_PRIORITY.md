# 🐛 Исправления: Personnel Handler

## Проблема #1: Приоритет Handlerа
При нажатии на кнопку "👨‍💼 Персонал" получали ошибку:
```
❌ Неизвестная команда управления уровнями доступа
```

### Причина
AccessLevelHandler имел более высокий приоритет (25) чем PersonnelHandler (26).

В системе обработки callbacks:
- **МЕНЬШЕЕ число = БОЛЕЕ ВЫСОКИЙ приоритет**

### Решение
Изменили приоритет PersonnelHandler с 26 на **24**

**Файл:** `CustomCommands/Handlers/PersonnelHandler.php` - строка 106

---

## Проблема #2: Отсутствие префикса в CallbackHandlerFactory

При нажатии на кнопку "👨‍💼 Персонал" продолжала появляться ошибка:
```
❌ Неизвестная команда управления персоналом
```

### Причина
В `CallbackHandlerFactory::extractCategory()` не было обработки префикса `personnel_`.

Callback `personnel_main` приходил, но category не извлекалась, и PersonnelHandler получал пустую строку.

### Решение
Добавили обработку `personnel_` префикса в `extractCategory()`:

```php
// Handle personnel callbacks (personnel_ prefix) - return full callback data
if (strpos($callbackData, 'personnel_') === 0) {
    $category = $callbackData; // Pass the full callback data to PersonnelHandler
    error_log("CallbackHandlerFactory extracted personnel category: '{$category}' from callback data: '{$callbackData}'");
    return $category;
}
```

**Файл:** `CustomCommands/Services/CallbackHandlerFactory.php` - строка ~167

### Было:
```php
public function getPriority(): int
{
    return 26; // Between AccessLevelHandler (25) and other handlers
}
```

### Стало:
```php
public function getPriority(): int
{
    return 24; // Higher priority than AccessLevelHandler (25)
}
```

## Результат
Теперь порядок проверки handlers:
1. AdminHandler (30) - самый высокий
2. **PersonnelHandler (24)** ← НОВОЕ
3. AccessLevelHandler (25)
4. Остальные handlers...

PersonnelHandler теперь обрабатывает все `personnel_*` callbacks до того, как их увидит AccessLevelHandler.

## Файл
`CustomCommands/Handlers/PersonnelHandler.php` - строка 106

## Статус
✅ **ИСПРАВЛЕНО** - Готово к повторному тестированию
