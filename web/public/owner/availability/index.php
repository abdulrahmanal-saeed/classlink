<?php
/**
 * /owner/availability
 * Owner manages weekly availability rules and unavailable/blocked times.
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
        if ($action === 'add_rule') {
            booking_create_rule((int) $user['id'], (int) $_POST['day_of_week'], $_POST['start_time'], $_POST['end_time'], (int) $_POST['duration'], (int) $_POST['buffer'], $_POST['max_sessions_per_day'] !== '' ? (int) $_POST['max_sessions_per_day'] : null, 'Asia/Dubai', trim($_POST['notes'] ?? ''));
            $message = 'Availability rule created.';
        } elseif ($action === 'add_block') {
            booking_create_unavailable((int) $user['id'], $_POST['start_at'], $_POST['end_at'], trim($_POST['reason'] ?? ''));
            $message = 'Blocked time created.';
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$rules = booking_owner_rules((int) $user['id']);
$blocks = booking_owner_unavailable((int) $user['id']);
$days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Create working days, times, duration, buffer, and blocked times.</p>
    <small class="text-muted">Slots are generated from these rules.</small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/calendar">Calendar</a>
</div>

<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Add weekly availability</h2>
      <form method="post" class="row g-3">
        <input type="hidden" name="action" value="add_rule">
        <div class="col-12"><label class="form-label">Working day</label><select class="form-select" name="day_of_week"><?php foreach ($days as $i => $day): ?><option value="<?= $i ?>"><?= $day ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Start time</label><input class="form-control" type="time" name="start_time" required></div>
        <div class="col-md-6"><label class="form-label">End time</label><input class="form-control" type="time" name="end_time" required></div>
        <div class="col-md-4"><label class="form-label">Session minutes</label><input class="form-control" type="number" name="duration" value="90" required></div>
        <div class="col-md-4"><label class="form-label">Buffer minutes</label><input class="form-control" type="number" name="buffer" value="0" required></div>
        <div class="col-md-4"><label class="form-label">Max/day optional</label><input class="form-control" type="number" name="max_sessions_per_day"></div>
        <div class="col-12"><label class="form-label">Notes</label><input class="form-control" name="notes"></div>
        <div class="col-12"><button class="btn btn-brand" type="submit">Add rule</button></div>
      </form>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Add blocked time</h2>
      <form method="post" class="row g-3">
        <input type="hidden" name="action" value="add_block">
        <div class="col-md-6"><label class="form-label">Start</label><input class="form-control" type="datetime-local" name="start_at" required></div>
        <div class="col-md-6"><label class="form-label">End</label><input class="form-control" type="datetime-local" name="end_at" required></div>
        <div class="col-12"><label class="form-label">Reason</label><input class="form-control" name="reason" placeholder="Holiday, personal appointment..."></div>
        <div class="col-12"><button class="btn btn-outline-brand" type="submit">Block time</button></div>
      </form>
    </div>
  </div>
</div>

<div class="row g-4 mt-1">
  <div class="col-lg-6">
    <div class="foundation-card">
      <h2 class="h5 fw-bold mb-3">Active rules</h2>
      <?php if (!$rules): ?><div class="alert alert-light border">No availability rules yet.</div><?php else: ?>
      <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Day</th><th>Time</th><th>Duration</th><th>Buffer</th></tr></thead><tbody>
      <?php foreach ($rules as $rule): ?><tr><td><?= htmlspecialchars($days[(int) $rule['day_of_week']], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($rule['start_time'] . ' - ' . $rule['end_time'], ENT_QUOTES, 'UTF-8') ?></td><td><?= (int) ($rule['session_duration_minutes'] ?: 90) ?></td><td><?= (int) ($rule['buffer_minutes'] ?: 0) ?></td></tr><?php endforeach; ?>
      </tbody></table></div><?php endif; ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="foundation-card">
      <h2 class="h5 fw-bold mb-3">Blocked times</h2>
      <?php if (!$blocks): ?><div class="alert alert-light border">No blocked times yet.</div><?php else: ?>
      <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Start</th><th>End</th><th>Reason</th></tr></thead><tbody>
      <?php foreach ($blocks as $block): ?><tr><td><?= htmlspecialchars($block['start_at'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($block['end_at'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($block['reason'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?>
      </tbody></table></div><?php endif; ?>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Owner Availability', $content);
