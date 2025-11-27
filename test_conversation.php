<?php
// This is a test script to check if the conversation system is working properly

// We'll simulate the flow of adding a project

echo "Testing conversation system...\n";

// Simulate setting up a conversation
class MockConversation {
    public $notes = [];
    
    public function update() {
        echo "Conversation updated with notes: " . json_encode($this->notes, JSON_UNESCAPED_UNICODE) . "\n";
    }
}

// Simulate the flow
$conversation = new MockConversation();
$conversation->notes['admin_action'] = 'add_project';
$conversation->update();

echo "Conversation state set to 'add_project'\n";

// Simulate checking the conversation state
$adminAction = $conversation->notes['admin_action'] ?? null;
$isAddingProject = $adminAction === 'add_project';

echo "Current admin action: " . ($adminAction ?? "null") . "\n";
echo "Is adding project: " . ($isAddingProject ? "true" : "false") . "\n";

echo "Test completed.\n";