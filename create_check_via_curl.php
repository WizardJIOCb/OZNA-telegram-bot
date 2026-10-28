<?php
// This script creates check.php on the server via HTTP PUT if possible
// Or shows manual instructions

$checkScript = '<?php
header("Content-Type: text/plain");
$file = "/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/Handlers/ProjectHandler.php";
echo "=== File Check ===\n";
if (file_exists($file)) {
    echo "Size: ".filesize($file)."\n";
    echo "Modified: ".date("Y-m-d H:i:s", filemtime($file))."\n";
    echo "MD5: ".md5_file($file)."\n";
    $c = file_get_contents($file);
    $cnt = substr_count($c, "\$result->free();");
    echo "Free calls: {$cnt}\n";
    echo ($cnt >= 15 ? "OK\n" : "NOT UPDATED!\n");
}
echo "\n=== Logs ===\n";
if (file_exists("/var/log/nginx/error.log")) {
    echo file_get_contents("/var/log/nginx/error.log", false, null, -1, 5000);
}
?>';

echo "Please upload this content to http://api.ladyzenko.pro/tg/ozna/check_remote.php:\n\n";
echo $checkScript;
echo "\n\nThen visit: http://api.ladyzenko.pro/tg/ozna/check_remote.php\n";
?>
