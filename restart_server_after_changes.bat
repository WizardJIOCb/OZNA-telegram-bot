@echo off
chcp 65001 >nul
echo ================================================
echo   RESTARTING SERVER AFTER CODE CHANGES
echo ================================================
echo.

set SERVER=api.ladyzenko.pro
set USERNAME=root
set REMOTE_PATH=/var/www/api.ladyzenko.pro/tg/ozna

echo 1. Checking syntax on server...
echo    - MessageService.php...
plink -P 22 %USERNAME%@%SERVER% "php -l %REMOTE_PATH%/CustomCommands/Services/MessageService.php"
echo    - NotesHandler.php...
plink -P 22 %USERNAME%@%SERVER% "php -l %REMOTE_PATH%/CustomCommands/Handlers/NotesHandler.php"

echo.
echo 2. Clearing OPcache...
plink -P 22 %USERNAME%@%SERVER% "cd %REMOTE_PATH% && php -r \"if (function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache cleared successfully\n'; } else { echo 'OPcache not enabled\n'; }\""

echo.
echo 3. Clearing file cache...
plink -P 22 %USERNAME%@%SERVER% "cd %REMOTE_PATH% && php clear_cache.php"

echo.
echo 4. Restarting PHP-FPM...
plink -P 22 %USERNAME%@%SERVER% "systemctl restart php-fpm"

echo.
echo 5. Checking PHP-FPM status...
plink -P 22 %USERNAME%@%SERVER% "systemctl status php-fpm | head -10"

echo.
echo 6. Restarting Nginx...
plink -P 22 %USERNAME%@%SERVER% "systemctl restart nginx"

echo.
echo 7. Checking Nginx status...
plink -P 22 %USERNAME%@%SERVER% "systemctl status nginx | head -10"

echo.
echo ================================================
echo   SERVER RESTART COMPLETED!
echo ================================================
echo.
echo The bot should now work with the updated code.
echo.
echo NEXT STEPS:
echo 1. Open Telegram and send /start to the bot
echo 2. Navigate to: ШМР → Проектная деятельность
echo 3. You should see the 📌 Заметки button
echo.
echo To monitor live logs:
echo    plink -P 22 %USERNAME%@%SERVER% "tail -f %REMOTE_PATH%/telegram_bot_error.log"
echo.
pause
