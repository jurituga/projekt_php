<?php
require_once __DIR__ . '/../config/init.php';
requireLogin(ROLE_USER);

$userId = currentUserId();
$pdo = getDB();

$stmt = $pdo->prepare("
    SELECT a.id, a.status, a.cover_letter, a.created_at, j.title AS job_title, j.id AS job_id, c.company_name
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    JOIN companies c ON c.id = j.company_id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
");
$stmt->execute([$userId]);
$applications = $stmt->fetchAll();

$pageTitle = 'My Applications';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>My Applications</h1>
    <?php if (empty($applications)): ?>
        <p class="muted">You haven't applied to any jobs yet. <a href="<?= BASE_URL ?>/jobs.php">Browse jobs</a>.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Job</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><a href="<?= BASE_URL ?>/job.php?id=<?= (int)$app['job_id'] ?>"><?= e($app['job_title']) ?></a></td>
                        <td><?= e($app['company_name']) ?></td>
                        <td><span class="status status-<?= e($app['status']) ?>"><?= e($app['status']) ?></span></td>
                        <td><?= date('M j, Y', strtotime($app['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <p><a href="<?= BASE_URL ?>/user/dashboard.php">&larr; Back to Dashboard</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
