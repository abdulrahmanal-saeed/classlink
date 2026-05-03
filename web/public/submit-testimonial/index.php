<?php
/**
 * /submit-testimonial
 *
 * Public testimonial submission form. Submissions are always pending and must
 * be moderated by the Owner before appearing publicly.
 */

require_once __DIR__ . '/../../../backend/php/config/db.php';
require_once __DIR__ . '/../../../web/components/layout/public_layout.php';

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $roleLabel = trim($_POST['role_label'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $rating = (int) ($_POST['rating'] ?? 0);
    $consent = isset($_POST['consent_to_publish']) ? 1 : 0;

    if ($name === '' || $body === '') {
        $error = 'Name and testimonial are required.';
    } elseif (!$consent) {
        $error = 'Please confirm consent to publish after moderation.';
    } else {
        $rating = max(1, min(5, $rating ?: 5));
        $statement = db()->prepare('INSERT INTO testimonials (name, role_label, body, rating, source, consent_to_publish, status) VALUES (:name, :role_label, :body, :rating, "public_form", :consent, "pending")');
        $statement->execute([
            ':name' => $name,
            ':role_label' => $roleLabel ?: null,
            ':body' => $body,
            ':rating' => $rating,
            ':consent' => $consent,
        ]);
        $message = 'Thank you. Your testimonial was submitted and will appear after approval.';
    }
}

ob_start();
?>
<section class="py-5"><div class="container"><div class="foundation-card" style="max-width:720px;margin:auto;"><h1 class="hero-title mb-3">Submit a testimonial</h1><p class="text-muted">Your testimonial will be reviewed before it appears publicly.</p><?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?><?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?><form method="post"><div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" required></div><div class="mb-3"><label class="form-label">Role label</label><input class="form-control" name="role_label" placeholder="Student, Parent, etc."></div><div class="mb-3"><label class="form-label">Rating</label><select class="form-select" name="rating"><option value="5">5</option><option value="4">4</option><option value="3">3</option><option value="2">2</option><option value="1">1</option></select></div><div class="mb-3"><label class="form-label">Testimonial</label><textarea class="form-control" name="body" rows="5" required></textarea></div><div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="consent_to_publish" id="consent" required><label class="form-check-label" for="consent">I agree that this testimonial may be published after moderation.</label></div><button class="btn btn-brand" type="submit">Submit testimonial</button></form></div></div></section>
<?php
render_public_layout('Submit Testimonial | Habiba Nabil Arabic Academy', 'Submit a moderated testimonial for Habiba Nabil Arabic Academy.', ob_get_clean(), true);
