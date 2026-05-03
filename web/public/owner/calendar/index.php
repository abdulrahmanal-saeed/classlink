<?php
/**
 * /owner/calendar
 * Owner calendar overview: upcoming generated slots and bookings.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/BookingCalendar.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$slots = booking_generate_slots((int) $user['id'], 14);
$bookings = booking_list_owner();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Calendar overview for availability and bookings.</p>
    <small class="text-muted">Available slots respect working rules, blocked times, and existing bookings.</small>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-outline-brand" href="/owner/availability">Availability</a>
    <a class="btn btn-brand" href="/owner/bookings">Bookings</a>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-5">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold mb-3">Next available slots</h2>
      <?php if (!$slots): ?>
        <div class="alert alert-light border">No available slots. Add availability rules or remove blocked times.</div>
      <?php else: ?>
        <div class="list-group list-group-flush">
          <?php foreach (array_slice($slots, 0, 20) as $slot): ?>
            <div class="list-group-item px-0">
              <strong><?= htmlspecialchars($slot['label'], ENT_QUOTES, 'UTF-8') ?></strong><br>
              <small class="text-muted"><?= htmlspecialchars($slot['start_at'] . ' → ' . $slot['end_at'], ENT_QUOTES, 'UTF-8') ?></small>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold mb-3">Latest bookings</h2>
      <?php if (!$bookings): ?>
        <div class="alert alert-light border">No bookings yet.</div>
      <?php else: ?>
        <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Date</th><th>Student</th><th>Status</th><th>Meeting</th></tr></thead><tbody>
        <?php foreach (array_slice($bookings, 0, 20) as $booking): ?>
          <tr>
            <td><?= htmlspecialchars($booking['start_at'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($booking['student_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="badge text-bg-light border"><?= htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td><?php if (!empty($booking['meeting_link'])): ?><a href="<?= htmlspecialchars($booking['meeting_link'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">Open</a><?php else: ?>-<?php endif; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody></table></div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Owner Calendar', $content);
