<?php

/**
 * API Configuration
 * Shares DB credentials with the main bot config.
 */

return [
    'db' => [
        'host'     => '82.146.42.213',
        'username' => 'WizardJIOCb',
        'password' => '7qQ#28051961',
        'database' => 'tg_bot',
    ],

    'jwt' => [
        'secret'              => 'ozna_pwa_jwt_secret_2026_xK9mP2qL7v',
        'access_token_ttl'    => 3600,       // 1 hour
        'refresh_token_ttl'   => 2592000,    // 30 days
        'issuer'              => 'ozna_pwa',
    ],

    'telegram' => [
        'bot_token'    => '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ',
        'bot_username' => 'OznaWork_bot',
    ],
];
