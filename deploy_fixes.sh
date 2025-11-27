#!/bin/bash
# Deployment script for emoji handling fixes

echo "Deploying emoji handling fixes..."

# Copy the updated files to the server
# Note: Update these paths to match your actual deployment setup
scp encoding_helper.php user@server:/var/www/api.ladyzenko.pro/tg/ozna/
scp hook.php user@server:/var/www/api.ladyzenko.pro/tg/ozna/

echo "Deployment complete!"
echo "Remember to restart the web server to ensure changes take effect"