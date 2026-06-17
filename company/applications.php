<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_COMPANY);

$userId = currentUserId();
$pdo = getDB();

$stmt = $pdo->prepare('SELECT id FROM companies WHERE user_id = ?');
$stmt->execute([$userId]);
$company = $stmt->fetch();
if (!$company) {
    header('Location: /web/company/profile.php');
    exit;
}
$companyId = (int) $company['id'];

$stmt = $pdo->prepare("
    SELECT a.id, a.status, a.cover_letter, a.created_at, a.cv_id, j.title AS job_title, j.id AS job_id, u.name AS applicant_name, u.email AS applicant_email
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    JOIN users u ON u.id = a.user_id
    WHERE j.company_id = ?
    ORDER BY a.created_at DESC
");
$stmt->execute([$companyId]);
$applications = $stmt->fetchAll();

$pageTitle = 'All Applications';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>All Applications</h1>
    <?php if (empty($applications)): ?>
        <p class="muted">No applications yet.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Job</th>
                    <th>Applicant</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>CV</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><a href="<?= BASE_URL ?>/company/job_applications.php?job_id=<?= (int)$app['job_id'] ?>"><?= e($app['job_title']) ?></a></td>
                        <td><?= e($app['applicant_name']) ?></td>
                        <td><?= e($app['applicant_email']) ?></td>
                        <td><span class="status status-<?= e($app['status']) ?>"><?= e($app['status']) ?></span></td>
                        <td><?php if ($app['cv_id']): ?><a href="<?= BASE_URL ?>/download_cv.php?id=<?= (int)$app['cv_id'] ?>">Download</a><?php else: ?>—<?php endif; ?></td>
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
    <p><a href="<?= BASE_URL ?>/company/dashboard.php">&larr; Back to Dashboard</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
