# 🔍 Руководство по отладке Grok AI

## Проблема: "Думает, но не возвращается"

Бот показывает "⏳ Думаю над ответом...", но ответ не приходит.

---

## 📋 Что добавлено для отладки

### 1. **Детальное логирование**

Все компоненты теперь пишут подробные логи:

#### GrokService.php:
- ✅ Запрос API с полным payload
- ✅ HTTP статус коды
- ✅ CURL ошибки
- ✅ Время выполнения запроса
- ✅ Структура ответа
- ✅ Проблемы с JSON

#### GrokHandler.php:
- ✅ Получение вопроса
- ✅ Вызов GrokService
- ✅ Результат от GrokService
- ✅ Успех/ошибка обработки
- ✅ Exception с trace

#### GenericmessageCommand.php:
- ✅ Получение текста вопроса
- ✅ Создание GrokHandler
- ✅ Вызов processQuestion
- ✅ Exception handling

### 2. **Улучшенная обработка ошибок**

- ✅ Try-catch блоки везде
- ✅ Понятные сообщения пользователю
- ✅ Кнопки "Попробовать снова"
- ✅ Таймауты: 30 сек connection, 60 сек total

---

## 🔧 Как проверить, что не так

### Шаг 1: Прямой тест API

На production сервере (где есть curl):

```bash
cd /var/www/ozna_bot
php test_grok_direct.php
```

Этот скрипт:
- Проверит наличие curl
- Отправит простой запрос к Grok API
- Покажет HTTP статус, время ответа, токены
- Выявит проблемы с API key или сетью

**Ожидаемый результат:**
```
=== DIRECT GROK API TEST ===
...
10. SUCCESS! ✓
    Answer: Привет! Как дела?
...
Grok API is working correctly! ✓
```

---

### Шаг 2: Проверка логов Apache

```bash
# Последние Grok-логи
tail -n 100 /var/log/apache2/error.log | grep Grok

# Мониторинг в реальном времени
tail -f /var/log/apache2/error.log | grep --line-buffered Grok
```

Или используй скрипт:
```bash
bash check_grok_logs.sh
```

**Что искать в логах:**

✅ **Нормальный flow:**
```
GenericmessageCommand: Handling Grok question for user 123...
GenericmessageCommand: Creating GrokHandler instance
GenericmessageCommand: GrokHandler created successfully
GenericmessageCommand: Calling GrokHandler->processQuestion()
GrokHandler: Processing question from chat 123...
GrokHandler: Sent 'thinking' message...
GrokHandler: Calling GrokService->answerProjectQuestion()
GrokService: answerProjectQuestion() called...
GrokService: Making API request to Grok...
GrokService: Request completed in 3.5s with HTTP code 200
GrokService: Response received, length: 500 bytes
GrokService: Successfully decoded JSON response
GrokService: Got answer, length: 200 chars
GrokHandler: GrokService returned: {"success":true,...}
GrokHandler: Success! Answer length: 200
GrokHandler: Sending final response...
GenericmessageCommand: GrokHandler->processQuestion() completed
```

❌ **Проблемы:**

**A) API Key неверный:**
```
GrokService: API request failed with code 401
GrokService: Response body: {"error":"Invalid API key"}
```

**B) Таймаут:**
```
GrokService: CURL error: Operation timed out after 60000 milliseconds
```

**C) Нет curl:**
```
GrokService: CURL error: ...
```

**D) Неожиданный формат ответа:**
```
GrokService: Response missing expected structure
GrokService: Full response: {"error":"rate_limit"}
```

**E) Exception в коде:**
```
GrokHandler: EXCEPTION in processQuestion: ...
```

---

### Шаг 3: Тест через бота

1. Админка → AI-помощник → AI-Ассистент
2. Напиши: "Привет"
3. Смотри логи в реальном времени:

```bash
tail -f /var/log/apache2/error.log | grep --line-buffered Grok
```

---

## 🐛 Возможные причины проблемы

### 1. **API Key проблема**
- ❌ Ключ истек
- ❌ Ключ неверный
- ❌ Недостаточно credits на аккаунте

**Решение:**
- Проверь в личном кабинете xAI: https://console.x.ai
- Проверь баланс
- Создай новый ключ если нужно
- Обнови в `grok_config.php`

### 2. **Rate Limit**
- ❌ Превышен лимит запросов в минуту

**Решение:**
- Подожди 1 минуту
- Проверь plan в xAI console

### 3. **Network/Firewall**
- ❌ Сервер не может достучаться до api.x.ai
- ❌ Firewall блокирует исходящие запросы

**Решение:**
```bash
# Проверь доступность API
curl -I https://api.x.ai/v1/chat/completions

# Должен вернуть: HTTP/2 405 (Method Not Allowed - это нормально)
```

### 4. **PHP/Apache проблемы**
- ❌ Memory limit
- ❌ Execution time limit
- ❌ curl extension отключен

**Решение:**
Проверь `php.ini`:
```ini
max_execution_time = 120
memory_limit = 256M
```

### 5. **Exception в коде**
- ❌ DatabaseService ошибка
- ❌ MessageService ошибка

**Решение:**
Смотри полный stack trace в логах

---

## 📊 Checklist для диагностики

Проверь по порядку:

- [ ] 1. API key правильный в `grok_config.php`
- [ ] 2. Баланс в xAI console > 0
- [ ] 3. curl extension доступен (`php -m | grep curl`)
- [ ] 4. Сервер может достучаться до api.x.ai
- [ ] 5. `test_grok_direct.php` работает успешно
- [ ] 6. В логах нет EXCEPTION
- [ ] 7. HTTP code = 200
- [ ] 8. Response structure валидна
- [ ] 9. MessageService может отправлять сообщения
- [ ] 10. Нет rate limit ошибок

---

## 🔄 Что изменилось в коде

### GrokService.php
- Добавлены timeouts (30s connect, 60s total)
- Детальное логирование каждого шага
- Try-catch обработка
- Проверка структуры response

### GrokHandler.php
- Try-catch в processQuestion()
- Логирование результата от GrokService
- Понятные сообщения об ошибках
- Кнопки для повторной попытки

### GenericmessageCommand.php
- Try-catch при создании GrokHandler
- Логирование всех шагов
- Exception handling

---

## 🚀 Следующие шаги

1. **Запусти тест API:**
   ```bash
   php test_grok_direct.php
   ```

2. **Если тест успешен**, протестируй через бота и смотри логи

3. **Если тест НЕ успешен**, проблема в:
   - API key
   - Network
   - Credentials

4. **Пришли мне логи:**
   ```bash
   tail -n 200 /var/log/apache2/error.log | grep Grok > grok_debug.log
   ```

---

## 💡 Временное решение (если API не работает)

Можно временно сделать mock ответ для тестирования:

В `GrokService.php` добавь в начало `answerProjectQuestion()`:
```php
// TEMPORARY: Mock response for testing
return [
    'success' => true,
    'answer' => 'Это тестовый ответ. API будет подключен после проверки.'
];
```

Это позволит проверить, что весь flow работает, кроме реального API.

---

## 📞 Контакты

Если нужна помощь, пришли:
1. Вывод `test_grok_direct.php`
2. Последние 200 строк логов с Grok
3. Скриншот из xAI console (баланс, план)

Удачи в отладке! 🚀
