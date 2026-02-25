-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS `punjabidomas_lms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create dedicated application user and grant privileges
-- Replace 'ChangeMe123!' with the same password set in .env
CREATE USER IF NOT EXISTS 'punjabidomas_user'@'localhost' IDENTIFIED BY 'ChangeMe123!';
GRANT ALL PRIVILEGES ON `punjabidomas_lms`.* TO 'punjabidomas_user'@'localhost';
FLUSH PRIVILEGES;

-- Optional: show grants for verification
SELECT user, host FROM mysql.user WHERE user = 'punjabidomas_user';
SHOW GRANTS FOR 'punjabidomas_user'@'localhost';
