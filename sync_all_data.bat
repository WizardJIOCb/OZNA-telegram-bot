@echo off
echo ======================================
echo   COMPREHENSIVE PROJECT BACKUP SCRIPT
echo ======================================
echo.

:: Set variables
set BACKUP_NAME=backup_%date:~-4,4%-%date:~-10,2%-%date:~-7,2%_%time:~0,2%-%time:~3,2%-%time:~6,2%
set BACKUP_NAME=%BACKUP_NAME: =0%
set SERVER=api.ladyzenko.pro
set USERNAME=root
set REMOTE_PATH=/var/www/api.ladyzenko.pro/tg/ozna

echo Creating comprehensive backup: %BACKUP_NAME%
echo.

:: Step 1: Create local backup
echo 1. Creating local backup of all project data...
php backup_all_data.php
echo.

:: Step 2: Backup server files
echo 2. Downloading current server files...
if not exist "server_backups" mkdir server_backups
if not exist "server_backups\%BACKUP_NAME%" mkdir server_backups\%BACKUP_NAME%

echo    Downloading projects.json from server...
pscp -P 22 %USERNAME%@%SERVER%:%REMOTE_PATH%/data/projects.json server_backups\%BACKUP_NAME%\server_projects.json

echo    Downloading access_requests.json from server...
pscp -P 22 %USERNAME%@%SERVER%:%REMOTE_PATH%/data/access_requests.json server_backups\%BACKUP_NAME%\server_access_requests.json

:: Step 3: Create database dump
echo 3. Creating database backup...
echo    Dumping ozna_data table...
plink -P 22 %USERNAME%@%SERVER% "mysqldump -h 82.146.42.213 -u WizardJIOCb -p7qQ#28051961 tg_bot ozna_data > /tmp/ozna_data_%BACKUP_NAME%.sql"

echo    Downloading database dump...
pscp -P 22 %USERNAME%@%SERVER%:/tmp/ozna_data_%BACKUP_NAME%.sql server_backups\%BACKUP_NAME%\ozna_data_%BACKUP_NAME%.sql

echo    Cleaning up temporary files on server...
plink -P 22 %USERNAME%@%SERVER% "rm /tmp/ozna_data_%BACKUP_NAME%.sql"

:: Step 4: Verify integrity
echo 4. Verifying data integrity...
php verify_data_integrity.php > verification_report_%BACKUP_NAME%.txt
echo    Verification report saved to: verification_report_%BACKUP_NAME%.txt

:: Step 5: Upload updated files to server
echo 5. Uploading local files to server...
echo    Uploading projects.json...
pscp -P 22 data\projects.json %USERNAME%@%SERVER%:%REMOTE_PATH%/data/projects.json

echo    Uploading access_requests.json...
pscp -P 22 data\access_requests.json %USERNAME%@%SERVER%:%REMOTE_PATH%/data/access_requests.json

:: Step 6: Sync user data from local database to server
echo 6. Creating user data sync script...
echo    Generating SQL sync commands...
php generate_user_data_sync.php > user_data_sync_%BACKUP_NAME%.sql

echo    Uploading sync script to server...
pscp -P 22 user_data_sync_%BACKUP_NAME%.sql %USERNAME%@%SERVER%:/tmp/

echo    Executing user data sync on server...
plink -P 22 %USERNAME%@%SERVER% "mysql -h 82.146.42.213 -u WizardJIOCb -p7qQ#28051961 tg_bot < /tmp/user_data_sync_%BACKUP_NAME%.sql"

echo    Cleaning up sync script on server...
plink -P 22 %USERNAME%@%SERVER% "rm /tmp/user_data_sync_%BACKUP_NAME%.sql"

:: Step 7: Final verification
echo 7. Final verification on server...
echo    Running server-side verification...
pscp -P 22 verify_data_integrity.php %USERNAME%@%SERVER%:%REMOTE_PATH%/
plink -P 22 %USERNAME%@%SERVER% "cd %REMOTE_PATH% && php verify_data_integrity.php" > server_verification_%BACKUP_NAME%.txt
echo    Server verification report saved to: server_verification_%BACKUP_NAME%.txt

echo.
echo ======================================
echo   BACKUP AND SYNC COMPLETED
echo ======================================
echo.
echo Backup files created:
echo - Local backup in: backups\[timestamp]
echo - Server backup in: server_backups\%BACKUP_NAME%
echo - Verification reports: verification_report_%BACKUP_NAME%.txt, server_verification_%BACKUP_NAME%.txt
echo.
echo The following data has been synchronized:
echo - projects.json (file-based project list)
echo - access_requests.json (pending access requests)
echo - User project data (database-based user settings)
echo.
echo To restore from this backup later, use:
echo restore_backup.php backups\[timestamp]
echo.
pause