<?php
/**
 * Phase 9 Booking Calendar helper.
 * Handles availability rules, unavailable times, available slots, booking requests,
 * Owner confirmation, and simple reschedule flow.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/LessonCredits.php';

function booking_setting(string $key, $default = null)
{
    $s = db()->prepare('SELECT setting_value, value_type FROM settings WHERE setting_key = :key LIMIT 1');
    $s->execute([':key' => $key]);
    $row = $s->fetch();
    if (!$row) return $default;
    return match ($row['value_type']) {
        'boolean' => in_array(strtolower((string) $row['setting_value']), ['1','true','yes','on'], true),
        'number' => (int) $row['setting_value'],
        default => $row['setting_value'],
    };
}

function booking_owner_id(): ?int
{
    $id = db()->query('SELECT id FROM users WHERE role = "owner_teacher" AND status = "active" ORDER BY id ASC LIMIT 1')->fetchColumn();
    return $id ? (int) $id : null;
}

function booking_owner_rules(?int $ownerId = null): array
{
    $ownerId = $ownerId ?: booking_owner_id();
    if (!$ownerId) return [];
    $s = db()->prepare('SELECT * FROM availability_rules WHERE owner_user_id = :owner AND is_active = 1 ORDER BY day_of_week, start_time');
    $s->execute([':owner' => $ownerId]);
    return $s->fetchAll();
}

function booking_owner_unavailable(?int $ownerId = null, ?string $from = null, ?string $to = null): array
{
    $ownerId = $ownerId ?: booking_owner_id();
    if (!$ownerId) return [];
    $sql = 'SELECT * FROM blocked_times WHERE owner_user_id = :owner';
    $params = [':owner' => $ownerId];
    if ($from && $to) {
        $sql .= ' AND start_at < :to_date AND end_at > :from_date';
        $params[':from_date'] = $from;
        $params[':to_date'] = $to;
    }
    $s = db()->prepare($sql . ' ORDER BY start_at');
    $s->execute($params);
    return $s->fetchAll();
}

function booking_create_rule(int $ownerId, int $day, string $start, string $end, int $duration, int $buffer, ?int $maxDay, string $timezone = 'Asia/Dubai', string $notes = ''): void
{
    if ($day < 0 || $day > 6) throw new RuntimeException('Invalid day. Use 0 Sunday to 6 Saturday.');
    if ($start >= $end) throw new RuntimeException('Start time must be before end time.');
    db()->prepare('INSERT INTO availability_rules (owner_user_id, day_of_week, start_time, end_time, timezone, session_duration_minutes, buffer_minutes, max_sessions_per_day, notes, is_active) VALUES (:owner, :day, :start, :end, :tz, :duration, :buffer, :max_day, :notes, 1)')
        ->execute([':owner' => $ownerId, ':day' => $day, ':start' => $start, ':end' => $end, ':tz' => $timezone, ':duration' => $duration, ':buffer' => $buffer, ':max_day' => $maxDay, ':notes' => $notes ?: null]);
    audit_log($ownerId, 'availability_rule_created', 'availability_rule', (string) db()->lastInsertId(), ['day_of_week' => $day]);
}

function booking_create_unavailable(int $ownerId, string $startAt, string $endAt, string $reason = ''): void
{
    if (strtotime($startAt) >= strtotime($endAt)) throw new RuntimeException('Start must be before end.');
    db()->prepare('INSERT INTO blocked_times (owner_user_id, start_at, end_at, reason) VALUES (:owner, :start_at, :end_at, :reason)')
        ->execute([':owner' => $ownerId, ':start_at' => $startAt, ':end_at' => $endAt, ':reason' => $reason ?: null]);
    audit_log($ownerId, 'calendar_unavailable_time_created', 'blocked_time', (string) db()->lastInsertId(), ['start_at' => $startAt, 'end_at' => $endAt]);
}

function booking_ranges_overlap(string $aStart, string $aEnd, string $bStart, string $bEnd): bool
{
    return strtotime($aStart) < strtotime($bEnd) && strtotime($aEnd) > strtotime($bStart);
}

function booking_slot_has_unavailable(string $startAt, string $endAt, array $items): bool
{
    foreach ($items as $item) {
        if (booking_ranges_overlap($startAt, $endAt, $item['start_at'], $item['end_at'])) return true;
    }
    return false;
}

function booking_slot_is_taken(string $startAt, string $endAt, ?int $ignoreId = null): bool
{
    $sql = 'SELECT id FROM bookings WHERE status IN ("requested","confirmed","reschedule_requested","rescheduled") AND start_at < :end_at AND end_at > :start_at';
    $params = [':start_at' => $startAt, ':end_at' => $endAt];
    if ($ignoreId) {
        $sql .= ' AND id != :ignore_id';
        $params[':ignore_id'] = $ignoreId;
    }
    $s = db()->prepare($sql . ' LIMIT 1');
    $s->execute($params);
    return (bool) $s->fetch();
}

function booking_generate_slots(?int $ownerId = null, int $daysAhead = 14): array
{
    $ownerId = $ownerId ?: booking_owner_id();
    if (!$ownerId) return [];
    $rules = booking_owner_rules($ownerId);
    $today = new DateTime('today', new DateTimeZone('Asia/Dubai'));
    $to = (clone $today)->modify('+' . $daysAhead . ' days');
    $unavailable = booking_owner_unavailable($ownerId, $today->format('Y-m-d 00:00:00'), $to->format('Y-m-d 23:59:59'));
    $slots = [];

    for ($i = 0; $i <= $daysAhead; $i++) {
        $date = (clone $today)->modify('+' . $i . ' days');
        $dow = (int) $date->format('w');
        foreach ($rules as $rule) {
            if ((int) $rule['day_of_week'] !== $dow) continue;
            $duration = (int) ($rule['session_duration_minutes'] ?: booking_setting('booking.default_session_duration_minutes', 90));
            $buffer = (int) ($rule['buffer_minutes'] ?: booking_setting('booking.default_buffer_minutes', 0));
            $cursor = new DateTime($date->format('Y-m-d') . ' ' . $rule['start_time']);
            $limit = new DateTime($date->format('Y-m-d') . ' ' . $rule['end_time']);
            while (true) {
                $end = (clone $cursor)->modify('+' . $duration . ' minutes');
                if ($end > $limit) break;
                $startAt = $cursor->format('Y-m-d H:i:s');
                $endAt = $end->format('Y-m-d H:i:s');
                if ($cursor > new DateTime() && !booking_slot_has_unavailable($startAt, $endAt, $unavailable) && !booking_slot_is_taken($startAt, $endAt)) {
                    $slots[] = ['start_at' => $startAt, 'end_at' => $endAt, 'label' => $cursor->format('D, d M Y H:i') . ' - ' . $end->format('H:i')];
                }
                $cursor = $end->modify('+' . $buffer . ' minutes');
            }
        }
    }
    return $slots;
}

function booking_assert_slot_available(string $startAt, string $endAt, ?int $ignoreId = null): void
{
    $ownerId = booking_owner_id();
    if (!$ownerId) throw new RuntimeException('No active Owner/Teacher found.');
    $date = date('Y-m-d', strtotime($startAt));
    $dow = (int) date('w', strtotime($startAt));
    $inside = false;
    foreach (booking_owner_rules($ownerId) as $rule) {
        if ((int) $rule['day_of_week'] !== $dow) continue;
        if (strtotime($startAt) >= strtotime($date . ' ' . $rule['start_time']) && strtotime($endAt) <= strtotime($date . ' ' . $rule['end_time'])) {
            $inside = true;
            break;
        }
    }
    if (!$inside) throw new RuntimeException('Selected slot is outside availability.');
    if (booking_slot_has_unavailable($startAt, $endAt, booking_owner_unavailable($ownerId, $startAt, $endAt))) throw new RuntimeException('Selected slot is unavailable.');
    if (booking_slot_is_taken($startAt, $endAt, $ignoreId)) throw new RuntimeException('Selected slot is already booked.');
}

function booking_create_request(int $studentId, ?int $parentId, int $requestedBy, string $startAt, string $endAt, string $note = ''): int
{
    booking_assert_slot_available($startAt, $endAt);
    $package = credits_active_package($studentId);
    if (!$package || (float) $package['remaining_credits'] <= 0) throw new RuntimeException('No remaining credits available.');
    db()->prepare('INSERT INTO bookings (student_user_id, parent_user_id, owner_user_id, requested_by_user_id, package_id, title, start_at, end_at, status, student_note) VALUES (:student, :parent, :owner, :requested_by, :package_id, "Arabic lesson booking request", :start_at, :end_at, "requested", :note)')
        ->execute([':student' => $studentId, ':parent' => $parentId, ':owner' => booking_owner_id(), ':requested_by' => $requestedBy, ':package_id' => (int) $package['id'], ':start_at' => $startAt, ':end_at' => $endAt, ':note' => $note ?: null]);
    $id = (int) db()->lastInsertId();
    audit_log($requestedBy, 'booking_requested', 'booking', (string) $id, ['student_user_id' => $studentId]);
    return $id;
}

function booking_detail(int $id): ?array
{
    $s = db()->prepare('SELECT bookings.*, users.display_name AS student_name, users.email AS student_email, lesson_packages.package_name FROM bookings LEFT JOIN users ON users.id = bookings.student_user_id LEFT JOIN lesson_packages ON lesson_packages.id = bookings.package_id WHERE bookings.id = :id LIMIT 1');
    $s->execute([':id' => $id]);
    $row = $s->fetch();
    return $row ?: null;
}

function booking_confirm(int $id, int $ownerId, string $meeting = '', string $note = ''): int
{
    $booking = booking_detail($id);
    if (!$booking) throw new RuntimeException('Booking not found.');
    if (!in_array($booking['status'], ['requested','reschedule_requested'], true)) throw new RuntimeException('Only requested bookings can be confirmed.');
    booking_assert_slot_available($booking['start_at'], $booking['end_at'], $id);
    db()->prepare('INSERT INTO lesson_sessions (student_user_id, teacher_user_id, package_id, title, start_at, end_at, status, notes) VALUES (:student, :teacher, :package_id, :title, :start_at, :end_at, "confirmed", :notes)')
        ->execute([':student' => (int) $booking['student_user_id'], ':teacher' => $ownerId, ':package_id' => $booking['package_id'], ':title' => 'Arabic lesson', ':start_at' => $booking['start_at'], ':end_at' => $booking['end_at'], ':notes' => $meeting ? 'Meeting link: ' . $meeting : null]);
    $sessionId = (int) db()->lastInsertId();
    db()->prepare('UPDATE bookings SET status = "confirmed", session_id = :session, meeting_link = :meeting, owner_note = :note, confirmed_by_user_id = :owner, confirmed_at = NOW() WHERE id = :id')
        ->execute([':session' => $sessionId, ':meeting' => $meeting ?: null, ':note' => $note ?: null, ':owner' => $ownerId, ':id' => $id]);
    audit_log($ownerId, 'booking_confirmed', 'booking', (string) $id, ['session_id' => $sessionId]);
    return $sessionId;
}

function booking_set_status(int $id, string $status, int $ownerId, string $note = ''): void
{
    if (!in_array($status, ['rejected','canceled','completed','no_show'], true)) throw new RuntimeException('Invalid status.');
    $booking = booking_detail($id);
    if (!$booking) throw new RuntimeException('Booking not found.');
    db()->prepare('UPDATE bookings SET status = :status, owner_note = :note, canceled_at = CASE WHEN :status2 = "canceled" THEN NOW() ELSE canceled_at END WHERE id = :id')
        ->execute([':status' => $status, ':status2' => $status, ':note' => $note ?: null, ':id' => $id]);
    if (!empty($booking['session_id']) && in_array($status, ['completed','no_show'], true)) credits_mark_attendance((int) $booking['session_id'], $status, $ownerId, $note);
    audit_log($ownerId, 'booking_status_updated', 'booking', (string) $id, ['status' => $status]);
}

function booking_request_reschedule(int $id, int $userId, string $startAt, string $endAt, string $reason = ''): void
{
    $booking = booking_detail($id);
    if (!$booking) throw new RuntimeException('Booking not found.');
    booking_assert_slot_available($startAt, $endAt, $id);
    db()->prepare('UPDATE bookings SET status = "reschedule_requested", start_at = :start_at, end_at = :end_at, reschedule_reason = :reason, reschedule_requested_at = NOW() WHERE id = :id')
        ->execute([':start_at' => $startAt, ':end_at' => $endAt, ':reason' => $reason ?: null, ':id' => $id]);
    audit_log($userId, 'booking_reschedule_requested', 'booking', (string) $id, ['start_at' => $startAt]);
}

function booking_list_owner(): array
{
    return db()->query('SELECT bookings.*, users.display_name AS student_name, users.email AS student_email, lesson_packages.package_name FROM bookings LEFT JOIN users ON users.id = bookings.student_user_id LEFT JOIN lesson_packages ON lesson_packages.id = bookings.package_id ORDER BY bookings.start_at DESC LIMIT 300')->fetchAll();
}

function booking_list_student(int $studentId): array
{
    $s = db()->prepare('SELECT bookings.*, lesson_packages.package_name FROM bookings LEFT JOIN lesson_packages ON lesson_packages.id = bookings.package_id WHERE bookings.student_user_id = :student ORDER BY bookings.start_at DESC LIMIT 200');
    $s->execute([':student' => $studentId]);
    return $s->fetchAll();
}

function booking_parent_children(int $parentId): array
{
    $s = db()->prepare('SELECT parent_child_links.child_user_id, users.display_name, users.email FROM parent_child_links LEFT JOIN users ON users.id = parent_child_links.child_user_id WHERE parent_child_links.parent_user_id = :parent AND parent_child_links.status = "active" ORDER BY users.display_name');
    $s->execute([':parent' => $parentId]);
    return $s->fetchAll();
}

function booking_parent_can_access_child(int $parentId, int $childId): bool
{
    $s = db()->prepare('SELECT id FROM parent_child_links WHERE parent_user_id = :parent AND child_user_id = :child AND status = "active" LIMIT 1');
    $s->execute([':parent' => $parentId, ':child' => $childId]);
    return (bool) $s->fetch();
}
