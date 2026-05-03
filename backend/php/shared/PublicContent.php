<?php
/**
 * Public content helper for Phase 3.
 *
 * These helpers keep public website and Owner CMS pages using the same database
 * access patterns. Later we can move this into repository classes if needed.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/Settings.php';

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\x{0600}-\x{06FF}]+/u', '-', $text);
    $text = trim($text, '-');

    return $text !== '' ? $text : 'item-' . time();
}

function public_setting_enabled(string $key, bool $default = true): bool
{
    $value = setting_get($key, $default ? '1' : '0');
    return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
}

function public_plans(): array
{
    $statement = db()->query('SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
    return $statement->fetchAll();
}

function published_articles(int $limit = 20): array
{
    $statement = db()->prepare('SELECT * FROM articles WHERE status = "published" ORDER BY published_at DESC, id DESC LIMIT :limit');
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetchAll();
}

function article_by_slug(string $slug): ?array
{
    $statement = db()->prepare('SELECT * FROM articles WHERE slug = :slug AND status = "published" LIMIT 1');
    $statement->execute([':slug' => $slug]);
    $article = $statement->fetch();
    return $article ?: null;
}

function published_videos(int $limit = 20): array
{
    $statement = db()->prepare('SELECT * FROM videos WHERE status = "published" ORDER BY id DESC LIMIT :limit');
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetchAll();
}

function video_by_slug(string $slug): ?array
{
    $statement = db()->prepare('SELECT * FROM videos WHERE slug = :slug AND status = "published" LIMIT 1');
    $statement->execute([':slug' => $slug]);
    $video = $statement->fetch();
    return $video ?: null;
}

function approved_testimonials(int $limit = 20): array
{
    $statement = db()->prepare('SELECT * FROM testimonials WHERE status = "approved" ORDER BY id DESC LIMIT :limit');
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetchAll();
}

function page_content(string $pageKey): ?array
{
    $statement = db()->prepare('SELECT * FROM public_page_contents WHERE page_key = :page_key LIMIT 1');
    $statement->execute([':page_key' => $pageKey]);
    $page = $statement->fetch();
    return $page ?: null;
}

function seo_text(?array $row, string $fallbackTitle, string $fallbackDescription = ''): array
{
    return [
        'title' => $row['seo_title'] ?? $fallbackTitle,
        'description' => $row['seo_description'] ?? $fallbackDescription,
    ];
}
