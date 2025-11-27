#!/bin/bash
# Скрипт автоматического деплоя бота
# Замените переменные на ваши данные

SERVER_USER="your_user"
SERVER_HOST="your_server.com"
SERVER_PATH="/path/to/your/bot"
LOCAL_PATH="."

echo "=== АВТОМАТИЧЕСКИЙ ДЕПЛОЙ БОТА ==="
echo "Сервер: $SERVER_USER@$SERVER_HOST"
echo "Путь: $SERVER_PATH"
echo ""

# 1. Создаём бэкап данных локально
echo "1. Создание локального бэкапа..."
php create_backup.php

# 2. Создаём бэкап на сервере
echo "2. Создание бэкапа на сервере..."
BACKUP_NAME="backup_$(date +%Y-%m-%d_%H-%M-%S)"
ssh $SERVER_USER@$SERVER_HOST "cp -r $SERVER_PATH/data $SERVER_PATH/${BACKUP_NAME}_data"

# 3. Копируем код
echo "3. Копирование кода..."
rsync -avz --exclude='data/' --exclude='backups/' --exclude='.git/' $LOCAL_PATH/ $SERVER_USER@$SERVER_HOST:$SERVER_PATH/

# 4. Копируем данные отдельно
echo "4. Копирование данных..."
scp -r data/ $SERVER_USER@$SERVER_HOST:$SERVER_PATH/

# 5. Устанавливаем права
echo "5. Установка прав доступа..."
ssh $SERVER_USER@$SERVER_HOST "chmod 755 $SERVER_PATH/data && chmod 644 $SERVER_PATH/data/*.json"

# 6. Проверяем результат
echo "6. Проверка результата..."
ssh $SERVER_USER@$SERVER_HOST "cd $SERVER_PATH && php check_server_data.php"

echo ""
echo "=== ДЕПЛОЙ ЗАВЕРШЁН ==="
echo "Бэкап сервера сохранён как: ${BACKUP_NAME}_data"