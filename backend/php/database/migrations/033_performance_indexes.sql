-- Phase 33: Performance indexes for common dashboard, public, and workflow queries.
-- These indexes are best-effort and use dynamic checks so the migration can run safely
-- even if some optional phase tables/columns are missing.

DELIMITER $$

CREATE PROCEDURE add_index_if_possible(
  IN p_table_name VARCHAR(128),
  IN p_index_name VARCHAR(128),
  IN p_columns TEXT
)
BEGIN
  IF EXISTS (
    SELECT 1 FROM information_schema.tables
    WHERE table_schema = DATABASE() AND table_name = p_table_name
  ) AND NOT EXISTS (
    SELECT 1 FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = p_table_name AND index_name = p_index_name
  ) THEN
    SET @sql = CONCAT('ALTER TABLE `', p_table_name, '` ADD INDEX `', p_index_name, '` (', p_columns, ')');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END$$

DELIMITER ;

CALL add_index_if_possible('users', 'idx_users_role_status', '`role`, `status`');
CALL add_index_if_possible('audit_logs', 'idx_audit_created_entity', '`created_at`, `entity_type`, `entity_id`');
CALL add_index_if_possible('settings', 'idx_settings_group', '`setting_group`');

CALL add_index_if_possible('checkout_orders', 'idx_checkout_reference_status', '`checkout_reference`, `payment_status`');
CALL add_index_if_possible('purchases', 'idx_purchases_status_created', '`payment_status`, `created_at`');
CALL add_index_if_possible('payment_records', 'idx_payment_status_created', '`status`, `created_at`');

CALL add_index_if_possible('student_profiles', 'idx_student_profiles_user', '`user_id`');
CALL add_index_if_possible('parent_child_links', 'idx_parent_child_parent', '`parent_user_id`, `child_user_id`');
CALL add_index_if_possible('academy_briefs', 'idx_academy_briefs_partner_status', '`academy_partner_user_id`, `status`, `created_at`');

CALL add_index_if_possible('lesson_packages', 'idx_lesson_packages_student_status', '`student_user_id`, `status`');
CALL add_index_if_possible('lesson_credit_transactions', 'idx_credit_student_created', '`student_user_id`, `created_at`');
CALL add_index_if_possible('lesson_sessions', 'idx_sessions_student_start', '`student_user_id`, `start_at`');
CALL add_index_if_possible('lesson_sessions', 'idx_sessions_status_start', '`status`, `start_at`');
CALL add_index_if_possible('bookings', 'idx_bookings_status_start', '`status`, `start_at`');
CALL add_index_if_possible('bookings', 'idx_bookings_student_status', '`student_user_id`, `status`');

CALL add_index_if_possible('homeworks', 'idx_homeworks_student_status', '`student_user_id`, `status`, `created_at`');
CALL add_index_if_possible('homework_submissions', 'idx_hw_submissions_student_status', '`student_user_id`, `status`, `created_at`');
CALL add_index_if_possible('scenarios', 'idx_scenarios_student_status', '`student_user_id`, `status`, `created_at`');
CALL add_index_if_possible('scenario_submissions', 'idx_scenario_submissions_student_status', '`student_user_id`, `status`, `created_at`');
CALL add_index_if_possible('review_tests', 'idx_reviews_status_created', '`status`, `created_at`');
CALL add_index_if_possible('review_submissions', 'idx_review_submissions_student_status', '`student_user_id`, `status`, `created_at`');

CALL add_index_if_possible('course_materials', 'idx_materials_status_type', '`status`, `type`, `created_at`');
CALL add_index_if_possible('material_assignments', 'idx_material_assignments_student_visible', '`student_id`, `visible`, `assigned_at`');
CALL add_index_if_possible('material_progress', 'idx_material_progress_student_status', '`student_id`, `status`, `last_opened_at`');

CALL add_index_if_possible('notifications', 'idx_notifications_user_read_created', '`target_user_id`, `read_at`, `created_at`');
CALL add_index_if_possible('analytics_events', 'idx_analytics_type_created', '`event_type`, `created_at`');

CALL add_index_if_possible('articles', 'idx_articles_status_slug', '`status`, `slug`');
CALL add_index_if_possible('videos', 'idx_videos_status_slug', '`status`, `slug`');
CALL add_index_if_possible('testimonials', 'idx_testimonials_status_featured', '`status`, `featured`, `show_on_homepage`');

CALL add_index_if_possible('media_buyer_profiles', 'idx_media_buyer_code_status', '`partner_code`, `status`');
CALL add_index_if_possible('attribution_events', 'idx_attribution_buyer_type_created', '`media_buyer_id`, `event_type`, `created_at`');
CALL add_index_if_possible('order_attributions', 'idx_order_attr_buyer_order', '`media_buyer_id`, `checkout_order_id`');
CALL add_index_if_possible('commission_records', 'idx_commissions_buyer_status', '`media_buyer_id`, `status`, `created_at`');

DROP PROCEDURE IF EXISTS add_index_if_possible;
