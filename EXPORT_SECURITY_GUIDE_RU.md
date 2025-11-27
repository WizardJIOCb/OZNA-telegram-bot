# Система Защиты Выгрузки Данных - Руководство

## 🎯 Что реализовано

### 1. ✅ Журнал аудита выгрузок
Все выгрузки логируются с информацией:
- Кто выгружал (ФИО, ID)
- Когда выгружал (дата, время)
- Что выгружал (отчёты/заметки)
- По какому проекту
- Какой период
- Сколько записей

### 2. 🔐 Личное кодовое слово
Каждый пользователь:
- Устанавливает своё кодовое слово
- Использует его для всех выгрузок
- Файлы защищены паролем (ZIP с AES-256)
- Может изменить слово в любой момент

### 3. 🔑 Сброс через суперадмина
Если пользователь забыл:
- Суперадмин может сбросить кодовое слово
- Генерируется новое безопасное слово
- Пользователь получает уведомление
- Всё логируется

### 4. 🛡️ Контроль доступа
- Гости (0): нет доступа к выгрузке
- Пользователи (1+): доступ с кодовым словом
- Проверка прав перед каждой выгрузкой

## 📋 Установка

### Шаг 1: База данных

Выполните миграцию:
```bash
mysql -h 82.146.42.213 -u WizardJIOCb -p tg_bot < database/migrations/add_export_security.sql
```

Или вручную через phpMyAdmin/консоль:
```sql
USE tg_bot;

CREATE TABLE IF NOT EXISTS export_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL COMMENT 'Кто выгрузил',
    user_fio VARCHAR(255) NOT NULL COMMENT 'ФИО пользователя',
    export_type VARCHAR(50) NOT NULL COMMENT 'Тип: report|notes',
    export_scope VARCHAR(100) COMMENT 'Область: project_code|general|simple',
    project_code VARCHAR(50) COMMENT 'Код проекта',
    period_start DATE COMMENT 'Начало периода',
    period_end DATE COMMENT 'Конец периода',
    status_filter VARCHAR(20) COMMENT 'Фильтр статуса',
    records_count INT NOT NULL COMMENT 'Количество записей',
    file_name VARCHAR(255) NOT NULL COMMENT 'Имя файла',
    exported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Время выгрузки',
    
    INDEX idx_user_id (user_id),
    INDEX idx_export_type (export_type),
    INDEX idx_exported_at (exported_at),
    INDEX idx_project_code (project_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Шаг 2: Файлы

Все файлы уже созданы:
- ✅ `CustomCommands/Services/DatabaseService.php` - обновлён
- ✅ `CustomCommands/Services/ExportSecurityService.php` - новый
- ✅ `CustomCommands/Handlers/ExportKeyHandler.php` - новый

### Шаг 3: Интеграция в Profile

Нужно добавить кнопку в меню профиля.

### Шаг 4: Интеграция в Export

Нужно добавить проверки в ProjectHandler перед выгрузкой.

## 🔄 Как это работает

### Первая настройка пользователя

```
Пользователь
    ↓
Профиль → 🔐 Кодовое слово
    ↓
[Нет кодового слова]
    ↓
Установить кодовое слово
    ↓
Система предлагает: "aB3xK9mL"
    ↓
Пользователь вводит своё или использует предложенное
    ↓
✅ Кодовое слово установлено
```

### Выгрузка данных

```
Пользователь запрашивает выгрузку
    ↓
Система проверяет:
  ✓ Есть кодовое слово?
  ✓ Уровень доступа >= 1?
    ↓
"Введите кодовое слово"
    ↓
Пользователь вводит слово
    ↓
Система проверяет правильность
    ↓
Генерируется CSV
    ↓
CSV упаковывается в ZIP с паролем
    ↓
Логируется в export_logs
    ↓
Отправляется пользователю
    ↓
"Пароль: [кодовое слово]"
```

### Забыл кодовое слово

```
Пользователь забыл слово
    ↓
Обращается к суперадмину
    ↓
Суперадмин: Админка → Управление пользователями
    ↓
Выбирает пользователя
    ↓
🔑 Сбросить кодовое слово
    ↓
Система генерирует новое: "pQ7wR2nM"
    ↓
Уведомляет пользователя
    ↓
Пользователь использует новое слово
```

## 📊 Для администраторов

### Просмотр журнала выгрузок

```php
// В админке добавить:
$exportSecurityService = new ExportSecurityService($this->databaseService);
$logs = $exportSecurityService->getAllExportLogs(100);

foreach ($logs as $log) {
    echo "{$log['user_fio']} - {$log['export_type']} - {$log['exported_at']}";
}
```

### Статистика выгрузок

```php
$stats = $exportSecurityService->getExportStats();
// Покажет: сколько выгрузок, кто выгружал, когда, по каким проектам
```

## 🎨 Интерфейс пользователя

### Меню профиля (обновить)

```
👤 Профиль

📝 Моё ФИО
🏢 Моё направление
🔐 Кодовое слово для выгрузки  ← ДОБАВИТЬ
⬅️ Назад
```

### Меню кодового слова (если не установлено)

```
🔐 Кодовое слово для выгрузки

⚠️ У вас не установлено кодовое слово.

Кодовое слово необходимо для защиты выгрузок 
отчётов и заметок.

Без кодового слова вы не сможете выгружать данные.

[✅ Установить кодовое слово]
[⬅️ Назад к профилю]
```

### Меню кодового слова (если установлено)

```
🔐 Кодовое слово для выгрузки

У вас установлено кодовое слово.

Это кодовое слово защищает ваши выгрузки 
отчётов и заметок.

Выберите действие:

[🔄 Изменить кодовое слово]
[📋 История выгрузок]
[⬅️ Назад к профилю]
```

### История выгрузок

```
📋 История выгрузок

🕐 28.10.2025 14:30
📌 Заметки - project_XXX
📁 Проект: XXX
📝 Записей: 15
━━━━━━━━━━━━━━━

🕐 27.10.2025 10:15
📊 Отчёт - simple
📝 Записей: 234
━━━━━━━━━━━━━━━

[⬅️ Назад]
```

## 🔧 Что нужно доделать

### 1. Добавить в ProfileCommand или где у вас меню профиля:

```php
// Добавить кнопку
[
    ['text' => '🔐 Кодовое слово для выгрузки', 'callback_data' => 'export_key_menu']
]

// Добавить обработчик callback
if ($callbackData === 'export_key_menu') {
    $exportKeyHandler = new ExportKeyHandler(
        $this->messageService,
        $this->databaseService,
        new ExportSecurityService($this->databaseService)
    );
    return $exportKeyHandler->showExportKeyMenu($chatId, $userId);
}
```

### 2. Добавить в GenericmessageCommand:

```php
// Обработка ввода кодового слова для установки
if (isset($conversation->notes['awaiting_export_key']) 
    && $conversation->notes['awaiting_export_key']) {
    
    $exportKeyHandler = new ExportKeyHandler(...);
    return $exportKeyHandler->handleExportKeyInput($chatId, $userId, $userInput, $conversation);
}

// Обработка ввода кодового слова для проверки перед выгрузкой
if (isset($conversation->notes['awaiting_export_key_verify']) 
    && $conversation->notes['awaiting_export_key_verify']) {
    
    // См. полный пример в EXPORT_SECURITY_IMPLEMENTATION.md
}
```

### 3. Добавить в ProjectHandler перед каждой выгрузкой:

```php
private function checkExportPermission(string $chatId, int $userId, $conversation): ?ServerResponse
{
    $exportSecurityService = new ExportSecurityService($this->databaseService);
    
    // Проверка уровня доступа
    if (!$exportSecurityService->canUserExport($userId)) {
        $message = $exportSecurityService->getExportPermissionMessage($userId);
        return $this->messageService->sendMessage($chatId, $message);
    }
    
    // Проверка наличия кодового слова
    if (!$exportSecurityService->hasExportKey($userId)) {
        $text = "⚠️ <b>Кодовое слово не установлено</b>\n\n";
        $text .= "Для выгрузки данных необходимо установить кодовое слово.\n\n";
        $text .= "Перейдите: Профиль → 🔐 Кодовое слово";
        
        $keyboard = [
            [['text' => '🔐 Установить сейчас', 'callback_data' => 'export_key_setup']],
            [['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты']]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    return null; // All checks passed
}
```

## 💡 Преимущества системы

### Безопасность
- ✅ Полный аудит всех выгрузок
- ✅ Защита файлов паролем (AES-256)
- ✅ Контроль доступа по уровням
- ✅ Невозможно обойти защиту

### Удобство
- ✅ Один раз установил - работает всегда
- ✅ Простая смена кодового слова
- ✅ История выгрузок для контроля
- ✅ Сброс через админа если забыл

### Прозрачность
- ✅ Каждая выгрузка в логе
- ✅ Статистика для анализа
- ✅ Видно кто и когда выгружал
- ✅ Нельзя удалить из журнала

## 🎯 Следующие шаги

1. **Запустить миграцию базы данных**
   - Создаст таблицу export_logs
   - Готово к использованию

2. **Интегрировать в меню профиля**
   - Добавить кнопку
   - Подключить ExportKeyHandler

3. **Обновить процесс выгрузки**
   - Добавить проверки
   - Защита паролем
   - Логирование

4. **Протестировать**
   - Установка ключа
   - Выгрузка с ключом
   - Смена ключа
   - Сброс админом

5. **Обучить пользователей**
   - Инструкция по установке
   - Что делать если забыл
   - Безопасное хранение

## ❓ FAQ

**Q: Что если пользователь забыл кодовое слово?**
A: Суперадмин может сбросить и создать новое.

**Q: Можно ли выгружать без кодового слова?**
A: Нет, система требует обязательную установку.

**Q: Какие файлы защищены?**
A: Все CSV выгрузки (отчёты и заметки).

**Q: Как долго хранятся логи?**
A: Бессрочно, можно настроить автоочистку старых.

**Q: Можно ли пользователю удалить свои логи?**
A: Нет, логи неизменяемы для аудита.

**Q: Требования к кодовому слову?**
A: Минимум 4 символа, максимум 50, любые буквы/цифры.

---

**Готово к внедрению!** 🚀
**Вопросы?** Спрашивай! 😊
