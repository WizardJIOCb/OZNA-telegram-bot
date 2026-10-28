-- Migration: Add share_location column to pwa_users
-- Default TRUE (1) since this is a work tool - location sharing is on by default

ALTER TABLE pwa_users ADD COLUMN share_location TINYINT(1) NOT NULL DEFAULT 1;
