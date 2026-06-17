<?php
require_once __DIR__ . '/config/init.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare('SELECT file_path, file_name, user_id FROM cvs WHERE id = ?');
$stmt->execute([$id]);
$cv = $stmt->fetch();

if (!$cv) {
    echo 'CV not found.';
    exit;
}

// Only owner or admin can download
$userId = currentUserId();
$role = currentUserRole();
$allowed = ($userId && (int)$cv['user_id'] === $userId) || $role === ROLE_ADMIN;

// Companies can download CVs of applicants who applied to their jobs
if (!$allowed && $role === ROLE_COMPANY && $userId) {
    $stmt = $pdo->prepare('
        SELECT a.id
        FROM applications a
        JOIN jobs j ON j.id = a.job_id
        JOIN companies comp ON comp.id = j.company_id
        WHERE comp.user_id = ? AND a.cv_id = ?
        LIMIT 1
    ');
    $stmt->execute([$userId, $id]);
    if ($stmt->fetch()) {
        $allowed = true;
    }

    if (!$allowed) {
        $stmt = $pdo->prepare('
            SELECT a.id
            FROM applications a
            JOIN jobs j ON j.id = a.job_id
            JOIN companies comp ON comp.id = j.company_id
            WHERE comp.user_id = ? AND a.user_id = ?
            LIMIT 1
        ');
        $stmt->execute([$userId, (int)$cv['user_id']]);
        if ($stmt->fetch()) {
            $allowed = true;
        }
    }
}

if (!$allowed) {
    echo 'Access denied.';
    exit;
}

$fullPath = UPLOAD_PATH_CV . $cv['file_path'];
if (!file_exists($fullPath)) {
    echo 'File not found on server.';
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($cv['file_name']) . '"');
header('Content-Length: ' . filesize($fullPath));
readfile($fullPath);
exit;
