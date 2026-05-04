<?php
/**
 * Phase 34 Upload security helper.
 *
 * Central validation for uploads. Existing upload routes can adopt this helper
 * incrementally without changing business logic.
 */

function upload_security_blocked_extensions(): array
{
    return ['php','phtml','phar','exe','bat','cmd','sh','msi','dll','ps1','com','scr','cgi','pl','py','rb','jar'];
}

function upload_security_allowed_extensions(): array
{
    return [
        'audio' => ['mp3','wav','m4a','webm'],
        'video' => ['mp4','webm','mov'],
        'image' => ['jpg','jpeg','png','webp'],
        'pdf' => ['pdf'],
        'powerpoint' => ['ppt','pptx'],
        'document' => ['doc','docx'],
        'html' => ['html'],
    ];
}

function upload_security_allowed_mimes(): array
{
    return [
        'audio' => ['audio/mpeg','audio/wav','audio/x-wav','audio/mp4','audio/webm'],
        'video' => ['video/mp4','video/webm','video/quicktime'],
        'image' => ['image/jpeg','image/png','image/webp'],
        'pdf' => ['application/pdf'],
        'powerpoint' => ['application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'document' => ['application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'html' => ['text/html'],
    ];
}

function upload_security_extension(string $filename): string
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function upload_security_detect_mime(string $tmpPath): string
{
    if (!is_file($tmpPath)) return 'application/octet-stream';
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    return $finfo->file($tmpPath) ?: 'application/octet-stream';
}

function upload_security_max_size(string $type): int
{
    $map = [
        'audio' => (int)(getenv('MAX_AUDIO_UPLOAD_BYTES') ?: 25 * 1024 * 1024),
        'video' => (int)(getenv('MAX_VIDEO_UPLOAD_BYTES') ?: 200 * 1024 * 1024),
        'image' => (int)(getenv('MAX_IMAGE_UPLOAD_BYTES') ?: 10 * 1024 * 1024),
        'pdf' => (int)(getenv('MAX_DOCUMENT_UPLOAD_BYTES') ?: 25 * 1024 * 1024),
        'powerpoint' => (int)(getenv('MAX_DOCUMENT_UPLOAD_BYTES') ?: 50 * 1024 * 1024),
        'document' => (int)(getenv('MAX_DOCUMENT_UPLOAD_BYTES') ?: 25 * 1024 * 1024),
        'html' => (int)(getenv('MAX_HTML_UPLOAD_BYTES') ?: 2 * 1024 * 1024),
    ];
    return $map[$type] ?? (10 * 1024 * 1024);
}

function upload_security_validate(array $file, string $type): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [false, 'Upload failed. Please try again.', null];
    }

    $originalName = (string)($file['name'] ?? 'upload');
    $tmpPath = (string)($file['tmp_name'] ?? '');
    $size = (int)($file['size'] ?? 0);
    $extension = upload_security_extension($originalName);
    $mime = upload_security_detect_mime($tmpPath);

    if (!$extension || in_array($extension, upload_security_blocked_extensions(), true)) {
        return [false, 'This file type is not allowed.', null];
    }

    $allowedExtensions = upload_security_allowed_extensions()[$type] ?? [];
    $allowedMimes = upload_security_allowed_mimes()[$type] ?? [];

    if (!in_array($extension, $allowedExtensions, true)) {
        return [false, 'Invalid file extension for this upload type.', null];
    }

    if ($allowedMimes && !in_array($mime, $allowedMimes, true)) {
        return [false, 'Invalid file content type.', null];
    }

    if ($size <= 0 || $size > upload_security_max_size($type)) {
        return [false, 'File is too large or empty.', null];
    }

    if ($type === 'html') {
        // HTML must be sandboxed or download-only by the viewer. This validator
        // allows .html only and rejects script-like extensions.
    }

    $safeName = bin2hex(random_bytes(16)) . '.' . $extension;
    return [true, null, [
        'original_filename' => $originalName,
        'saved_filename' => $safeName,
        'extension' => $extension,
        'mime_type' => $mime,
        'file_size' => $size,
        'tmp_path' => $tmpPath,
    ]];
}

function upload_security_move(array $validated, string $targetDir): string
{
    if (!is_dir($targetDir)) @mkdir($targetDir, 0755, true);
    $target = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $validated['saved_filename'];
    if (!move_uploaded_file($validated['tmp_path'], $target)) {
        throw new RuntimeException('Could not save uploaded file.');
    }
    @chmod($target, 0644);
    return $target;
}

function upload_security_private_download_headers(string $filename, string $mimeType = 'application/octet-stream'): void
{
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename) . '"');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, no-store, max-age=0');
}
