<?php
/**
 * Test callback parsing logic to identify the exact issue
 * Following safety protocols for non-programmer user
 */

echo "=== TESTING CALLBACK PARSING LOGIC ===\n\n";

// Test the callback data patterns that should be generated
$testCallbacks = [
    // Project settings access
    'proj_ctrl_set_ОИ789',
    
    // Group configuration 
    'proj_ctrl_cfg_group_ОИ789',
    'proj_ctrl_group_select_ОИ789_-1001234567890',
    
    // Chat configuration
    'proj_ctrl_cfg_chat_ОИ789', 
    'proj_ctrl_chat_select_ОИ789_personal',
    
    // Notification types
    'proj_ctrl_cfg_types_ОИ789',
    'proj_ctrl_toggle_type_ОИ789_daily_reports',
    
    // Notification modes
    'proj_ctrl_cfg_mode_ОИ789',
    'proj_ctrl_mode_select_ОИ789_standard',
    
    // Test notification
    'proj_ctrl_test_ОИ789'
];

echo "1. Testing callback patterns that should be generated:\n";
foreach ($testCallbacks as $callback) {
    echo "   Testing: $callback\n";
    
    // Test proj_ctrl_set_ pattern
    if (str_starts_with($callback, 'proj_ctrl_set_')) {
        $projectCode = str_replace('proj_ctrl_set_', '', $callback);
        echo "     ✓ Matches proj_ctrl_set_ → Project: '$projectCode'\n";
    }
    
    // Test proj_ctrl_cfg_ patterns  
    if (str_starts_with($callback, 'proj_ctrl_cfg_group_')) {
        $projectCode = str_replace('proj_ctrl_cfg_group_', '', $callback);
        echo "     ✓ Matches proj_ctrl_cfg_group_ → Project: '$projectCode'\n";
    }
    
    if (str_starts_with($callback, 'proj_ctrl_cfg_chat_')) {
        $projectCode = str_replace('proj_ctrl_cfg_chat_', '', $callback);
        echo "     ✓ Matches proj_ctrl_cfg_chat_ → Project: '$projectCode'\n";
    }
    
    if (str_starts_with($callback, 'proj_ctrl_cfg_types_')) {
        $projectCode = str_replace('proj_ctrl_cfg_types_', '', $callback);
        echo "     ✓ Matches proj_ctrl_cfg_types_ → Project: '$projectCode'\n";
    }
    
    if (str_starts_with($callback, 'proj_ctrl_cfg_mode_')) {
        $projectCode = str_replace('proj_ctrl_cfg_mode_', '', $callback);
        echo "     ✓ Matches proj_ctrl_cfg_mode_ → Project: '$projectCode'\n";
    }
    
    // Test selection patterns with complex parsing
    if (str_starts_with($callback, 'proj_ctrl_group_select_')) {
        $parts = explode('_', $callback);
        echo "     Parts: " . implode(' | ', $parts) . "\n";
        if (count($parts) >= 6) {
            $projectCode = $parts[4];
            $groupId = implode('_', array_slice($parts, 5));
            echo "     ✓ Matches proj_ctrl_group_select_ → Project: '$projectCode', Group: '$groupId'\n";
        } else {
            echo "     ✗ Not enough parts for proj_ctrl_group_select_\n";
        }
    }
    
    if (str_starts_with($callback, 'proj_ctrl_chat_select_')) {
        $parts = explode('_', $callback);
        echo "     Parts: " . implode(' | ', $parts) . "\n";
        if (count($parts) >= 6) {
            $projectCode = $parts[4];
            $chatType = $parts[5];
            echo "     ✓ Matches proj_ctrl_chat_select_ → Project: '$projectCode', Chat: '$chatType'\n";
        } else {
            echo "     ✗ Not enough parts for proj_ctrl_chat_select_\n";
        }
    }
    
    if (str_starts_with($callback, 'proj_ctrl_toggle_type_')) {
        $parts = explode('_', $callback);
        echo "     Parts: " . implode(' | ', $parts) . "\n";
        if (count($parts) >= 6) {
            $projectCode = $parts[4];
            $notificationType = $parts[5];
            echo "     ✓ Matches proj_ctrl_toggle_type_ → Project: '$projectCode', Type: '$notificationType'\n";
        } else {
            echo "     ✗ Not enough parts for proj_ctrl_toggle_type_\n";
        }
    }
    
    if (str_starts_with($callback, 'proj_ctrl_mode_select_')) {
        $parts = explode('_', $callback);
        echo "     Parts: " . implode(' | ', $parts) . "\n";
        if (count($parts) >= 6) {
            $projectCode = $parts[4];
            $mode = $parts[5];
            echo "     ✓ Matches proj_ctrl_mode_select_ → Project: '$projectCode', Mode: '$mode'\n";
        } else {
            echo "     ✗ Not enough parts for proj_ctrl_mode_select_\n";
        }
    }
    
    if (str_starts_with($callback, 'proj_ctrl_test_')) {
        $projectCode = str_replace('proj_ctrl_test_', '', $callback);
        echo "     ✓ Matches proj_ctrl_test_ → Project: '$projectCode'\n";
    }
    
    echo "\n";
}

echo "=== ANALYSIS ===\n";
echo "The parsing logic is complex and may be causing issues.\n";
echo "The current code uses:\n";
echo "  explode('_', \$callbackData) and checks count(\$parts) >= 6\n";
echo "  Then uses \$parts[4] for project and \$parts[5] for value\n\n";

echo "But the actual callback patterns may not follow this structure exactly.\n";
echo "For example:\n";
echo "  'proj_ctrl_group_select_ОИ789_-1001234567890' splits to:\n";
echo "  [0]=proj, [1]=ctrl, [2]=group, [3]=select, [4]=ОИ789, [5]=-1001234567890\n";
echo "  This SHOULD work with the current parsing.\n\n";

echo "The issue might be in the callback generation, not the parsing.\n";
echo "Let's check what callbacks are actually being generated...\n";

?>