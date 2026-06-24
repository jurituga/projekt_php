<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$userId = currentUserId();

if (!$id) {
    header('Location: ' . BASE_URL . '/notifications/index.php');
    exit;
}

ensureNotificationsTable();
$pdo = getDB();
$stmt = $pdo->prepare('SELECT id, link FROM notifications WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $userId]);
$row = $stmt->fetch();

if (!$row) {
    header('Location: ' . BASE_URL . '/notifications/index.php');
    exit;
}

markNotificationRead($id, $userId);

$target = !empty($row['link']) ? $row['link'] : (BASE_URL . '/notifications/index.php');
header('Location: ' . $target);
exit;
