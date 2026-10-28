<?php
// Check if file was uploaded to server
$remoteFile = '/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/Handlers/ProjectHandler.php';
$localFile = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';

echo "Checking ProjectHandler.php sync status...\n\n";

// Get local file hash
if (file_exists($localFile)) {
    $localHash = md5_file($localFile);
    $localSize = filesize($localFile);
    $localMtime = date('Y-m-d H:i:s', filemtime($localFile));
    echo "Local file:\n";
    echo "  Size: {$localSize} bytes\n";
    echo "  MD5: {$localHash}\n";
    echo "  Modified: {$localMtime}\n\n";
} else {
    echo "❌ Local file not found: {$localFile}\n\n";
}

// Try to get remote file info via FTP or direct access
echo "To check remote file, please run on server:\n";
echo "  md5sum {$remoteFile}\n";
echo "  stat {$remoteFile}\n";
echo "  tail -50 /var/log/nginx/error.log\n";
echo "  tail -50 /var/log/php-fpm/error.log\n";
