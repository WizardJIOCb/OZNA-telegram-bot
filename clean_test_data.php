<?php
$conn = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
$conn->query("DELETE FROM project_moderation WHERE project_code LIKE 'TEST_%'");
echo "Test data cleaned\n";
