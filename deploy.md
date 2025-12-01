# Deployment Guide for Ozna Telegram Bot on Linux

This guide provides step-by-step instructions for deploying the Ozna Telegram bot on a Linux server.

## Prerequisites

- Linux server (Ubuntu 20.04 LTS or newer recommended)
- Root or sudo access
- Internet connection
- Domain name or static IP address (for webhook)

## System Requirements

- PHP 7.4 or higher
- MySQL or MariaDB
- Composer
- Apache or Nginx web server
- Git (optional, for cloning repository)

## Installation Steps

### 1. Update System Packages

```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Install Required Software

```bash
# Install PHP and extensions
sudo apt install php php-cli php-mysql php-curl php-json php-mbstring php-xml php-zip -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install MySQL (if not already installed)
sudo apt install mysql-server -y

# Install Apache (or nginx)
sudo apt install apache2 -y
```

### 3. Clone or Copy Project Files

```bash
# If using Git:
git clone <repository-url> /var/www/ozna-bot

# Or copy files to:
sudo mkdir -p /var/www/ozna-bot
# Copy your project files to this directory
```

### 4. Set Proper Permissions

```bash
sudo chown -R www-data:www-data /var/www/ozna-bot
sudo chmod -R 755 /var/www/ozna-bot
```

### 5. Install PHP Dependencies

```bash
cd /var/www/ozna-bot
composer install
```

### 6. Configure Database

1. Start MySQL service:
```bash
sudo systemctl start mysql
sudo systemctl enable mysql
```

2. Secure MySQL installation:
```bash
sudo mysql_secure_installation
```

3. Create database and user:
```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE tg_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'WizardJIOCb'@'localhost' IDENTIFIED BY '7qQ#28051961';
GRANT ALL PRIVILEGES ON tg_bot.* TO 'WizardJIOCb'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

4. Import database schema (if applicable):
```bash
# If you have a SQL dump file
mysql -u WizardJIOCb -p tg_bot < database_schema.sql
```

### 7. Configure Bot Settings

Edit the [set.php](file:///c:/Projects/BorisTgBot/set.php) file to configure your bot:

```bash
nano /var/www/ozna-bot/set.php
```

Update these values:
- `$bot_api_key` - Your Telegram Bot API token
- `$bot_username` - Your bot's username
- `$hook_url` - Your webhook URL (e.g., https://yourdomain.com/tg/ozna/hook.php)

### 8. Configure Webhook Handler

Edit the [hook.php](file:///c:/Projects/BorisTgBot/hook.php) file to match your database credentials:

```bash
nano /var/www/ozna-bot/hook.php
```

Ensure the database credentials in the file match your setup:
- Host: `82.146.42.213` (or localhost if database is on the same server)
- User: `WizardJIOCb`
- Password: `7qQ#28051961`
- Database: `tg_bot`

### 9. Configure Apache Virtual Host

Create a new virtual host file:

```bash
sudo nano /etc/apache2/sites-available/ozna-bot.conf
```

Add the following configuration:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/ozna-bot

    <Directory /var/www/ozna-bot>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/ozna-bot_error.log
    CustomLog ${APACHE_LOG_DIR}/ozna-bot_access.log combined
</VirtualHost>
```

Enable the site and rewrite module:

```bash
sudo a2ensite ozna-bot.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 10. Set Up SSL Certificate (Recommended)

Install Certbot for Let's Encrypt SSL:

```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d yourdomain.com
```

### 11. Set Webhook

Run the set.php script to register your webhook:

```bash
cd /var/www/ozna-bot
php set.php
```

### 12. Configure Database Service

The bot uses a DatabaseService class located in [CustomCommands/Services/DatabaseService.php](file:///c:/Projects/BorisTgBot/CustomCommands/Services/DatabaseService.php). Ensure the database connection settings in this file match your setup.

### 13. Set Up Auto-loading

The project uses PSR-4 autoloading configured in [composer.json](file:///c:/Projects/BorisTgBot/composer.json). Make sure to run `composer dump-autoload` after any file changes:

```bash
cd /var/www/ozna-bot
composer dump-autoload
```

## Testing Deployment

1. Check Apache is running:
```bash
sudo systemctl status apache2
```

2. Test webhook endpoint:
```bash
curl -X POST https://yourdomain.com/tg/ozna/hook.php
```

3. Check bot responds in Telegram

4. Run the test script to verify class loading:
```bash
cd /var/www/ozna-bot
php class_loading_test.php
```

## Troubleshooting

### Common Issues

1. **Permission Errors**: Ensure www-data has proper access to all files:
```bash
sudo chown -R www-data:www-data /var/www/ozna-bot
```

2. **Database Connection Issues**: 
   - Verify database credentials in [hook.php](file:///c:/Projects/BorisTgBot/hook.php) and [DatabaseService.php](file:///c:/Projects/BorisTgBot/CustomCommands/Services/DatabaseService.php)
   - Check MySQL is running: `sudo systemctl status mysql`
   - Verify database and user exist

3. **Webhook Not Working**:
   - Check webhook URL is accessible from the internet
   - Verify SSL certificate is valid
   - Check Apache error logs: `sudo tail -f /var/log/apache2/error.log`

4. **Missing PHP Extensions**:
```bash
# Install required extensions
sudo apt install php-mysql php-curl php-json php-mbstring php-xml -y
sudo systemctl restart apache2
```

### Log Files

Check these log files for troubleshooting:
- Apache error log: `/var/log/apache2/error.log`
- Bot-specific logs: `/var/www/ozna-bot/telegram_bot_error.log`
- Access logs: `/var/log/apache2/access.log`

## Maintenance

### Updating the Bot

1. Pull latest changes or copy new files
2. Run composer install if dependencies changed:
```bash
cd /var/www/ozna-bot
composer install
```
3. Update autoload if files were added/removed:
```bash
composer dump-autoload
```
4. Restart Apache:
```bash
sudo systemctl reload apache2
```

### Managing Access Levels

Access levels can be managed through:
1. Web interface using [admin_access_manager.php](file:///c:/Projects/BorisTgBot/admin_access_manager.php)
2. Command line using [manage_access_levels.bat](file:///c:/Projects/BorisTgBot/manage_access_levels.bat) (can be run with wine or converted to shell script)
3. Direct database manipulation (see [ACCESS_MANAGEMENT_GUIDE.md](file:///c:/Projects/BorisTgBot/ACCESS_MANAGEMENT_GUIDE.md))

## Security Considerations

1. Restrict file permissions:
```bash
sudo find /var/www/ozna-bot -type f -exec chmod 644 {} \;
sudo find /var/www/ozna-bot -type d -exec chmod 755 {} \;
```

2. Protect sensitive files:
```bash
sudo chmod 600 /var/www/ozna-bot/set.php
sudo chmod 600 /var/www/ozna-bot/hook.php
```

3. Use firewall to restrict access:
```bash
sudo ufw allow ssh
sudo ufw allow 'Apache Full'
sudo ufw enable
```

## Backup Procedures

Regularly backup:
1. Database:
```bash
mysqldump -u WizardJIOCb -p tg_bot > tg_bot_backup_$(date +%F).sql
```

2. Application files:
```bash
tar -czvf ozna-bot-backup-$(date +%F).tar.gz /var/www/ozna-bot
```