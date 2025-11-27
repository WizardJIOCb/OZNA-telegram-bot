# 🚀 Grok AI - Быстрый старт

## ⚡ 3 простых шага до запуска

### Шаг 1: Получи API ключ (2 минуты)
1. Зайди на https://console.x.ai/
2. Войди в аккаунт (у тебя уже оплачена подписка!)
3. Создай API key → Скопируй его

### Шаг 2: Добавь ключ в конфиг (1 минута)
Открой файл `grok_config.php` и вставь свой ключ:

```php
'grok_api_key' => 'xai-ТВОЙ_КЛЮЧ_СЮДА',  // ← СЮДА!
```

### Шаг 3: Готово! ✅
GrokHandler уже зарегистрирован в системе. Можешь использовать!

---

## 🎮 Как использовать в коде

### 1. Анализ проекта (самое полезное!)

```php
use Longman\TelegramBot\Commands\SystemCommands\Services\GrokService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

// В твоем ProjectHandler.php
$databaseService = new DatabaseService();
$grokService = new GrokService($databaseService);

// Анализируем проект
$result = $grokService->analyzeProjectReport(
    'XXX',                    // Код проекта
    '2025-10-01',            // Дата начала
    '2025-10-31'             // Дата конца
);

if ($result['success']) {
    $text = "🤖 <b>Анализ от Grok AI:</b>\n\n";
    $text .= $result['analysis'];
    
    // Отправь пользователю
    $this->messageService->sendMessage($chatId, $text);
}
```

### 2. Паттерны в заметках

```php
// Анализируй заметки за последние 30 дней
$result = $grokService->analyzeNotesPatterns('XXX', 30);

if ($result['success']) {
    echo "💡 Найденные паттерны:\n";
    echo $result['patterns'];
    echo "\nПроанализировано: {$result['notes_analyzed']} заметок";
}
```

### 3. Прогноз загрузки

```php
// Прогноз на 14 дней
$result = $grokService->predictResourceLoad('ПНР', 14);

if ($result['success']) {
    echo "🎯 Прогноз загрузки:\n";
    echo $result['prediction'];
}
```

### 4. Executive Summary для руководства

```php
// После генерации CSV отчета
$reportData = [...]; // Твои данные из отчета

$result = $grokService->generateExecutiveSummary($reportData);

if ($result['success']) {
    // Красивый краткий отчет для начальства!
    echo $result['summary'];
}
```

### 5. AI-Ассистент (вопросы-ответы)

```php
$result = $grokService->answerProjectQuestion(
    'Сколько часов на электромонтаже проекта XXX?',
    'XXX'  // Код проекта (опционально)
);

if ($result['success']) {
    echo $result['answer'];
}
```

---

## 💡 Где применить в текущем боте?

### В ProjectHandler при генерации отчета:

```php
// После экспорта CSV
if ($user_wants_ai_summary) {  // Добавь кнопку "📊 AI-анализ"
    $grokService = new GrokService($this->databaseService);
    $analysis = $grokService->analyzeProjectReport(
        $projectCode, 
        $startDate, 
        $endDate
    );
    
    if ($analysis['success']) {
        $text = "🤖 <b>Умный анализ проекта {$projectCode}</b>\n\n";
        $text .= $analysis['analysis'];
        
        return $this->messageService->sendMessage($chatId, $text);
    }
}
```

### В NotesHandler при просмотре заметок:

```php
// Добавь кнопку "💡 Найти паттерны"
$keyboard = [
    [
        ['text' => '💡 AI-анализ заметок', 'callback_data' => 'grok_analyze_notes']
    ]
];

// При нажатии:
if ($callback === 'grok_analyze_notes') {
    $grokService = new GrokService($this->databaseService);
    $patterns = $grokService->analyzeNotesPatterns($projectCode, 30);
    
    if ($patterns['success']) {
        $text = "💡 <b>AI нашел паттерны:</b>\n\n";
        $text .= $patterns['patterns'];
        
        return $this->messageService->sendMessage($chatId, $text);
    }
}
```

---

## ⚙️ Настройки (опционально)

### Уровни доступа
Кто может использовать AI? В `grok_config.php`:

```php
'min_access_level' => 2,  // 0=Гость, 1=User, 2=Supervisor, 3=Admin
```

### Включить/выключить функции

```php
'features' => [
    'project_analysis' => true,     // ✅ Вкл
    'notes_patterns' => true,       // ✅ Вкл
    'resource_prediction' => false, // ❌ Выкл
],
```

---

## 🎯 Рекомендуемая интеграция (мой совет)

### 1. Добавь в отчеты по проектам ✨
После кнопки "📊 Экспорт CSV" добавь:
```
[📊 Экспорт CSV] [🤖 AI-анализ]
```

### 2. Добавь в заметки 💡
После списка заметок:
```
[📌 Создать заметку] [💡 Найти паттерны]
```

### 3. Добавь в главное меню (для супервайзеров)
```
[📊 Табель] [👤 Профиль]
[🤖 Grok AI] [❓ Помощь]
```

---

## 📊 Примеры результатов

### Анализ проекта:
```
📊 АНАЛИЗ ПРОЕКТА XXX

ОСНОВНЫЕ НАБЛЮДЕНИЯ:
✅ Выполнено 245 часов работ
✅ Основная активность: электромонтаж
✅ Участвовало 8 человек

⚠️ ПРОБЛЕМЫ:
- Высокий процент простоев (12%)
- Задержки на наладке

🎯 РЕКОМЕНДАЦИИ:
1. Пересмотреть график
2. Добавить 2 монтажников
```

### Паттерны в заметках:
```
💡 НАЙДЕННЫЕ ПАТТЕРНЫ

🔄 ПОВТОРЯЮЩИЕСЯ:
- Несоответствие отверстий (5 раз)
- Нехватка кабеля (3 раза)

🎯 РЕКОМЕНДАЦИИ:
Проверять чертежи ДО монтажа
```

---

## ❓ FAQ

**Q: Сколько стоит?**  
A: ~$1-2 в день при активном использовании

**Q: Безопасно ли?**  
A: Да! Отправляются только коды проектов и часы работ, никаких личных данных

**Q: Работает ли оффлайн?**  
A: Нет, нужен интернет для запросов к Grok API

**Q: Можно ли отключить?**  
A: Да, в `grok_config.php` поставь все `false`

---

## ✅ Checklist

- [ ] Получил API ключ
- [ ] Вставил в `grok_config.php`
- [ ] Протестировал (код выше)
- [ ] Работает! 🎉

---

**Готово! Теперь у тебя есть AI-помощник в боте! 🤖🚀**

*P.S. Если хочешь, я могу прямо сейчас интегрировать Grok в отчеты по проектам — будет очень круто! Скажи слово.*
