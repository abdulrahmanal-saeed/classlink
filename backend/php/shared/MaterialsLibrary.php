<?php
/**
 * Phase 25 Advanced Materials Library helper.
 * PHP + MySQL + local/Hostinger storage. No Supabase Storage.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/CommunicationCenter.php';

function material_setting(string $key, $default = null)
{
    $s = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1');
    $s->execute([':key' => $key]);
    $v = $s->fetchColumn();
    return $v === false ? $default : $v;
}

function material_bool(string $key, bool $default = false): bool
{
    return material_setting($key, $default ? '1' : '0') === '1';
}

function material_categories(bool $activeOnly = true): array
{
    $sql = 'SELECT * FROM material_categories ' . ($activeOnly ? 'WHERE active=1 ' : '') . 'ORDER BY sort_order ASC, name ASC';
    return db()->query($sql)->fetchAll();
}

function material_upload_root(): string
{
    $base = material_setting('materials_upload_base_path', 'web/public/uploads/materials');
    if (!str_starts_with($base, '/')) $base = __DIR__ . '/../../../' . $base;
    if (!is_dir($base)) mkdir($base, 0755, true);
    return rtrim($base, '/');
}

function material_type_folder(string $type): string
{
    return match ($type) {
        'video_upload' => 'videos',
        'audio_upload' => 'audio',
        'pdf' => 'pdf',
        'powerpoint' => 'powerpoint',
        'image' => 'images',
        'html_file' => 'html',
        'document' => 'documents',
        default => 'other',
    };
}

function material_public_url(string $folder, string $filename): string
{
    $baseUrl = trim((string) material_setting('materials_public_file_base_url', ''));
    $relative = '/uploads/materials/' . $folder . '/' . date('Y/m') . '/' . $filename;
    return $baseUrl ? rtrim($baseUrl, '/') . $relative : $relative;
}

function material_allowed_extensions(string $type): array
{
    return match ($type) {
        'video_upload' => ['mp4'=>['video/mp4'], 'webm'=>['video/webm'], 'mov'=>['video/quicktime']],
        'audio_upload' => ['mp3'=>['audio/mpeg','audio/mp3'], 'wav'=>['audio/wav','audio/x-wav'], 'm4a'=>['audio/mp4','audio/x-m4a'], 'webm'=>['audio/webm','video/webm']],
        'pdf' => ['pdf'=>['application/pdf']],
        'powerpoint' => ['ppt'=>['application/vnd.ms-powerpoint','application/octet-stream'], 'pptx'=>['application/vnd.openxmlformats-officedocument.presentationml.presentation','application/octet-stream']],
        'document' => ['doc'=>['application/msword','application/octet-stream'], 'docx'=>['application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/octet-stream']],
        'image' => ['jpg'=>['image/jpeg'], 'jpeg'=>['image/jpeg'], 'png'=>['image/png'], 'webp'=>['image/webp']],
        'html_file' => ['html'=>['text/html','application/octet-stream']],
        default => [],
    };
}

function material_max_bytes(string $type): int
{
    $mb = match ($type) {
        'video_upload' => (int) material_setting('materials_max_video_mb', 250),
        'audio_upload' => (int) material_setting('materials_max_audio_mb', 50),
        'image' => (int) material_setting('materials_max_image_mb', 20),
        default => (int) material_setting('materials_max_document_mb', 80),
    };
    return $mb * 1024 * 1024;
}

function material_validate_upload(array $file, string $type): ?array
{
    if (empty($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) throw new RuntimeException('Upload failed.');
    if ($type === 'html_file' && !material_bool('materials_allow_html_upload', true)) throw new RuntimeException('HTML uploads are disabled.');

    $dangerous = ['php','exe','bat','cmd','sh','js','msi','dll','ps1','com','scr'];
    $original = $file['name'] ?? 'upload';
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if (in_array($ext, $dangerous, true)) throw new RuntimeException('This file type is blocked for security.');

    $allowed = material_allowed_extensions($type);
    if (!$allowed || !isset($allowed[$ext])) throw new RuntimeException('Invalid file extension for this material type.');
    if ((int)($file['size'] ?? 0) > material_max_bytes($type)) throw new RuntimeException('File is too large for this material type.');

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']) ?: 'application/octet-stream';
    if (!in_array($mime, $allowed[$ext], true)) throw new RuntimeException('Invalid file MIME type.');

    $folder = material_type_folder($type) . '/' . date('Y/m');
    $targetDir = material_upload_root() . '/' . $folder;
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    $saved = $type . '_' . bin2hex(random_bytes(10)) . '.' . $ext;
    $target = $targetDir . '/' . $saved;
    if (!move_uploaded_file($file['tmp_name'], $target)) throw new RuntimeException('Could not save uploaded file.');

    return [
        'file_url' => material_public_url(material_type_folder($type), $saved),
        'file_path' => $target,
        'original_filename' => $original,
        'saved_filename' => $saved,
        'mime_type' => $mime,
        'file_size' => (int)$file['size'],
        'storage_driver' => 'local',
    ];
}

function material_create(array $data, array $actor, array $files = []): int
{
    $type = $data['material_type'] ?? 'text_article';
    $allowedTypes = ['video_upload','external_video','audio_upload','pdf','powerpoint','document','image','external_link','text_article','html_file','mixed_page'];
    if (!in_array($type, $allowedTypes, true)) throw new RuntimeException('Invalid material type.');
    $upload = isset($files['material_file']) ? material_validate_upload($files['material_file'], $type) : null;
    $htmlMode = $type === 'html_file' ? material_setting('materials_html_default_mode', 'download_only') : null;

    db()->prepare('INSERT INTO course_materials (title, description, material_type, category_id, level, tags, material_language, estimated_study_minutes, status, file_url, file_path, external_url, html_sandbox_mode, allow_download, text_content, teacher_notes, created_by_user_id) VALUES (:title,:description,:type,:category,:level,:tags,:lang,:minutes,:status,:file_url,:file_path,:external,:html_mode,:download,:text,:notes,:creator)')
        ->execute([
            ':title' => trim($data['title'] ?? ''),
            ':description' => trim($data['description'] ?? ''),
            ':type' => $type,
            ':category' => $data['category_id'] ?: null,
            ':level' => $data['level'] ?: 'not_set',
            ':tags' => trim($data['tags'] ?? ''),
            ':lang' => $data['material_language'] ?? 'both',
            ':minutes' => $data['estimated_study_minutes'] ?: null,
            ':status' => $data['status'] ?? 'draft',
            ':file_url' => $upload['file_url'] ?? null,
            ':file_path' => $upload['file_path'] ?? null,
            ':external' => trim($data['external_url'] ?? '') ?: null,
            ':html_mode' => $htmlMode,
            ':download' => !empty($data['allow_download']) ? 1 : 0,
            ':text' => trim($data['text_content'] ?? '') ?: null,
            ':notes' => trim($data['teacher_notes'] ?? '') ?: null,
            ':creator' => (int)$actor['id'],
        ]);
    $id = (int) db()->lastInsertId();
    if ($upload) {
        db()->prepare('INSERT INTO material_files (material_id, file_url, file_path, original_filename, saved_filename, mime_type, file_size, storage_driver) VALUES (:material,:url,:path,:original,:saved,:mime,:size,:driver)')
            ->execute([':material'=>$id, ':url'=>$upload['file_url'], ':path'=>$upload['file_path'], ':original'=>$upload['original_filename'], ':saved'=>$upload['saved_filename'], ':mime'=>$upload['mime_type'], ':size'=>$upload['file_size'], ':driver'=>'local']);
        audit_log((int)$actor['id'], 'material_uploaded', 'material', (string)$id, ['type'=>$type, 'filename'=>$upload['original_filename']]);
    }
    audit_log((int)$actor['id'], 'material_created', 'material', (string)$id, ['type'=>$type, 'status'=>$data['status'] ?? 'draft']);
    return $id;
}

function material_update(int $id, array $data, array $actor): void
{
    $before = material_find($id);
    if (!$before) throw new RuntimeException('Material not found.');
    db()->prepare('UPDATE course_materials SET title=:title, description=:description, category_id=:category, level=:level, tags=:tags, material_language=:lang, estimated_study_minutes=:minutes, status=:status, external_url=:external, allow_download=:download, text_content=:text, teacher_notes=:notes WHERE id=:id')
        ->execute([
            ':title'=>trim($data['title'] ?? ''), ':description'=>trim($data['description'] ?? ''), ':category'=>$data['category_id'] ?: null,
            ':level'=>$data['level'] ?: 'not_set', ':tags'=>trim($data['tags'] ?? ''), ':lang'=>$data['material_language'] ?? 'both', ':minutes'=>$data['estimated_study_minutes'] ?: null,
            ':status'=>$data['status'] ?? 'draft', ':external'=>trim($data['external_url'] ?? '') ?: null, ':download'=>!empty($data['allow_download']) ? 1 : 0,
            ':text'=>trim($data['text_content'] ?? '') ?: null, ':notes'=>trim($data['teacher_notes'] ?? '') ?: null, ':id'=>$id,
        ]);
    audit_log((int)$actor['id'], 'material_edited', 'material', (string)$id, ['before'=>$before, 'after'=>$data]);
}

function material_find(int $id): ?array
{
    $s = db()->prepare('SELECT cm.*, mc.name AS category_name FROM course_materials cm LEFT JOIN material_categories mc ON mc.id = cm.category_id WHERE cm.id=:id LIMIT 1');
    $s->execute([':id'=>$id]);
    $row = $s->fetch();
    return $row ?: null;
}

function material_list(array $filters = []): array
{
    $where = ['1=1']; $params = [];
    foreach (['status'=>'status','material_type'=>'material_type','level'=>'level','material_language'=>'material_language'] as $k=>$col) {
        if (!empty($filters[$k])) { $where[] = "cm.$col = :$k"; $params[":$k"] = $filters[$k]; }
    }
    if (!empty($filters['q'])) { $where[] = '(cm.title LIKE :q OR cm.description LIKE :q OR cm.tags LIKE :q)'; $params[':q'] = '%' . $filters['q'] . '%'; }
    $sql = 'SELECT cm.*, mc.name AS category_name FROM course_materials cm LEFT JOIN material_categories mc ON mc.id=cm.category_id WHERE ' . implode(' AND ', $where) . ' ORDER BY cm.created_at DESC LIMIT 300';
    $s = db()->prepare($sql); $s->execute($params); return $s->fetchAll();
}

function material_assign(int $materialId, int $studentId, array $actor, ?string $notes = null, ?string $dueDate = null, bool $required = false): void
{
    db()->prepare('INSERT INTO material_assignments (material_id, student_user_id, assigned_by_user_id, assigned_at, visible, notes, due_date, required) VALUES (:material,:student,:owner,NOW(),1,:notes,:due,:required) ON DUPLICATE KEY UPDATE visible=1, notes=VALUES(notes), due_date=VALUES(due_date), required=VALUES(required)')
        ->execute([':material'=>$materialId, ':student'=>$studentId, ':owner'=>(int)$actor['id'], ':notes'=>$notes, ':due'=>$dueDate ?: null, ':required'=>$required ? 1 : 0]);
    db()->prepare('INSERT IGNORE INTO material_progress (material_id, student_user_id, status) VALUES (:material,:student,"assigned")')->execute([':material'=>$materialId, ':student'=>$studentId]);
    $mat = material_find($materialId);
    comm_create_notification(['user_id'=>$studentId, 'title'=>'New material assigned', 'message'=>'You have a new material: ' . ($mat['title'] ?? ''), 'type'=>'material_assigned', 'target_role'=>'student', 'related_entity_type'=>'material', 'related_entity_id'=>(string)$materialId, 'action_label'=>'Open Material', 'action_url'=>'/student/materials/view?id='.$materialId]);
    audit_log((int)$actor['id'], 'material_assigned', 'material', (string)$materialId, ['student_id'=>$studentId]);
}

function material_student_assigned(int $studentId): array
{
    $s = db()->prepare('SELECT cm.*, mc.name AS category_name, ma.due_date, ma.required, mp.status AS progress_status, mp.completed_at FROM material_assignments ma JOIN course_materials cm ON cm.id=ma.material_id LEFT JOIN material_categories mc ON mc.id=cm.category_id LEFT JOIN material_progress mp ON mp.material_id=cm.id AND mp.student_user_id=ma.student_user_id WHERE ma.student_user_id=:student AND ma.visible=1 AND cm.status="published" ORDER BY ma.assigned_at DESC');
    $s->execute([':student'=>$studentId]); return $s->fetchAll();
}

function material_can_student_access(int $studentId, int $materialId): bool
{
    $s = db()->prepare('SELECT 1 FROM material_assignments ma JOIN course_materials cm ON cm.id=ma.material_id WHERE ma.student_user_id=:student AND ma.material_id=:material AND ma.visible=1 AND cm.status="published" LIMIT 1');
    $s->execute([':student'=>$studentId, ':material'=>$materialId]); return (bool)$s->fetchColumn();
}

function material_parent_can_access(int $parentId, int $childId, int $materialId): bool
{
    $s = db()->prepare('SELECT 1 FROM parent_child_links pcl JOIN material_assignments ma ON ma.student_user_id=pcl.student_user_id JOIN course_materials cm ON cm.id=ma.material_id WHERE pcl.parent_user_id=:parent AND pcl.student_user_id=:child AND ma.material_id=:material AND ma.visible=1 AND cm.status="published" LIMIT 1');
    $s->execute([':parent'=>$parentId, ':child'=>$childId, ':material'=>$materialId]); return (bool)$s->fetchColumn();
}

function material_track_view(int $materialId, int $studentId, ?array $actor = null): void
{
    db()->prepare('INSERT INTO material_progress (material_id, student_user_id, viewed_at, last_opened_at, status) VALUES (:m,:s,NOW(),NOW(),"viewed") ON DUPLICATE KEY UPDATE last_opened_at=NOW(), viewed_at=COALESCE(viewed_at,NOW()), status=IF(status="completed",status,"viewed")')->execute([':m'=>$materialId, ':s'=>$studentId]);
    audit_log($actor['id'] ?? $studentId, 'material_viewed', 'material', (string)$materialId, ['student_id'=>$studentId]);
}

function material_mark_completed(int $materialId, int $studentId, ?array $actor = null): void
{
    db()->prepare('INSERT INTO material_progress (material_id, student_user_id, viewed_at, last_opened_at, completed_at, status, completion_source) VALUES (:m,:s,NOW(),NOW(),NOW(),"completed","student_marked") ON DUPLICATE KEY UPDATE completed_at=NOW(), status="completed", completion_source="student_marked"')->execute([':m'=>$materialId, ':s'=>$studentId]);
    audit_log($actor['id'] ?? $studentId, 'material_completed', 'material', (string)$materialId, ['student_id'=>$studentId]);
}

function material_render_viewer(array $m): string
{
    $url = htmlspecialchars((string)($m['file_url'] ?: $m['external_url']), ENT_QUOTES, 'UTF-8');
    $type = $m['material_type'];
    if ($type === 'text_article' || $type === 'mixed_page') return '<article class="material-reading">' . nl2br(htmlspecialchars((string)$m['text_content'], ENT_QUOTES, 'UTF-8')) . '</article>';
    if ($type === 'video_upload') return '<video controls preload="metadata" class="w-100 rounded-4" src="'.$url.'"></video>';
    if ($type === 'external_video' || $type === 'external_link') return '<a class="btn btn-brand" target="_blank" rel="noopener noreferrer" href="'.$url.'">Open Link</a>';
    if ($type === 'audio_upload') return '<audio controls preload="metadata" class="w-100" src="'.$url.'"></audio>';
    if ($type === 'pdf') return '<iframe class="w-100 border rounded-4" style="height:70vh" src="'.$url.'"></iframe>';
    if ($type === 'image') return '<img class="img-fluid rounded-4" src="'.$url.'" alt="Material image">';
    if ($type === 'html_file') {
        if (($m['html_sandbox_mode'] ?? '') === 'sandboxed_iframe') return '<iframe class="w-100 border rounded-4" style="height:70vh" sandbox src="'.$url.'"></iframe>';
        return '<div class="alert alert-warning">HTML preview is download-only for safety.</div><a class="btn btn-outline-brand" href="'.$url.'" download>Download HTML</a>';
    }
    if ($type === 'powerpoint') return '<div class="alert alert-info">PowerPoint preview may not be available. You can download the file.</div><a class="btn btn-outline-brand" href="'.$url.'" download>Download PowerPoint</a>';
    if ($type === 'document') return '<div class="alert alert-info">Document preview may not be available. You can download the file.</div><a class="btn btn-outline-brand" href="'.$url.'" download>Download Document</a>';
    return '<a class="btn btn-outline-brand" href="'.$url.'" target="_blank" rel="noopener noreferrer">Open Material</a>';
}

function material_storage_health(): array
{
    $root = material_upload_root();
    return [
        'storage_driver' => material_setting('materials_upload_storage_driver', 'local'),
        'upload_root_exists' => is_dir($root),
        'upload_root_writable' => is_writable($root),
        'database_connected' => (bool) db()->query('SELECT 1')->fetchColumn(),
        'notes' => 'MySQL stores metadata only. Files are stored on local/Hostinger storage.',
    ];
}
