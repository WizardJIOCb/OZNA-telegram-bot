<?php

echo "=== СРОЧНАЯ ДИАГНОСТИКА: Проверка логов сервера ===\n\n";

echo "🔍 ЗАДАНИЕ ДЛЯ ВАШЕГО ДРУГА:\n\n";

echo "1. Выполните эту команду на сервере:\n\n";
echo "   tail -100 /var/log/nginx/error.log | grep -E 'ProjectHandler|DEBUG_SIMPLE|proj_report'\n\n";

echo "2. ИЛИ проверьте логи PHP-FPM:\n\n";
echo "   tail -100 /var/log/php-fpm/error.log | grep -E 'ProjectHandler|DEBUG_SIMPLE|proj_report'\n\n";
echo "   (или /var/log/php8.3-fpm/error.log)\n\n";

echo "3. Скопируйте ВСЕ логи, которые появились после нажатия кнопки\n\n";

echo "=== ЧТО ИСКАТЬ В ЛОГАХ ===\n\n";

echo "СЦЕНАРИЙ 1: Логов вообще НЕТ\n";
echo "   → PHP-FPM не был перезапущен\n";
echo "   → Или файлы не обновились\n";
echo "   → РЕШЕНИЕ: Перезапустить PHP-FPM и проверить снова\n\n";

echo "СЦЕНАРИЙ 2: Есть лог 'canHandle result for proj_report_simple: false'\n";
echo "   → ProjectHandler не распознаёт callback\n";
echo "   → Проблема с методом canHandle\n";
echo "   → РЕШЕНИЕ: Нужно проверить код\n\n";

echo "СЦЕНАРИЙ 3: Есть 'canHandle: true', но НЕТ 'handleProjectReportCallback'\n";
echo "   → Callback доходит, но не обрабатывается\n";
echo "   → Проблема в методе handle\n";
echo "   → РЕШЕНИЕ: Нужно добавить логирование\n\n";

echo "СЦЕНАРИЙ 4: Есть ошибка PHP (Fatal error, Warning, и т.д.)\n";
echo "   → Синтаксическая ошибка в коде\n";
echo "   → РЕШЕНИЕ: Исправить ошибку\n\n";

echo "=== КОМАНДЫ ДЛЯ ДРУГА ===\n\n";

echo "# Перезапустить PHP-FPM:\n";
echo "sudo systemctl restart php8.3-fpm\n\n";

echo "# Проверить статус:\n";
echo "sudo systemctl status php8.3-fpm\n\n";

echo "# Проверить дату изменения файлов:\n";
echo "ls -la /var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/Handlers/ProjectHandler.php\n\n";

echo "# Посмотреть последние 100 строк логов:\n";
echo "tail -100 /var/log/nginx/error.log\n\n";

echo "# Следить за логами в реальном времени (ВАЖНО!):\n";
echo "tail -f /var/log/nginx/error.log\n";
echo "# Затем нажать кнопку в боте и смотреть, что появится\n\n";

echo "=== ПРИШЛИТЕ МНЕ ===\n\n";
echo "1. Последние 50-100 строк из логов после нажатия кнопки\n";
echo "2. Дату изменения файла ProjectHandler.php\n";
echo "3. Результат команды: systemctl status php8.3-fpm\n\n";

?>
