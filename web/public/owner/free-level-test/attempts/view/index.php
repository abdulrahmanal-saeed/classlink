<?php
/**
 * /owner/free-level-test/attempts/view?id=...
 * Owner/Teacher review page for free public level tests.
 */

require_once __DIR__ . '/../../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../../backend/php/shared/FreeLevelTest.php';
require_once __DIR__ . '/../../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$attemptId = (int) ($_GET['id'] ?? 0);
$message = null;
$error = null;

$stmt = db()->prepare('SELECT a.*, p.full_name, p.whatsapp AS applicant_whatsapp, p.email, p.age, p.country, p.applicant_type, p.existing_student_code
    FROM free_level_test_attempts a
    LEFT JOIN free_level_test_applicants p ON p.id = a.applicant_id
    WHERE a.id = :id LIMIT 1');
$stmt->execute([':id' => $attemptId]);
$attempt = $stmt->fetch();

if (!$attempt) {
    http_response_code(404);
    $content = '<div class="alert alert-danger">Attempt not found.</div><a class="btn btn-outline-brand" href="/owner/free-level-test/attempts">Back</a>';
    render_dashboard_shell($user, 'Attempt Not Found', $content);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $writingScore = $_POST['writing_score'] !== '' ? (float) $_POST['writing_score'] : null;
        $speakingScores = [
            'speaking_fluency' => $_POST['speaking_fluency'] !== '' ? (float) $_POST['speaking_fluency'] : null,
            'speaking_grammar' => $_POST['speaking_grammar'] !== '' ? (float) $_POST['speaking_grammar'] : null,
            'speaking_vocabulary' => $_POST['speaking_vocabulary'] !== '' ? (float) $_POST['speaking_vocabulary'] : null,
            'speaking_pronunciation' => $_POST['speaking_pronunciation'] !== '' ? (float) $_POST['speaking_pronunciation'] : null,
            'speaking_depth' => $_POST['speaking_depth'] !== '' ? (float) $_POST['speaking_depth'] : null,
        ];
        $speakingTotal = array_sum(array_filter($speakingScores, fn($v) => $v !== null));
        $speakingLevel = $speakingTotal <= 6 ? 'A2' : ($speakingTotal <= 10 ? 'B1' : ($speakingTotal <= 14 ? 'B2' : ($speakingTotal <= 17 ? 'C1' : 'C2')));
        $finalLevel = trim($_POST['final_level'] ?? '') ?: ($attempt['auto_estimated_level'] ?: $attempt['preliminary_level']);

        db()->prepare('INSERT INTO free_level_test_manual_reviews
            (attempt_id, writing_score, writing_level, writing_feedback, speaking_fluency, speaking_grammar, speaking_vocabulary, speaking_pronunciation, speaking_depth, speaking_total, speaking_level, speaking_feedback, final_level, next_step_notes, reviewed_by_user_id)
            VALUES (:attempt, :writing_score, :writing_level, :writing_feedback, :fluency, :grammar, :vocabulary, :pronunciation, :depth, :speaking_total, :speaking_level, :speaking_feedback, :final_level, :next_step, :reviewer)
            ON DUPLICATE KEY UPDATE writing_score = VALUES(writing_score), writing_level = VALUES(writing_level), writing_feedback = VALUES(writing_feedback), speaking_fluency = VALUES(speaking_fluency), speaking_grammar = VALUES(speaking_grammar), speaking_vocabulary = VALUES(speaking_vocabulary), speaking_pronunciation = VALUES(speaking_pronunciation), speaking_depth = VALUES(speaking_depth), speaking_total = VALUES(speaking_total), speaking_level = VALUES(speaking_level), speaking_feedback = VALUES(speaking_feedback), final_level = VALUES(final_level), next_step_notes = VALUES(next_step_notes), reviewed_by_user_id = VALUES(reviewed_by_user_id)')
            ->execute([
                ':attempt' => $attemptId,
                ':writing_score' => $writingScore,
                ':writing_level' => $_POST['writing_level'] ?? null,
                ':writing_feedback' => trim($_POST['writing_feedback'] ?? ''),
                ':fluency' => $speakingScores['speaking_fluency'],
                ':grammar' => $speakingScores['speaking_grammar'],
                ':vocabulary' => $speakingScores['speaking_vocabulary'],
                ':pronunciation' => $speakingScores['speaking_pronunciation'],
                ':depth' => $speakingScores['speaking_depth'],
                ':speaking_total' => $speakingTotal,
                ':speaking_level' => $speakingLevel,
                ':speaking_feedback' => trim($_POST['speaking_feedback'] ?? ''),
                ':final_level' => $finalLevel,
                ':next_step' => trim($_POST['next_step_notes'] ?? ''),
                ':reviewer' => (int) $user['id'],
            ]);
        db()->prepare('UPDATE free_level_test_attempts SET status = "reviewed", current_step = "reviewed", final_level = :level, teacher_notes = :notes, reviewed_by_user_id = :reviewer, reviewed_at = NOW() WHERE id = :id')
            ->execute([':level' => $finalLevel, ':notes' => trim($_POST['teacher_notes'] ?? ''), ':reviewer' => (int) $user['id'], ':id' => $attemptId]);
        audit_log((int) $user['id'], 'free_level_test_reviewed', 'free_level_test_attempt', (string) $attemptId, ['final_level' => $finalLevel]);
        $message = 'Review saved and attempt marked reviewed.';
        $stmt->execute([':id' => $attemptId]);
        $attempt = $stmt->fetch();
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$answersStmt = db()->prepare('SELECT * FROM free_level_test_answers WHERE attempt_id = :id ORDER BY id ASC');
$answersStmt->execute([':id' => $attemptId]);
$answers = $answersStmt->fetchAll();

$uploadsStmt = db()->prepare('SELECT * FROM free_level_test_uploads WHERE attempt_id = :id ORDER BY id ASC');
$uploadsStmt->execute([':id' => $attemptId]);
$uploads = $uploadsStmt->fetchAll();

$reviewStmt = db()->prepare('SELECT * FROM free_level_test_manual_reviews WHERE attempt_id = :id LIMIT 1');
$reviewStmt->execute([':id' => $attemptId]);
$review = $reviewStmt->fetch() ?: [];

$snapshot = json_decode($attempt['snapshot_json'] ?? '{}', true) ?: [];

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Review free public level test. Final level is manual.</p>
    <small class="text-muted">Attempt ID: <?= (int) $attemptId ?></small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/free-level-test/attempts">Back to attempts</a>
</div>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="status-box mb-4">
      <h2 class="h5 fw-bold">Applicant and scores</h2>
      <dl class="row mb-0 mt-3">
        <dt class="col-sm-4">Name</dt><dd class="col-sm-8"><?= htmlspecialchars($attempt['full_name'] ?? 'Anonymous', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">WhatsApp</dt><dd class="col-sm-8"><?= htmlspecialchars($attempt['applicant_whatsapp'] ?? $attempt['whatsapp'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Email</dt><dd class="col-sm-8"><?= htmlspecialchars($attempt['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Type</dt><dd class="col-sm-8"><?= htmlspecialchars($attempt['test_type'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Listening</dt><dd class="col-sm-8"><?= htmlspecialchars((string) ($attempt['listening_score'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars($attempt['listening_estimated_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Reading</dt><dd class="col-sm-8"><?= htmlspecialchars((string) ($attempt['reading_score'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars($attempt['reading_estimated_level'] ?? $attempt['preliminary_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Auto estimate</dt><dd class="col-sm-8"><?= htmlspecialchars($attempt['auto_estimated_level'] ?? $attempt['preliminary_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Warnings</dt><dd class="col-sm-8"><?= htmlspecialchars($attempt['warnings_json'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
      </dl>
    </div>

    <div class="foundation-card mb-4">
      <h2 class="h5 fw-bold mb-3">Answers</h2>
      <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Section</th><th>Question</th><th>Answer</th><th>Correct</th><th>Score</th></tr></thead><tbody>
      <?php foreach ($answers as $answer): ?>
        <tr><td><?= htmlspecialchars($answer['section_key'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars(mb_strimwidth($answer['question_text'] ?? '', 0, 120, '...'), ENT_QUOTES, 'UTF-8') ?></td><td><?= nl2br(htmlspecialchars($answer['answer_text'] ?? $answer['selected_option'] ?? '', ENT_QUOTES, 'UTF-8')) ?></td><td><?= htmlspecialchars($answer['correct_option'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($answer['score'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td></tr>
      <?php endforeach; ?>
      </tbody></table></div>
    </div>

    <div class="foundation-card mb-4">
      <h2 class="h5 fw-bold mb-3">Uploads</h2>
      <?php if (!$uploads): ?><div class="alert alert-light border">No uploads.</div><?php else: ?><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Purpose</th><th>File</th><th>MIME</th><th>Size</th></tr></thead><tbody><?php foreach ($uploads as $upload): ?><tr><td><?= htmlspecialchars($upload['purpose'], ENT_QUOTES, 'UTF-8') ?></td><td><a href="/<?= htmlspecialchars($upload['file_path'], ENT_QUOTES, 'UTF-8') ?>" target="_blank"><?= htmlspecialchars($upload['original_filename'] ?? $upload['file_path'], ENT_QUOTES, 'UTF-8') ?></a></td><td><?= htmlspecialchars($upload['mime_type'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td><td><?= round(((int) $upload['size_bytes']) / 1024, 1) ?> KB</td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
    </div>

    <details class="foundation-card"><summary class="fw-bold">Attempt snapshot</summary><pre class="small mt-3" style="white-space:pre-wrap;"><?= htmlspecialchars(json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?></pre></details>
  </div>
  <div class="col-lg-5">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Manual review</h2>
      <form method="post" class="row g-3">
        <div class="col-md-6"><label class="form-label">Writing score</label><input class="form-control" type="number" step="0.01" name="writing_score" value="<?= htmlspecialchars((string) ($review['writing_score'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="col-md-6"><label class="form-label">Writing level</label><input class="form-control" name="writing_level" value="<?= htmlspecialchars($review['writing_level'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="col-12"><label class="form-label">Writing feedback</label><textarea class="form-control" name="writing_feedback" rows="4"><?= htmlspecialchars($review['writing_feedback'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
        <?php foreach (['speaking_fluency' => 'Fluency', 'speaking_grammar' => 'Grammar', 'speaking_vocabulary' => 'Vocabulary', 'speaking_pronunciation' => 'Pronunciation', 'speaking_depth' => 'Depth / Organization'] as $field => $label): ?>
          <div class="col-md-6"><label class="form-label"><?= $label ?> /4</label><input class="form-control" type="number" step="0.01" min="0" max="4" name="<?= $field ?>" value="<?= htmlspecialchars((string) ($review[$field] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
        <?php endforeach; ?>
        <div class="col-12"><label class="form-label">Speaking feedback</label><textarea class="form-control" name="speaking_feedback" rows="4"><?= htmlspecialchars($review['speaking_feedback'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
        <div class="col-md-6"><label class="form-label">Final level</label><select class="form-select" name="final_level"><option value="">Choose...</option><?php foreach (['A1','A2','B1','B2','C1','C2'] as $level): ?><option value="<?= $level ?>" <?= ($attempt['final_level'] ?? '') === $level ? 'selected' : '' ?>><?= $level ?></option><?php endforeach; ?></select></div>
        <div class="col-12"><label class="form-label">Teacher notes</label><textarea class="form-control" name="teacher_notes" rows="4"><?= htmlspecialchars($attempt['teacher_notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
        <div class="col-12"><label class="form-label">Next step notes</label><textarea class="form-control" name="next_step_notes" rows="4"><?= htmlspecialchars($review['next_step_notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
        <div class="col-12"><button class="btn btn-brand" type="submit">Save review and mark reviewed</button></div>
      </form>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Free Level Test Review', $content);
