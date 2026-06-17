<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_FREELANCER);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/freelancer/dashboard.php');
    exit;
}

$requestId = (int)($_POST['request_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$requestId || !in_array($action, ['accept', 'reject', 'complete'], true)) {
    header('Location: ' . BASE_URL . '/freelancer/dashboard.php');
    exit;
}

$pdo = getDB();

// Determine which statuses we can transition FROM based on action
if ($action === 'accept') {
    $allowedStatuses = [SERVICE_REQUEST_PENDING];
} elseif ($action === 'reject') {
    $allowedStatuses = [SERVICE_REQUEST_PENDING, SERVICE_REQUEST_ACCEPTED];
} elseif ($action === 'complete') {
    $allowedStatuses = [SERVICE_REQUEST_ACCEPTED];
}

$placeholders = implode(',', array_fill(0, count($allowedStatuses), '?'));
$params = [$requestId, currentUserId()];
$params = array_merge($params, $allowedStatuses);

$stmt = $pdo->prepare("
    SELECT sr.id, sr.booking_slot_id, sr.status AS current_status
    FROM service_requests sr
    JOIN services s ON s.id = sr.service_id
    WHERE sr.id = ? AND s.freelancer_id = ? AND sr.status IN ($placeholders)
");
$stmt->execute($params);
$request = $stmt->fetch();

if (!$request) {
    header('Location: ' . BASE_URL . '/freelancer/dashboard.php');
    exit;
}

if ($action === 'accept') {
    $pdo->prepare('UPDATE service_requests SET status = ?, updated_at = NOW() WHERE id = ?')
        ->execute([SERVICE_REQUEST_ACCEPTED, $requestId]);
} elseif ($action === 'complete') {
    $pdo->prepare('UPDATE service_requests SET status = ?, updated_at = NOW() WHERE id = ?')
        ->execute([SERVICE_REQUEST_COMPLETED, $requestId]);
} elseif ($action === 'reject') {
    $reason = trim($_POST['rejection_reason'] ?? '');
    if ($reason === '') {
        $_SESSION['reject_error'] = 'Please provide a reason for the rejection.';
        $_SESSION['reject_request_id'] = $requestId;
        header('Location: ' . BASE_URL . '/freelancer/requests.php');
        exit;
    }

    $pdo->prepare('UPDATE service_requests SET status = ?, rejection_reason = ?, updated_at = NOW() WHERE id = ?')
        ->execute([SERVICE_REQUEST_REJECTED, $reason, $requestId]);

    // Free up the booked slot
    if (!empty($request['booking_slot_id'])) {
        $pdo->prepare('UPDATE service_availability SET is_booked = 0 WHERE id = ?')
            ->execute([$request['booking_slot_id']]);
    }
}

header('Location: ' . BASE_URL . '/freelancer/requests.php');
exit;
