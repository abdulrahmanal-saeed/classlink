<?php
/**
 * /student/book
 * Student sees generated available slots and creates booking requests.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/BookingCalendar.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $startAt = $_POST['start_at'] ?? '';
        $endAt = $_POST['end_at'] ?? '';
        booking_create_request((int) $user['id'], null, (int) $user['id'], $startAt, $endAt, trim($_POST['note'] ?? ''));
        $message = 'Booking request sent. Owner/Teacher will confirm it soon.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$slots = booking_generate_slots(null, (int) booking_setting('booking.max_days_ahead', 30));

ob_start();
?>
<p class="text-muted">Choose an available slot. Your request will be confirmed by the Owner/Teacher.</p>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<?php if (!$slots): ?>
  <div class="alert alert-light border">No available slots right now. Please check again later.</div>
<?php else: ?>
  <form method="post" class="foundation-card">
    <div class="mb-3">
      <label class="form-label">Available slot</label>
      <select class="form-select" name="slot" id="slotSelect" required>
        <?php foreach ($slots as $slot): ?>
          <option value="<?= htmlspecialchars($slot['start_at'] . '|' . $slot['end_at'], ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($slot['label'], ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
      <input type="hidden" name="start_at" id="startAt">
      <input type="hidden" name="end_at" id="endAt">
    </div>
    <div class="mb-3"><label class="form-label">Note optional</label><textarea class="form-control" name="note" rows="3"></textarea></div>
    <button class="btn btn-brand" type="submit">Request booking</button>
  </form>
  <script>
    const slotSelect = document.getElementById('slotSelect');
    const startAt = document.getElementById('startAt');
    const endAt = document.getElementById('endAt');
    function syncSlot(){ const parts = slotSelect.value.split('|'); startAt.value = parts[0]; endAt.value = parts[1]; }
    slotSelect.addEventListener('change', syncSlot); syncSlot();
  </script>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Book a Lesson', $content);
