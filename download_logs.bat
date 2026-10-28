@echo off
echo Downloading logs from server...
echo.

REM Try WinSCP command line if available
if exist "C:\Program Files (x86)\WinSCP\WinSCP.com" (
    echo Using WinSCP...
    "C:\Program Files (x86)\WinSCP\WinSCP.com" /command ^
        "open sftp://root:7qQ%%2328051961@82.146.42.213/" ^
        "get /var/log/nginx/error.log c:\ozna_bot\nginx_error.log" ^
        "get /var/log/php/error.log c:\ozna_bot\php_error.log" ^
        "exit"
    
    echo.
    echo Done! Check downloaded files:
    echo   c:\ozna_bot\nginx_error.log
    echo   c:\ozna_bot\php_error.log
) else (
    echo WinSCP not found. Please use VS Code SFTP extension:
    echo 1. Press Ctrl+Shift+P
    echo 2. Type "SFTP: Download File"
    echo 3. Select /var/log/nginx/error.log
    echo.
    echo OR manually run these commands:
    echo scp root@82.146.42.213:/var/log/nginx/error.log c:\ozna_bot\
)

pause
