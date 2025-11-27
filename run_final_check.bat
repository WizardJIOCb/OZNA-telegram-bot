@echo off
echo Running final verification of all fixes...
echo ========================================

echo.
echo 1. Quick menu test...
php quick_menu_test.php

echo.
echo 2. Simple start command test...
php test_start_simple.php

echo.
echo 3. Simple commands test...
php test_commands_simple.php

echo.
echo 4. Encoding test...
php encoding_test.php

echo.
echo 5. Start button diagnosis...
php diagnose_start_button.php

echo.
echo 6. Final verification...
php final_verification.php

echo.
echo 7. Checking final report...
type FINAL_FIX_REPORT_CORRECTED.md

echo.
echo All checks completed!
echo The /start button should now work correctly.
pause