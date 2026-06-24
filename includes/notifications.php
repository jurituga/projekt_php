<?php
/**
 * In-app notifications (auto-creates table if missing).
 */

function ensureNotificationsTable(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $pdo = getDB();
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            type VARCHAR(50) NOT NULL DEFAULT 'info',
            title VARCHAR(255) NOT NULL,
            message TEXT NULL,
            link VARCHAR(500) NULL,
            read_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_notifications_user (user_id),
            INDEX idx_notifications_read (user_id, read_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

function createNotification(int $userId, string $title, string $message, ?string $link = null, string $type = 'info'): void
{
    if ($userId <= 0) {
        return;
    }
    try {
        ensureNotificationsTable();
        $pdo = getDB();
        $stmt = $pdo->prepare('INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $type, $title, $message, $link]);
    } catch (Throwable $e) {
        // ignore if DB unavailable
    }
}

function unreadNotificationCount(?int $userId = null): int
{
    $userId = $userId ?? currentUserId();
    if (!$userId) {
        return 0;
    }
    try {
        ensureNotificationsTable();
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

function getNotifications(int $userId, int $limit = 50): array
{
    ensureNotificationsTable();
    $pdo = getDB();
    $stmt = $pdo->prepare('
        SELECT id, type, title, message, link, read_at, created_at
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ');
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function markNotificationRead(int $notificationId, int $userId): void
{
    ensureNotificationsTable();
    $pdo = getDB();
    $pdo->prepare('UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ? AND read_at IS NULL')
        ->execute([$notificationId, $userId]);
}

function markAllNotificationsRead(int $userId): void
{
    ensureNotificationsTable();
    $pdo = getDB();
    $pdo->prepare('UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL')
        ->execute([$userId]);
}
