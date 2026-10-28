@echo off
echo ================================================================
echo UPLOAD ProjectHandler.php TO SERVER
echo ================================================================
echo.
echo This script will upload the fixed file to the server
echo.

REM Check if sshpass is available
where sshpass >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    echo Found sshpass, uploading...
    echo.
    
    sshpass -p "7qQ#28051961" scp -o StrictHostKeyChecking=no ^
        "c:\ozna_bot\CustomCommands\Handlers\ProjectHandler.php" ^
        root@82.146.42.213:/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/Handlers/ProjectHandler.php
    
    if %ERRORLEVEL% EQU 0 (
        echo.
        echo ✅ Upload successful!
        echo.
        echo Restarting PHP-FPM...
        sshpass -p "7qQ#28051961" ssh -o StrictHostKeyChecking=no ^
            root@82.146.42.213 ^
            "sudo systemctl restart php-fpm"
        
        echo.
        echo ✅ PHP-FPM restarted!
        echo.
        echo ================================================================
        echo DONE! Try opening project card OI846 in the bot now.
        echo ================================================================
    ) else (
        echo.
        echo ❌ Upload failed!
        echo.
        echo Please use VS Code instead:
        echo   1. Open CustomCommands/Handlers/ProjectHandler.php
        echo   2. Press Ctrl+Shift+P
        echo   3. Type: SFTP: Upload File
        echo   4. Press Enter
    )
) else (
    echo sshpass not found. Please use VS Code SFTP extension:
    echo.
    echo MANUAL UPLOAD INSTRUCTIONS:
    echo ================================
    echo.
    echo 1. Open file in editor:
    echo    CustomCommands/Handlers/ProjectHandler.php
    echo.
    echo 2. Press Ctrl+Shift+P
    echo.
    echo 3. Type: SFTP: Upload File
    echo.
    echo 4. Press Enter and wait for "Upload done"
    echo.
    echo 5. Then restart PHP-FPM via SSH:
    echo    ssh root@82.146.42.213
    echo    Password: 7qQ#28051961
    echo    sudo systemctl restart php-fpm
    echo.
    echo ================================================================
)

pause
