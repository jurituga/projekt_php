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

$stmt = $pdo->prepare('SELECT j.id, j.title, j.location, j.job_type, j.status, j.created_at, (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS app_count FROM jobs j WHERE j.company_id = ? ORDER BY j.created_at DESC');
$stmt->execute([$companyId]);
$jobs = $stmt->fetchAll();

$pageTitle = 'My Jobs';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>My Jobs</h1>
    <p><a href="<?= BASE_URL ?>/company/job_edit.php" class="btn btn-primary">Post a Job</a></p>

    <?php if (empty($jobs)): ?>
        <p class="muted">No jobs yet.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Applications</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $j): ?>
                    <tr>
                        <td><strong><?= e($j['title']) ?></strong></td>
                        <td><?= e($j['location'] ?? '—') ?></td>
                        <td><?= e($j['job_type']) ?></td>
                        <td><span class="status status-<?= e($j['status']) ?>"><?= e($j['status']) ?></span></td>
                        <td><a href="<?= BASE_URL ?>/company/job_applications.php?job_id=<?= (int)$j['id'] ?>"><?= (int)$j['app_count'] ?></a></td>
                        <td><?= date('M j, Y', strtotime($j['created_at'])) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/company/job_edit.php?id=<?= (int)$j['id'] ?>" class="btn btn-small">Edit</a>
                            <form method="post" action="<?= BASE_URL ?>/company/job_delete.php" style="display:inline" onsubmit="return confirm('Delete this job?');">
                                <input type="hidden" name="id" value="<?= (int)$j['id'] ?>">
                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <p><a href="<?= BASE_URL ?>/company/dashboard.php">&larr; Back to Dashboard</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
