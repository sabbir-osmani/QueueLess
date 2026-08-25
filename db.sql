-- ============================================
-- QueueLess - Full Database Schema
-- Multi-Service Digital Queue Management System
-- Green University of Bangladesh - Web Programming Lab
-- ============================================
-- Import this file in phpMyAdmin (or run via SQL tab)
-- to create the complete database from scratch.

CREATE DATABASE IF NOT EXISTS queueless_db;
USE queueless_db;

-- ============================================
-- Table 1: students
-- Holds every registered student account.
-- ============================================
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL
);

-- ============================================
-- Table 2: tokens
-- The actual queue entries - the heart of the system.
-- One student (1) -> many tokens (many), so student_id
-- is a foreign key here (the "many" side always holds the FK).
-- ============================================
CREATE TABLE tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    service VARCHAR(30) NOT NULL,
    token_no VARCHAR(10) NOT NULL,
    status ENUM('waiting', 'serving', 'completed', 'skipped', 'cancelled') NOT NULL DEFAULT 'waiting',
    created_at DATETIME NOT NULL,

    FOREIGN KEY (student_id) REFERENCES students(id),

    -- prevents two different rows from ever getting the same
    -- token number within the same service (e.g. two "A007" for accounts)
    UNIQUE KEY unique_service_token (service, token_no)
);

-- ============================================
-- Table 3: admins
-- Each admin is tied to exactly ONE service and can only
-- manage that service's queue (enforced in PHP via session).
-- Multiple admins can share the same service if needed.
-- ============================================
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL,
    service VARCHAR(30) NOT NULL
);

-- ============================================
-- Notes on column choices (useful for viva):
--
-- pass VARCHAR(255)
--   -> password_hash() output (bcrypt) is ~60 chars, 255 gives
--      comfortable room and avoids truncation bugs.
--
-- tokens.service VARCHAR(30)
--   -> one of: 'accounts', 'library', 'cse', 'lab'
--      (not an ENUM here so new services can be added later
--      without altering the table structure)
--
-- tokens.token_no VARCHAR(10)
--   -> looks like "A007", a letter + number combo, so it can
--      never be a numeric column type.
--
-- tokens.status ENUM(...)
--   -> restricts the column to exactly 5 known states, which
--      prevents typos like 'compelted' silently breaking queries.
--   -> waiting   = token created, not yet called
--   -> serving   = admin has called this token in, in progress
--   -> completed = admin marked the visit finished
--   -- skipped   = admin skipped this token (no-show)
--   -> cancelled = student cancelled their own token while waiting
--
-- unique_service_token (service, token_no)
--   -> database-level guarantee that no two rows for the same
--      service can ever share a token number, even under a race
--      condition where two students click "Take Token" at once.
-- ============================================