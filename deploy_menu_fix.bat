@echo off
echo Deploying Menu Button Fix to Server
echo ==================================

echo 1. Creating backup of current files...
pscp -r -P 22 root@api.ladyzenko.pro:/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/TextmenuCommand.php ./backups/TextmenuCommand_backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%.php
pscp -r -P 22 root@api.ladyzenko.pro:/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/GenericmessageCommand.php ./backups/GenericmessageCommand_backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%.php

echo 2. Uploading fixed files to server...
pscp -P 22 CustomCommands/TextmenuCommand.php root@api.ladyzenko.pro:/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/TextmenuCommand.php
pscp -P 22 CustomCommands/GenericmessageCommand.php root@api.ladyzenko.pro:/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/GenericmessageCommand.php

echo 3. Regenerating autoloader on server...
plink -P 22 root@api.ladyzenko.pro "cd /var/www/api.ladyzenko.pro/tg/ozna && composer dump-autoload"

echo 4. Restarting webhook...
plink -P 22 root@api.ladyzenko.pro "cd /var/www/api.ladyzenko.pro/tg/ozna && php set.php"

echo Deployment completed!
echo Please test the menu buttons to verify they are working correctly.
pause