<?php
/**
 * Phase 28 Media Buyer Agreement helper.
 * Not legal advice. Agreement templates should be reviewed by a lawyer before real use.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/MediaBuyer.php';

function media_agreement_required(): bool
{
    return media_setting('media_buyer_agreement_required', '1') === '1';
}

function media_agreement_active_template(): ?array
{
    $s = db()->query('SELECT * FROM media_buyer_agreement_templates WHERE active = 1 ORDER BY id DESC LIMIT 1');
    $row = $s->fetch();
    return $row ?: null;
}

function media_agreement_latest_acceptance(int $mediaBuyerId): ?array
{
    $s = db()->prepare('SELECT * FROM media_buyer_agreement_acceptances WHERE media_buyer_id = :id ORDER BY accepted_at DESC, id DESC LIMIT 1');
    $s->execute([':id' => $mediaBuyerId]);
    $row = $s->fetch();
    return $row ?: null;
}

function media_agreement_has_valid_acceptance(int $mediaBuyerId): bool
{
    if (!media_agreement_required()) return true;
    $template = media_agreement_active_template();
    if (!$template) return false;
    $acceptance = media_agreement_latest_acceptance($mediaBuyerId);
    if (!$acceptance) return false;
    if ((int)$acceptance['template_id'] !== (int)$template['id']) return false;
    if ((string)$acceptance['template_version'] !== (string)$template['version']) return false;
    if ((int)$template['requires_reacceptance'] === 1) return false;
    return true;
}

function media_agreement_redirect_if_needed(array $user): void
{
    if (($user['role'] ?? '') !== 'media_buyer') return;
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if (str_starts_with($path, '/media/agreement')) return;
    $buyer = media_buyer_by_user((int)$user['id']);
    if (!$buyer) return;
    if (!media_agreement_has_valid_acceptance((int)$buyer['id'])) {
        header('Location: /media/agreement');
        exit;
    }
}

function media_agreement_hash(?string $value): ?string
{
    $value = trim((string)$value);
    return $value === '' ? null : hash('sha256', $value);
}

function media_agreement_accept(int $mediaBuyerId, array $input, array $actor): int
{
    $template = media_agreement_active_template();
    if (!$template) throw new RuntimeException('No active agreement template is configured.');
    $typedName = trim((string)($input['typed_name'] ?? ''));
    if (mb_strlen($typedName) < 3) throw new RuntimeException('Please type your legal name to accept the agreement.');
    if (empty($input['confirm_acceptance'])) throw new RuntimeException('You must confirm that you accept the agreement.');

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    db()->prepare('INSERT INTO media_buyer_agreement_acceptances (media_buyer_id, template_id, template_version, accepted_content_snapshot, typed_name, signature_data, ip_hash, user_agent_hash, accepted_at) VALUES (:buyer,:template,:version,:snapshot,:typed,:signature,:ip,:ua,NOW())')
        ->execute([
            ':buyer' => $mediaBuyerId,
            ':template' => (int)$template['id'],
            ':version' => $template['version'],
            ':snapshot' => $template['content'],
            ':typed' => $typedName,
            ':signature' => trim((string)($input['signature_data'] ?? '')) ?: null,
            ':ip' => media_agreement_hash($ip),
            ':ua' => media_agreement_hash($ua),
        ]);
    $id = (int) db()->lastInsertId();
    if ((int)$template['requires_reacceptance'] === 1) {
        db()->prepare('UPDATE media_buyer_agreement_templates SET requires_reacceptance = 0 WHERE id = :id')->execute([':id' => (int)$template['id']]);
    }
    audit_log((int)$actor['id'], 'media_buyer_agreement_accepted', 'media_buyer_agreement_acceptance', (string)$id, ['media_buyer_id'=>$mediaBuyerId, 'template_id'=>(int)$template['id'], 'version'=>$template['version']]);
    return $id;
}

function media_agreement_save_template(array $input, array $owner): int
{
    $title = trim((string)($input['title'] ?? ''));
    $version = trim((string)($input['version'] ?? ''));
    $content = trim((string)($input['content'] ?? ''));
    if ($title === '' || $version === '' || $content === '') throw new RuntimeException('Title, version, and content are required.');
    $requires = !empty($input['requires_reacceptance']) ? 1 : 0;
    db()->prepare('UPDATE media_buyer_agreement_templates SET active = 0')->execute();
    db()->prepare('INSERT INTO media_buyer_agreement_templates (title, version, content, active, requires_reacceptance, created_by_user_id) VALUES (:title,:version,:content,1,:requires,:owner)')
        ->execute([':title'=>$title, ':version'=>$version, ':content'=>$content, ':requires'=>$requires, ':owner'=>(int)$owner['id']]);
    $id = (int) db()->lastInsertId();
    audit_log((int)$owner['id'], 'media_buyer_agreement_template_saved', 'media_buyer_agreement_template', (string)$id, ['version'=>$version, 'requires_reacceptance'=>$requires]);
    return $id;
}

function media_agreement_acceptances(?int $mediaBuyerId = null): array
{
    if ($mediaBuyerId) {
        $s = db()->prepare('SELECT a.*, mbp.display_name, mbp.partner_code FROM media_buyer_agreement_acceptances a JOIN media_buyer_profiles mbp ON mbp.id=a.media_buyer_id WHERE a.media_buyer_id=:id ORDER BY a.accepted_at DESC');
        $s->execute([':id'=>$mediaBuyerId]);
        return $s->fetchAll();
    }
    return db()->query('SELECT a.*, mbp.display_name, mbp.partner_code FROM media_buyer_agreement_acceptances a JOIN media_buyer_profiles mbp ON mbp.id=a.media_buyer_id ORDER BY a.accepted_at DESC LIMIT 500')->fetchAll();
}

function media_agreement_acceptance_find(int $id): ?array
{
    $s = db()->prepare('SELECT a.*, mbp.display_name, mbp.partner_code FROM media_buyer_agreement_acceptances a JOIN media_buyer_profiles mbp ON mbp.id=a.media_buyer_id WHERE a.id=:id LIMIT 1');
    $s->execute([':id'=>$id]);
    $row = $s->fetch();
    return $row ?: null;
}

function media_agreement_render_html(array $acceptance): string
{
    $content = nl2br(htmlspecialchars($acceptance['accepted_content_snapshot'], ENT_QUOTES, 'UTF-8'));
    return '<div class="foundation-card"><h1 class="h3">Accepted Media Buyer Agreement</h1><p><strong>Partner:</strong> '.htmlspecialchars($acceptance['display_name'], ENT_QUOTES, 'UTF-8').' ('.htmlspecialchars($acceptance['partner_code'], ENT_QUOTES, 'UTF-8').')</p><p><strong>Version:</strong> '.htmlspecialchars($acceptance['template_version'], ENT_QUOTES, 'UTF-8').'</p><p><strong>Typed legal name:</strong> '.htmlspecialchars($acceptance['typed_name'], ENT_QUOTES, 'UTF-8').'</p><p><strong>Accepted at:</strong> '.htmlspecialchars($acceptance['accepted_at'], ENT_QUOTES, 'UTF-8').'</p><hr>'.$content.'</div>';
}
