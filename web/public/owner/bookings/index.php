<?php
/**
 * /owner/bookings
 * Owner confirms, rejects, completes, and marks no-show bookings.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/BookingCalendar.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        $bookingId = (int) ($_POST['booking_id'] ?? 0);
        if ($action === 'confirm') {
            booking_confirm($bookingId, (int) $user['id'], trim($_POST['meeting_link'] ?? ''), trim($_POST['owner_note'] ?? ''));
            $message = 'Booking confirmed and lesson session created.';
        } elseif ($action === 'status') {
            booking_set_status($bookingId, $_POST['status'] ?? 'rejected', (int) $user['id'], trim($_POST['owner_note'] ?? ''));
            $message = 'Booking status updated.';
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$bookings = booking_list_owner();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Review booking requests and manage lesson statuses.</p>
    <small class="text-muted">Confirming a booking creates a confirmed lesson session.</small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/calendar">Calendar</a>
</div>

<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<?php if (!$bookings): ?>
  <div class="alert alert-light border">No booking requests yet.</div>
<?php else: ?>
  <?php foreach ($bookings as $booking): ?>
    <div class="foundation-card mb-3">
      <div class="row g-3 align-items-start">
        <div class="col-lg-4">
          <h2 class="h5 fw-bold mb-1"><?= htmlspecialchars($booking['student_name'] ?? 'Student', ENT_QUOTES, 'UTF-8') ?></h2>
          <div class="small text-muted"><?= htmlspecialchars($booking['student_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
          <div class="mt-2"><span class="badge text-bg-light border"><?= htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8') ?></span></div>
        </div>
        <div class="col-lg-4">
          <strong><?= htmlspecialchars($booking['start_at'], ENT_QUOTES, 'UTF-8') ?></strong><br>
          <small class="text-muted">to <?= htmlspecialchars($booking['end_at'], ENT_QUOTES, 'UTF-8') ?></small><br>
          <small class="text-muted">Package: <?= htmlspecialchars($booking['package_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></small>
          <?php if (!empty($booking['reschedule_reason'])): ?><div class="alert alert-warning mt-2 mb-0 small">Reschedule: <?= htmlspecialchars($booking['reschedule_reason'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        </div>
        <div class="col-lg-4">
          <?php if (in_array($booking['status'], ['requested','reschedule_requested'], true)): ?>
            <form method="post" class="mb-2">
              <input type="hidden" name="action" value="confirm">
              <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
              <input class="form-control form-control-sm mb-2" name="meeting_link" placeholder="Meeting link optional" value="<?= htmlspecialchars($booking['meeting_link'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              <input class="form-control form-control-sm mb-2" name="owner_note" placeholder="Owner note optional">
              <button class="btn btn-sm btn-brand" type="submit">Confirm</button>
            </form>
          <?php endif; ?>
          <form method="post" class="d-flex gap-2 flex-wrap">
            <input type="hidden" name="action" value="status">
            <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
            <select class="form-select form-select-sm" name="status" style="max-width: 160px;">
              <?php foreach (['rejected','canceled','completed','no_show'] as $status): ?><option value="<?= $status ?>"><?= $status ?></option><?php endforeach; ?>
            </select>
            <button class="btn btn-sm btn-outline-brand" type="submit">Update</button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Owner Bookings', $content);
