<?php
// Самый простой тест - только создание файла
file_put_contents(__DIR__ . '/simple_test_hook.log', date('Y-m-d H:i:s') . " - HOOK CALLED\n", FILE_APPEND);

echo "OK";
?>