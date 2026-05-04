<?php
/** /owner/testimonials - moderation dashboard. */
require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/Testimonials.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$status = $_GET['status'] ?? '';
$media = $_GET['media_type'] ?? '';
$type = $_GET['submitter_type'] ?? '';
$rows = testimonial_all(['status'=>$status, 'media_type'=>$media, 'submitter_type'=>$type]);

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div><p class="text-muted mb-1">Review, approve, reject, archive, and control public testimonial display.</p></div>
  <a class="btn btn-outline-brand" href="/owner/settings/testimonials">Settings</a>
</div>
<form class="foundation-card mb-4" method="get"><div class="row g-3 align-items-end">
  <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="">All</option><?php foreach(['pending_review','approved','rejected','archived'] as $s):?><option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option><?php endforeach;?></select></div>
  <div class="col-md-3"><label class="form-label">Media</label><select class="form-select" name="media_type"><option value="">All</option><?php foreach(['text','audio','video','mixed'] as $s):?><option value="<?= $s ?>" <?= $media===$s?'selected':'' ?>><?= $s ?></option><?php endforeach;?></select></div>
  <div class="col-md-3"><label class="form-label">Submitter</label><select class="form-select" name="submitter_type"><option value="">All</option><?php foreach(['student','parent','public','owner'] as $s):?><option value="<?= $s ?>" <?= $type===$s?'selected':'' ?>><?= $s ?></option><?php endforeach;?></select></div>
  <div class="col-md-3"><button class="btn btn-brand">Filter</button></div>
</div></form>
<div class="foundation-card"><h2 class="h5 fw-bold">Testimonials</h2>
<?php if(!$rows):?><div class="alert alert-light border mb-0">No testimonials found.</div><?php else:?><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Date</th><th>Submitter</th><th>Rating</th><th>Media</th><th>Status</th><th>Display</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $row):?><tr>
<td><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars($row['submitter_type'], ENT_QUOTES, 'UTF-8') ?><br><small class="text-muted"><?= htmlspecialchars($row['source'], ENT_QUOTES, 'UTF-8') ?></small></td>
<td><?= str_repeat('★', (int)$row['rating']) ?></td>
<td><span class="badge text-bg-light border"><?= htmlspecialchars($row['media_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
<td><span class="badge text-bg-light border"><?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
<td><small>Home: <?= (int)$row['show_on_homepage'] ?> · Page: <?= (int)$row['show_on_testimonials_page'] ?> · Featured: <?= (int)$row['featured'] ?></small></td>
<td><a class="btn btn-sm btn-outline-brand" href="/owner/testimonials/view?id=<?= (int)$row['id'] ?>">Review</a></td>
</tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></div>
<?php
$content=ob_get_clean();
render_dashboard_shell($user,'Testimonials',$content);
