<?php
/**
 * Simulation of manual input confirmation - shows what user should see
 */

echo "=== MANUAL INPUT SIMULATION ===\n\n";

$userId = 230379668;
$projectCode = "ОИ834";
$groupId = "-1001234567890";

echo "SIMULATING: User enters Group ID manually\n";
echo "User ID: {$userId}\n";
echo "Project: {$projectCode}\n";
echo "Group ID entered: {$groupId}\n\n";

echo "WHAT USER SHOULD SEE:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$confirmationMessage = "✅ ID группы сохранён

Для проекта {$projectCode}:
ID группы: {$groupId}

💡 Теперь уведомления будут отправляться в указанную группу.";

echo $confirmationMessage . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "[⬅️ К настройкам проекта] (navigation button)\n\n";

echo "This is the confirmation message that should appear\n";
echo "after entering Group ID or Chat ID manually.\n\n";

echo "If you are not seeing this message in the actual bot,\n";
echo "it means the cURL extension needs to be enabled.\n";
?>