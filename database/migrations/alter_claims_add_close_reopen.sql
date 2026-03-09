-- Migration: Add close/reopen tracking columns to claims table
-- Date: 2026-03-07

ALTER TABLE claims 
  ADD COLUMN closed_by INT NULL AFTER moderation_comment,
  ADD COLUMN closed_at DATETIME NULL AFTER closed_by,
  ADD COLUMN close_reason TEXT NULL AFTER closed_at,
  ADD COLUMN reopened_by INT NULL AFTER close_reason,
  ADD COLUMN reopened_at DATETIME NULL AFTER reopened_by;
