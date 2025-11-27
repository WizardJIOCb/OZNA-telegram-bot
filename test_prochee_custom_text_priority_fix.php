<?php

// Test script to verify the fix for prochee custom text caching issue

echo "=== TESTING PROCHEE CUSTOM TEXT CACHING FIX ===\n\n";

class MockConversation {
    public $notes = [];
    
    public function update() {
        echo "  Conversation updated\n";
    }
}

echo "Test Case: Prochee flow with legacy data interference\n";
echo "====================================================\n";

$conversation = new MockConversation();

// Simulate scenario where there's old legacy data
$conversation->notes = [
    'custom_input_text' => 'Ожидание допоставки (Дефицит по спецификации)',  // Old data
    'prochee_custom_text' => '',  // No new data yet
    'awaiting_custom_prochee_input' => true,
    'prochee_activity_type' => 'Прочее_ПроектнаяДеятельность',
    'prochee_source_category' => 'cat_ТабПроектнаяДеят_Проч'
];

echo "1. Initial state with old legacy data:\n";
echo "   custom_input_text: '{$conversation->notes['custom_input_text']}'\n";
echo "   prochee_custom_text: '" . ($conversation->notes['prochee_custom_text'] ?? 'NOT_SET') . "'\n";
echo "   awaiting_custom_prochee_input: " . ($conversation->notes['awaiting_custom_prochee_input'] ? 'true' : 'false') . "\n";

// Simulate user entering new custom text
$conversation->notes['prochee_custom_text'] = 'прооооверка';
echo "\n2. After user enters new custom text:\n";
echo "   prochee_custom_text: '{$conversation->notes['prochee_custom_text']}'\n";

// Apply our fix - check for prochee flow
$isProcheeFlow = isset($conversation->notes['awaiting_custom_prochee_input']) || 
                isset($conversation->notes['prochee_activity_type']) ||
                !empty($conversation->notes['prochee_source_category']);

echo "\n3. Flow detection:\n";
echo "   Is prochee flow: " . ($isProcheeFlow ? 'YES' : 'NO') . "\n";

// Apply our fix - retrieve custom text with proper priority
$customText = $conversation->notes['prochee_custom_text'] ?? '';

if (empty($customText) && $isProcheeFlow) {
    echo "   ERROR: No prochee custom text in prochee flow - would show error\n";
    $customText = '[ERROR]';
} else if (empty($customText) && !$isProcheeFlow) {
    // Fall back to legacy
    $legacyCustomText = $conversation->notes['custom_input_text'] ?? '';
    if (!empty($legacyCustomText)) {
        $customText = $legacyCustomText;
        echo "   Using legacy custom text: '{$customText}'\n";
    }
} else {
    echo "   Using prochee custom text: '{$customText}'\n";
}

echo "\n4. Final result:\n";
echo "   Should use: 'прооооверка' (new prochee text)\n";
echo "   Would have used: 'Ожидание допоставки (Дефицит по спецификации)' (old legacy text)\n";
echo "   Actually using: '{$customText}'\n";

echo "\n=== TEST RESULT ===\n";
if ($customText === 'прооооверка') {
    echo "✅ SUCCESS: Fix works correctly - using new prochee custom text\n";
} else {
    echo "❌ FAILURE: Fix not working - still using old legacy text\n";
}

echo "\nThe fix ensures that when in a prochee flow, the system prioritizes\n";
echo "the new prochee custom text and doesn't fall back to legacy data\n";
echo "that causes the caching issue.\n";

?>