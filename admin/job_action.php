<?php
require_once __DIR__ . '/../config/init.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /web/admin/jobs.php');
    exit;
}

$jobId = (int)($_POST['job_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$jobId || $action !== 'delete') {
    header('Location: /web/admin/jobs.php');
    exit;
}

$pdo = getDB();
$pdo->prepare('DELETE FROM jobs WHERE id = ?')->execute([$jobId]);

header('Location: /web/admin/jobs.php');
exit;
