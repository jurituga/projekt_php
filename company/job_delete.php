<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_COMPANY);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /web/company/jobs.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) {
    header('Location: /web/company/jobs.php');
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare('SELECT j.id FROM jobs j JOIN companies c ON c.id = j.company_id WHERE j.id = ? AND c.user_id = ?');
$stmt->execute([$id, currentUserId()]);
if (!$stmt->fetch()) {
    header('Location: /web/company/jobs.php');
    exit;
}
$pdo->prepare('DELETE FROM jobs WHERE id = ?')->execute([$id]);

header('Location: /web/company/jobs.php');
exit;
