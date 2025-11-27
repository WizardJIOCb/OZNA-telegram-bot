# 🐛 ИСПРАВЛЕНО: Ошибка "Не выбран пункт меню"

## Проблема

При нажатии на кнопки:
- 📋 Общий журнал заметок
- 📁 Заметки по проекту

Бот отвечал: **"Не выбран пункт меню"**

## Причина

CallbackqueryCommand не знал про новые callback-данные для выгрузки заметок.

Callback'ы начинаются с `proj_notes_*`, но в списке разрешённых префиксов их не было.

## Решение

Добавлена строка в `CallbackqueryCommand.php` (линия 220):

```php
strpos($callback_data, 'proj_notes_') === 0 ||
```

Теперь CallbackqueryCommand распознаёт все callback'ы для заметок:
- `proj_notes_type_all` - общий журнал
- `proj_notes_type_byproject` - по проекту
- `proj_notes_project_{CODE}` - выбор проекта
- `proj_notes_filter_{STATUS}_{PROJECT}` - фильтр статуса
- `proj_notes_period_{STATUS}_{PROJECT}_{PERIOD}` - период
- `proj_notes_custom_{STATUS}_{PROJECT}` - свои даты
- `proj_notes_filter_back_{PROJECT}` - назад

## Изменённый файл

**`CustomCommands/CallbackqueryCommand.php`** (строка 220)

## Тестирование

После этого исправления кнопки должны работать:

1. Открыть: 📁 Проекты → 📋 Отчёт по проекту → 📌 Выгрузка заметок
2. Нажать: 📋 Общий журнал заметок
3. Должно открыться меню фильтра статуса ✅

---

*Исправлено: 18.10.2025*  
*Статус: ✅ Готово к тестированию*
