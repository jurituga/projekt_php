<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_FREELANCER);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/freelancer/services.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) {
    header('Location: ' . BASE_URL . '/freelancer/services.php');
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare('DELETE FROM services WHERE id = ? AND freelancer_id = ?');
$stmt->execute([$id, currentUserId()]);

header('Location: ' . BASE_URL . '/freelancer/services.php');
exit;
