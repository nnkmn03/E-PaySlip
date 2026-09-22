-- hr_login.sql
-- ---------------------------------------------------------------
-- Run this once in phpMyAdmin's SQL tab against the `epayslip` database.
-- It only creates the table -- it does NOT insert a starting password,
-- so there's no default credential sitting in your codebase/git history.
--
-- After running this, generate your real first password:
--   1. In Laragon's Terminal: php hash_password.php "yourChosenPassword"
--   2. Copy the hash it prints
--   3. Run the INSERT below in phpMyAdmin, pasting that hash in:
--
--   INSERT INTO hr_credentials (username, password_hash)
--   VALUES ('hr', 'PASTE_THE_HASH_HERE');
-- ---------------------------------------------------------------

CREATE TABLE IF NOT EXISTS hr_credentials (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    username       VARCHAR(50) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
