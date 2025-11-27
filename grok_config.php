<?php

/**
 * Grok AI Configuration
 * 
 * Add your xAI Grok API key here
 * Get your API key from: https://console.x.ai/
 */

return [
    // Your Grok API key (replace with your actual key)
    'grok_api_key' => 'xai-cHb9kO337kfWzpTHAPV7uHSPcQwl9djhAJhvSD5rvd9zdl9KGPIoPdNxkrH6o5hCB0pAgKhwIau2OS5s',
    
    // API settings
    'api_url' => 'https://api.x.ai/v1/chat/completions',
    'model' => 'grok-beta',
    
    // Default parameters
    'default_temperature' => 0.7,
    'default_max_tokens' => 2000,
    
    // Feature flags (enable/disable specific features)
    'features' => [
        'project_analysis' => true,       // Analyze project reports
        'notes_patterns' => true,          // Find patterns in notes
        'resource_prediction' => true,     // Predict resource load
        'executive_summary' => true,       // Generate summaries
        'ai_assistant' => true,            // Answer questions
    ],
    
    // Access control (which user levels can use AI features)
    'min_access_level' => 2,  // 0=Guest, 1=User, 2=Supervisor, 3=Admin, 4=SuperAdmin
];
