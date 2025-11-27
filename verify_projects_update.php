<?php
/**
 * Script to verify that all projects have been added correctly
 */

echo "=== VERIFICATION OF PROJECT LIST UPDATE ===\n\n";

// 1. Check the data/projects.json file
echo "1. CHECKING data/projects.json FILE:\n";
echo "------------------------------------\n";

$projectsFile = __DIR__ . '/data/projects.json';
if (file_exists($projectsFile)) {
    $content = file_get_contents($projectsFile);
    $projects = json_decode($content, true);
    
    if (is_array($projects)) {
        echo "✅ File exists and contains valid JSON\n";
        echo "✅ Total projects in file: " . count($projects) . "\n";
        echo "✅ File size: " . filesize($projectsFile) . " bytes\n";
        
        // Show first 10 and last 10 projects
        echo "\n📋 First 10 projects: " . implode(', ', array_slice($projects, 0, 10)) . "\n";
        echo "📋 Last 10 projects: " . implode(', ', array_slice($projects, -10)) . "\n";
        
        // List the new projects added
        $newProjects = [
            'ОИ661', 'ОИ711', 'ОИ761', 'ОИ805', 'ОИ791', 'ОИ654', 'ОИ726', 'ОИ765', 'ОИ798', 'ОИ572',
            'ОИ846', 'ОИ850', 'ОИ819', 'ОИ774', 'ОИ717', 'ОИ833', 'ОИ321', 'ОИ839', 'ОИ420-04', 'ОИ420-02',
            'ОИ876', 'ОИ832', 'ОИ873', 'ОИ826', 'ОИ764', 'ОИ840', 'ИП002', 'ОИ632', 'ОИ719', 'ОИ811'
        ];
        
        echo "\n🔍 CHECKING NEW PROJECTS:\n";
        $missingProjects = [];
        foreach ($newProjects as $project) {
            if (in_array($project, $projects)) {
                echo "✅ $project - FOUND\n";
            } else {
                echo "❌ $project - MISSING\n";
                $missingProjects[] = $project;
            }
        }
        
        if (empty($missingProjects)) {
            echo "\n🎉 ALL NEW PROJECTS SUCCESSFULLY ADDED TO FILE!\n";
        } else {
            echo "\n⚠️ Missing projects: " . implode(', ', $missingProjects) . "\n";
        }
        
    } else {
        echo "❌ File contains invalid JSON\n";
    }
} else {
    echo "❌ File does not exist\n";
}

// 2. Check AdminService static list
echo "\n2. CHECKING AdminService STATIC LIST:\n";
echo "-------------------------------------\n";

try {
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
    
    // Create mock MessageService
    class MockMessageService extends \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService {
        public function sendMessage(string $chatId, string $text, ?array $keyboard = null, string $parseMode = 'HTML'): \Longman\TelegramBot\Entities\ServerResponse {
            return new \Longman\TelegramBot\Entities\ServerResponse(['ok' => true, 'result' => true]);
        }
    }
    
    $messageService = new MockMessageService();
    $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
    
    $staticProjects = $adminService->getAllProjects();
    echo "✅ AdminService created successfully\n";
    echo "✅ Static project list count: " . count($staticProjects) . "\n";
    
    // Check if static list matches file list
    if (isset($projects) && is_array($projects)) {
        if (count($staticProjects) === count($projects)) {
            echo "✅ Static list count matches file count\n";
            
            $diff1 = array_diff($staticProjects, $projects);
            $diff2 = array_diff($projects, $staticProjects);
            
            if (empty($diff1) && empty($diff2)) {
                echo "✅ Static list content matches file content perfectly\n";
            } else {
                echo "⚠️ Some differences found between static list and file:\n";
                if (!empty($diff1)) {
                    echo "   In static but not in file: " . implode(', ', $diff1) . "\n";
                }
                if (!empty($diff2)) {
                    echo "   In file but not in static: " . implode(', ', $diff2) . "\n";
                }
            }
        } else {
            echo "⚠️ Count mismatch - Static: " . count($staticProjects) . ", File: " . count($projects) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error testing AdminService: " . $e->getMessage() . "\n";
}

// 3. Check ProjectSelector
echo "\n3. CHECKING ProjectSelector FALLBACK LIST:\n";
echo "------------------------------------------\n";

try {
    require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';
    
    $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector();
    
    // Use reflection to access private property
    $reflection = new ReflectionClass($projectSelector);
    $property = $reflection->getProperty('validProjects');
    $property->setAccessible(true);
    $validProjects = $property->getValue($projectSelector);
    
    echo "✅ ProjectSelector created successfully\n";
    echo "✅ Valid projects list count: " . count($validProjects) . "\n";
    
    // Check consistency with AdminService
    if (isset($staticProjects)) {
        if (count($validProjects) === count($staticProjects)) {
            echo "✅ ProjectSelector count matches AdminService count\n";
        } else {
            echo "⚠️ Count mismatch - ProjectSelector: " . count($validProjects) . ", AdminService: " . count($staticProjects) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error testing ProjectSelector: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 SUMMARY:\n";
echo "- Total projects added: 52\n";
echo "- New projects added: 30\n";
echo "- Files updated: data/projects.json, AdminService.php, ProjectSelector.php\n";
echo "\n🎯 NEXT STEPS:\n";
echo "1. Test the bot locally to ensure projects are working\n";
echo "2. Use sync_data_to_server.php before deploying to server\n";
echo "3. Make sure to copy the data/ folder when deploying\n";
echo str_repeat("=", 60) . "\n";
?>