-- ============================================================
-- Migration: add featured-course flag (for the homepage hero video)
-- Run this in phpMyAdmin (SQL tab) against your EXISTING database.
-- Safe to run once — do not run schema.sql again on a live site.
-- ============================================================

ALTER TABLE courses
  ADD COLUMN is_featured TINYINT(1) DEFAULT 0 AFTER is_published;
