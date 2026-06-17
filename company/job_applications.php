<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_COMPANY);

$userId = currentUserId();
$jobId = (int)($_GET['job_id'] ?? 0);
if (!$jobId) {
    header('Location: /web/company/jobs.php');
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare('SELECT j.id, j.title FROM jobs j JOIN companies c ON c.id = j.company_id WHERE j.id = ? AND c.user_id = ?');
$stmt->execute([$jobId, $userId]);
$job = $stmt->fetch();
if (!$job) {
    header('Location: /web/company/jobs.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT a.id, a.status, a.cover_letter, a.created_at, a.cv_id, u.name AS applicant_name, u.email AS applicant_email
    FROM applications a
    JOIN users u ON u.id = a.user_id
    WHERE a.job_id = ?
    ORDER BY a.created_at DESC
");
$stmt->execute([$jobId]);
$applications = $stmt->fetchAll();

$pageTitle = 'Applications: ' . $job['title'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Applications: <?= e($job['title']) ?></h1>
    <p><a href="<?= BASE_URL ?>/company/jobs.php">&larr; Back to Jobs</a></p>

    <?php if (empty($applications)): ?>
        <p class="muted">No applications for this job yet.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Applicant</th>
                    <th>Email</th>
                    <th>Cover letter</th>
                    <th>CV</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?= e($app['applicant_name']) ?></td>
                        <td><?= e($app['applicant_email']) ?></td>
                        <td><?= e(mb_substr($app['cover_letter'] ?? '', 0, 100)) ?><?= mb_strlen($app['cover_letter'] ?? '') > 100 ? '...' : '' ?></td>
                        <td><?php if ($app['cv_id']): ?><a href="<?= BASE_URL ?>/download_cv.php?id=<?= (int)$app['cv_id'] ?>">Download</a><?php else: ?>—<?php endif; ?></td>
                        <td><span class="status status-<?= e($app['status']) ?>"><?= e($app['status']) ?></span></td>
                        <td><?= date('M j, Y', strtotime($app['created_at'])) ?></td>
                        <td>
                            <?php if ($app['status'] === APPLICATION_STATUS_PENDING || $app['status'] === APPLICATION_STATUS_VIEWED): ?>
                                <form method="post" action="<?= BASE_URL ?>/company/application_action.php" style="display:inline">
                                    <input type="hidden" name="application_id" value="<?= (int)$app['id'] ?>">
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit" class="btn btn-small">Accept</button>
                                </form>
                                <form method="post" action="<?= BASE_URL ?>/company/application_action.php" style="display:inline">
                                    <input type="hidden" name="application_id" value="<?= (int)$app['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-small btn-danger">Reject</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
