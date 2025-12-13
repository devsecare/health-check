-- Setup local MySQL database for eCare Health Checker
-- Run this file: mysql -u root -p < setup-local-db.sql

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS ecare_health_checker 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Create user if it doesn't exist and grant privileges
-- If user exists, this will update the password
CREATE USER IF NOT EXISTS 'ecare_health_checker_ai'@'localhost' IDENTIFIED BY '7Ze*@oW6ouTau8yv';

-- Grant all privileges on the database
GRANT ALL PRIVILEGES ON ecare_health_checker.* 
TO 'ecare_health_checker_ai'@'localhost';

-- Also allow connections from 127.0.0.1
CREATE USER IF NOT EXISTS 'ecare_health_checker_ai'@'127.0.0.1' IDENTIFIED BY '7Ze*@oW6ouTau8yv';
GRANT ALL PRIVILEGES ON ecare_health_checker.* 
TO 'ecare_health_checker_ai'@'127.0.0.1';

-- Flush privileges to apply changes
FLUSH PRIVILEGES;

-- Show confirmation
SELECT 'Database and user created successfully!' as Status;
SHOW DATABASES LIKE 'ecare_health_checker';

