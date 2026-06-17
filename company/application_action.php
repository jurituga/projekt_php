<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_COMPANY);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /web/company/dashboard.php');
    exit;
}

$applicationId = (int)($_POST['application_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$applicationId || !in_array($action, ['accept', 'reject'], true)) {
    header('Location: /web/company/dashboard.php');
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare('
    SELECT a.id FROM applications a
    JOIN jobs j ON j.id = a.job_id
    JOIN companies c ON c.id = j.company_id
    WHERE a.id = ? AND c.user_id = ?
');
$stmt->execute([$applicationId, currentUserId()]);
if (!$stmt->fetch()) {
    header('Location: /web/company/dashboard.php');
    exit;
}

$newStatus = $action === 'accept' ? APPLICATION_STATUS_ACCEPTED : APPLICATION_STATUS_REJECTED;
$pdo->prepare('UPDATE applications SET status = ?, updated_at = NOW() WHERE id = ?')->execute([$newStatus, $applicationId]);

$referer = $_SERVER['HTTP_REFERER'] ?? '/web/company/applications.php';
header('Location: ' . $referer);
exit;
