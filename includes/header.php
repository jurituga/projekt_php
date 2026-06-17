<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Vacanto';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | Vacanto</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect rx='18' width='100' height='100' fill='%230d9488'/><text x='50' y='72' font-size='62' font-weight='800' text-anchor='middle' fill='white'>V</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="nav-overlay" id="navOverlay"></div>

<header class="site-header" id="siteHeader">
    <div class="header-inner container">
        <a href="<?= BASE_URL ?>/index.php" class="logo">
            <span class="logo-icon">V</span>
            <span class="logo-text">Vacanto</span>
        </a>

        <button class="nav-toggle" id="navToggle" type="button" aria-label="Toggle navigation" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav" id="mainNav">
            <div class="nav-links">
                <a href="<?= BASE_URL ?>/index.php" class="nav-link">Home</a>
                <a href="<?= BASE_URL ?>/jobs.php" class="nav-link">Jobs</a>
                <a href="<?= BASE_URL ?>/services.php" class="nav-link">Services</a>
            </div>

            <div class="nav-actions">
                <?php if (isLoggedIn()): ?>
                    <?php $role = currentUserRole(); ?>
                    <?php if ($role === ROLE_ADMIN): ?>
                        <a href="<?= BASE_URL ?>/admin/index.php" class="nav-link">Admin Panel</a>
                    <?php elseif ($role === ROLE_COMPANY): ?>
                        <a href="<?= BASE_URL ?>/company/dashboard.php" class="nav-link">Dashboard</a>
                        <a href="<?= BASE_URL ?>/company/jobs.php" class="nav-link">My Jobs</a>
                    <?php elseif ($role === ROLE_FREELANCER): ?>
                        <a href="<?= BASE_URL ?>/freelancer/dashboard.php" class="nav-link">Dashboard</a>
                        <a href="<?= BASE_URL ?>/freelancer/services.php" class="nav-link">My Services</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/user/dashboard.php" class="nav-link">Dashboard</a>
                    <?php endif; ?>

                    <?php if ($role !== ROLE_ADMIN): ?>
                        <?php
                        $__unread = 0;
                        try {
                            $__pdo = getDB();
                            $__s = $__pdo->prepare("SELECT COUNT(*) FROM messages m JOIN conversations c ON c.id = m.conversation_id WHERE m.is_read = 0 AND m.sender_id != ? AND (c.user_one = ? OR c.user_two = ?)");
                            $__s->execute([currentUserId(), currentUserId(), currentUserId()]);
                            $__unread = (int) $__s->fetchColumn();
                        } catch (Exception $__e) {}
                        ?>
                        <a href="<?= BASE_URL ?>/messages/inbox.php" class="nav-link nav-messages">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            <span class="nav-label">Messages</span>
                            <?php if ($__unread > 0): ?><span class="msg-badge" id="msgBadge"><?= $__unread ?></span><?php else: ?><span class="msg-badge" id="msgBadge" style="display:none"></span><?php endif; ?>
                        </a>
                    <?php endif; ?>

                    <div class="nav-user">
                        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-ghost btn-sm">
                            Logout <span class="nav-user-name">(<?= e($_SESSION['user_name'] ?? '') ?>)</span>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-ghost btn-sm">Log in</a>
                    <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-primary btn-sm">Sign up</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>
<main class="main-content">
