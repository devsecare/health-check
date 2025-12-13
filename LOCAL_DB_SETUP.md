# Local MySQL Database Setup Guide

## ✅ Configuration Updated

Your Laravel project is now configured for **local MySQL**:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecare_health_checker
DB_USERNAME=ecare_health_checker_ai
DB_PASSWORD=7Ze*@oW6ouTau8yv
```

## 🚀 Step-by-Step Setup

### Step 1: Start MySQL Server

Choose one method based on how MySQL is installed:

**Option A: Using MySQL Server Script (Most Common)**
```bash
sudo /usr/local/mysql/support-files/mysql.server start
```

**Option B: Using Homebrew (if installed via Homebrew)**
```bash
brew services start mysql
```

**Option C: Using Launchctl (macOS)**
```bash
launchctl load -w ~/Library/LaunchAgents/com.mysql.mysqld.plist
```

**Option D: If using MAMP/XAMPP**
- Start MAMP/XAMPP from Applications
- MySQL should start automatically

### Step 2: Verify MySQL is Running

```bash
# Check if MySQL process is running
ps aux | grep mysql | grep -v grep

# Test connection as root
mysql -u root -e "SELECT VERSION();"
```

### Step 3: Create Database and User

Once MySQL is running, execute these commands:

```bash
# Connect to MySQL as root (you'll be prompted for password)
mysql -u root -p
```

Then run these SQL commands:

```sql
-- Create database
CREATE DATABASE IF NOT EXISTS ecare_health_checker 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Create user for localhost
CREATE USER IF NOT EXISTS 'ecare_health_checker_ai'@'localhost' 
IDENTIFIED BY '7Ze*@oW6ouTau8yv';

-- Create user for 127.0.0.1
CREATE USER IF NOT EXISTS 'ecare_health_checker_ai'@'127.0.0.1' 
IDENTIFIED BY '7Ze*@oW6ouTau8yv';

-- Grant privileges
GRANT ALL PRIVILEGES ON ecare_health_checker.* 
TO 'ecare_health_checker_ai'@'localhost';

GRANT ALL PRIVILEGES ON ecare_health_checker.* 
TO 'ecare_health_checker_ai'@'127.0.0.1';

-- Apply changes
FLUSH PRIVILEGES;

-- Verify
SHOW DATABASES LIKE 'ecare_health_checker';
SELECT user, host FROM mysql.user WHERE user = 'ecare_health_checker_ai';

-- Exit
EXIT;
```

**OR use the SQL file:**
```bash
mysql -u root -p < setup-local-db.sql
```

### Step 4: Test Connection

```bash
# Test direct connection
mysql -h 127.0.0.1 -u ecare_health_checker_ai -p'7Ze*@oW6ouTau8yv' ecare_health_checker -e "SELECT 'Connected!' as Test;"

# Test via Laravel
php test-db-connection.php

# Test via Laravel Artisan
php artisan migrate:status
```

## ✅ Quick Setup Script

If you prefer, you can run all commands at once (as root):

```bash
# Start MySQL
sudo /usr/local/mysql/support-files/mysql.server start

# Create database and user (will prompt for root password)
mysql -u root -p << EOF
CREATE DATABASE IF NOT EXISTS ecare_health_checker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'ecare_health_checker_ai'@'localhost' IDENTIFIED BY '7Ze*@oW6ouTau8yv';
CREATE USER IF NOT EXISTS 'ecare_health_checker_ai'@'127.0.0.1' IDENTIFIED BY '7Ze*@oW6ouTau8yv';
GRANT ALL PRIVILEGES ON ecare_health_checker.* TO 'ecare_health_checker_ai'@'localhost';
GRANT ALL PRIVILEGES ON ecare_health_checker.* TO 'ecare_health_checker_ai'@'127.0.0.1';
FLUSH PRIVILEGES;
EOF

# Test connection
php test-db-connection.php
```

## 🎯 After Setup

Once connected, you can:

1. **Run Laravel Migrations:**
   ```bash
   php artisan migrate
   ```

2. **Use Database in Code:**
   ```php
   DB::table('users')->get();
   ```

3. **Connect to Real Data in Admin Dashboard:**
   - Update dashboard views to use database queries
   - Replace mock data with real data

## 🔧 Troubleshooting

### MySQL Won't Start
```bash
# Check MySQL error log
tail -f /usr/local/mysql/data/*.err

# Check if port 3306 is in use
lsof -i :3306

# Try starting with verbose output
sudo /usr/local/mysql/bin/mysqld_safe --user=mysql &
```

### Permission Denied
```bash
# Reset MySQL root password if needed
sudo mysql -u root
ALTER USER 'root'@'localhost' IDENTIFIED BY 'your_new_password';
FLUSH PRIVILEGES;
```

### Socket File Not Found
```bash
# Find MySQL socket
find /tmp -name "mysql.sock" 2>/dev/null
find /var/mysql -name "mysql.sock" 2>/dev/null

# If found, you may need to create symlink
ln -s /path/to/mysql.sock /tmp/mysql.sock
```

## 📝 Files Created

- ✅ `.env` - Configured for local MySQL
- ✅ `setup-local-db.sql` - Database setup SQL script
- ✅ `test-db-connection.php` - Connection test script

## ✨ Next Steps

1. Start MySQL server
2. Create database and user
3. Test connection
4. Run migrations: `php artisan migrate`
5. Start using database in your admin dashboard!

---

**Current Status**: Configuration ready ✅ | MySQL server needs to be started ⚠️

