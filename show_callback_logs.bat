@echo off
echo === ПРОСМОТР ЛОГОВ CALLBACK ЗАПРОСОВ ===
echo.
echo Последние 50 строк лога:
echo.
type c:\ozna_bot\telegram_bot_error.log | findstr /C:"CALLBACK" /C:"acc_lvl" /C:"AccessLevel"
echo.
echo === КОНЕЦ ЛОГОВ ===
pause
