@echo off
cls
echo ==========================================
echo Создание тестовой заявки на доступ
echo ==========================================
echo.

REM Создаем JSON файл с тестовой заявкой
echo [
echo     {
echo         "user_id": 123456789,
echo         "user_name": "Тестовый пользователь",
echo         "user_telegram_name": "Test User",
echo         "user_fio": "Иванов Иван Иванович",
echo         "chat_id": "987654321",
echo         "timestamp": %date:~-4%%date:~3,2%%date:~0,2%
echo     }
echo ]> data\access_requests.json

echo Тестовая заявка успешно создана!
echo.
echo Содержимое файла access_requests.json:
echo ------------------------------------------
type data\access_requests.json
echo.
echo ==========================================
echo Готово!
echo ==========================================
pause