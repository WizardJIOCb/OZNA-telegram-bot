@echo off
echo Создание тестовой заявки на доступ...
echo.

REM Проверяем, существует ли файл access_requests.json
if not exist "data\access_requests.json" (
    echo Файл access_requests.json не найден!
    echo Создаем новый файл...
    echo [] > "data\access_requests.json"
)

REM Создаем временный PHP-скрипт
echo ^<?php > create_test_request_temp.php
echo $testRequest = [ >> create_test_request_temp.php
echo     [ >> create_test_request_temp.php
echo         'user_id' =^> 123456789, >> create_test_request_temp.php
echo         'user_name' =^> 'Тестовый пользователь', >> create_test_request_temp.php
echo         'user_telegram_name' =^> 'Test User', >> create_test_request_temp.php
echo         'user_fio' =^> 'Иванов Иван Иванович', >> create_test_request_temp.php
echo         'chat_id' =^> '987654321', >> create_test_request_temp.php
echo         'timestamp' =^> time^(^) >> create_test_request_temp.php
echo     ] >> create_test_request_temp.php
echo ]; >> create_test_request_temp.php
echo. >> create_test_request_temp.php
echo $filePath = __DIR__ . '/data/access_requests.json'; >> create_test_request_temp.php
echo $jsonData = json_encode($testRequest, JSON_PRETTY_PRINT ^| JSON_UNESCAPED_UNICODE); >> create_test_request_temp.php
echo file_put_contents($filePath, $jsonData); >> create_test_request_temp.php
echo. >> create_test_request_temp.php
echo echo "Тестовая заявка успешно создана!\n"; >> create_test_request_temp.php
echo echo "Файл: " . $filePath . "\n"; >> create_test_request_temp.php
echo echo "Содержимое файла:\n"; >> create_test_request_temp.php
echo echo $jsonData . "\n"; >> create_test_request_temp.php
echo ?^> >> create_test_request_temp.php

REM Пытаемся запустить скрипт разными способами
echo Попытка запуска скрипта...

if exist "C:\xampp\php\php.exe" (
    echo Найден PHP в C:\xampp\php\php.exe
    "C:\xampp\php\php.exe" create_test_request_temp.php
    goto cleanup
)

if exist "C:\php\php.exe" (
    echo Найден PHP в C:\php\php.exe
    "C:\php\php.exe" create_test_request_temp.php
    goto cleanup
)

echo PHP не найден в стандартных местах.
echo Попробуйте запустить скрипт вручную через веб-сервер.

:cleanup
echo.
echo Удаление временных файлов...
del create_test_request_temp.php
echo Готово!
echo.
pause