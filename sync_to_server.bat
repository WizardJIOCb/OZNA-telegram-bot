@echo off
echo ========================================
echo  СИНХРОНИЗАЦИЯ С СЕРВЕРОМ
echo ========================================
echo.

REM Проверка наличия исправленного файла
if not exist "CustomCommands\Services\MessageService.php" (
    echo ОШИБКА: MessageService.php не найден!
    pause
    exit /b 1
)

echo Файлы для загрузки на сервер:
echo   - CustomCommands/Services/MessageService.php
echo.
echo ВАЖНО: Загрузите эти файлы на сервер:
echo   Путь на сервере: /var/www/api.ladyzenko.pro/tg/ozna/
echo.
echo Используйте:
echo   - FTP/SFTP клиент (FileZilla, WinSCP)
echo   - SCP команду
echo   - rsync
echo.
pause
