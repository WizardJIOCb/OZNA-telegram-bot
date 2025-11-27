@echo off
REM СКРИПТ ДЛЯ ВКЛЮЧЕНИЯ PHP EXTENSIONS

echo ========================================
echo  ВКЛЮЧЕНИЕ OPENSSL И CURL В PHP
echo ========================================
echo.

REM Найти php.ini
set PHP_INI=
if exist "C:\php\php.ini" set PHP_INI=C:\php\php.ini
if exist "C:\PHP\php.ini" set PHP_INI=C:\PHP\php.ini
if exist "C:\Windows\php.ini" set PHP_INI=C:\Windows\php.ini
if exist "%ProgramFiles%\PHP\php.ini" set PHP_INI=%ProgramFiles%\PHP\php.ini

if "%PHP_INI%"=="" (
    echo ОШИБКА: Файл php.ini не найден!
    echo.
    echo Пожалуйста, найдите файл php.ini вручную и:
    echo 1. Откройте его в Notepad
    echo 2. Найдите строки:
    echo    ;extension=php_openssl.dll
    echo    ;extension=php_curl.dll
    echo 3. Уберите точку с запятой в начале
    echo 4. Сохраните файл
    echo 5. Перезапустите Apache/IIS
    pause
    exit /b 1
)

echo Найден php.ini: %PHP_INI%
echo.

REM Создать backup
set BACKUP=%PHP_INI%.backup_%date:~-4,4%%date:~-7,2%%date:~-10,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set BACKUP=%BACKUP: =0%
copy "%PHP_INI%" "%BACKUP%" >nul
echo Backup создан: %BACKUP%
echo.

REM Включить extensions
powershell -Command "(Get-Content '%PHP_INI%') -replace ';extension=php_openssl', 'extension=php_openssl' -replace ';extension=php_curl', 'extension=php_curl' | Set-Content '%PHP_INI%'"

echo ✅ Расширения включены!
echo.
echo ⚠️  ВАЖНО: Теперь нужно перезапустить веб-сервер!
echo.
echo Для Apache: net stop Apache2.4 ^&^& net start Apache2.4
echo Для IIS: iisreset
echo.
pause
