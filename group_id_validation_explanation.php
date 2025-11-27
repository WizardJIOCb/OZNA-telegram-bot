<?php
/**
 * Simple validation test to show the difference between Group ID and Chat ID requirements
 */

echo "=== GROUP ID vs CHAT ID VALIDATION COMPARISON ===\n\n";

echo "ISSUE ANALYSIS:\n";
echo "✓ Chat ID input works (shows confirmation)\n";
echo "✗ Group ID input doesn't work (no confirmation)\n\n";

echo "VALIDATION REQUIREMENTS:\n\n";

echo "GROUP ID VALIDATION:\n";
echo "- Must start with -100\n";
echo "- Followed by digits only\n";
echo "- Pattern: /^-100\\d+$/\n";
echo "- Examples:\n";
echo "  ✓ -1001234567890 (VALID)\n";
echo "  ✓ -1009876543210 (VALID)\n";
echo "  ✗ -123456789 (INVALID - doesn't start with -100)\n";
echo "  ✗ 123456789 (INVALID - no minus, doesn't start with -100)\n";
echo "  ✗ -1001234567890abc (INVALID - contains letters)\n\n";

echo "CHAT ID VALIDATION:\n";
echo "- Can be positive or negative number\n";
echo "- Digits only (with optional minus)\n";
echo "- Pattern: /^-?\\d+$/\n";
echo "- Examples:\n";
echo "  ✓ 123456789 (VALID - User ID)\n";
echo "  ✓ -1001234567890 (VALID - Group/Channel ID)\n";
echo "  ✓ -123456789 (VALID - Any negative number)\n";
echo "  ✗ 123abc (INVALID - contains letters)\n";
echo "  ✗ abc123 (INVALID - contains letters)\n\n";

echo "TEST YOUR INPUT:\n";
echo "What Group ID are you trying to enter?\n\n";

// Test common Group ID formats
$testGroupIds = [
    '-1001234567890',
    '-1009876543210', 
    '-123456789',
    '123456789',
    '-100123456789',
    '-1002722176147' // Your existing group ID from database
];

echo "Testing common Group ID formats:\n";
foreach ($testGroupIds as $id) {
    $isValid = preg_match('/^-100\\d+$/', trim($id));
    echo "  '{$id}': " . ($isValid ? "✓ VALID" : "✗ INVALID") . "\n";
}

echo "\nTesting same IDs as Chat IDs:\n";
foreach ($testGroupIds as $id) {
    $isValid = preg_match('/^-?\\d+$/', trim($id));
    echo "  '{$id}': " . ($isValid ? "✓ VALID" : "✗ INVALID") . "\n";
}

echo "\n=== SOLUTION ===\n\n";

echo "LIKELY CAUSE:\n";
echo "You might be entering a Group ID that doesn't start with -100.\n";
echo "Telegram Group IDs always start with -100 followed by numbers.\n\n";

echo "HOW TO GET CORRECT GROUP ID:\n";
echo "1. Add @userinfobot to your group\n";
echo "2. Send any message in the group\n";
echo "3. @userinfobot will show the group ID\n";
echo "4. Copy the full ID including the -100 prefix\n\n";

echo "EXAMPLES OF CORRECT GROUP IDS:\n";
echo "- -1001234567890\n";
echo "- -1009876543210\n";
echo "- -1002722176147 (your existing one)\n\n";

echo "If you're entering a Group ID that starts with -100 and still\n";
echo "not getting confirmation, then there might be another issue\n";
echo "like a silent error in the Group ID handler.\n";

?>