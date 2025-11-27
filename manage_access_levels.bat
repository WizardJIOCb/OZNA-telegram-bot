@echo off
setlocal enabledelayedexpansion

echo ====================================================
echo Управление уровнями доступа
echo ====================================================

REM Попытка найти PHP
set PHP_PATH=
for %%X in (php.exe) do (set PHP_PATH=%%~$PATH:X)
if defined PHP_PATH (
    echo PHP найден: %PHP_PATH%
) else (
    echo PHP не найден в PATH
    echo Пожалуйста, укажите путь к PHP.exe:
    set /p PHP_PATH="Путь к PHP.exe: "
    if not exist "!PHP_PATH!" (
        echo Указанный путь к PHP не существует!
        pause
        exit /b 1
    )
)

echo.
echo Выберите действие:
echo 1. Проверить текущие уровни доступа
echo 2. Одобрить заявку на доступ
echo 3. Добавить администратора
echo 4. Сбросить пользователя до уровня Гость
echo 5. Просмотреть заявки на доступ
echo.

set /p choice="Введите номер действия (1-5): "

if "%choice%"=="1" (
    echo Проверка текущих уровней доступа...
    "%PHP_PATH%" check_user_access_levels.php
) else if "%choice%"=="2" (
    echo Одобрение заявки на доступ...
    "%PHP_PATH%" test_approve_access_request.php
) else if "%choice%"=="3" (
    echo Добавление администратора...
    "%PHP_PATH%" add_first_admins.php
) else if "%choice%"=="4" (
    echo Сброс пользователя до уровня Гость...
    "%PHP_PATH%" reset_users_to_guest_level.php
) else if "%choice%"=="5" (
    echo Просмотр заявок на доступ...
    type data\access_requests.json
) else (
    echo Неверный выбор!
)

echo.
pause