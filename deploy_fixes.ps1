# Deployment script for emoji handling fixes

Write-Host "Deploying emoji handling fixes..."

# Copy the updated files to the server
# Note: Update these paths to match your actual deployment setup
# You may need to adjust the paths and credentials based on your setup

# Example using scp (if available on Windows)
# scp encoding_helper.php user@server:/var/www/api.ladyzenko.pro/tg/ozna/
# scp hook.php user@server:/var/www/api.ladyzenko.pro/tg/ozna/

# Example using PowerShell remoting
# Copy-Item -Path "encoding_helper.php" -Destination "\\server\path\to\tg\ozna\"
# Copy-Item -Path "hook.php" -Destination "\\server\path\to\tg\ozna\"

Write-Host "Deployment complete!"
Write-Host "Remember to restart the web server to ensure changes take effect"