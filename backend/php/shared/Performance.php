<?php
/**
 * Phase 33 performance helper.
 *
 * Lightweight server-side helpers for caching, pagination, lazy media, and
 * skeleton UI. These helpers do not change business logic.
 */

function perf_asset_version(string $path): string
{
    $fullPath = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/') . $path;
    if (is_file($fullPath)) {
        return $path . '?v=' . filemtime($fullPath);
    }
    return $path;
}

function perf_cache_headers(int $seconds = 300, bool $public = true): void
{
    if (headers_sent()) return;
    $visibility = $public ? 'public' : 'private';
    header('Cache-Control: ' . $visibility . ', max-age=' . max(0, $seconds));
    header('X-Content-Type-Options: nosniff');
}

function perf_no_store_headers(): void
{
    if (headers_sent()) return;
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');
}

function perf_pagination_params(int $defaultPerPage = 25, int $maxPerPage = 100): array
{
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = (int)($_GET['per_page'] ?? $defaultPerPage);
    $perPage = max(1, min($perPage, $maxPerPage));
    $offset = ($page - 1) * $perPage;
    return ['page' => $page, 'per_page' => $perPage, 'offset' => $offset, 'limit' => $perPage];
}

function perf_render_pagination(int $page, int $perPage, int $count, string $baseUrl): string
{
    $prev = max(1, $page - 1);
    $next = $page + 1;
    $hasNext = $count >= $perPage;
    $sep = str_contains($baseUrl, '?') ? '&' : '?';
    $html = '<nav class="perf-pagination d-flex gap-2 align-items-center justify-content-between flex-wrap mt-3">';
    $html .= '<span class="text-muted small">Page ' . (int)$page . ' · Showing up to ' . (int)$perPage . ' records</span><div class="d-flex gap-2">';
    if ($page > 1) $html .= '<a class="btn btn-sm btn-outline-brand" href="' . htmlspecialchars($baseUrl . $sep . 'page=' . $prev . '&per_page=' . $perPage, ENT_QUOTES, 'UTF-8') . '">Previous</a>';
    if ($hasNext) $html .= '<a class="btn btn-sm btn-outline-brand" href="' . htmlspecialchars($baseUrl . $sep . 'page=' . $next . '&per_page=' . $perPage, ENT_QUOTES, 'UTF-8') . '">Next</a>';
    $html .= '</div></nav>';
    return $html;
}

function perf_skeleton_cards(int $count = 3): string
{
    $html = '<div class="row g-3 perf-skeleton-wrap">';
    for ($i = 0; $i < $count; $i++) {
        $html .= '<div class="col-md-4"><div class="perf-skeleton-card"><div class="perf-skeleton-line w-75"></div><div class="perf-skeleton-line w-50"></div><div class="perf-skeleton-line w-100"></div></div></div>';
    }
    return $html . '</div>';
}

function perf_lazy_image(string $src, string $alt, string $class = '', int $width = 800, int $height = 450): string
{
    return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '" class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '" width="' . (int)$width . '" height="' . (int)$height . '" loading="lazy" decoding="async">';
}

function perf_lazy_video_embed(string $url, string $title = 'Video'): string
{
    $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    return '<div class="perf-video-shell ratio ratio-16x9" data-video-src="' . $safeUrl . '"><button class="perf-video-load btn btn-brand" type="button">Load video</button><noscript><iframe src="' . $safeUrl . '" title="' . $safeTitle . '" loading="lazy" allowfullscreen></iframe></noscript></div>';
}

function perf_search_input(string $name = 'q', string $placeholder = 'Search...', string $value = ''): string
{
    return '<input class="form-control perf-debounce-search" data-debounce-ms="350" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '" placeholder="' . htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') . '">';
}
