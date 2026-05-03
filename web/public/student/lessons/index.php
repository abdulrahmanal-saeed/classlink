<?php
/**
 * /student/lessons
 * Student lesson booking list and simple reschedule request.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/BookingCalendar.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        booking_request_reschedule((int) $_POST['booking_id'], (int) $user['id'], $_POST['start_at'], $_POST['end_at'], trim($_POST['reason'] ?? ''));
        $message = 'Reschedule request sent.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$bookings = booking_list_student((int) $user['id']);
$slots = booking_generate_slots(null, 30);

ob_start();
?>
<p class="text-muted">Your requested and confirmed lessons.</p>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<?php if (!$bookings): ?>
  <div class="alert alert-light border">No lessons yet. <a href="/student/book">Book your first lesson</a>.</div>
<?php else: ?>
  <?php foreach ($bookings as $booking): ?>
    <div class="foundation-card mb-3">
      <div class="row g-3">
        <div class="col-lg-5">
          <h2 class="h5 fw-bold"><?= htmlspecialchars($booking['title'] ?: 'Arabic lesson', ENT_QUOTES, 'UTF-8') ?></h2>
          <div><?= htmlspecialchars($booking['start_at'], ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars($booking['end_at'], ENT_QUOTES, 'UTF-8') ?></div>
          <span class="badge text-bg-light border mt-2"><?= htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php if (!empty($booking['meeting_link'])): ?><div class="mt-2"><a href="<?= htmlspecialchars($booking['meeting_link'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">Open meeting link</a></div><?php endif; ?>
        </div>
        <div class="col-lg-7">
          <?php if (in_array($booking['status'], ['requested','confirmed'], true) && $slots): ?>
            <form method="post" class="row g-2">
              <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
              <div class="col-12"><label class="form-label">Request reschedule to</label><select class="form-select slot-select" required><?php foreach ($slots as $slot): ?><option value="<?= htmlspecialchars($slot['start_at'] . '|' . $slot['end_at'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($slot['label'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
              <input type="hidden" name="start_at" class="start-field"><input type="hidden" name="end_at" class="end-field">
              <div class="col-12"><input class="form-control" name="reason" placeholder="Reason optional"></div>
              <div class="col-12"><button class="btn btn-sm btn-outline-brand" type="submit">Request reschedule</button></div>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <script>
    document.querySelectorAll('form').forEach(form => {
      const select = form.querySelector('.slot-select'); if (!select) return;
      const start = form.querySelector('.start-field'); const end = form.querySelector('.end-field');
      function sync(){ const p = select.value.split('|'); start.value = p[0]; end.value = p[1]; }
      select.addEventListener('change', sync); sync();
    });
  </script>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'My Lessons', $content);
