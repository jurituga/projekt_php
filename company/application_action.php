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
    SELECT a.id, a.user_id, a.status, j.title AS job_title
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    JOIN companies c ON c.id = j.company_id
    WHERE a.id = ? AND c.user_id = ?
');
$stmt->execute([$applicationId, currentUserId()]);
$application = $stmt->fetch();
if (!$application) {
    header('Location: /web/company/dashboard.php');
    exit;
}

$newStatus = $action === 'accept' ? APPLICATION_STATUS_ACCEPTED : APPLICATION_STATUS_REJECTED;
$pdo->prepare('UPDATE applications SET status = ?, updated_at = NOW() WHERE id = ?')->execute([$newStatus, $applicationId]);

$statusLabel = $action === 'accept' ? 'accepted' : 'rejected';
createNotification(
    (int) $application['user_id'],
    'Application ' . $statusLabel,
    'Your application for "' . $application['job_title'] . '" was ' . $statusLabel . '.',
    BASE_URL . '/user/applications.php',
    'application'
);

$referer = $_SERVER['HTTP_REFERER'] ?? '/web/company/applications.php';
header('Location: ' . $referer);
exit;
