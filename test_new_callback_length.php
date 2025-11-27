<?php
$callback = 'cat_ТабИсполнОбяз_yes';
$length = strlen($callback);
echo "Callback: '{$callback}'\n";
echo "Length: {$length} bytes\n";
echo "Within 64-byte limit: " . ($length <= 64 ? 'YES ✅' : 'NO ❌') . "\n";
?>