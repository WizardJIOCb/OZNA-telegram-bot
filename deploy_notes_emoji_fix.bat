@echo off
chcp 65001 >nul
echo ================================================
echo   DEPLOYING NOTES EMOJI UPDATE TO SERVER
echo ================================================
echo.

set SERVER=api.ladyzenko.pro
set USERNAME=root
set REMOTE_PATH=/var/www/api.ladyzenko.pro/tg/ozna

echo 1. Creating backup of server files...
if not exist "backups" mkdir backups
pscp -P 22 %USERNAME%@%SERVER%:%REMOTE_PATH%/CustomCommands/Services/MessageService.php backups\MessageService_before_emoji_fix_%date:~-4,4%%date:~-10,2%%date:~-7,2%.php
pscp -P 22 %USERNAME%@%SERVER%:%REMOTE_PATH%/CustomCommands/Handlers/NotesHandler.php backups\NotesHandler_before_emoji_fix_%date:~-4,4%%date:~-10,2%%date:~-7,2%.php

echo.
echo 2. Uploading fixed files to server...
echo    - MessageService.php (with 📌 emoji changes)...
pscp -P 22 CustomCommands\Services\MessageService.php %USERNAME%@%SERVER%:%REMOTE_PATH%/CustomCommands/Services/MessageService.php

echo    - NotesHandler.php (with 📌 emoji change)...
pscp -P 22 CustomCommands\Handlers\NotesHandler.php %USERNAME%@%SERVER%:%REMOTE_PATH%/CustomCommands/Handlers/NotesHandler.php

echo.
echo 3. Clearing PHP cache on server...
plink -P 22 %USERNAME%@%SERVER% "cd %REMOTE_PATH% && php -r \"if (function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache cleared\n'; } else { echo 'OPcache not enabled\n'; }\""

echo.
echo 4. Testing syntax on server...
plink -P 22 %USERNAME%@%SERVER% "php -l %REMOTE_PATH%/CustomCommands/Services/MessageService.php"
plink -P 22 %USERNAME%@%SERVER% "php -l %REMOTE_PATH%/CustomCommands/Handlers/NotesHandler.php"

echo.
echo ================================================
echo   DEPLOYMENT COMPLETED!
echo ================================================
echo.
echo Changes deployed:
echo  ✓ Changed 📝 to 📌 in 4 places in MessageService.php
echo  ✓ Changed 📝 to 📌 in NotesHandler.php header
echo  ✓ Added 📌 Заметки button to ШМР Project Activity menu
echo.
echo NEXT STEPS:
echo 1. Open your Telegram bot
echo 2. Send /start command
echo 3. Test the buttons - they should work now!
echo.
echo If buttons still don't work, check server logs with:
echo    plink -P 22 %USERNAME%@%SERVER% "tail -f %REMOTE_PATH%/telegram_bot_error.log"
echo.
pause
