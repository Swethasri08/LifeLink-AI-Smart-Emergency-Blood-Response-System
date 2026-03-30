# 🚀 Installation Guide

This detailed guide will help you set up the Blood Donation Management System (BDMS) on your local machine or server.

## 📋 System Requirements

### Minimum Requirements
- **PHP**: 8.1 or higher
- **MySQL/MariaDB**: 10.4 or higher
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **RAM**: 2GB minimum
- **Storage**: 500MB free space

### Recommended Requirements
- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or higher
- **RAM**: 4GB or more
- **Storage**: 1GB or more

### Required PHP Extensions
- `mysqli` - Database connectivity
- `pdo_mysql` - Database abstraction
- `mbstring` - Multi-byte string handling
- `curl` - HTTP client functionality
- `gd` - Image processing
- `openssl` - SSL/TLS support
- `json` - JSON handling
- `session` - Session management
- `fileinfo` - File information

## 🔧 Installation Methods

### Method 1: XAMPP/WAMP/MAMP (Recommended for Beginners)

#### Step 1: Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org)
2. Run the installer with default settings
3. Start Apache and MySQL from XAMPP Control Panel

#### Step 2: Download the Project
```bash
# Option A: Clone from GitHub
git clone https://github.com/Swethasri08/LifeLink-AI-Smart-Emergency-Blood-Response-System.git

# Option B: Download ZIP
# Extract the ZIP file to C:/xampp/htdocs/bdms/
```

#### Step 3: Database Setup
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click "New" to create a database
3. Name it `bdms`
4. Click "Import" tab
5. Select `database_complete.sql` from the project
6. Click "Go" to import

#### Step 4: Configure Database
Edit `C:/xampp/htdocs/bdms/config/database.php`:
```php
<?php
$host = "localhost";
$username = "root";
$password = ""; // Leave empty for XAMPP default
$database = "bdms";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
```

#### Step 5: Access the Application
Open your browser and go to: http://localhost/bdms

---

### Method 2: LAMP/LEMP Stack (Linux)

#### Step 1: Install Required Packages
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install apache2 mysql-server php8.1 php8.1-mysql php8.1-mbstring php8.1-curl php8.1-gd php8.1-xml composer git

# CentOS/RHEL
sudo yum install httpd mariadb-server php php-mysql php-mbstring php-curl php-gd php-xml composer git
```

#### Step 2: Configure Apache
```bash
# Enable Apache
sudo systemctl enable apache2
sudo systemctl start apache2

# Create virtual host (optional)
sudo nano /etc/apache2/sites-available/bdms.conf
```

Virtual host configuration:
```apache
<VirtualHost *:80>
    ServerName bdms.local
    DocumentRoot /var/www/bdms
    
    <Directory /var/www/bdms>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Enable the site:
```bash
sudo a2ensite bdms.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

#### Step 3: Configure MySQL
```bash
sudo mysql_secure_installation
sudo mysql -u root -p
```

Create database:
```sql
CREATE DATABASE bdms;
CREATE USER 'bdms_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON bdms.* TO 'bdms_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Step 4: Deploy Application
```bash
sudo git clone https://github.com/Swethasri08/LifeLink-AI-Smart-Emergency-Blood-Response-System.git /var/www/bdms
sudo chown -R www-data:www-data /var/www/bdms
sudo chmod -R 755 /var/www/bdms
```

#### Step 5: Import Database
```bash
mysql -u bdms_user -p bdms < /var/www/bdms/database_complete.sql
```

#### Step 6: Configure Application
Edit `/var/www/bdms/config/database.php`:
```php
<?php
$host = "localhost";
$username = "bdms_user";
$password = "your_password";
$database = "bdms";

$conn = mysqli_connect($host, $username, $password, $database);
?>
```

---

### Method 3: Docker (Advanced)

#### Step 1: Create Dockerfile
```dockerfile
FROM php:8.1-apache

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo_mysql mbstring curl gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Expose port
EXPOSE 80
```

#### Step 2: Create docker-compose.yml
```yaml
version: '3.8'

services:
  web:
    build: .
    ports:
      - "8000:80"
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_NAME=bdms
      - DB_USER=bdms_user
      - DB_PASSWORD=password

  db:
    image: mysql:8.0
    environment:
      - MYSQL_DATABASE=bdms
      - MYSQL_USER=bdms_user
      - MYSQL_PASSWORD=password
      - MYSQL_ROOT_PASSWORD=rootpassword
    volumes:
      - db_data:/var/lib/mysql
      - ./database_complete.sql:/docker-entrypoint-initdb.d/init.sql

volumes:
  db_data:
```

#### Step 3: Run Docker
```bash
docker-compose up -d
```

---

### Method 4: PHP Built-in Server (Development Only)

#### Step 1: Install PHP and MySQL
Follow Method 1 or 2 for installing PHP and MySQL.

#### Step 2: Setup Database
Use phpMyAdmin or command line to import `database_complete.sql`

#### Step 3: Start Server
```bash
cd /path/to/bdms
php -S localhost:8000
```

Access: http://localhost:8000

---

## 🔍 Verification Steps

After installation, verify everything is working:

### 1. Database Connection
Create a test file `test_db.php`:
```php
<?php
require_once 'config/database.php';

if ($conn) {
    echo "✅ Database connection successful!";
} else {
    echo "❌ Database connection failed!";
}
?>
```

### 2. Application Access
- Open browser to your application URL
- You should see the login page
- Try logging in with default credentials

### 3. Test Key Features
- User registration
- Login functionality
- Dashboard access
- Database operations

## 🛠️ Troubleshooting

### Common Issues

#### 1. Database Connection Error
**Problem**: "Connection failed" error
**Solution**:
- Check MySQL service is running
- Verify database credentials
- Ensure database exists
- Check firewall settings

#### 2. PHP Extensions Missing
**Problem**: "Call to undefined function" errors
**Solution**:
```bash
# Ubuntu/Debian
sudo apt install php8.1-mysql php8.1-mbstring php8.1-curl php8.1-gd

# Restart Apache
sudo systemctl restart apache2
```

#### 3. Permission Issues
**Problem**: "Permission denied" errors
**Solution**:
```bash
sudo chown -R www-data:www-data /path/to/bdms
sudo chmod -R 755 /path/to/bdms
```

#### 4. Blank White Page
**Problem**: White screen of death
**Solution**:
- Enable PHP error reporting
- Check Apache error logs
- Verify .htaccess configuration

#### 5. Session Issues
**Problem**: Login not persisting
**Solution**:
- Check session save path permissions
- Verify session configuration in php.ini
- Clear browser cookies

### Error Logs Locations
- **Apache Error Log**: `/var/log/apache2/error.log` (Linux) or `C:/xampp/apache/logs/error.log` (Windows)
- **PHP Error Log**: Check php.ini for `error_log` directive
- **MySQL Error Log**: `/var/log/mysql/error.log` (Linux) or `C:/xampp/mysql/data/mysql.err` (Windows)

## 🔧 Configuration Options

### PHP Configuration (php.ini)
```ini
; Recommended settings for BDMS
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 10M
post_max_size = 10M
session.gc_maxlifetime = 7200
```

### Apache Configuration (.htaccess)
```apache
# Enable URL rewriting
RewriteEngine On

# Security headers
Header set X-Frame-Options "SAMEORIGIN"
Header set X-Content-Type-Options "nosniff"

# PHP settings
php_value memory_limit 256M
php_value max_execution_time 300
```

## 🚀 Production Deployment

### Security Considerations
1. **Change default passwords**
2. **Use HTTPS** (SSL certificate)
3. **Disable error display** in production
4. **Set up firewall** rules
5. **Regular backups**
6. **Update dependencies** regularly

### Performance Optimization
1. **Enable PHP OPcache**
2. **Use Redis** for session storage
3. **Configure MySQL** properly
4. **Enable gzip compression**
5. **Use CDN** for static assets

### Backup Strategy
```bash
# Database backup
mysqldump -u root -p bdms > backup_$(date +%Y%m%d).sql

# Files backup
tar -czf bdms_files_$(date +%Y%m%d).tar.gz /path/to/bdms
```

## 📞 Support

If you encounter issues during installation:

1. **Check the troubleshooting section** above
2. **Search existing GitHub issues**
3. **Create a new issue** with:
   - Your operating system
   - PHP and MySQL versions
   - Error messages
   - Steps you've tried

4. **Join our community discussions** for help from other users

---

🎉 **Congratulations!** You have successfully installed the Blood Donation Management System. Start saving lives! 🩸❤️
