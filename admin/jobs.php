<?php
require_once __DIR__ . '/../config/init.php';
requireAdmin();

$pdo = getDB();

$stmt = $pdo->query("
    SELECT j.id, j.title, j.status, j.created_at, c.company_name
    FROM jobs j
    JOIN companies c ON c.id = j.company_id
    ORDER BY j.created_at DESC
");
$jobs = $stmt->fetchAll();

$pageTitle = 'Manage Jobs';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Manage Jobs</h1>
    <p><a href="<?= BASE_URL ?>/admin/index.php">&larr; Admin Panel</a></p>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Company</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($jobs as $j): ?>
                <tr>
                    <td><?= (int)$j['id'] ?></td>
                    <td><a href="<?= BASE_URL ?>/job.php?id=<?= (int)$j['id'] ?>"><?= e($j['title']) ?></a></td>
                    <td><?= e($j['company_name']) ?></td>
                    <td><span class="status status-<?= e($j['status']) ?>"><?= e($j['status']) ?></span></td>
                    <td><?= date('M j, Y', strtotime($j['created_at'])) ?></td>
                    <td>
                        <form method="post" action="<?= BASE_URL ?>/admin/job_action.php" style="display:inline" onsubmit="return confirm('Delete this job and all applications?');">
                            <input type="hidden" name="job_id" value="<?= (int)$j['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-small btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
