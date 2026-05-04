<?php
/**
 * Phase 27 Media Buyer / Marketing Partner helper.
 * Media buyers only see their own tracking, attributed orders, and commissions.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/CommunicationCenter.php';

function media_setting(string $key, $default = null)
{
    $s = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1');
    $s->execute([':key' => $key]);
    $v = $s->fetchColumn();
    return $v === false ? $default : $v;
}

function media_bool(string $key, bool $default = false): bool
{
    return media_setting($key, $default ? '1' : '0') === '1';
}

function media_buyer_by_user(int $userId): ?array
{
    $s = db()->prepare('SELECT mbp.*, u.email, u.status AS user_status FROM media_buyer_profiles mbp JOIN users u ON u.id=mbp.user_id WHERE mbp.user_id=:id LIMIT 1');
    $s->execute([':id'=>$userId]);
    $row=$s->fetch();
    return $row ?: null;
}

function media_buyer_by_code(string $code): ?array
{
    $s = db()->prepare('SELECT * FROM media_buyer_profiles WHERE partner_code=:code AND status="active" LIMIT 1');
    $s->execute([':code'=>strtolower(trim($code))]);
    $row=$s->fetch();
    return $row ?: null;
}

function media_mask_customer(?string $name): string
{
    $name = trim((string)$name);
    if ($name === '') return 'Customer';
    $parts = preg_split('/\s+/', $name);
    return ($parts[0] ?? 'Customer') . ' #' . substr(hash('sha256', $name), 0, 6);
}

function media_create_buyer(array $data, array $owner): int
{
    $email = strtolower(trim($data['email'] ?? ''));
    $name = trim($data['display_name'] ?? 'Marketing Partner');
    $code = strtolower(preg_replace('/[^a-z0-9_-]+/i', '', $data['partner_code'] ?? ''));
    if (!$email || !$code) throw new RuntimeException('Email and partner code are required.');
    $password = $data['password'] ?? bin2hex(random_bytes(5));

    db()->prepare('INSERT INTO users (email, password_hash, role, status, display_name) VALUES (:email,:hash,"media_buyer","active",:name)')
        ->execute([':email'=>$email, ':hash'=>password_hash($password, PASSWORD_DEFAULT), ':name'=>$name]);
    $userId=(int)db()->lastInsertId();
    $rate=(float)($data['commission_rate'] ?? media_setting('media_global_commission_rate','10'));
    db()->prepare('INSERT INTO media_buyer_profiles (user_id, display_name, partner_code, commission_type, commission_rate, fixed_amount, status, notes) VALUES (:user,:name,:code,:type,:rate,:fixed,"active",:notes)')
        ->execute([':user'=>$userId, ':name'=>$name, ':code'=>$code, ':type'=>$data['commission_type'] ?? 'percentage', ':rate'=>$rate, ':fixed'=>(float)($data['fixed_amount'] ?? 0), ':notes'=>$data['notes'] ?? null]);
    $id=(int)db()->lastInsertId();
    audit_log((int)$owner['id'], 'media_buyer_created', 'media_buyer', (string)$id, ['email'=>$email, 'partner_code'=>$code]);
    return $id;
}

function media_tracking_link(array $buyer, string $landing = '/pricing', array $utm = []): string
{
    $base = rtrim(getenv('PUBLIC_SITE_URL') ?: 'https://mshabibanabil.com', '/');
    $query = array_filter([
        'partner' => $buyer['partner_code'],
        'utm_source' => $utm['utm_source'] ?? null,
        'utm_medium' => $utm['utm_medium'] ?? null,
        'utm_campaign' => $utm['utm_campaign'] ?? null,
        'utm_content' => $utm['utm_content'] ?? null,
        'utm_term' => $utm['utm_term'] ?? null,
    ]);
    return $base . $landing . '?' . http_build_query($query);
}

function media_register_event(array $payload): int
{
    $partnerCode = strtolower(trim($payload['partner'] ?? $payload['partner_code'] ?? ''));
    $buyer = $partnerCode ? media_buyer_by_code($partnerCode) : null;
    $visitorHash = !empty($payload['visitor_id']) ? hash('sha256', $payload['visitor_id']) : null;
    $utm = [
        'partner' => $partnerCode ?: null,
        'utm_source' => $payload['utm_source'] ?? null,
        'utm_medium' => $payload['utm_medium'] ?? null,
        'utm_campaign' => $payload['utm_campaign'] ?? null,
        'utm_content' => $payload['utm_content'] ?? null,
        'utm_term' => $payload['utm_term'] ?? null,
    ];
    db()->prepare('INSERT INTO attribution_events (media_buyer_id, event_type, session_id, visitor_id_hash, checkout_order_id, utm_data, landing_page, referrer, first_touch_at, last_touch_at) VALUES (:buyer,:type,:session,:visitor,:order,:utm,:landing,:referrer,NOW(),NOW())')
        ->execute([':buyer'=>$buyer['id'] ?? null, ':type'=>$payload['event_type'] ?? 'media_link_click', ':session'=>$payload['session_id'] ?? null, ':visitor'=>$visitorHash, ':order'=>$payload['checkout_order_id'] ?? null, ':utm'=>json_encode($utm, JSON_UNESCAPED_SLASHES), ':landing'=>$payload['landing_page'] ?? null, ':referrer'=>$payload['referrer'] ?? null]);
    return (int)db()->lastInsertId();
}

function media_attach_order_attribution(array $data): void
{
    $checkoutId = (string)($data['checkout_order_id'] ?? '');
    if ($checkoutId === '') throw new RuntimeException('checkout_order_id is required.');
    $partnerCode = strtolower(trim($data['partner_code'] ?? $data['partner'] ?? ''));
    $buyer = $partnerCode ? media_buyer_by_code($partnerCode) : null;
    $utm = $data['utm_data'] ?? [
        'partner'=>$partnerCode ?: null,
        'utm_source'=>$data['utm_source'] ?? null,
        'utm_medium'=>$data['utm_medium'] ?? null,
        'utm_campaign'=>$data['utm_campaign'] ?? null,
        'utm_content'=>$data['utm_content'] ?? null,
        'utm_term'=>$data['utm_term'] ?? null,
    ];
    db()->prepare('INSERT INTO order_attributions (checkout_order_id, media_buyer_id, partner_code, attribution_model, utm_data, landing_page, selected_plan, order_amount, payment_status, customer_name_masked) VALUES (:order,:buyer,:code,:model,:utm,:landing,:plan,:amount,:status,:customer) ON DUPLICATE KEY UPDATE media_buyer_id=VALUES(media_buyer_id), partner_code=VALUES(partner_code), utm_data=VALUES(utm_data), payment_status=VALUES(payment_status), order_amount=VALUES(order_amount), selected_plan=VALUES(selected_plan)')
        ->execute([':order'=>$checkoutId, ':buyer'=>$buyer['id'] ?? null, ':code'=>$partnerCode ?: null, ':model'=>media_setting('media_default_attribution_model','last_touch'), ':utm'=>is_string($utm)?$utm:json_encode($utm, JSON_UNESCAPED_SLASHES), ':landing'=>$data['landing_page'] ?? null, ':plan'=>$data['selected_plan'] ?? null, ':amount'=>(float)($data['order_amount'] ?? 0), ':status'=>$data['payment_status'] ?? 'pending', ':customer'=>media_mask_customer($data['customer_name'] ?? null)]);
    if (($data['payment_status'] ?? '') === 'paid') media_create_commission_for_order($checkoutId);
}

function media_create_commission_for_order(string $checkoutOrderId): ?int
{
    $s=db()->prepare('SELECT oa.*, mbp.commission_type, mbp.commission_rate, mbp.fixed_amount FROM order_attributions oa JOIN media_buyer_profiles mbp ON mbp.id=oa.media_buyer_id WHERE oa.checkout_order_id=:order AND oa.payment_status="paid" LIMIT 1');
    $s->execute([':order'=>$checkoutOrderId]);
    $order=$s->fetch();
    if(!$order) return null;
    $type=$order['commission_type'];
    $amount=(float)$order['order_amount'];
    $rate=(float)$order['commission_rate'];
    $commission=$type==='percentage' ? round($amount*($rate/100),2) : ($type==='fixed' ? (float)$order['fixed_amount'] : 0.0);
    db()->prepare('INSERT IGNORE INTO commission_records (media_buyer_id, checkout_order_id, package_name, order_amount, commission_type, commission_rate, commission_amount, status) VALUES (:buyer,:order,:package,:amount,:type,:rate,:commission,"pending")')
        ->execute([':buyer'=>(int)$order['media_buyer_id'], ':order'=>$checkoutOrderId, ':package'=>$order['selected_plan'], ':amount'=>$amount, ':type'=>$type, ':rate'=>$rate, ':commission'=>$commission]);
    return (int)db()->lastInsertId();
}

function media_update_commission_status(int $commissionId, string $status, array $owner, ?string $reason=null): void
{
    if(!in_array($status,['approved','rejected','paid','reversed','pending'],true)) throw new RuntimeException('Invalid commission status.');
    $before=db()->prepare('SELECT * FROM commission_records WHERE id=:id LIMIT 1');$before->execute([':id'=>$commissionId]);$row=$before->fetch();
    if(!$row) throw new RuntimeException('Commission not found.');
    $fields='status=:status, reason=:reason';
    if($status==='approved') $fields.=', approved_at=NOW()';
    if($status==='paid') $fields.=', paid_at=NOW()';
    if($status==='reversed') $fields.=', reversed_at=NOW()';
    db()->prepare('UPDATE commission_records SET '.$fields.' WHERE id=:id')->execute([':status'=>$status, ':reason'=>$reason, ':id'=>$commissionId]);
    audit_log((int)$owner['id'], 'media_commission_'.$status, 'commission', (string)$commissionId, ['before_status'=>$row['status'], 'after_status'=>$status, 'reason'=>$reason]);
}

function media_summary(int $mediaBuyerId): array
{
    $clicks=(int)db()->prepare('SELECT COUNT(*) FROM attribution_events WHERE media_buyer_id=:id AND event_type="media_link_click"')->execute([':id'=>$mediaBuyerId]);
    $q=db()->prepare('SELECT COUNT(*) clicks FROM attribution_events WHERE media_buyer_id=:id AND event_type="media_link_click"');$q->execute([':id'=>$mediaBuyerId]);$clicks=(int)$q->fetchColumn();
    $q=db()->prepare('SELECT COUNT(*) FROM attribution_events WHERE media_buyer_id=:id AND event_type="checkout_start"');$q->execute([':id'=>$mediaBuyerId]);$checkoutStarts=(int)$q->fetchColumn();
    $q=db()->prepare('SELECT COUNT(*) FROM order_attributions WHERE media_buyer_id=:id AND payment_status="paid"');$q->execute([':id'=>$mediaBuyerId]);$paidOrders=(int)$q->fetchColumn();
    $q=db()->prepare('SELECT COALESCE(SUM(order_amount),0) FROM order_attributions WHERE media_buyer_id=:id AND payment_status="paid"');$q->execute([':id'=>$mediaBuyerId]);$revenue=(float)$q->fetchColumn();
    $q=db()->prepare('SELECT status, COALESCE(SUM(commission_amount),0) amount FROM commission_records WHERE media_buyer_id=:id GROUP BY status');$q->execute([':id'=>$mediaBuyerId]);$commissions=[];foreach($q->fetchAll() as $r){$commissions[$r['status']] = (float)$r['amount'];}
    return ['clicks'=>$clicks,'checkout_starts'=>$checkoutStarts,'paid_orders'=>$paidOrders,'revenue'=>$revenue,'conversion_rate'=>$clicks?round(($paidOrders/$clicks)*100,2):0,'commission_pending'=>$commissions['pending']??0,'commission_approved'=>$commissions['approved']??0,'commission_paid'=>$commissions['paid']??0,'commission_reversed'=>$commissions['reversed']??0];
}

function media_orders(int $mediaBuyerId): array
{
    $s=db()->prepare('SELECT oa.*, cr.status AS commission_status, cr.commission_amount FROM order_attributions oa LEFT JOIN commission_records cr ON cr.checkout_order_id=oa.checkout_order_id AND cr.media_buyer_id=oa.media_buyer_id WHERE oa.media_buyer_id=:id ORDER BY oa.created_at DESC LIMIT 300');
    $s->execute([':id'=>$mediaBuyerId]);return $s->fetchAll();
}

function media_commissions(int $mediaBuyerId): array
{
    $s=db()->prepare('SELECT * FROM commission_records WHERE media_buyer_id=:id ORDER BY created_at DESC LIMIT 300');
    $s->execute([':id'=>$mediaBuyerId]);return $s->fetchAll();
}

function media_campaigns(int $mediaBuyerId): array
{
    $s=db()->prepare('SELECT * FROM marketing_campaigns WHERE media_buyer_id=:id ORDER BY created_at DESC');
    $s->execute([':id'=>$mediaBuyerId]);return $s->fetchAll();
}

function media_all_buyers(): array
{
    return db()->query('SELECT mbp.*, u.email FROM media_buyer_profiles mbp JOIN users u ON u.id=mbp.user_id ORDER BY mbp.created_at DESC')->fetchAll();
}
