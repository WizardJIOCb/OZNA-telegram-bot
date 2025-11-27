# 🔧 Установка CURL Extension для PHP

## Проблема
```
Call to undefined function curl_init()
```

CURL extension не установлен/не включен в PHP на production сервере.

---

## ✅ Решение (на Ubuntu/Debian сервере)

### Вариант 1: Через SSH (РЕКОМЕНДУЕТСЯ)

Подключись к серверу и выполни:

```bash
# 1. Узнай версию PHP
php -v

# Если PHP 8.1:
sudo apt-get update
sudo apt-get install php8.1-curl

# Если PHP 8.0:
sudo apt-get install php8.0-curl

# Если PHP 7.4:
sudo apt-get install php7.4-curl

# 2. Перезапусти Apache
sudo systemctl restart apache2

# 3. Проверь, что curl установлен
php -m | grep curl
```

Должно вывести: `curl`

---

### Вариант 2: Проверка php.ini (если extension уже установлен, но не включен)

```bash
# Найди php.ini
php --ini

# Отредактируй php.ini
sudo nano /etc/php/8.1/apache2/php.ini

# Найди строку (Ctrl+W для поиска):
;extension=curl

# Убери точку с запятой:
extension=curl

# Сохрани (Ctrl+O, Enter, Ctrl+X)

# Перезапусти Apache
sudo systemctl restart apache2
```

---

### Вариант 3: Через панель управления (если есть cPanel/Plesk)

**cPanel:**
1. Software → Select PHP Version
2. Найди "curl" в списке extensions
3. Отметь галочку
4. Save

**Plesk:**
1. Tools & Settings → Updates → PHP Settings
2. Выбери свою версию PHP
3. Включи curl extension

---

## 🧪 Проверка после установки

### 1. Проверь через CLI:
```bash
php -m | grep curl
```

Должно вывести: `curl`

### 2. Создай тестовый файл:
```bash
cd /var/www/api.ladyzenko.pro/tg/ozna
nano test_curl.php
```

Содержимое:
```php
<?php
if (function_exists('curl_init')) {
    echo "✅ CURL extension is installed!\n";
    echo "Version: " . curl_version()['version'] . "\n";
} else {
    echo "❌ CURL extension NOT found!\n";
}
```

Запусти:
```bash
php test_curl.php
```

Должно вывести: `✅ CURL extension is installed!`

### 3. Проверь через веб:
Создай файл `phpinfo.php`:
```php
<?php phpinfo(); ?>
```

Открой в браузере:
```
https://api.ladyzenko.pro/tg/ozna/phpinfo.php
```

Найди секцию "curl" (Ctrl+F "curl")

**ВАЖНО:** После проверки УДАЛИ phpinfo.php (безопасность!)

---

## 🚀 После установки

1. **Тест прямого API:**
```bash
cd /var/www/api.ladyzenko.pro/tg/ozna
php test_grok_direct.php
```

2. **Тест через бота:**
   - Админка → AI-помощник → AI-Ассистент
   - Напиши: "Привет"
   - Должен вернуться ответ от Grok AI

---

## 📊 Команды для диагностики

```bash
# Проверь версию PHP
php -v

# Посмотри все установленные extensions
php -m

# Посмотри конфигурационные файлы PHP
php --ini

# Проверь статус Apache
sudo systemctl status apache2

# Посмотри логи Apache
sudo tail -f /var/log/apache2/error.log
```

---

## ❓ Если не получается

Пришли мне вывод этих команд:
```bash
php -v
php -m | grep curl
php --ini
```

И я подскажу точные команды для твоей конфигурации!

---

## 💡 Временное решение (ТОЛЬКО для теста UI)

Если нужно срочно проверить, что весь flow работает (кроме API), можно временно замокать ответ:

В `GrokService.php` в начале метода `answerProjectQuestion()` добавь:
```php
// TEMPORARY MOCK - REMOVE AFTER CURL IS INSTALLED!
return [
    'success' => true,
    'answer' => '👋 Привет! Это тестовый ответ.\n\n🔧 CURL extension пока не установлен на сервере.\n\nПосле установки CURL я смогу давать реальные ответы от Grok AI!'
];
```

Но это только для проверки UI! Реальный AI не будет работать.

---

## 🎯 Итог

**Простой путь:**
```bash
sudo apt-get update
sudo apt-get install php8.1-curl    # Замени 8.1 на свою версию
sudo systemctl restart apache2
php -m | grep curl                  # Проверка
```

Готово! 🚀
