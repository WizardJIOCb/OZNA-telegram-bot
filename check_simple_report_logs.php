<?php

echo "=== ПРОВЕРКА ЛОГОВ: Простой отчёт ===\n\n";

echo "📋 Инструкция по проверке:\n\n";

echo "1. Откройте логи сервера (обычно в /var/log/nginx/error.log или /var/log/php-fpm/error.log)\n\n";

echo "2. Ищите следующие записи после нажатия кнопки 'Простой отчёт':\n\n";

echo "   🔍 Должны быть такие логи:\n";
echo "   ----------------------------------------\n";
echo "   ProjectHandler: canHandle result for 'proj_report_simple': true\n";
echo "   ProjectHandler: startsWithProjectReport: true\n";
echo "   DEBUG_SIMPLE_REPORT: handleProjectReportCallback called with: proj_report_simple\n";
echo "   DEBUG_SIMPLE_REPORT: Matched proj_report_simple, calling showSimpleReportStartDateMenu\n";
echo "   DEBUG_SIMPLE_REPORT: showSimpleReportStartDateMenu called for user: [ваш ID]\n";
echo "   DEBUG_SIMPLE_REPORT: Conversation state set, awaiting_simple_report_start_date = true\n";
echo "   ----------------------------------------\n\n";

echo "3. Если НЕ видите эти логи:\n\n";

echo "   ❌ НЕТ ЛОГА 'ProjectHandler: canHandle result for proj_report_simple: true'\n";
echo "      → ProjectHandler не получает callback\n";
echo "      → Проблема в CallbackqueryCommand или CallbackHandlerFactory\n\n";

echo "   ❌ ЕСТЬ 'canHandle: true', но НЕТ 'handleProjectReportCallback called'\n";
echo "      → callback доходит до ProjectHandler, но не вызывается метод handle\n";
echo "      → Проблема в маршрутизации внутри ProjectHandler\n\n";

echo "   ❌ ЕСТЬ 'handleProjectReportCallback called', но НЕТ 'Matched proj_report_simple'\n";
echo "      → Метод вызывается, но условие не срабатывает\n";
echo "      → Проблема с кодировкой callback данных\n\n";

echo "4. Альтернативная проверка:\n\n";
echo "   Выполните команду на сервере:\n";
echo "   tail -f /var/log/nginx/error.log | grep DEBUG_SIMPLE_REPORT\n\n";
echo "   Затем нажмите кнопку 'Простой отчёт' в боте\n\n";

echo "5. Если логов вообще НЕТ:\n\n";
echo "   → Файл ProjectHandler.php не был загружен на сервер\n";
echo "   → Или нужно перезапустить PHP-FPM:\n";
echo "     sudo systemctl restart php-fpm\n";
echo "     (или php8.3-fpm, в зависимости от версии)\n\n";

echo "=== ЧТО ДЕЛАТЬ ДАЛЬШЕ ===\n\n";

echo "Пожалуйста, проверьте логи и скажите:\n";
echo "• Какие из этих логов вы ВИДИТЕ?\n";
echo "• Какие логи ОТСУТСТВУЮТ?\n";
echo "• Или логов вообще нет?\n\n";

echo "Это поможет мне понять, на каком этапе происходит проблема! 🔍\n";

?>
