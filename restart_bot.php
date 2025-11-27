<?php
/**
 * Simple script to restart the bot by setting a flag file
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "=== Restarting Bot ===\n";

// Create a restart flag file
$restartFlag = __DIR__ . '/bot_restart.flag';
if (file_put_contents($restartFlag, time())) {
    echo "✅ Restart flag created successfully\n";
    echo "The bot should restart within a few minutes\n";
} else {
    echo "❌ Failed to create restart flag\n";
}

echo "=== Restart Process Complete ===\n";