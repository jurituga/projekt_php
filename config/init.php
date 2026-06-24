<?php
/**
 * Global init: session, constants, autoload
 */

// Show PHP errors so "blank page" issues are visible (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/notifications.php';

// Composer autoloader (for Stripe etc.)
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Ensure upload directories exist (suppress permission errors so the site still loads)
if (!is_dir(UPLOAD_PATH_CV)) {
    @mkdir(UPLOAD_PATH_CV, 0755, true);
}
if (!is_dir(UPLOAD_PATH_IMAGES)) {
    @mkdir(UPLOAD_PATH_IMAGES, 0755, true);
}
if (!is_dir(UPLOAD_PATH_GOV_ID)) {
    @mkdir(UPLOAD_PATH_GOV_ID, 0755, true);
}
if (!is_dir(UPLOAD_PATH_CERTIFICATIONS)) {
    @mkdir(UPLOAD_PATH_CERTIFICATIONS, 0755, true);
}

/**
 * Require login and optionally a specific role
 */
function requireLogin(?string $allowedRole = null): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
    if ($allowedRole !== null && $_SESSION['role'] !== $allowedRole) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

/**
 * Require admin role
 */
function requireAdmin(): void {
    requireLogin(ROLE_ADMIN);
}

/**
 * Get current user ID or null
 */
function currentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role or null
 */
function currentUserRole(): ?string {
    return $_SESSION['role'] ?? null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Escape for HTML output
 */
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/**
 * Render star rating HTML (read-only display)
 * @param float|int $rating  The rating value (1-5)
 * @param int       $max     Maximum stars (default 5)
 * @return string   HTML string
 */
function renderStars($rating, int $max = 5): string {
    $filled = (int) round((float) $rating);
    if ($filled < 0) $filled = 0;
    if ($filled > $max) $filled = $max;
    $empty = $max - $filled;
    $html = '<span class="stars-display">';
    $html .= '<span class="star-filled">' . str_repeat('&#9733;', $filled) . '</span>';
    $html .= '<span class="star-empty">' . str_repeat('&#9734;', $empty) . '</span>';
    $html .= '</span>';
    return $html;
}
