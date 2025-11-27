@echo off
echo Running all bot diagnostics...
echo =============================

echo.
echo 1. Checking database connection...
php test_database_connection.php

echo.
echo 2. Checking user access levels...
php check_user_access_level.php

echo.
echo 3. Testing menu display...
php test_menu_display.php

echo.
echo 4. Testing StartCommand functionality...
php test_start_command_isolated.php

echo.
echo 5. Running comprehensive test...
php comprehensive_test.php

echo.
echo 6. Checking bot logs...
php check_bot_logs.php

echo.
echo 7. Diagnosing Start button issue...
php diagnose_start_button.php

echo.
echo 8. Fixing user access levels...
php fix_user_access_levels.php

echo.
echo 9. Checking and fixing webhook...
php check_and_fix_webhook.php

echo.
echo 10. Running full bot diagnostic...
php full_bot_diagnostic.php

echo.
echo All diagnostics completed!
pause