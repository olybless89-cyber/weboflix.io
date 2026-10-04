-- ============================================================
-- Migration: add activity tracking
-- Run this in phpMyAdmin (SQL tab) against your EXISTING database.
-- Safe to run once — do not run schema.sql again on a live site,
-- it would try to recreate tables you already have data in.
-- ============================================================

ALTER TABLE users
  ADD COLUMN last_active_at TIMESTAMP NULL DEFAULT NULL AFTER is_admin,
  ADD KEY idx_last_active (last_active_at);
