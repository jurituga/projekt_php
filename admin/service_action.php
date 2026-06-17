<?php
require_once __DIR__ . '/../config/init.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /web/admin/services.php');
    exit;
}

$serviceId = (int)($_POST['service_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$serviceId || $action !== 'delete') {
    header('Location: /web/admin/services.php');
    exit;
}

$pdo = getDB();
$pdo->prepare('DELETE FROM services WHERE id = ?')->execute([$serviceId]);

header('Location: /web/admin/services.php');
exit;
