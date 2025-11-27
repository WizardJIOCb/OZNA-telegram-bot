@echo off
echo Running all tests and diagnostics for the bot...
echo ================================================

echo.
echo 1. Quick menu test...
php quick_menu_test.php

echo.
echo 2. Database connection test...
php test_database_connection.php

echo.
echo 3. User access level check...
php check_user_access_level.php

echo.
echo 4. Menu display test...
php test_menu_display.php

echo.
echo 5. Start command isolated test...
php test_start_command_isolated.php

echo.
echo 6. Comprehensive test...
php comprehensive_test.php

echo.
echo 7. Bot logs check...
php check_bot_logs.php

echo.
echo 8. Start button diagnosis...
php diagnose_start_button.php

echo.
echo 9. User access levels fix...
php fix_user_access_levels.php

echo.
echo 10. Webhook check and fix...
php check_and_fix_webhook.php

echo.
echo 11. Full bot diagnostic...
php full_bot_diagnostic.php

echo.
echo 12. Encoding check and fix...
php check_encoding_fix.php

echo.
echo 13. Encoding test...
php encoding_test.php

echo.
echo All tests completed!
echo Please review the output above for any issues.
pause