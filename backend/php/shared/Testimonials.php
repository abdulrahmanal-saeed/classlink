<?php
/**
 * Phase 24 Advanced Testimonials helper.
 * Text/audio/video testimonial submission, moderation, privacy, and public display.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/CommunicationCenter.php';
require_once __DIR__ . '/PushNotifications.php';

function testimonial_setting(string $key, $default = null)
{
    $s = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1');
    $s->execute([':key' => $key]);
    $value = $s->fetchColumn();
    return $value === false ? $default : $value;
}

function testimonial_bool(string $key, bool $default = false): bool
{
    return testimonial_setting($key, $default ? '1' : '0') === '1';
}

function testimonial_media_type(?string $text, ?string $audio, ?string $video): string
{
    $hasText = trim((string)$text) !== '';
    $hasAudio = trim((string)$audio) !== '';
    $hasVideo = trim((string)$video) !== '';
    if ($hasText && ($hasAudio || $hasVideo)) return 'mixed';
    if ($hasVideo) return 'video';
    if ($hasAudio) return 'audio';
    return 'text';
}

function testimonial_first_name(?string $name): string
{
    $parts = preg_split('/\s+/', trim((string)$name));
    return $parts[0] ?? 'Student';
}

function testimonial_display_name(array $user, string $preference): string
{
    $name = $user['display_name'] ?? $user['name'] ?? 'Student';
    return match ($preference) {
        'full_name' => $name,
        'anonymous' => 'Anonymous',
        default => testimonial_first_name($name),
    };
}

function testimonial_upload_dir(): string
{
    $dir = __DIR__ . '/../../../web/public/uploads/testimonials/' . date('Y/m');
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    return $dir;
}

function testimonial_public_upload_url(string $filename): string
{
    return '/uploads/testimonials/' . date('Y/m') . '/' . $filename;
}

function testimonial_validate_file(array $file, string $type): ?array
{
    if (empty($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) throw new RuntimeException('Upload failed. Please try again.');

    $allowed = $type === 'audio'
        ? ['mp3' => ['audio/mpeg','audio/mp3'], 'wav' => ['audio/wav','audio/x-wav'], 'm4a' => ['audio/mp4','audio/x-m4a'], 'webm' => ['audio/webm','video/webm']]
        : ['mp4' => ['video/mp4'], 'webm' => ['video/webm'], 'mov' => ['video/quicktime']];
    $maxMb = (int) testimonial_setting($type === 'audio' ? 'testimonials_max_audio_mb' : 'testimonials_max_video_mb', $type === 'audio' ? 20 : 150);
    $size = (int) ($file['size'] ?? 0);
    if ($size > ($maxMb * 1024 * 1024)) throw new RuntimeException(ucfirst($type) . ' file is too large. Max size is ' . $maxMb . 'MB.');

    $original = $file['name'] ?? 'upload';
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if (!isset($allowed[$ext])) throw new RuntimeException('Invalid ' . $type . ' extension.');

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']) ?: '';
    if (!in_array($mime, $allowed[$ext], true)) throw new RuntimeException('Invalid ' . $type . ' file type.');

    $filename = $type . '_' . bin2hex(random_bytes(10)) . '.' . $ext;
    $target = testimonial_upload_dir() . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) throw new RuntimeException('Could not save uploaded file.');

    return [
        'url' => testimonial_public_upload_url($filename),
        'original_filename' => $original,
        'mime_type' => $mime,
        'file_size' => $size,
    ];
}

function testimonial_create(array $data, ?array $actor = null, array $files = []): int
{
    $text = trim((string)($data['text'] ?? $data['testimonial_text'] ?? ''));
    $rating = (int)($data['rating'] ?? 0);
    if ($rating < 1 || $rating > 5) throw new RuntimeException('Please choose a rating from 1 to 5.');

    $permission = !empty($data['permission_to_publish']) || !empty($data['permission']);
    if (testimonial_bool('testimonials_require_publish_permission', true) && !$permission) {
        throw new RuntimeException('Publishing permission is required.');
    }

    $audio = null;
    $video = null;
    $audioMeta = null;
    $videoMeta = null;

    if (testimonial_bool('testimonials_allow_audio', true) && isset($files['audio'])) {
        $audioMeta = testimonial_validate_file($files['audio'], 'audio');
        $audio = $audioMeta['url'] ?? null;
    }
    if (testimonial_bool('testimonials_allow_video', true) && isset($files['video'])) {
        $videoMeta = testimonial_validate_file($files['video'], 'video');
        $video = $videoMeta['url'] ?? null;
    }
    if ($text === '' && !$audio && !$video) throw new RuntimeException('Please add text, audio, or video testimonial.');

    $submitterType = $data['submitter_type'] ?? 'public';
    $source = $data['source'] ?? 'public_form';
    $status = testimonial_setting('testimonials_default_status', 'pending_review');
    $displayPreference = $data['display_preference'] ?? 'first_name';
    $displayName = $data['display_name'] ?? null;

    db()->prepare(
        'INSERT INTO testimonials
        (submitter_type, student_user_id, parent_user_id, child_student_user_id, source, rating, testimonial_text, audio_url, video_url, media_type, display_name, display_preference, level, learning_goal, child_learning_focus, permission_to_publish, status)
        VALUES
        (:submitter, :student, :parent, :child, :source, :rating, :text, :audio, :video, :media, :display_name, :display_pref, :level, :goal, :focus, :permission, :status)'
    )->execute([
        ':submitter' => $submitterType,
        ':student' => $data['student_user_id'] ?? null,
        ':parent' => $data['parent_user_id'] ?? null,
        ':child' => $data['child_student_user_id'] ?? null,
        ':source' => $source,
        ':rating' => $rating,
        ':text' => $text ?: null,
        ':audio' => $audio,
        ':video' => $video,
        ':media' => testimonial_media_type($text, $audio, $video),
        ':display_name' => $displayName,
        ':display_pref' => $displayPreference,
        ':level' => $data['level'] ?: null,
        ':goal' => $data['learning_goal'] ?: null,
        ':focus' => $data['child_learning_focus'] ?: null,
        ':permission' => $permission ? 1 : 0,
        ':status' => $status,
    ]);

    $id = (int) db()->lastInsertId();
    foreach ([['audio',$audioMeta], ['video',$videoMeta]] as [$type,$meta]) {
        if ($meta) {
            db()->prepare('INSERT INTO testimonial_media (testimonial_id, media_type, file_url, original_filename, mime_type, file_size) VALUES (:id, :type, :url, :original, :mime, :size)')
                ->execute([':id'=>$id, ':type'=>$type, ':url'=>$meta['url'], ':original'=>$meta['original_filename'], ':mime'=>$meta['mime_type'], ':size'=>$meta['file_size']]);
            audit_log($actor['id'] ?? null, 'testimonial_media_uploaded', 'testimonial', (string)$id, ['media_type'=>$type]);
        }
    }

    audit_log($actor['id'] ?? null, 'testimonial_submitted', 'testimonial', (string)$id, ['submitter_type'=>$submitterType, 'source'=>$source, 'media_type'=>testimonial_media_type($text,$audio,$video)]);
    testimonial_notify_owner($id, $submitterType, testimonial_media_type($text,$audio,$video));
    return $id;
}

function testimonial_notify_owner(int $id, string $submitterType, string $mediaType): void
{
    $title = 'New testimonial submitted';
    $message = 'A ' . $submitterType . ' submitted a new ' . $mediaType . ' testimonial.';
    $owners = db()->query('SELECT id FROM users WHERE role = "owner_teacher"')->fetchAll();
    foreach ($owners as $owner) {
        try {
            comm_create_notification([
                'user_id' => (int)$owner['id'],
                'title' => $title,
                'message' => $message,
                'type' => 'testimonial_submitted',
                'target_role' => 'owner',
                'related_entity_type' => 'testimonial',
                'related_entity_id' => (string)$id,
                'action_label' => 'Review Testimonial',
                'action_url' => '/owner/testimonials/view?id=' . $id,
            ]);
            push_send_to_user((int)$owner['id'], 'testimonial_submitted', $title, $message, '/owner/testimonials/view?id=' . $id, ['testimonial_id'=>$id], 'owner');
        } catch (Throwable $e) {}
    }
}

function testimonial_parent_can_access_child(int $parentId, int $childId): bool
{
    $s = db()->prepare('SELECT 1 FROM parent_child_links WHERE parent_user_id = :parent AND student_user_id = :child LIMIT 1');
    $s->execute([':parent'=>$parentId, ':child'=>$childId]);
    return (bool)$s->fetchColumn();
}

function testimonial_all(array $filters = []): array
{
    $where = ['1=1'];
    $params = [];
    if (!empty($filters['status'])) { $where[] = 'status = :status'; $params[':status'] = $filters['status']; }
    if (!empty($filters['submitter_type'])) { $where[] = 'submitter_type = :submitter'; $params[':submitter'] = $filters['submitter_type']; }
    if (!empty($filters['media_type'])) { $where[] = 'media_type = :media'; $params[':media'] = $filters['media_type']; }
    $sql = 'SELECT * FROM testimonials WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC LIMIT 300';
    $s = db()->prepare($sql);
    $s->execute($params);
    return $s->fetchAll();
}

function testimonial_find(int $id): ?array
{
    $s = db()->prepare('SELECT * FROM testimonials WHERE id = :id LIMIT 1');
    $s->execute([':id'=>$id]);
    $row = $s->fetch();
    return $row ?: null;
}

function testimonial_media(int $id): array
{
    $s = db()->prepare('SELECT * FROM testimonial_media WHERE testimonial_id = :id ORDER BY id ASC');
    $s->execute([':id'=>$id]);
    return $s->fetchAll();
}

function testimonial_update_owner(int $ownerId, int $id, array $data): void
{
    $before = testimonial_find($id);
    if (!$before) throw new RuntimeException('Testimonial not found.');
    db()->prepare('UPDATE testimonials SET display_name=:display_name, public_text_override=:public_text, owner_notes=:notes, featured=:featured, show_on_homepage=:home, show_on_testimonials_page=:page, sort_order=:sort WHERE id=:id')
        ->execute([
            ':display_name'=>trim($data['display_name'] ?? ''),
            ':public_text'=>trim($data['public_text_override'] ?? ''),
            ':notes'=>trim($data['owner_notes'] ?? ''),
            ':featured'=>!empty($data['featured']) ? 1 : 0,
            ':home'=>!empty($data['show_on_homepage']) ? 1 : 0,
            ':page'=>!empty($data['show_on_testimonials_page']) ? 1 : 0,
            ':sort'=>(int)($data['sort_order'] ?? 0),
            ':id'=>$id,
        ]);
    audit_log($ownerId, 'testimonial_edited', 'testimonial', (string)$id, ['before'=>$before, 'after'=>$data]);
}

function testimonial_set_status(int $ownerId, int $id, string $status): void
{
    $allowed = ['approved','rejected','archived','pending_review'];
    if (!in_array($status, $allowed, true)) throw new RuntimeException('Invalid status.');
    $before = testimonial_find($id);
    if (!$before) throw new RuntimeException('Testimonial not found.');
    $fields = 'status = :status';
    if ($status === 'approved') $fields .= ', approved_at = NOW(), approved_by_user_id = :owner';
    if ($status === 'rejected') $fields .= ', rejected_at = NOW()';
    if ($status === 'archived') $fields .= ', archived_at = NOW()';
    $params = [':status'=>$status, ':id'=>$id];
    if ($status === 'approved') $params[':owner'] = $ownerId;
    db()->prepare('UPDATE testimonials SET ' . $fields . ' WHERE id = :id')->execute($params);
    db()->prepare('UPDATE testimonial_media SET status = :media_status WHERE testimonial_id = :id')->execute([':media_status'=>$status === 'approved' ? 'public' : 'private', ':id'=>$id]);
    audit_log($ownerId, 'testimonial_' . $status, 'testimonial', (string)$id, ['before_status'=>$before['status'], 'after_status'=>$status]);
}

function testimonial_public(bool $homepageOnly = false): array
{
    $where = 'status = "approved" AND permission_to_publish = 1 AND show_on_testimonials_page = 1';
    if ($homepageOnly) $where .= ' AND featured = 1 AND show_on_homepage = 1';
    return db()->query('SELECT * FROM testimonials WHERE ' . $where . ' ORDER BY featured DESC, sort_order ASC, created_at DESC LIMIT 60')->fetchAll();
}

function testimonial_public_name(array $row): string
{
    if ($row['display_preference'] === 'anonymous') return 'Anonymous';
    if (!empty($row['display_name'])) return $row['display_name'];
    return $row['submitter_type'] === 'parent' ? 'Parent' : 'Student';
}

function testimonial_public_text(array $row): string
{
    return trim((string)($row['public_text_override'] ?: $row['testimonial_text'] ?: ''));
}
