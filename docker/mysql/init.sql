CREATE DATABASE IF NOT EXISTS seminar_ta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS seminar_ta_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'seminar'@'%' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON seminar_ta.* TO 'seminar'@'%';
GRANT ALL PRIVILEGES ON seminar_ta_test.* TO 'seminar'@'%';
FLUSH PRIVILEGES;
