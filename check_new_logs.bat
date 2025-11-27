@echo off
chcp 65001 >nul
echo Checking for new logs...
echo.
echo Last 5 lines of log:
echo ==================
powershell -Command "Get-Content c:\ozna_bot\telegram_bot_error.log -Tail 5 -Encoding UTF8"
echo ==================
echo.
echo Waiting for new activity... (press Ctrl+C to stop)
echo Try pressing buttons in the bot NOW!
echo.
powershell -Command "Get-Content c:\ozna_bot\telegram_bot_error.log -Wait -Tail 10 -Encoding UTF8"
