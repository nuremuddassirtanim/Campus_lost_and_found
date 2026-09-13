-- AIUB Lost & Found — schema + seed
-- Import this file in phpMyAdmin (Import tab). It creates the database and all tables.

CREATE DATABASE IF NOT EXISTS 
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aiub_lostfound;

DROP TABLE IF EXISTS claims;
DROP TABLE IF EXISTS items;
DROP TABLE IF EXISTS remember_tokens;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(120)  NOT NULL,
  student_id    VARCHAR(32)   NULL,
  email         VARCHAR(160)  NOT NULL UNIQUE,
  password_hash VARCHAR(255)  NOT NULL,
  role          ENUM('student','admin') NOT NULL DEFAULT 'student',
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE remember_tokens (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  selector   CHAR(24)     NOT NULL UNIQUE,
  validator  CHAR(64)     NOT NULL,          -- sha256 of the random half
  expires_at DATETIME     NOT NULL,
  CONSTRAINT fk_rt_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE items (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ref         VARCHAR(16)  NOT NULL UNIQUE,  -- LF-0001
  user_id     INT UNSIGNED NOT NULL,
  type        ENUM('lost','found')  NOT NULL,
  title       VARCHAR(160) NOT NULL,
  category    VARCHAR(60)  NOT NULL,
  location    VARCHAR(160) NOT NULL,
  description TEXT         NOT NULL,
  verify_q    VARCHAR(255) NULL,             -- question a claimant must answer
  photo       VARCHAR(255) NULL,
  status      ENUM('pending','open','matched','returned','rejected') NOT NULL DEFAULT 'pending',
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_item_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  KEY idx_status (status), KEY idx_type (type), KEY idx_cat (category)
) ENGINE=InnoDB;

CREATE TABLE claims (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  item_id    INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NOT NULL,
  answer     TEXT         NOT NULL,
  contact    VARCHAR(160) NOT NULL,
  status     ENUM('pending','approved','declined') NOT NULL DEFAULT 'pending',
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_claim_item FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
  CONSTRAINT fk_claim_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_claim (item_id, user_id)
) ENGINE=InnoDB;

-- Seed: the admin row. The password hash is written by PHP, not by SQL --
-- open http://localhost/lostfound-php/public/setup.php once after importing.
INSERT INTO users (name, student_id, email, password_hash, role) VALUES
  ('Campus Security Desk', NULL, 'admin@aiub.edu', 'NEEDS_SETUP', 'admin');
