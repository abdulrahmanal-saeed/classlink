<?php
/**
 * Phase 32 UX helper components.
 *
 * Lightweight reusable UI patterns to make pages answer:
 * 1. Where am I?
 * 2. What is the status?
 * 3. What should I do next?
 * 4. What happens after I click?
 */

function ux_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function ux_page_intro(string $eyebrow, string $title, string $description, array $actions = []): string
{
    $html = '<div class="ux-page-intro foundation-card mb-4">';
    $html .= '<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">';
    $html .= '<div><div class="ux-eyebrow">' . ux_h($eyebrow) . '</div><h2 class="h4 fw-bold mb-2">' . ux_h($title) . '</h2><p class="text-muted mb-0">' . ux_h($description) . '</p></div>';
    if ($actions) {
        $html .= '<div class="d-flex gap-2 flex-wrap">';
        foreach ($actions as $action) {
            $class = !empty($action['primary']) ? 'btn btn-brand' : 'btn btn-outline-brand';
            $html .= '<a class="' . $class . '" href="' . ux_h($action['href']) . '">' . ux_h($action['label']) . '</a>';
        }
        $html .= '</div>';
    }
    $html .= '</div></div>';
    return $html;
}

function ux_next_step_card(string $title, string $body, string $href, string $label, string $tone = 'brand'): string
{
    $btn = $tone === 'brand' ? 'btn btn-brand' : 'btn btn-outline-brand';
    return '<div class="ux-next-step foundation-card mb-4"><div><div class="ux-eyebrow">Recommended next step</div><h2 class="h5 fw-bold">' . ux_h($title) . '</h2><p class="text-muted mb-0">' . ux_h($body) . '</p></div><a class="' . $btn . '" href="' . ux_h($href) . '">' . ux_h($label) . '</a></div>';
}

function ux_empty_state(string $title, string $body, ?string $href = null, ?string $label = null): string
{
    $html = '<div class="ux-empty-state">';
    $html .= '<div class="ux-empty-icon">✨</div><h3 class="h6 fw-bold">' . ux_h($title) . '</h3><p class="text-muted mb-3">' . ux_h($body) . '</p>';
    if ($href && $label) $html .= '<a class="btn btn-sm btn-outline-brand" href="' . ux_h($href) . '">' . ux_h($label) . '</a>';
    $html .= '</div>';
    return $html;
}

function ux_status_badge(string $status): string
{
    $normalized = strtolower(trim($status));
    $tone = match ($normalized) {
        'paid', 'approved', 'confirmed', 'completed', 'corrected', 'published', 'active', 'converted_to_student' => 'success',
        'pending', 'pending_verification', 'submitted', 'requested', 'under_review', 'needs_correction', 'pending_review' => 'warning',
        'failed', 'rejected', 'cancelled', 'canceled', 'no_show', 'refunded', 'reversed' => 'danger',
        'draft', 'hidden', 'archived', 'not_started' => 'muted',
        default => 'neutral',
    };
    return '<span class="ux-status ux-status-' . ux_h($tone) . '">' . ux_h(str_replace('_', ' ', $status)) . '</span>';
}

function ux_step_indicator(array $steps, int $currentIndex): string
{
    $html = '<div class="ux-stepper mb-4">';
    foreach ($steps as $index => $step) {
        $class = $index < $currentIndex ? 'done' : ($index === $currentIndex ? 'current' : 'upcoming');
        $html .= '<div class="ux-step ' . $class . '"><span>' . ($index + 1) . '</span><strong>' . ux_h($step) . '</strong></div>';
    }
    $html .= '</div>';
    return $html;
}

function ux_confirm_attrs(string $message): string
{
    return ' data-confirm-message="' . ux_h($message) . '"';
}

function ux_helper_text(string $text, ?string $href = null, ?string $label = null): string
{
    $html = '<div class="ux-helper-text">' . ux_h($text);
    if ($href && $label) $html .= ' <a href="' . ux_h($href) . '">' . ux_h($label) . '</a>';
    $html .= '</div>';
    return $html;
}
