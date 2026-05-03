-- Phase 9: Real Booking Availability Calendar
-- Run after Phase 8 migration.

ALTER TABLE availability_rules
  ADD COLUMN IF NOT EXISTS session_duration_minutes INT UNSIGNED NULL AFTER end_time,
  ADD COLUMN IF NOT EXISTS buffer_minutes INT UNSIGNED NULL AFTER session_duration_minutes,
  ADD COLUMN IF NOT EXISTS max_sessions_per_day INT UNSIGNED NULL AFTER buffer_minutes,
  ADD COLUMN IF NOT EXISTS notes TEXT NULL AFTER max_sessions_per_day;

ALTER TABLE blocked_times
  ADD COLUMN IF NOT EXISTS is_full_day TINYINT(1) NOT NULL DEFAULT 0 AFTER end_at;

-- Keep legacy 'cancelled' while adding the Phase 9 statuses.
ALTER TABLE bookings
  MODIFY COLUMN status ENUM('requested','confirmed','rejected','reschedule_requested','rescheduled','canceled','cancelled','completed','no_show') NOT NULL DEFAULT 'requested',
  ADD COLUMN IF NOT EXISTS owner_user_id INT UNSIGNED NULL AFTER parent_user_id,
  ADD COLUMN IF NOT EXISTS requested_by_user_id INT UNSIGNED NULL AFTER owner_user_id,
  ADD COLUMN IF NOT EXISTS package_id INT UNSIGNED NULL AFTER session_id,
  ADD COLUMN IF NOT EXISTS title VARCHAR(190) NULL AFTER package_id,
  ADD COLUMN IF NOT EXISTS meeting_link VARCHAR(255) NULL AFTER end_at,
  ADD COLUMN IF NOT EXISTS student_note TEXT NULL AFTER meeting_link,
  ADD COLUMN IF NOT EXISTS owner_note TEXT NULL AFTER student_note,
  ADD COLUMN IF NOT EXISTS reschedule_reason TEXT NULL AFTER owner_note,
  ADD COLUMN IF NOT EXISTS reschedule_requested_at DATETIME NULL AFTER reschedule_reason,
  ADD COLUMN IF NOT EXISTS confirmed_by_user_id INT UNSIGNED NULL AFTER reschedule_requested_at,
  ADD COLUMN IF NOT EXISTS confirmed_at DATETIME NULL AFTER confirmed_by_user_id,
  ADD COLUMN IF NOT EXISTS canceled_at DATETIME NULL AFTER confirmed_at;

CREATE INDEX IF NOT EXISTS idx_booking_time_status ON bookings (start_at, end_at, status);
CREATE INDEX IF NOT EXISTS idx_booking_student_status ON bookings (student_user_id, status);
CREATE INDEX IF NOT EXISTS idx_booking_parent_status ON bookings (parent_user_id, status);
CREATE INDEX IF NOT EXISTS idx_booking_owner_status ON bookings (owner_user_id, status);

-- Optional FK additions are intentionally avoided here to keep the migration portable
-- across existing staging databases that may already have partial data.

INSERT INTO settings (setting_key, setting_value, setting_group, value_type, is_public)
VALUES
('booking.default_session_duration_minutes', '90', 'booking', 'number', 0),
('booking.default_buffer_minutes', '0', 'booking', 'number', 0),
('booking.max_days_ahead', '30', 'booking', 'number', 0),
('booking.allow_parent_booking', '1', 'booking', 'boolean', 0),
('booking.allow_student_booking', '1', 'booking', 'boolean', 0),
('booking.reschedule_min_hours_before_lesson', '24', 'booking', 'number', 0),
('booking.default_meeting_link', '', 'booking', 'string', 0)
ON DUPLICATE KEY UPDATE setting_key = VALUES(setting_key);
