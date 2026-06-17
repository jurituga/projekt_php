<?php
require_once __DIR__ . '/../config/init.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /web/admin/users.php');
    exit;
}

$userId = (int)($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$userId || !in_array($action, ['block', 'activate', 'delete'], true)) {
    header('Location: /web/admin/users.php');
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare('SELECT id, role FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user || $user['role'] === ROLE_ADMIN) {
    header('Location: /web/admin/users.php');
    exit;
}

if ($action === 'block') {
    $pdo->prepare('UPDATE users SET status = ? WHERE id = ?')->execute([USER_STATUS_BLOCKED, $userId]);
} elseif ($action === 'activate') {
    $pdo->prepare('UPDATE users SET status = ? WHERE id = ?')->execute([USER_STATUS_ACTIVE, $userId]);
} elseif ($action === 'delete') {
    $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);
}

header('Location: /web/admin/users.php');
exit;
