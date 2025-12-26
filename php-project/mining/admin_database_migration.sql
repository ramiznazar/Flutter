-- Admin Panel Authentication - Database Migration
-- Run this script to create the admin table for authentication

CREATE TABLE IF NOT EXISTS `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` datetime NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- IMPORTANT: After running this migration, create your first admin user
-- Option 1: Use the create_admin.php script in the admin folder
-- Option 2: Manually insert an admin user with a hashed password:
--   INSERT INTO `admin` (`username`, `email`, `password`, `name`) 
--   VALUES ('admin', 'admin@crutox.com', '$2y$10$...', 'Admin User');
--   
--   To generate password hash, use PHP:
--   echo password_hash('your_password', PASSWORD_DEFAULT);

