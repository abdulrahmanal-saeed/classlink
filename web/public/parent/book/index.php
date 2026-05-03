<?php
/**
 * /parent/book
 * Parent books a lesson for one linked child.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/BookingCalendar.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('parent');
$message = null;
$error = null;
$children = booking_parent_children((int) $user['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $childId = (int) ($_POST['child_user_id'] ?? 0);
        if (!booking_parent_can_access_child((int) $user['id'], $childId)) throw new RuntimeException('You cannot book for this child.');
        booking_create_request($childId, (int) $user['id'], (int) $user['id'], $_POST['start_at'], $_POST['end_at'], trim($_POST['note'] ?? ''));
        $message = 'Booking request sent for your child.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$slots = booking_generate_slots(null, 30);

ob_start();
?>
<p class="text-muted">Choose a child and an available slot. Owner/Teacher will confirm the booking.</p>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<?php if (!$children): ?>
  <div class="alert alert-light border">No child learner is linked to your account.</div>
<?php elseif (!$slots): ?>
  <div class="alert alert-light border">No available slots right now.</div>
<?php else: ?>
  <form method="post" class="foundation-card">
    <div class="mb-3"><label class="form-label">Child</label><select class="form-select" name="child_user_id" required><?php foreach ($children as $child): ?><option value="<?= (int) $child['child_user_id'] ?>"><?= htmlspecialchars($child['display_name'] ?? 'Child learner', ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
    <div class="mb-3"><label class="form-label">Available slot</label><select class="form-select" id="slotSelect" required><?php foreach ($slots as $slot): ?><option value="<?= htmlspecialchars($slot['start_at'] . '|' . $slot['end_at'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($slot['label'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select><input type="hidden" name="start_at" id="startAt"><input type="hidden" name="end_at" id="endAt"></div>
    <div class="mb-3"><label class="form-label">Note optional</label><textarea class="form-control" name="note" rows="3"></textarea></div>
    <button class="btn btn-brand" type="submit">Request booking</button>
  </form>
  <script>
    const s=document.getElementById('slotSelect'), a=document.getElementById('startAt'), b=document.getElementById('endAt');
    function sync(){const p=s.value.split('|');a.value=p[0];b.value=p[1];} s.addEventListener('change',sync); sync();
  </script>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Book for Child', $content);
