<?php
// Simulate the exact callback handling logic
// This tests what would happen when callbacks are received

function simulateCallback($callbackData) {
    echo "Simulating callback: $callbackData\n";
    
    // Test the main patterns from handleControlProjectsCallback
    if ($callbackData === "proj_ctrl_add") {
        echo "  ✓ Would call showAddControlProjectMenu\n";
        return "showAddControlProjectMenu";
    }
    
    if ($callbackData === "proj_ctrl_remove") {
        echo "  ✓ Would call showRemoveControlProjectMenu\n";
        return "showRemoveControlProjectMenu";
    }
    
    if ($callbackData === "proj_ctrl_list") {
        echo "  ✓ Would call showControlProjectsList\n";
        return "showControlProjectsList";
    }
    
    if ($callbackData === "proj_ctrl_settings") {
        echo "  ✓ Would call showControlProjectSettings\n";
        return "showControlProjectSettings";
    }
    
    // Test specific project actions
    if (str_starts_with($callbackData, "proj_ctrl_add_")) {
        $projectCode = str_replace("proj_ctrl_add_", "", $callbackData);
        echo "  ✓ Would call addProjectToControlList with project: $projectCode\n";
        return "addProjectToControlList";
    }
    
    if (str_starts_with($callbackData, "proj_ctrl_remove_")) {
        $projectCode = str_replace("proj_ctrl_remove_", "", $callbackData);
        echo "  ✓ Would call removeProjectFromControlList with project: $projectCode\n";
        return "removeProjectFromControlList";
    }
    
    if (str_starts_with($callbackData, "proj_ctrl_set_")) {
        $projectCode = str_replace("proj_ctrl_set_", "", $callbackData);
        echo "  ✓ Would call showProjectControlSettings with project: $projectCode\n";
        return "showProjectControlSettings";
    }
    
    echo "  ✗ Would reach fallback error message\n";
    return "ERROR: Неизвестная команда управления проектами на контроле";
}

// Test the problematic callbacks
$testCallbacks = [
    "proj_ctrl_set_ОИ789",
    "proj_ctrl_remove_ОИ789", 
    "proj_ctrl_add_TestProject"
];

foreach ($testCallbacks as $callback) {
    $result = simulateCallback($callback);
    echo "Result: $result\n\n";
}
?>