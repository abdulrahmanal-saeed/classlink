-- Final current audit fix: make Phase 1/2 demo seeders safer to rerun.
-- Run this after migrations 001 and 002.

-- user_profiles seeder uses ON DUPLICATE KEY on user_id, so user_id must be unique.
-- The delete keeps the oldest profile per user if duplicates were created before this fix.
DELETE up1 FROM user_profiles up1
INNER JOIN user_profiles up2
  ON up1.user_id = up2.user_id
 AND up1.id > up2.id;

ALTER TABLE user_profiles
  ADD UNIQUE KEY uniq_user_profiles_user (user_id);

-- Plans need a stable uniqueness rule so demo seed data does not create duplicate plan rows.
ALTER TABLE plans
  ADD UNIQUE KEY uniq_plans_name_currency (name_en, currency);
