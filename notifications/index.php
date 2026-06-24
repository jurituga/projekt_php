<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();

$userId = currentUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    markAllNotificationsRead($userId);
    header('Location: ' . BASE_URL . '/notifications/index.php');
    exit;
}

$notifications = getNotifications($userId);

$pageTitle = 'Notifications';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header-row">
        <h1 style="margin:0">Notifications</h1>
        <?php if (!empty($notifications) && unreadNotificationCount($userId) > 0): ?>
            <form method="post">
                <input type="hidden" name="mark_all_read" value="1">
                <button type="submit" class="btn btn-ghost btn-sm">Mark all as read</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="card" style="text-align:center;padding:2.5rem">
            <p class="muted" style="margin:0">No notifications yet.</p>
        </div>
    <?php else: ?>
        <div class="notification-list">
            <?php foreach ($notifications as $n): ?>
                <?php
                $href = !empty($n['link']) ? $n['link'] : (BASE_URL . '/notifications/read.php?id=' . (int)$n['id']);
                $isUnread = empty($n['read_at']);
                ?>
                <a href="<?= e($href) ?>" class="notification-item<?= $isUnread ? ' unread' : '' ?>">
                    <div class="notification-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </div>
                    <div class="notification-body">
                        <div class="notification-title-row">
                            <span class="notification-title"><?= e($n['title']) ?></span>
                            <?php if ($isUnread): ?><span class="notification-dot"></span><?php endif; ?>
                        </div>
                        <?php if (!empty($n['message'])): ?>
                            <p class="notification-message"><?= e($n['message']) ?></p>
                        <?php endif; ?>
                        <span class="notification-time"><?= date('M j, Y g:i A', strtotime($n['created_at'])) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
