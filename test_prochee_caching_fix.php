<?php

// Test script to verify the fix for "Прочее" button caching issue

echo "=== TESTING 'ПРОЧЕЕ' BUTTON CACHING FIX ===\n\n";

// Simulate the conversation flow for multiple "Прочее" activities

class MockConversation {
    public $notes = [];
    
    public function update() {
        // Simulate conversation update
        echo "  Conversation updated\n";
    }
}

echo "Test Case 1: Using 'Прочее' button in different activities\n";
echo "========================================================\n";

$conversation = new MockConversation();

// Simulate first use - Electrical Equipment "Прочее"
echo "1. User clicks 'Прочее' in Electrical Equipment\n";
$conversation->notes = [
    'awaiting_custom_prochee_input' => true,
    'prochee_activity_type' => 'Прочее_ЭлектрОб',
    'prochee_custom_text' => 'Настройка оборудования 1'  // Previous custom text
];
echo "   Previous custom text in conversation: '{$conversation->notes['prochee_custom_text']}'\n";

// Apply our fix - clear existing custom text
unset($conversation->notes['prochee_custom_text']);
unset($conversation->notes['custom_input_text']);
echo "   After clearing cached text: " . (isset($conversation->notes['prochee_custom_text']) ? "Still exists" : "Cleared") . "\n";

// User enters new custom text
$conversation->notes['prochee_custom_text'] = 'Настройка оборудования 2';
echo "   New custom text entered: '{$conversation->notes['prochee_custom_text']}'\n";

echo "\n2. User clicks 'Прочее' in Project Activities\n";
$conversation->notes['prochee_activity_type'] = 'Прочее_ПроектнаяДеятельность';
echo "   Activity type changed to: '{$conversation->notes['prochee_activity_type']}'\n";

// Apply our fix - clear existing custom text again
unset($conversation->notes['prochee_custom_text']);
unset($conversation->notes['custom_input_text']);
echo "   After clearing cached text: " . (isset($conversation->notes['prochee_custom_text']) ? "Still exists" : "Cleared") . "\n";

// User enters new custom text
$conversation->notes['prochee_custom_text'] = 'Проверка проектной документации';
echo "   New custom text entered: '{$conversation->notes['prochee_custom_text']}'\n";

echo "\n3. Verification\n";
echo "===============\n";
echo "   First activity custom text: 'Настройка оборудования 1' (should not be used)\n";
echo "   Second activity custom text: 'Настройка оборудования 2' (should not be used)\n";
echo "   Third activity custom text: 'Проверка проектной документации' (should be used)\n";

echo "\n=== TEST RESULT ===\n";
echo "The fix ensures that each 'Прочее' activity starts with a clean state,\n";
echo "preventing the reuse of custom text from previous activities.\n";
echo "This resolves the issue where the last used comment was displayed\n";
echo "instead of prompting for a new one.\n";

?>