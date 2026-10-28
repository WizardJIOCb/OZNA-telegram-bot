#!/bin/bash
# ============================================================
# ОЗНА PWA — Скрипт развертывания
# Запуск: bash deploy_pwa.sh
# ============================================================

set -e

echo "=========================================="
echo "  ОЗНА PWA — Развертывание"
echo "=========================================="
echo ""

# Определяем корневую директорию (там, где лежит этот скрипт)
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
BOT_ROOT="$SCRIPT_DIR"

echo "[1/4] Создание таблицы pwa_users..."
echo "---"

# Читаем данные подключения из config
DB_HOST="82.146.42.213"
DB_USER="WizardJIOCb"
DB_PASS="7qQ#28051961"
DB_NAME="tg_bot"

mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<'EOSQL'
CREATE TABLE IF NOT EXISTS pwa_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    linked_telegram_id BIGINT NULL UNIQUE,
    access_level TINYINT DEFAULT 0,
    fio VARCHAR(255),
    functional_direction VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_telegram_id (linked_telegram_id),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOSQL

if [ $? -eq 0 ]; then
    echo "  OK: Таблица pwa_users создана (или уже существует)"
else
    echo "  ОШИБКА: Не удалось создать таблицу. Проверьте доступ к БД."
    exit 1
fi
echo ""

echo "[2/4] Заполнение pwa_users существующими пользователями..."
echo "---"
php "$BOT_ROOT/api/migrations/seed_pwa_users.php"
echo ""

echo "[3/4] Проверка директории /app/ для фронтенда..."
echo "---"
if [ -d "$BOT_ROOT/app" ] && [ -f "$BOT_ROOT/app/index.html" ]; then
    echo "  OK: Директория app/ найдена, index.html на месте"
    echo "  Файлы:"
    ls -la "$BOT_ROOT/app/"
else
    echo "  ОШИБКА: Директория app/ не найдена или index.html отсутствует"
    echo "  Убедитесь что app/ загружена на сервер"
    exit 1
fi
echo ""

echo "[4/4] Проверка директории /api/ для бэкенда..."
echo "---"
if [ -d "$BOT_ROOT/api" ] && [ -f "$BOT_ROOT/api/index.php" ]; then
    echo "  OK: Директория api/ найдена, index.php на месте"
    
    # Проверяем что activity-tree.json на месте
    if [ -f "$BOT_ROOT/api/data/activity-tree.json" ]; then
        echo "  OK: activity-tree.json найден"
    else
        echo "  ПРЕДУПРЕЖДЕНИЕ: activity-tree.json не найден в api/data/"
    fi
else
    echo "  ОШИБКА: Директория api/ не найдена или index.php отсутствует"
    exit 1
fi
echo ""

echo "[INFO] Проверка health endpoint..."
echo "---"
# Пробуем обратиться к API локально (если Apache уже настроен)
HEALTH_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/api/health" 2>/dev/null || echo "000")
if [ "$HEALTH_RESPONSE" = "200" ]; then
    echo "  OK: API health endpoint отвечает (HTTP 200)"
else
    echo "  INFO: API health endpoint недоступен локально (HTTP $HEALTH_RESPONSE)"
    echo "  Это нормально если Apache ещё не настроен для /api/ роутинга"
fi
echo ""

echo "=========================================="
echo "  Развертывание завершено!"
echo "=========================================="
echo ""
echo "Следующие шаги:"
echo ""
echo "1. Настройте Apache для обработки /api/ и /app/ роутов:"
echo "   - /app/  -> статические файлы из $BOT_ROOT/app/"
echo "   - /api/  -> PHP через $BOT_ROOT/api/index.php"
echo ""
echo "   Пример для Apache VirtualHost (добавить в конфиг):"
echo "   ---"
echo "   Alias /app $BOT_ROOT/app"
echo "   <Directory $BOT_ROOT/app>"
echo "       AllowOverride All"
echo "       Require all granted"
echo "   </Directory>"
echo ""
echo "   Alias /api $BOT_ROOT/api"
echo "   <Directory $BOT_ROOT/api>"
echo "       AllowOverride All"
echo "       Require all granted"
echo "   </Directory>"
echo "   ---"
echo ""
echo "2. Раздайте логины/пароли пользователям"
echo "   CSV файл с паролями сохранен в: $BOT_ROOT/api/migrations/"
echo ""
echo "3. Откройте https://ваш-домен/app/ в браузере для проверки"
echo ""
echo "4. Тест в ТГ боте: Профиль -> 🌐 Внешнее подключение -> Установить пароль"
echo ""
